<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A drop-in replacement for the built-in PageList class.
 */
class ClovPageList extends PageList {
	/**
	 * Add more accurate permissions checking (at a performance cost).
	 */
	public function setBaseQuery($additionalFields = '') {
		parent::setBaseQuery($additionalFields);
		
		$user = new User;
		// Only check if needed.
		if(!$user->isSuperUser() && !$this->ignorePermissions) {
			Loader::helper('clov_cache', 'clov');
			// Check permissions of each page for real. This will be slower 
			// than PageList's method, but it's the only sane way to be sure 
			// that permissions are actually correct for custom access entities 
			// (PageList special cases page_owner, but really all page-
			// dependent access entities would need similar specialness if we 
			// want to do everything in one query).
			$pages = $this->get(INF);
			foreach($pages as $pageToCheck) {
				// Disable the cache to work around a C5 bug:
				// http://www.concrete5.org/index.php?cID=436968
				$pageList = $this;
				ClovCacheHelper::ignoreCache(function() use ($pageToCheck, &$pageList) {
					$permissions = new Permissions($pageToCheck);
					if(!$permissions->canViewPage() && !$permissions->canViewPageVersions()) {
						// Filter out this page.
						$pageList->filter('p1.cID', $pageToCheck->getCollectionID(), '!=');
					}
				});
			}
		}
	}
}
