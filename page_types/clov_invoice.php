<?php  defined('C5_EXECUTE') or die('Access Denied.'); ?>
<div class="clov">
	<div class="clov-invoice">
		<h2><?php  echo $c->getCollectionName(); ?></h2>
		<p>
			<?php  echo $c->getCollectionDescription(); ?>
		</p>
		<?php  Loader::element('attribute', array('handle' => 'clov_invoice_reference_number'), 'clov'); ?>
		<?php  Loader::element('attribute', array('handle' => 'clov_invoice_amount'), 'clov'); ?>
		<?php  Loader::element('attribute', array('handle' => 'clov_invoice_date'), 'clov'); ?>
		<?php  Loader::element('attribute', array('handle' => 'clov_invoice_project'), 'clov'); ?>
	</div>
	
	<ul class="clov-action-list">
		<li><a class="clov-action-link clov-edit-link" href="<?php  echo $this->url('clov/invoices/-/edit/'.$c->getCollectionID()); ?>"><?php  echo t('Edit This Invoice'); ?></a></li>
		<li><a class="clov-action-link clov-add-link" href="<?php  echo $this->url('clov/invoices/-/add'); ?>"><?php  echo t('Add A New Invoice'); ?></a></li>
	</ul>
	
	<?php  
		$mainArea = new Area('Main');
		$mainArea->display($c);
	?>
</div>
