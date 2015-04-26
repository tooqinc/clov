<?php  defined('C5_EXECUTE') or die('Access Denied.');
	
	/**
	 * Default view for clov_task_list blocks.
	 */
	
	$pageList = $controller->getPageList();
	
	$columns = array(
		array(
			'column' => 'cvName',
			'name' => t('Name'),
			'value' => function($page) {
				Loader::helper('clov_page', 'clov');
				return ClovPageHelper::getPageAnchor($page);
			},
		),
	);
	// Only show the assignee if the list isn't limited to a single assignee.
	if(!$controller->getAssignee()) {
		$columns[] = CollectionAttributeKey::getByHandle('clov_task_assignee');
	}
?>

<div class="clov-list clov-task-list">
	<h3><?php  echo $controller->getTitle(); ?></h3>
	
	<?php  Loader::element('alerts', array('error' => $error), 'clov'); ?>
	
	<?php  if($pageList->getTotal() == 0): ?>
		<div class="clov-empty"><?php  echo t('No tasks.'); ?></div>
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
