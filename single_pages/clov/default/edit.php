<?php  defined('C5_EXECUTE') or die('Access Denied.'); ?>
<div class="clov">
	<h2><?php  echo t('Edit %s', $entry->getCollectionTypeName()); ?></h2>
	
	<?php 
		Loader::element('compose_form', array(
			'entry' => $entry,
			'error' => $error,
			'info' => $info,
			'success' => $success,
			'showNameField' => $showNameField,
			'showDescriptionField' => $showDescriptionField,
			'showSaveDraft' => $showSaveDraft,
		), 'clov');
	?>
</div>
