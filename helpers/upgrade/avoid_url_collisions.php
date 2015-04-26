<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Fix up URLs to avoid collisions between controller action URLs and page 
 * route URLs. These could occur when a Clov entity was given a handle like 
 * "add"; /clov/projects/add could point to both a project named "add" and the 
 * "add" controller action.
 */
class ClovUpgradeAvoidUrlCollisionsHelper {
	/**
	 * The only stored routes (as far as Clov is concerned) are relative link 
	 * block actions. Check all instances of this block type and convert their 
	 * stored actions to the legacy URL format (controller-route/-/task) when 
	 * needed to avoid collisions.
	 */
	public static function upgrade() {
		$db = Loader::db();
		$relativeLinkType = BlockType::getByHandle('clov_relative_link');
		$relativeLinkRows = $db->getAll('select bID from Blocks where btID = ?', array($relativeLinkType->getBlockTypeID()));
		foreach($relativeLinkRows as $relativeLinkRow) {
			$relativeLink = Block::getByID($relativeLinkRow['bID']);
			self::convertRelativeLinkActionToLegacyRoute($relativeLink);
		}
	}
	
	/**
	 * Fix up a relative link block if needed.
	 */
	private static function convertRelativeLinkActionToLegacyRoute($relativeLink) {
		$relativeLinkController = $relativeLink->getController();
		$record = $relativeLinkController->getBlockControllerData();
		$oldAction = $record->action;
		
		$newAction = self::convertToLegacyRouteIfSuspect($oldAction);
		// The update method has side effects (like the "bDateModified" 
		// column), so only call it if necessary.
		if($newAction !== $oldAction) {
			// ->update() needs all of the columns (even unchanged ones).
			$updateData = array();
			$attributeNames = $record->getAttributeNames();
			foreach($attributeNames as $attributeName) {
				$updateData[$attributeName] = $record->$attributeName;
			}
			
			$updateData['action'] = $newAction;
			$relativeLink->update($updateData);
		}
	}
	
	/**
	 * Convert a route to legacy format if it looks like it could lead to a 
	 * collision. This just looks for Clov actions known to be troublemakers. 
	 * Non-suspect routes are returned as-is.
	 * 
	 * @return string
	 */
	private static function convertToLegacyRouteIfSuspect($route) {
		static $tasks = array(
			'add',
			'edit',
		);
		static $baseRoutes = array(
			'clov/expenses/',
			'clov/invoices/',
			'clov/projects/',
			'clov/tasks/',
			'clov/timesheets/',
		);
		$preg_quote_with_delimiter = function($string) {
			return preg_quote($string, '/');
		};
		
		$baseRoutePattern = implode('|', array_map($preg_quote_with_delimiter, $baseRoutes));
		$taskPattern = implode('|', array_map($preg_quote_with_delimiter, $tasks));
		$afterTaskPattern = '(?:'.$preg_quote_with_delimiter('/').'.*|\z)';
		
		// Matches a string starting with any of the base routes followed by 
		// any of the tasks followed by a slash or the end of the string (\z).
		$suspectRoutePattern = '/^('.$baseRoutePattern.')('.$taskPattern.')('.$afterTaskPattern.')/';
		
		$matches = array();
		if(preg_match($suspectRoutePattern, $route, $matches) === 1) {
			// Convert to legacy format ("/-/" before the task).
			return $matches[1].'-/'.$matches[2].(isset($matches[3]) ? $matches[3] : '');
		} else {
			// Don't change anything.
			return $route;
		}
	}
}
