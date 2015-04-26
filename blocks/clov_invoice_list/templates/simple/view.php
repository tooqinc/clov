<?php  defined('C5_EXECUTE') or die('Access Denied.');
	
	/**
	 * Default view for clov_invoice_list blocks.
	 */
	
	Loader::helper('clov_page', 'clov');
	
	$pageList = $controller->getPageList();
	$invoices = $pageList->getPage();
?>

<div class="clov-list clov-invoice-list">
	<h3><?php  echo $controller->getTitle(); ?></h3>
	
	<?php  Loader::element('alerts', array('error' => $error), 'clov'); ?>
	
	<?php  if(empty($invoices)): ?>
		<div class="clov-empty"><?php  echo t('No invoices.'); ?></div>
	<?php  else: ?>
		<ul>
			<?php  foreach($invoices as $invoice): ?>
				<li>
					<?php  echo ClovPageHelper::getPageAnchor($invoice); ?>
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
