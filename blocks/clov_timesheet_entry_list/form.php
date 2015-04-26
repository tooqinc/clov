<?php  defined('C5_EXECUTE') or die('Access Denied.');
	
	/**
	 * The form used for adding/editing clov_timesheet_entry_list blocks.
	 */
	
	Loader::helper('clov_form', 'clov');
	$formHelper = Loader::helper('form');
	$projectIDOptions = $controller->getProjectIDOptions();
	
	$currentPageIsProject = $controller->getCollectionObject()->getCollectionTypeHandle() == 'clov_project';
	if(!$currentPageIsProject) {
		// The "current project" option only makes sense when adding this block 
		// to a project page.
		unset($projectIDOptions[0]);
	}
?>

<div class="ccm-block-field-group">
	<h2><?php  echo t('Employee'); ?></h2>
	<?php  echo t('Show timesheet entries for'); ?>
	<?php  echo $formHelper->select('uID', $controller->getUIDOptions(), ClovFormHelper::normalizeValue($uID)); ?>
</div>

<div class="ccm-block-field-group">
	<h2><?php  echo t('Project'); ?></h2>
	<?php  echo t('Show timesheet entries for'); ?>
	<?php  echo $formHelper->select('projectID', $projectIDOptions, ClovFormHelper::normalizeValue($projectID)); ?>
	<?php  if($currentPageIsProject): ?>
		<p>
			<small><?php  echo t('Selecting "%s" will show the timesheet entries for the current page (only works if this block is on a project page).', $projectIDOptions[0]); ?></small>
		</p>
	<?php  endif; ?>
</div>

<div class="ccm-block-field-group">
	<h2><?php  echo t('Approval'); ?></h2>
	<?php  echo t('Show'); ?>
	<?php  echo $formHelper->select('approvedPages', $controller->getApprovedPagesOptions(), ClovFormHelper::normalizeValue($approvedPages)); ?>
	<?php  echo t('timesheet entries.'); ?>
</div>

<div class="ccm-block-field-group">
	<h2><?php  echo t('Drafts'); ?></h2>
	<?php  echo t('Show'); ?>
	<?php  echo $formHelper->select('activePages', $controller->getActivePagesOptions(), ClovFormHelper::normalizeValue($activePages)); ?>
	<?php  echo t('timesheet entries.'); ?>
</div>

<div class="ccm-block-field-group">
	<h2><?php  echo t('Number'); ?></h2>
	<?php  echo t('Show'); ?>
	<input name="num" value="<?php  echo $num; ?>" type="number" step="1" min="0" style="width: 3em" />
	<?php  echo t('timesheet entries at a time (use 0 for unlimited).'); ?>
</div>

<div class="ccm-block-field-group">
	<h2><?php  echo t('Sorting'); ?></h2>
	<?php  echo t('Sort by'); ?>
	<?php  echo $formHelper->select('sortBy', $controller->getSortByOptions(), $sortBy); ?>
	<?php  echo $formHelper->select('sortByDirection', $controller->getSortByDirectionOptions(), ClovFormHelper::normalizeValue($sortByDirection)); ?>
</div>
