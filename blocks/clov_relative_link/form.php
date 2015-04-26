<?php  defined('C5_EXECUTE') or die('Access Denied.');
	
	/**
	 * The form used for adding/editing clov_expense_list blocks.
	 */
	
	Loader::helper('clov_url', 'clov');
	$formHelper = Loader::helper('form');
	
	$scheme = !empty($_SERVER['HTTPS']) ? 'https' : 'http';
	$exampleBaseUrl = $scheme.'://'.$_SERVER['HTTP_HOST'].ClovUrlHelper::absolutize('/');
?>

<div class="ccm-block-field-group">
	<h2><?php  echo t('Relative URL'); ?></h2>
	<?php  echo $exampleBaseUrl.$formHelper->text('action', $action); ?>
</div>

<div class="ccm-block-field-group">
	<h2><?php  echo t('Link Text'); ?></h2>
	<?php  echo $formHelper->text('text', $text); ?>
</div>

<div class="ccm-block-field-group">
	<h2><?php  echo t('Link Title'); ?></h2>
	<?php  echo $formHelper->text('title', $title); ?>
</div>

<div class="ccm-block-field-group">
	<h2><?php  echo t('Link Class'); ?></h2>
	<?php  echo $formHelper->text('class', $title); ?>
</div>
