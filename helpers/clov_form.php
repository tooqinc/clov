<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Helper functions for working with forms & inputs.
 */
class ClovFormHelper {
	/**
	 * Prepare a value for an input.
	 * 
	 * @return string
	 */
	public static function normalizeValue($value) {
		return (string) (isset($value) ? $value : 'NULL');
	}
}
