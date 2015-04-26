<?php  defined('C5_EXECUTE') or die('Access Denied.');
	
	/**
	 * Default view for clov_project_list blocks.
	 */
	
	Loader::helper('clov_page', 'clov');
	
	$pageList = $controller->getPageList();
	$projects = $pageList->getPage();
?>

<div class="clov-list clov-project-list">
	<h3><?php  echo $controller->getTitle(); ?></h3>
	
	<?php  Loader::element('alerts', array('error' => $error, 'info' => $info, 'success' => $success), 'clov'); ?>
	
	<?php  if(empty($projects)): ?>
		<div class="clov-empty"><?php  echo t('No projects.'); ?></div>
	<?php  else: ?>
		<ul>
			<?php  foreach($projects as $project): ?>
				<li>
					<?php  echo ClovPageHelper::getPageAnchor($project); ?>
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
