<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Some helper functions for working with HTML.
 */
class ClovHtmlHelper {
	/**
	 * Turns an array of attribute name => value into a string for use in HTML.
	 * 
	 * @return string
	 */
	public static function buildAttributeString($attributes) {
		$textHelper = Loader::helper('text');
		if(is_string($attributes)) {
			return $attributes;
		} else {
			$attributeString = '';
			foreach($attributes as $name => $value) {
				$attributeString .= ' '.$textHelper->specialchars($name).'="'.$textHelper->specialchars($value).'"';
			}
			return $attributeString;
		}
	}
}
