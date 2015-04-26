<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Some helper functions for working with the cache.
 */
class ClovCacheHelper {
	/**
	 * Execute some behavior without the cache.
	 * 
	 * @return mixed
	 */
	public static function ignoreCache($callback) {
		$cacheLocalWasEnabled = CacheLocal::get()->enabled;
		
		Cache::disableCache();
		Cache::disableLocalCache();
		
		$returnValue = $callback();
		
		// Revert to original cache settings.
		if($cacheLocalWasEnabled) {
			Cache::enableLocalCache();
		}
		if(ENABLE_CACHE) {
			Cache::enableCache();
		}
		
		return $returnValue;
	}
}
