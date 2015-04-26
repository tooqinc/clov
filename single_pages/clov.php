<?php  defined('C5_EXECUTE') or die('Access Denied.'); ?>
<div class="clov">
	<h1><?php  echo t('Clov Dashboard'); ?></h1>
	
	<?php 
		$navigationGlobalArea = new GlobalArea('Clov Navigation');
		$navigationGlobalArea->display();
		
		$mainArea = new Area('Main');
		$mainArea->display($c);
	?>
</div>
