<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Helper functions for working with URLs.
 */
class ClovUrlHelper {
	/**
	 * Returns the part of the URL useful for redirecting to a collection. 
	 * Includes a leading slash.
	 * 
	 * @return string
	 */
	public static function getCollectionRoute($collection) {
		$route = '/page_not_found';
		
		$collectionType = CollectionType::getByID($collection->getCollectionTypeID());
		if(!empty($collectionType)) {
			$composedCollection = ComposerPage::getByID($collection->getCollectionID());
		}
		
		if(!empty($composedCollection) && $composedCollection->isComposerDraft() && $collectionType->getPackageHandle() == 'clov') {
			// If the collection is a composer draft and is editable within 
			// Clov, use the edit URL.
			$collectionParent = SinglePage::getByID($collectionType->getCollectionTypeComposerPublishPageParentID());
			$route = $collectionParent->getCollectionPath().'/-/edit/'.$collection->getCollectionID();
		} else if($collection->getCollectionPath() != null) {
			$route = $collection->getCollectionPath();
		} else {
			$cID = ($collection->getCollectionPointerID() > 0) ? $collection->getCollectionPointerOriginalID() : $collection->getCollectionID();
			if($cID > 1) {
				$route = '/?cID='.$cID;
			}
		}
		
		return $route;
	}
	
	/**
	 * Returns the path prefix (everything after the domain & before the actual 
	 * Concrete5 route). Includes a trailing slash.
	 * 
	 * @return string
	 */
	public static function getUrlPathPrefix() {
		$dispatcher = '';
		$urlsAreRewritten = (defined('URL_REWRITING_ALL') && URL_REWRITING_ALL) || URL_REWRITING;
		if(!$urlsAreRewritten) {
			return DIR_REL.'/'.DISPATCHER_FILENAME.'/';
		} else {
			return DIR_REL.'/';
		}
	}
	
	/**
	 * Makes a dispatcher-relative URL absolute (does not include the domain).
	 * 
	 * @return string
	 */
	public static function absolutize($path) {
		return self::getUrlPathPrefix().ltrim($path, '/');
	}
	
	/**
	 * Makes an absolute URL dispatcher-relative. If it doesn't look like an 
	 * absolute URL, argument is returned as-is. $url must contain everything 
	 * from the the scheme (http:) onwards.
	 * 
	 * @return string
	 */
	// FIXME: It's kind of confusing that this isn't the opposite of absolutize 
	// (relativize(absolutize($url)) does not behave as expected).
	public static function relativize($url) {
		// Even if URL rewriting is enabled, the URL may still include the 
		// dispatcher, so check both.
		if(stripos($url, BASE_URL.DIR_REL.'/'.DISPATCHER_FILENAME) === 0) {
			$urlPrefix = BASE_URL.DIR_REL.'/'.DISPATCHER_FILENAME;
		} else if(stripos($url, BASE_URL.DIR_REL) === 0) {
			$urlPrefix = BASE_URL.DIR_REL;
		}
		
		if(isset($urlPrefix)) {
			return substr($url, strlen($urlPrefix));
		} else {
			return $url;
		}
	}
	
	/**
	 * If the referrer can be loaded as a C5 page object, return it. Otherwise 
	 * return false. Optionally validates that the page is of a certain type.
	 * Note that since this relies on the inherently untrustworthy referrer 
	 * header it should not be used for anything mission critical.
	 * 
	 * @return Page|false
	 */
	public static function loadReferrerPage($typeHandle = null) {
		$validatePage = function($page) use ($typeHandle) {
			if($page instanceof Page && !($page->isError() && $page->getError() == COLLECTION_NOT_FOUND)) {
				if(!isset($typeHandle) || $page->getCollectionTypeHandle() == $typeHandle) {
					return true;
				}
			}
			return false;
		};
		
		if(isset($_SERVER['HTTP_REFERER'])) {
			$referrerPath = self::relativize($_SERVER['HTTP_REFERER']);
			$page = Page::getByPath($referrerPath);
			if($validatePage($page)) {
				return $page;
			} else {
				// Parse the query string and look for a cID.
				$referrerQuery = array();
				parse_str(parse_url($referrerPath, PHP_URL_QUERY) ?: $referrerPath, $referrerQuery);
				if(isset($referrerQuery['cID'])) {
					$page = Page::getByID($referrerQuery['cID']);
					if($validatePage($page)) {
						return $page;
					}
				}
			}
		}
		return false;
	}
}
