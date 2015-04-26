<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Shared controller methods based around Clov's usage of page types.
 */
abstract class ClovPageTypeController extends Controller {
	/**
	 * Tell new pages to inherit from their page type defaults (instead of 
	 * their parent page, which is the normal behavior). Note that this means 
	 * every master collection must have permissions set up (since they will 
	 * be completely blank by default).
	 */
	public function on_page_add($page) {
		$page->inheritPermissionsFromDefaults();
	}
}
