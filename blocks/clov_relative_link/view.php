<?php  defined('C5_EXECUTE') or die('Access Denied.');
	
	Loader::helper('clov_html', 'clov');
	
	$url = $this->url($action);
	
	$attributes = array('href' => $url);
	if(!empty($title)) {
		$attributes['title'] = $title;
	}
	if(!empty($class)) {
		$attributes['class'] = $class;
	}
?>
<a<?php  echo ClovHtmlHelper::buildAttributeString($attributes); ?>><?php  echo $text; ?></a>
