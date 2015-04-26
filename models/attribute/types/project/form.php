<?php  defined('C5_EXECUTE') or die('Access Denied.');

$form = Loader::helper('form');
echo $form->select($controller->field('value'), $controller->getOptions(), $controller->getValue());
