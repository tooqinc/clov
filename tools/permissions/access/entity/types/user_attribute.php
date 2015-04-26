<?php  defined('C5_EXECUTE') or die('Access Denied.');

// Request should include an akHandle. This tool will respond with the 
// associated access entity ID and label.
if(Loader::helper('validation/token')->validate('process')) {
	$jsonHelper = Loader::helper('json');
	$accessEntity = UserAttributePermissionAccessEntity::getOrCreate($_REQUEST['akHandle']);
	if(!is_object($accessEntity)) {
		header('HTTP/1.0 400 Bad Request');
		echo $jsonHelper->encode(array(
			'label' => t('Invalid Handle'),
		));
	} else {
		echo $jsonHelper->encode(array(
			'label' => $accessEntity->getAccessEntityLabel(),
			'peID' => $accessEntity->getAccessEntityID(),
		));
	}
}
