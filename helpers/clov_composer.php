<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Some helper functions for working with the composer.
 */
class ClovComposerHelper {
	/**
	 * Add a collection type to the composer with all attributes available.
	 */
	public static function addToComposer($collectionType, $publishTargetPath) {
		if(is_string($collectionType)) {
			$collectionType = CollectionType::getByHandle($collectionType);
		}
		$collectionType->saveComposerPublishTargetPage(Page::getByPath($publishTargetPath));
		// All attributes assigned to the collection should be available in composer.
		$attributeKeyIDs = array_map(function($attributeKey) {
			return $attributeKey->getAttributeKeyID();
		}, $collectionType->getAvailableAttributeKeys());
		$collectionType->saveComposerAttributeKeys($attributeKeyIDs);
	}
}
