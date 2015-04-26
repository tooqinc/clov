<?php  defined('C5_EXECUTE') or die('Access Denied.');
	
	$gID = $this->controller->getGID();
	$formHelper = Loader::helper('form');
?>
<fieldset>
	<legend><?php  echo t('%s Options', $this->controller->getAttributeType()->getAttributeTypeName()); ?></legend>
	
	<div class="clearfix">
		<?php  echo $formHelper->label('akGID', t('Allow Users From')); ?>
		<div class="input">
			<?php  echo $formHelper->select('akGID', $this->controller->getGIDOptions(), isset($gID) ? $gID : 'NULL'); ?>
		</div>
	</div>
</fieldset>
