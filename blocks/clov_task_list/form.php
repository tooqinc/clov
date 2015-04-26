<?php  defined('C5_EXECUTE') or die('Access Denied.');
	
	/**
	 * The form used for adding/editing clov_task_list blocks.
	 */
	
	Loader::helper('clov_form', 'clov');
	$formHelper = Loader::helper('form');
?>

<div class="ccm-block-field-group">
	<h2><?php  echo t('Assignee'); ?></h2>
	<?php  echo t('Show tasks assigned to'); ?>
	<?php  echo $formHelper->select('uID', $controller->getUIDOptions(), ClovFormHelper::normalizeValue($uID)); ?>
</div>

<div class="ccm-block-field-group">
	<h2><?php  echo t('Approval'); ?></h2>
	<?php  echo t('Show'); ?>
	<?php  echo $formHelper->select('approvedPages', $controller->getApprovedPagesOptions(), ClovFormHelper::normalizeValue($approvedPages)); ?>
	<?php  echo t('tasks.'); ?>
</div>

<div class="ccm-block-field-group">
	<h2><?php  echo t('Drafts'); ?></h2>
	<?php  echo t('Show'); ?>
	<?php  echo $formHelper->select('activePages', $controller->getActivePagesOptions(), ClovFormHelper::normalizeValue($activePages)); ?>
	<?php  echo t('tasks.'); ?>
</div>

<div class="ccm-block-field-group">
	<h2><?php  echo t('Number'); ?></h2>
	<?php  echo t('Show'); ?>
	<input name="num" value="<?php  echo $num; ?>" type="number" step="1" min="0" style="width: 3em" />
	<?php  echo t('tasks at a time (use 0 for unlimited).'); ?>
</div>

<div class="ccm-block-field-group">
	<h2><?php  echo t('Sorting'); ?></h2>
	<?php  echo t('Sort by'); ?>
	<?php  echo $formHelper->select('sortBy', $controller->getSortByOptions(), ClovFormHelper::normalizeValue($sortBy)); ?>
	<?php  echo $formHelper->select('sortByDirection', $controller->getSortByDirectionOptions(), ClovFormHelper::normalizeValue($sortByDirection)); ?>
</div>
