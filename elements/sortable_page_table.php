<?php  defined('C5_EXECUTE') or die('Access Denied.');

// Use href-handler.js to make rows clickable.
$htmlHelper = Loader::helper('html');
$view = View::getInstance();
$view->addHeaderItem($htmlHelper->javascript('href-handler.js', 'clov'));

// Most of these options can be passed in. Some are optional; see 
// sortable_table for details.
Loader::helper('clov_url', 'clov');
Loader::element('sortable_table', array(
	'itemList' => $itemList,
	'columns' => $columns,
	'caption' => $caption,
	'tableClass' => $tableClass,
	'thDescClass' => $thDescClass,
	'thAscClass' => $thAscClass,
	'trAttributes' => function($item) {
		$attributes = array(
			// href-helper.js will use this to linkify the <tr>.
			'data-href' => ClovUrlHelper::absolutize(ClovUrlHelper::getCollectionRoute($item)),
		);
		if(ClovVersionHelper::isApproved($item) === false) {
			$attributes['class'] = 'clov-unapproved';
			$attributes['title'] = t('Unapproved changes');
		}
		return $attributes;
	},
), 'clov');
