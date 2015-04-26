<?php  defined('C5_EXECUTE') or die('Access Denied.');
	
	/**
	 * Default view for clov_expense_list blocks.
	 */
	
	Loader::helper('clov_page', 'clov');
	
	$pageList = $controller->getPageList();
	$expenses = $pageList->getPage();
?>

<div class="clov-list clov-expense-list">
	<h3><?php  echo $controller->getTitle(); ?></h3>
	
	<?php  Loader::element('alerts', array('error' => $error), 'clov'); ?>
	
	<?php  if(empty($expenses)): ?>
		<div class="clov-empty"><?php  echo t('No expenses.'); ?></div>
	<?php  else: ?>
		<ul>
			<?php  foreach($expenses as $expense): ?>
				<li>
					<?php  echo ClovPageHelper::getPageAnchor($expense); ?>
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
