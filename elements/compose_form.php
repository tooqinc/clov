<?php  defined('C5_EXECUTE') or die('Access Denied.');
	
	/**
	 * Generates a form which can be used to add/edit pages. Based off of 
	 * concrete/single_pages/dashboard/composer/write.php.
	 */
		
	// Optional arguments:
	$showNameField = isset($showNameField) ? $showNameField : true;
	$showDescriptionField = isset($showDescriptionField) ? $showDescriptionField : true;
	$showSaveDraft = isset($showSaveDraft) ? $showSaveDraft : false;
	
	// Required arguments:
	if(!isset($entry)) {
		throw new Exception('An $entry argument is required.');
	}
	
	// Reload the entry to make sure permissions, attributes, etc are up to 
	// date.
	$entry = ComposerPage::getById($entry->getCollectionID());
	
	$entryPermissions = new Permissions($entry);
	
	// Get a permission access list item to help determine what is permissible 
	// (sadly there is no can* method to check name/description permissions).
	$editPropertiesKey = PagePermissionKey::getByHandle('edit_page_properties');
	$editPropertiesKey->setPermissionObject($entry);
	$editPropertiesAccessListItem = $editPropertiesKey->getMyAssignment();
	
	$id = $entry->getCollectionID();
	$name = $entry->getCollectionName();
	$description = $entry->getCollectionDescription();
	
	$entryType = CollectionType::getByID($entry->getCollectionTypeID());
	
	// This will contain attribute keys and possibly blocks.
	$composerContentItems = $entryType->getComposerContentItems();
	
	Loader::helper('clov_page', 'clov');
	Loader::helper('clov_url', 'clov');
	$formHelper = Loader::helper('form');
?>

<?php 
	// Provide a place to display errors/info.
	Loader::element('alerts', array('error' => $error, 'info' => $info, 'success' => $success), 'clov');
?>

<form class="clov-compose-form" method="post" enctype="multipart/form-data" action="<?php  echo $this->action('save', $id); ?>">
	<?php 
		// Include a token to prevent CSRF.
		echo Loader::helper('validation/token')->output('save');
	?>
	
	<?php  if($showNameField && $editPropertiesAccessListItem->allowEditName()): ?>
		<div class="clov-control-group">
			<?php  echo $formHelper->label('cName', t('Name')); ?>
			<div class="clov-controls">
				<?php  echo $formHelper->text('cName', Loader::helper('text')->entities($name), array('required' => 'required')); ?>
			</div>
		</div>
	<?php  endif; ?>
	
	<?php  if($showDescriptionField && $editPropertiesAccessListItem->allowEditDescription()): ?>
		<div class="clov-control-group">
			<?php  echo $formHelper->label('cDescription', t('Description')); ?>
			<div class="clov-controls">
				<?php  echo $formHelper->textarea('cDescription', Loader::helper('text')->entities($description), array('rows' => 5)); ?>
			</div>
		</div>
	<?php  endif; ?>
	
	<?php  foreach($composerContentItems as $contentItem): ?>
		<?php  if($contentItem instanceof AttributeKey): ?>
			<?php  if($entryPermissions->canEditPageProperties($contentItem)): ?>
				<?php 
					// If the user is allowed to edit this attribute, show a 
					// normal attribute input.
				?>
				<div class="clov-control-group">
					<?php  $contentItem->render('label'); ?>
					<div class="clov-controls">
						<?php  $contentItem->render('composer', $entry->getAttributeValueObject($contentItem)); ?>
					</div>
				</div>
			<?php  elseif(is_object($entry->getAttributeValueObject($contentItem))): ?>
				<?php 
					// If the user is not allowed to edit this attribute but it 
					// is not empty, show what value will be used.
				?>
				<div class="clov-control-group">
					<?php  $contentItem->render('label'); ?>
					<div class="clov-controls">
						<?php  echo ClovPageHelper::renderAttributeValue($contentItem, $entry, true); ?>
					</div>
				</div>
			<?php  endif; ?>
		<?php  /* TODO: If I want to be able to edit blocks, use something like this:
		<?php  else: ?>
			<div class="clov-control-group">
				<?php 
					$block = $entry->getComposerBlockInstance($contentItem); 
					if(is_object($block)) {
						$blockView = new BlockView();
						$blockView->render($block, 'composer');
					} else {
						echo t('Block not found. Unable to edit in composer.');
					}
				?>
			</div>
		*/ ?>
		<?php  endif; ?>
	<?php  endforeach; ?>
	
	<?php 
		if($showSaveDraft) {
			echo '<button name="publish" type="submit">'.t('Submit For Review').'</button>';
			echo '<button class="clov-save-draft" name="save" type="submit">'.t('Save Draft').'</button>';
		} else {
			echo '<button name="publish" type="submit">'.t('Submit').'</button>';
		}
		
		// Cancel will either bring the user back to the referrer page or if 
		// that cannot be determined to the parent page.
		$cancelRedirectPage = ClovUrlHelper::loadReferrerPage() ?: Page::getByID($entry->getComposerDraftPublishParentID());
		if($cancelRedirectPage->isError()) {
			$cancelRedirectPage = Page::getByPath('/clov');
		}
		$cancelHref = ClovUrlHelper::absolutize(ClovUrlHelper::getCollectionRoute($cancelRedirectPage));
		echo '<a href="'.$cancelHref.'" class="clov-cancel-link">'.t('Cancel').'</a>';
	?>
</form>
