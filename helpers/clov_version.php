<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Some helper functions for working with versioned objects.
 */
class ClovVersionHelper {
	/**
	 * Check if a versioned object is approved. Returns null if the object is 
	 * not versioned or if approval state is indeterminable. Since there's 
	 * not really a uniform interface for this, it's a bit fudged; it should 
	 * at least work for files and pages.
	 * 
	 * @return null|boolean
	 */
	public static function isApproved($object) {
		if(is_callable(array($object, 'isApproved'))) {
			return (boolean) $object->isApproved();
		} else if(is_callable(array($object, 'getVersionObject')) && $version = $object->getVersionObject()) {
			return (boolean) $version->isApproved();
		} else {
			// Unable to determine if approved or unapproved.
			return null;
		}
	}
}
