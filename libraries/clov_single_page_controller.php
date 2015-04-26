<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Some extra controller methods based around the assumption that single pages 
 * are used to manage page types.
 */
abstract class ClovSinglePageController extends Controller {
	/**
	 * Add a message into the the set error var. Create a validation helper to 
	 * hold the message if one doesn't already exist.
	 */
	protected function addError($message) {
		$error = $this->getvar('error');
		if(!isset($error)) {
			$this->set('error', Loader::helper('validation/error'));
		}
		$this->getvar('error')->add($message);
	}
	
	/**
	 * Add a message into the the set info var. Create an array to hold the 
	 * message if one doesn't already exist.
	 */
	protected function addInfo($message) {
		$info = $this->getvar('info');
		if(!isset($info)) {
			$this->set('info', array());
		}
		array_push($this->getvar('info'), $message);
	}
	
	/**
	 * Add a message into the the set success var. Create an array to hold the 
	 * message if one doesn't already exist.
	 */
	protected function addSuccess($message) {
		$success = $this->getvar('success');
		if(!isset($success)) {
			$this->set('success', array());
		}
		array_push($this->getvar('success'), $message);
	}
	
	/**
	 * Assuming $this->getvar('error') is a ValidationErrorHelper, checks if 
	 * it has any errors.
	 * 
	 * @return boolean
	 */
	protected function hasErrors() {
		$validationErrorHelper = $this->getvar('error');
		return isset($validationErrorHelper) && $validationErrorHelper->has();
	}
	
	/**
	 * Create and return a draft page to use in add forms. Do some basic 
	 * permissions handling too.
	 * 
	 * @return Page
	 */
	protected function getEntryToAdd($pageTypeHandle) {
		$entry = ComposerPage::createDraft(CollectionType::getByHandle($pageTypeHandle));
		
		if(empty($entry)) {
			$view = View::getInstance();
			$view->render('/page_forbidden');
		} else {
			return $entry;
		}
	}
	
	/**
	 * Load a page to use in edit forms. Do some basic permissions/error 
	 * handling too.
	 * 
	 * @return Page
	 */
	protected function getEntryToEdit($cID) {
		$page = Page::getByID($cID, 'RECENT', 'ComposerPage');
		
		if($page->getError() === COLLECTION_NOT_FOUND) {
			$view = View::getInstance();
			$view->render('/page_not_found');
		} else {
			$entry = ComposerPage::getByID($cID);
			if(empty($entry)) {
				$view = View::getInstance();
				$view->render('/page_forbidden');
			} else {
				return $entry;
			}
		}
	}
	
	/**
	 * Load a page for saving. Currently defers to getEntryToEdit().
	 * 
	 * @return Page
	 */
	protected function getEntryToSave($cID) {
		return $this->getEntryToEdit($cID);
	}
	
