<?php  defined('C5_EXECUTE') or die('Access Denied.');
	
	/**
	 * Default view for clov_task_list blocks.
	 */
	
	Loader::helper('clov_page', 'clov');
	
	$pageList = $controller->getPageList();
	$tasks = $pageList->getPage();
?>

<div class="clov-list clov-task-list">
	<h3><?php  echo $controller->getTitle(); ?></h3>
	
	<?php  Loader::element('alerts', array('error' => $error), 'clov'); ?>
	
	<?php  if(empty($tasks)): ?>
		<div class="clov-empty"><?php  echo t('No tasks.'); ?></div>
	<?php  else: ?>
		<ul>
			<?php  foreach($tasks as $task): ?>
				<li>
					<?php  echo ClovPageHelper::getPageAnchor($task); ?>
				</li>
			<?php  endforeach; ?>
		</ul>
		<?php 
			if($pageList->requiresPaging()) {
				$pageList->displaySummary();
				$pageList->displayPaging();
			}
		?>
	<?php  endif; ?>
</div>
