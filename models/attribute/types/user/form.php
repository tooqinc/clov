<?php  defined('C5_EXECUTE') or die('Access Denied.');

$formHelper = Loader::helper('form');
$options = $controller->getValueOptions();
if(empty($options)) {
	echo $formHelper->select($controller->field('value'), array('NULL' => t('No Acceptable Users')), null, array('disabled' => 'disabled'));
	
	// If filtered by group, the group must not contain any usable users.
	$groupID = $controller->getGID();
	if(isset($groupID)) {
		$group = Group::getByID($groupID);
		echo "\n".'<small>'.t('The "%s" group contains no active users.', $group->getGroupName()).'</small>';
	}
} else {
	echo $formHelper->select($controller->field('value'), $controller->getValueOptions(), $controller->getValue());
}
