<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Some helper functions for working with pages & display.
 */
// TODO: This could probably be split up a bit. AttributeHelper & LinkHelper 
// maybe?
class ClovPageHelper {
	/**
	 * A "rendered attribute key" is just its name.
	 */
	public static function renderAttributeKey($key, $return = false) {
		if(is_string($key)) {
			$key = CollectionAttributeKey::getByHandle($key);
		}
		if($return) {
			return $key->getAttributeKeyName();
		} else {
			echo $key->getAttributeKeyName();
		}
	}
	
	/**
	 * See self::getRenderedAttributeValueObject for details about how 
	 * values are rendered.
	 */
	public static function renderAttributeValue($key, $collection = null, $return = false) {
		if(!isset($collection)) {
			$collection = Page::getCurrentPage();
		}
		if(is_string($key)) {
			$key = CollectionAttributeKey::getByHandle($key);
		}
		
		$valueObject = $collection->getAttributeValueObject($key);
		if($valueObject) {
			$renderedValue = self::getRenderedAttributeValueObject($valueObject);
		} else {
			$renderedValue = null;
		}
		
		if($return) {
			return $renderedValue;
		} else {
			echo $renderedValue;
		}
	}
	
	/**
	 * Prefer rendering the attribute's "view" view, fall back to the 
	 * controller's getDisplayValue() method, and finally default to 
	 * getValue().
	 * 
	 * @return string
	 */
	public static function getRenderedAttributeValueObject($valueObject) {
		if(!is_object($valueObject)) {
			return t('None');
		} else {
			$key = $valueObject->getAttributeKey();
			$displayValue = $valueObject->getValue('display'); // This ends up calling AttributeTypeController->getDisplayValue() if it exists.
			$value = $valueObject->getValue();
			
			if($rendered = $key->render('view', $value, true)) {
				return $rendered;
			} else if($displayValue) {
				return $displayValue;
			} else {
				return $value;
			}
		}
	}
	
	/**
	 * Creates an <a> element linking to a page and containing the page name.
	 * 
	 * @return string
	 */
	public static function getPageAnchor($collection) {
		Loader::helper('clov_url', 'clov');
		$text = Loader::helper('text')->entities($collection->getCollectionName());
		$url = ClovUrlHelper::absolutize(ClovUrlHelper::getCollectionRoute($collection));
		return '<a href="'.$url.'">'.$text.'</a>';
	}
	
	/**
	 * Return a link to the user profile if profiles are enabled, and just use 
	 * their username if not.
	 * 
	 * @return string
	 */
	public static function getRenderedUser($user) {
		if(ENABLE_USER_PROFILES) {
			$view = View::getInstance();
			return '<a href="'.$view->url('/profile', 'view', $user->getUserID()).'">'.$user->getUserName().'</a>';
		} else {
			return $user->getUserName();
		}
	}
	
	/**
	 * A shortcut for adding blocks to collections.
	 * 
	 * @return Block
	 */
	public static function addBlockByHandle($collection, $blockType, $blockData = array(), $area = 'Main') {
		if(is_string($blockType)) {
			$blockType = BlockType::getByHandle($blockType);
		}
		return $collection->addBlock($blockType, $area, $blockData);
	}
}
