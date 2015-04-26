<?php  defined('C5_EXECUTE') or die('Access Denied.');
	
	// $handle must be passed in as an argument.
	
	Loader::helper('clov_page', 'clov');
	
	$name = ClovPageHelper::renderAttributeKey($handle, true);
	$value = ClovPageHelper::renderAttributeValue($handle, Page::getCurrentPage(), true);
	$class = str_replace('_', '-', $handle);
?>

<div class="clov-attribute <?php  echo $class; ?>">
	<strong class="clov-attribute-name"><?php  echo $name; ?></strong>
	<span class="clov-attribute-value"><?php  echo $value; ?></span>
</div>