	/**
	 * Validate & save an entry. If there are errors they will be stored in a 
	 * ValidationErrorHelper in $this->getvar('error');
	 * 
	 * @return boolean success
	 */
	protected function saveEntry(&$entry) {
		Loader::helper('clov_cache', 'clov');
		if(!$this->isPost()) {
			$this->addError(t('Invalid method for saving.'));
			return false;
		} else {
			$entryType = CollectionType::getByID($entry->getCollectionTypeID());
			$entryTypeString = strtolower($entryType->getCollectionTypeName());
			
			// Make sure the token is legit to prevent CSRF.
			// There should be a "save" token in the form.
			$validationTokenHelper = Loader::helper('validation/token');
			if(!$validationTokenHelper->validate('save')) {
				$this->addError($validationTokenHelper->getErrorMessage());
			}
			
			if($entry->isComposerDraft()) {
				// Figure out where to publish $entry and make sure that its 
				// location is allowable.
				// This is more flexible than Clov actually requires (since 
				// Clov uses fixed publish locations), but it doesn't hurt to 
				// have it.
				// Skip the cache because otherwise sometimes this page can 
				// end up being used to validate permissions for $entry for 
				// some bizarre reason.
				// TODO: Figure out why this happens. It seems like the wrong 
				// page object somehow ends up in 
				// $entry->permissionAssignments[$x]->permissionObject. This is 
				// similar to the bug worked around in the project attribute 
				// controller and probably has a similar root cause. It was 
				// noticed at the same time too: on timesheet entry code 
				// editing/setting. This bug was noticed in Concrete5 5.6.0.2.
				$parent = ClovCacheHelper::ignoreCache(function() use ($entry, $entryType) {
					$publishMethod = $entryType->getCollectionTypeComposerPublishMethod();
					if($publishMethod == 'CHOOSE' || $publishMethod == 'PAGE_TYPE') { 
						return Page::getByID($entry->getComposerDraftPublishParentID());
					} else if($publishMethod == 'PARENT') {
						return Page::getByID($entryType->getCollectionTypeComposerPublishPageParentID());
					}
				});
				
				if(!is_object($parent) || ($parent->isInTrash() || $parent->isError())) {
					$this->addError(t('Invalid parent page.'));
				} else {
					$parentPermissions = new Permissions($parent);
					if(!$parentPermissions->canAddSubCollection($entryType)) {
						$this->addError(t('You do not have permissions to add a %s in that location.', $entryTypeString));
					}
				}
			}
			
			if($this->hasErrors()) {
				// Invalid!
				return false;
			} else {
				// Valid!
				
				// Create a new version of the entry.
				// Disable the cache to make sure ComposerPage::getByID always 
				// gets a fresh $entry. This is needed because 
				// getVersionToModify() set a cache key for page_recent (if it 
				// created a new version), whereas ComposerPage::getByID() 
				// tries to load from composerpage_recent (which may have 
				// previously been set to the old version when $entry was first 
				// loaded [via ComposerPage::getByID]). Also in 5.6.1 not 
				// disabling the cache here causes issues with blocks not being 
				// properly cloned to the new version.
				$entry = ClovCacheHelper::ignoreCache(function() use ($entry) {
					$entry->getVersionToModify();
					// Have to use ComposerPage::getByID() again because 
					// getVersionToModify() returns a Page object (and we need 
					// a ComposerPage object).
					return ComposerPage::getByID($entry->getCollectionID());
				});
				
				// Apply the updates.
				$this->saveBasicProperties($entry);
				$this->saveComposerAttributeKeys($entry);
				// TODO? $this->saveComposerBlocks($entry);
				
				// Check if there were any errors during the save.
				if($this->hasErrors()) {
					return false;
				} else {
					$newVersion = CollectionVersion::get($entry, 'RECENT');
					
					// Immediately approve the new verion if the user is allowed.
					$entryPermissions = new Permissions($entry);
					if($entryPermissions->canApprovePageVersions()) {
						$newVersion->approve();
					} else {
						// approve() will automatically reindex, but we want to 
						// make sure unapproved pages are in the index as well 
						// (this is required in order to filter unapproved 
						// pages in page lists by attribute key).
						$entry->reindex();
					}
					
					// The form can be submitted without a publish command to 
					// keep the entry in draft form.
					if($entry->isComposerDraft()) {
						if($this->post('publish') !== null) { 
							$entry->move($parent);
							$entry->markComposerPageAsPublished();
							$this->addSuccess(t('Saved.'));
						} else {
							$this->addSuccess(t('Saved draft.'));
						}
					} else {
						$this->addSuccess(t('Saved.'));
					}
					
					// Reload it once more so that the by-reference parameter 
					// is up-to-date.
					$entry = ComposerPage::getByID($entry->getCollectionID());
					
					return true;
				}
			}
		}
	}
	
	/**
	 * Save non-attribute properties.
	 */
	protected function saveBasicProperties($entry) {
		// Get a permission access list item to help determine what 
		// is permissible (sadly there is no can* method to check 
		// name/description permissions).
		$editPropertiesKey = PagePermissionKey::getByHandle('edit_page_properties');
		$editPropertiesKey->setPermissionObject($entry);
		$editPropertiesAccessListItem = $editPropertiesKey->getMyAssignment();
		
		// We only let the user set the name and description via the frontend.
		$updates = array();
		$cName = $this->post('cName');
		if(isset($cName)) {
			if($editPropertiesAccessListItem->allowEditName()) {
				$updates['cName'] = $cName;
			} else {
				$this->addError(t('You are not allowed to edit the page name.'));
			}
		}
		$cDescription = $this->post('cDescription');
		if(isset($cDescription)) {
			if($editPropertiesAccessListItem->allowEditDescription()) {
				$updates['cDescription'] = $cDescription;
			} else {
				$this->addError(t('You are not allowed to edit the page description.'));
			}
		}
		
		$entry->update($updates);
	}
	
	/**
	 * Save attribute keys that are available in the composer.
	 */
	protected function saveComposerAttributeKeys($entry) {
		$entryPermissions = new Permissions($entry);
		$entryType = CollectionType::getByID($entry->getCollectionTypeID());
		$attributeKeys = $entryType->getComposerAttributeKeys();
		foreach($attributeKeys as $attributeKey) {
			if($entryPermissions->canEditPageProperties($attributeKey)) {
				$attributeKey->saveAttributeForm($entry);
			}/* else {
				// FIXME: This also needs to check if the value has changed 
				// (otherwise it's yelling at people for no reason).
				// Actually this should also check if the form field was 
				// present at all, but this is impossible in some cases 
				// (e.g. unchecked boolean attributes).
				$this->addError(t('You are not allowed to edit the %s property.', strtolower($attributeKey->getAttributeKeyName())));
			}*/
		}
	}
	
	/**
	 * Save blocks that are available in the composer.
	 */
	/* TODO: Uncomment if I want to be able to edit blocks. Also see related 
	         code in the composer_form element.
	protected function saveComposerBlocks($entry) {
		$blocks = $entry->getComposerBlocks();
		foreach($blocks as $block) {
			if($block->hasComposerBlockTemplate()) {
				// Check this because if the block doesn't have a composer 
				// block template then we don't want to try and auto-save it.
				$req = $block->getController()->post();
				$block2 = Block::getByID($block->getBlockID(), $entry, $block->getAreaHandle());
				$block2->update($req);
			}
		}
	}
	*/
}
