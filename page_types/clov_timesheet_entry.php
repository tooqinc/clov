<?php  defined('C5_EXECUTE') or die('Access Denied.'); ?>
<div class="clov">
	<div class="clov-timesheet-entry">
		<h2><?php  echo $c->getCollectionName(); ?></h2>
		<p>
			<?php  echo $c->getCollectionDescription(); ?>
		</p>
		<?php  Loader::element('attribute', array('handle' => 'clov_timesheet_entry_start'), 'clov'); ?>
		<?php  Loader::element('attribute', array('handle' => 'clov_timesheet_entry_hours'), 'clov'); ?>
		<?php  Loader::element('attribute', array('handle' => 'clov_timesheet_entry_employee'), 'clov'); ?>
		<?php  Loader::element('attribute', array('handle' => 'clov_timesheet_entry_project'), 'clov'); ?>
		<?php  Loader::element('attribute', array('handle' => 'clov_timesheet_entry_code'), 'clov'); ?>
	</div>
	
	<ul class="clov-action-list">
		<li><a class="clov-action-link clov-edit-link" href="<?php  echo $this->url('clov/timesheets/-/edit/'.$c->getCollectionID()); ?>"><?php  echo t('Edit This Timesheet Entry'); ?></a></li>
		<li><a class="clov-action-link clov-add-link" href="<?php  echo $this->url('clov/timesheets/-/add'); ?>"><?php  echo t('Add A New Timesheet Entry'); ?></a></li>
	</ul>
	
	<?php 
		$mainArea = new Area('Main');
		$mainArea->display($c);
	?>
</div>
