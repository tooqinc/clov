<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Controller for the timesheets single page.
 */
Loader::library('clov_single_page_controller', 'clov');
class ClovTimesheetsController extends ClovSinglePageController {
	/**
	 * Set up page defaults.
	 */
	public function initializeDefaults() {
		$page = $this->getCollectionObject();
		Loader::helper('clov_page', 'clov');
		
		// Show non-draft timesheet entries and draft timesheet entries owned 
		// by the user.
		ClovPageHelper::addBlockByHandle($page, 'clov_timesheet_entry_list');
		ClovPageHelper::addBlockByHandle($page, 'clov_timesheet_entry_list', array(
			'uID' => '0',
			'activePages' => '0',
		));
		ClovPageHelper::addBlockByHandle($page, 'clov_relative_link', array(
			'action' => 'clov/timesheets/-/add',
			'text' => t('Add A New Timesheet Entry'),
			'class' => 'clov-action-link clov-add-link',
		));
		
		$this->initializePermissions();
	}
	
	/**
	 * Set up default permissions.
	 */
	public function initializePermissions() {
		Loader::helper('clov_permissions', 'clov');
		$page = $this->getCollectionObject();
		
		ClovPermissionsHelper::setBaselinePermissions($page);
		
		// Project managers and employees can add new timesheet entries.
		$clovGroups = Loader::package('clov')->getGroups();
		$page->assignPermissions($clovGroups[ClovPackage::PROJECT_MANAGERS], array('add_subpage'));
		$page->assignPermissions($clovGroups[ClovPackage::EMPLOYEES], array('add_subpage'));
		
		// Only allow clov_timesheet_entry pages under this one.
		ClovPermissionsHelper::restrictSubpageType($page, CollectionType::getByHandle('clov_timesheet_entry'));
	}
	
	/**
	 * Action to allow a user to add a new timesheet entry.
	 */
	public function add() {
		$entry = $this->getEntryToAdd('clov_timesheet_entry');
		
		// Default to the logged-in user.
		$loggedInUser = new User;
		$entry->setAttribute('clov_timesheet_entry_employee', $loggedInUser->getUserID());
		
		// If the user got here from a project page, pre-fill the project 
		// attribute.
		Loader::helper('clov_url', 'clov');
		if($project = ClovUrlHelper::loadReferrerPage('clov_project')) {
			$entry->setAttribute('clov_timesheet_entry_project', $project->getCollectionID());
		}
		
		$this->set('entry', $entry);
		$this->set('showNameField', false);
		$this->set('showSaveDraft', true);
		$this->render('clov/default/add');
	}
	
	/**
	 * Action to allow a user to edit an existing timesheet entry.
	 */
	public function edit($cID) {
		$entry = $this->getEntryToEdit($cID);
		$this->set('entry', $entry);
		$this->set('showNameField', false);
		$this->set('showSaveDraft', true);
		$this->render('clov/default/edit');
	}
	
	/**
	 * Save a timesheet entry. This is the action that both the add & edit 
	 * forms submit to.
	 */
	public function save($cID) {
		$entry = $this->getEntryToSave($cID);
		if($this->saveEntry($entry)) {
			Loader::helper('clov_url', 'clov');
			
			// Redirect to the associated project or the generic list page. 
			// Drafts always go to the generic list page because drafts are 
			// not shown on the project page.
			if(!$entry->isComposerDraft() && $projectID = $entry->getAttribute('clov_timesheet_entry_project')) {
				$project = Page::getByID($projectID);
				$this->redirect(ClovUrlHelper::getCollectionRoute($project));
			} else {
				$this->redirect('clov/timesheets');
			}
		} else {
			// Something went wrong. Show the edit view where errors can be 
			// displayed.
			$this->edit($entry->getCollectionID());
		}
	}
}
