<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Some helper functions for working with permissions.
 */
class ClovPermissionsHelper {
	/**
	 * Set the baseline permissions used for Clov pages. These may be 
	 * augmented/overridden by certain pages. Note that this will clear out 
	 * any existing permissions unless the page is already set to manual 
	 * override.
	 */
	public static function setBaselinePermissions($page, $groupNames = null) {
		$groups = Loader::package('clov')->getGroups($groupNames);
		
		$allPagePermissionKeys = PermissionKey::getList('page');
		$allPagePermissionKeyHandles = array_map(function($permissionKey) {
			return $permissionKey->getPermissionKeyHandle();
		}, $allPagePermissionKeys);
		
		if(isset($groups[ClovPackage::ADMINISTRATORS])) {
			// Administrators can do anything to the page.
			$page->assignPermissions($groups[ClovPackage::ADMINISTRATORS], $allPagePermissionKeyHandles);
		}
		
		// All Clov groups can view the page.
		foreach($groups as $group) {
			$page->assignPermissions($group, array('view_page', 'view_page_versions'));
		}
	}
	
	/**
	 * Like a more-general version of Page->assignPermissions() that can accept 
	 * any kind of access entity. Note that this does not reload the permission 
	 * assignments stored in the page itself, so they may become stale.
	 */
	public static function assignPermissions($permissionObject, $accessEntity, $permissionKeyHandles, $accessType = PermissionKey::ACCESS_TYPE_INCLUDE) {
		foreach($permissionKeyHandles as $keyHandle) {
			$key = PermissionKey::getByHandle($keyHandle);
			$key->setPermissionObject($permissionObject);
			$access = $key->getPermissionAccessObject();
			if(!is_object($access)) {
				$access = PermissionAccess::create($key);
			}
			$access->addListItem($accessEntity, false, $accessType);
			$assignment = $key->getPermissionAssignmentObject();
			$assignment->assignPermissionAccess($access);
		}
	}
	
	/**
	 * Restrict the "add_subpage" permission to only allow a certain page type 
	 * to be added as subpages. This is used throughout Clov to ensure that 
	 * type-aware single pages only allow their associated page type under them.
	 */
	public static function restrictSubpageType($parentPage, $pageType, $accessEntities = null) {
		self::setAccessDetails($parentPage, 'add_subpage', array(
			'pageTypesIncluded' => 'C',
			'ctIDInclude' => array($pageType->getCollectionTypeID()),
			'allowExternalLinksIncluded' => false,
		), $accessEntities);
	}
	
	/**
	 * Do not allow certain attributes to be edited. The way this actually 
	 * works is to explicitly allow all available attributes that are not 
	 * disallowed, a distinction that becomes important if more attributes are 
	 * added later on (they will be disallowed by default).
	 */
	public static function disallowEditingAttributes($page, $disallowedAttributeHandles, $accessEntities = null) {
		// Accept a single values or an array of disallowed handles.
		if(!is_array($disallowedAttributeHandles)) {
			$disallowedAttributeHandles = array($disallowedAttributeHandles);
		}
		
		// Generate a list of allowed keys.
		$pageType = CollectionType::getByID($page->getCollectionTypeID());
		$attributeKeys = $pageType->getAvailableAttributeKeys();
		$allowedAttributeKeyIDs = array();
		foreach($attributeKeys as $attributeKey) {
			if(!in_array($attributeKey->getAttributeKeyHandle(), $disallowedAttributeHandles)) {
				$allowedAttributeKeyIDs[] = $attributeKey->getAttributeKeyID();
			}
		}
		
		ClovPermissionsHelper::setAccessDetails($page, 'edit_page_properties', array(
			// These are the defaults, but they need to be specified because of 
			// how EditPagePropertiesPagePermissionAccess->save() works.
			'allowEditName' => true,
			'allowEditDescription' => true,
			'allowEditUID' => true,
			'allowEditDateTime' => true,
			'allowEditPaths' => true,
			
			'propertiesIncluded' => 'C', // "C" for "custom".
			'akIDInclude' => $allowedAttributeKeyIDs,
		), $accessEntities);
	}
	
	/**
	 * A slightly more convenient interface for setting permission "details" 
	 * such as subpage type restrictions and per-property/attribute permissions.
	 * Note that this only cares about the "include" access type; it doesn't 
	 * do anything with "exclude" permissions. Also, due to the way 
	 * PermissionAccess->save() works, it will overwrite any previous details 
	 * set. Because this function assigns the same details to all access 
	 * entities passed, it cannot be used to have different particulars for 
	 * different entities (however all unset entities will use the permission's 
	 * default details).
	 */
	private static function setAccessDetails($permissionObject, $permissionKey, array $details, $accessEntities = null) {
		if(!is_object($permissionKey)) {
			$permissionKey = PermissionKey::getByHandle($permissionKey);
		}
		$permissionKey->setPermissionObject($permissionObject);
		$access = $permissionKey->getPermissionAccessObject();
		
		if(!isset($accessEntities)) {
			// Apply the details to all access entities if not otherwise 
			// specified.
			$accessEntityIDs = array_map(function($listItem) {
				return $listItem->getAccessEntityObject()->getAccessEntityID();
			}, $access->getAccessListItems(PagePermissionKey::ACCESS_TYPE_INCLUDE));
		} else {
			if(!is_array($accessEntities)) {
				// Accept a single access entity as well.
				$accessEntities = array($accessEntities);
			}
			$accessEntityIDs = array_map(function($entity) {
				return $entity->getAccessEntityID();
			}, $accessEntities);
		}
		
		// Transform $details into an array of the form that $access->save() 
		// wants. Something like this:
		// 	array(
		// 		'optionName' => array(
		// 			$accessEntityID1 => 'optionValue',
		// 			$accessEntityID2 => 'optionValue',
		// 			...
		// 		),
		// 		...
		// 	)
		$saveArguments = array_map(function($detailValue) use ($accessEntityIDs) {
			return array_fill_keys($accessEntityIDs, $detailValue);
		}, $details);
		
		$access->save($saveArguments);
	}
}
