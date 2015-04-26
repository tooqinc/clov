<?php  defined('C5_EXECUTE') or die('Access Denied.'); ?>
<div class="clov">
	<div class="clov-project">
		<h2><?php  echo $c->getCollectionName(); ?></h2>
		<p>
			<?php  echo $c->getCollectionDescription(); ?>
		</p>
		<div class="clov-attribute-group">
			<?php  Loader::element('attribute', array('handle' => 'clov_project_reference_number'), 'clov'); ?>
			<?php  Loader::element('attribute', array('handle' => 'clov_project_start_date'), 'clov'); ?>
			<?php  Loader::element('attribute', array('handle' => 'clov_project_expected_hours'), 'clov'); ?>
			<?php  Loader::element('attribute', array('handle' => 'clov_project_managers'), 'clov'); ?>
			<?php  Loader::element('attribute', array('handle' => 'clov_project_assignees'), 'clov'); ?>
			<div class="clov-pseudo-attribute">
				<strong class="clov-pseudo-attribute-name">
					<?php  echo t('Total Budget'); ?>
				</strong>
				<span class="clov-pseudo-attribute-value">
					<?php 
						echo $this->controller->getTotalBudgetDisplayValue();
					?>
				</span>
			</div>
			<div class="clov-pseudo-attribute">
				<strong class="clov-pseudo-attribute-name">
					<?php  echo t('Total Invoiced Amount'); ?>
				</strong>
				<span class="clov-pseudo-attribute-value">
					<?php 
						echo $this->controller->getTotalInvoicedAmountDisplayValue();
					?>
				</span>
			</div>
		</div>
		<div class="clov-attribute-group">
			<?php 
				// Show all budget attributes. Doing it dynamically like this 
				// allows the addition of more budget attributes later.
				$budgets = $this->controller->getBudgetAttributeKeys();
				foreach($budgets as $budget) {
					Loader::element('attribute', array('handle' => $budget->getAttributeKeyHandle()), 'clov');
				}
			?>
		</div>
	</div>
	
	<ul class="clov-action-list">
		<li><a class="clov-action-link clov-edit-link" href="<?php  echo $this->url('clov/projects/-/edit/'.$c->getCollectionID()); ?>"><?php  echo t('Edit This Project'); ?></a></li>
		<li><a class="clov-action-link clov-add-link" href="<?php  echo $this->url('clov/projects/-/add'); ?>"><?php  echo t('Add A New Project'); ?></a></li>
	</ul>
	
	<?php  
		$mainArea = new Area('Main');
		$mainArea->display($c);
	?>
</div>
