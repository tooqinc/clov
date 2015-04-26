<?php  defined('C5_EXECUTE') or die('Access Denied.');
	
	/**
	 * Default view for clov_timesheet_entry_list blocks.
	 */
	
	Loader::helper('clov_page', 'clov');
	
	$pageList = $controller->getPageList();
	$timesheetEntries = $pageList->getPage();
?>

<div class="clov-list clov-timesheet-entry-list">
	<h3><?php  echo $controller->getTitle(); ?></h3>
	
	<?php  Loader::element('alerts', array('error' => $error), 'clov'); ?>
	
	<?php  if(empty($timesheetEntries)): ?>
		<div class="clov-empty"><?php  echo t('No timesheet entries.'); ?></div>
	<?php  else: ?>
		<ul>
			<?php  foreach($timesheetEntries as $timesheetEntry): ?>
				<li>
					<?php  echo ClovPageHelper::getPageAnchor($timesheetEntry); ?>
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
