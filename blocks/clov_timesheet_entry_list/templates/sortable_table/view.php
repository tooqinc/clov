<?php  defined('C5_EXECUTE') or die('Access Denied.');
	
	/**
	 * Default view for clov_timesheet_entry_list blocks.
	 */
	
	$pageList = $controller->getPageList();
	
	$columns = array(
		CollectionAttributeKey::getByHandle('clov_timesheet_entry_code'),
	);
	// Only show the project if the list isn't limited to a single project.
	if(!$controller->getProject()) {
		$columns[] = CollectionAttributeKey::getByHandle('clov_timesheet_entry_project');
	}
	$columns[] = CollectionAttributeKey::getByHandle('clov_timesheet_entry_employee');
	$columns[] = CollectionAttributeKey::getByHandle('clov_timesheet_entry_hours');
?>

<div class="clov-list clov-timesheet-entry-list">
	<h3><?php  echo $controller->getTitle(); ?></h3>
	
	<?php  Loader::element('alerts', array('error' => $error), 'clov'); ?>
	
	<?php  if($pageList->getTotal() == 0): ?>
		<div class="clov-empty"><?php  echo t('No timesheet entries.'); ?></div>
	<?php  else: ?>
		<?php 
			Loader::element('sortable_page_table', array(
				'itemList' => $pageList,
				'columns' => $columns,
			), 'clov');
			
			if($pageList->requiresPaging()) {
				$pageList->displaySummary();
				$pageList->displayPaging();
			}
		?>
	<?php  endif; ?>
</div>
