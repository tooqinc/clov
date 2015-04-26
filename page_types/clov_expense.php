<?php  defined('C5_EXECUTE') or die('Access Denied.'); ?>
<div class="clov">
	<div class="clov-expense">
		<h2><?php  echo $c->getCollectionName(); ?></h2>
		<p>
			<?php  echo $c->getCollectionDescription(); ?>
		</p>
		<?php  Loader::element('attribute', array('handle' => 'clov_expense_date'), 'clov'); ?>
		<?php  Loader::element('attribute', array('handle' => 'clov_expense_amount'), 'clov'); ?>
		<?php  Loader::element('attribute', array('handle' => 'clov_expense_location'), 'clov'); ?>
		<?php  Loader::element('attribute', array('handle' => 'clov_expense_payer'), 'clov'); ?>
		<?php  Loader::element('attribute', array('handle' => 'clov_expense_project'), 'clov'); ?>
	</div>
	
	<ul class="clov-action-list">
		<li><a class="clov-action-link clov-edit-link" href="<?php  echo $this->url('clov/expenses/-/edit/'.$c->getCollectionID()); ?>"><?php  echo t('Edit This Expense'); ?></a></li>
		<li><a class="clov-action-link clov-add-link" href="<?php  echo $this->url('clov/expenses/-/add'); ?>"><?php  echo t('Add A New Expense'); ?></a></li>
	</ul>
	
	<?php  
		$mainArea = new Area('Main');
		$mainArea->display($c);
	?>
</div>
