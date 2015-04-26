<?php  defined('C5_EXECUTE') or die('Access Denied.'); ?>
<div class="clov">
	<div class="clov-task">
		<h2><?php  echo $c->getCollectionName(); ?></h2>
		<p>
			<?php  echo $c->getCollectionDescription(); ?>
		</p>
		<?php  Loader::element('attribute', array('handle' => 'clov_task_assignee'), 'clov'); ?>
		<?php  Loader::element('attribute', array('handle' => 'clov_task_completed'), 'clov'); ?>
	</div>
	
	<ul class="clov-action-list">
		<?php  // Allow marking uncompleted tasks as completed. ?>
		<?php  if(!$c->getAttribute('clov_task_completed')): ?>
			<li>
				<form method="post" action="<?php  echo $this->action('complete'); ?>">
					<button class="clov-complete-task" name="complete" type="submit"><?php  echo t('Complete This Task'); ?></button>
				</form>
			</li>
		<?php  endif; ?>
		<li><a class="clov-action-link clov-edit-link" href="<?php  echo $this->url('clov/tasks/-/edit/'.$c->getCollectionID()); ?>"><?php  echo t('Edit This Task'); ?></a></li>
		<li><a class="clov-action-link clov-add-link" href="<?php  echo $this->url('clov/tasks/-/add'); ?>"><?php  echo t('Add A New Task'); ?></a></li>
	</ul>
	
	<?php  
		$mainArea = new Area('Main');
		$mainArea->display($c);
	?>
</div>
