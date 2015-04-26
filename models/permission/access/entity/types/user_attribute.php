<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Access entity whose permitted users are those present in a specified 
 * user/users attribute on the given page.
 * Note that this only works on page attributes (not file/user/whatever 
 * attributes).
 */
class UserAttributePermissionAccessEntity extends PermissionAccessEntity {
	/**
	 * The table where entity-specific data is stored. Must already exist.
	 */
	protected static $tableName = 'PermissionAccessEntityUserAttributes';
	
	/**
	 * The handle of the attribute key that this access entity will validate 
	 * the user against.
	 */
	protected $akHandle;
	
	/**
	 * Get an array of UserInfo objects for the users that have permission 
	 * for a specific PermissionAccess object. These will be all users who are 
	 * present in the user/users attribute that this access entity is 
	 * associated with for the specific collection that the PermissionAccess 
	 * object is for.
	 * 
	 * @return array
	 */
	public function getAccessEntityUsers(PermissionAccess $access) {
		if($access instanceof PagePermissionAccess) {
			$c = $access->getPermissionObject();
		} else if($access instanceof AreaPermissionAccess) {
			$c = $access->getPermissionObject()->getAreaCollectionObject();
		} else if($access instanceof BlockPermissionAccess) {
			$area = $access->getPermissionObject()->getBlockAreaObject();
			$c = $area->getAreaCollectionObject();
		}
		
		if(is_object($c) && ($c instanceof Page)) {
			// A version object needs to be loaded to get the attribute value.
			if(!$c->getVersionObject() || $c->getVersionObject()->getVersionID() === null) {
				$c->loadVersionObject('RECENT'); 
			}
			
			$attributeKey = CollectionAttributeKey::getByHandle($this->akHandle);
			if(is_object($attributeKey)) {
				$attributeValue = $c->getAttributeValueObject($attributeKey);
				if(is_object($attributeValue)) {
					$userIDs = $attributeValue->getValue();
				}
			}
			
			if(empty($userIDs)) {
				return array();
			} else {
				if(!is_array($userIDs)) {
					// Allow single values.
					$userIDs = array($userIDs);
				}
				// Return an array of UserInfo objects.
				return array_map(function($userID) {
					return UserInfo::getByID($userID);
				}, $userIDs);
			}
		}
	}
	
	/**
	 * Check if the logged in user is one of the permitted users.
	 * 
	 * @return boolean
	 */
	public function validate(PermissionAccess $access) {
		$users = $this->getAccessEntityUsers($access);
		if(!empty($users)) {
			$loggedInUser = new User;
			foreach($users as $user) {
				if($user->getUserID() == $loggedInUser->getUserID()) {
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Return HTML to be used to represent this access entity in the access 
	 * entity selector (in the dashboard permissions dialog). The link will 
	 * allow the user to choose an attribute key.
	 * 
	 * @return string
	 */
	public function getAccessEntityTypeLinkHTML() {
		$urlHelper = Loader::helper('concrete/urls');
		$dialogUrl = $urlHelper->getToolsURL('permissions/dialogs/access/entity/types/user_attribute', 'clov');
		return '<a href="'.$dialogUrl.'" dialog-width="250" dialog-height="250" class="dialog-launch" dialog-title="'.t('Attribute Handle').'">'.t('User Attribute').'</a>';
	}
	
	/**
	 * Get all access entities that could possibly permit the given user. 
	 * Note that this currently includes all access entities of this type (see 
	 * comments in function body).
	 * 
	 * @return array
	 */
	public static function getAccessEntitiesForUser($user) {
		$entities = array();
		$db = Loader::db();
		$entityRows = $db->GetAll(
			'select peID, akHandle from PermissionAccessEntities 
				inner join PermissionAccessEntityTypes using (petID) 
				inner join '.self::$tableName.' using (peID) 
				where petHandle = \'user_attribute\''
		);
		if(is_array($entityRows)) {
			foreach($entityRows as $entityRow) {
				$attributeKey = CollectionAttributeKey::getByHandle($entityRow['akHandle']);
				if(is_object($attributeKey)) {
					// Return all entities that correspond to valid attribute 
					// keys. See below for a more precise alternative.
					$entity = PermissionAccessEntity::getByID($entityRow['peID']);
					$entities[] = $entity;
					
					/* XXX: Even though checking if the user is actually set 
					        in an attribute seems like better behavior, it 
					        can lead to unexpected behavior when a user is 
					        added to an attribute and then the permission is 
					        checked on the same request. It's possible there 
					        is a workaround for this, at which point the 
					        below becomes viable (instead of the above).
					        Alternatively, the list could be narrowed down 
					        based on characteristics of the attribute key 
					        itself (e.g. group restriction).
					
					$attributeController = $attributeKey->getController();
					
					// Go through all attribute values for this key and check 
					// if they include $user.
					$attributeValueIDs = $attributeKey->getAttributeValueIDList();
					foreach($attributeValueIDs as $attributeValueID) {
						// Ask the controller whether the value contains 
						// the passed-in $user.
						$attributeValue = CollectionAttributeValue::getByID($attributeValueID);
						$attributeController->setAttributeValue($attributeValue);
						// Note that any attribute type that can work for this 
						// access entity must implement ->containsUser().
						if($attributeController->containsUser($user)) {
							// The attribute value contains the user, so 
							// include its entity in the returned array.
							$entity = PermissionAccessEntity::getByID($entityRow['peID']);
							$entities[] = $entity;
							break;
						}
					}
					XXX */
				}
			}
		}
		
		return $entities;
	}
	
	/**
	 * Get an instance of this access entity and create a new one if needed.
	 * There will only ever be one access entity for a given attribute key 
	 * handle.
	 * 
	 * return boolean|PermissionAccessEntity
	 */
	public static function getOrCreate($attributeKeyHandle) {
		if(empty($attributeKeyHandle)) {
			return false;
		} else {
			$db = Loader::db();
			$typeID = $db->GetOne('select petID from PermissionAccessEntityTypes where petHandle = \'user_attribute\'');
			$entityID = $db->GetOne(
				'select peID from PermissionAccessEntities inner join '.self::$tableName.' using (peID) 
				where petID = ? and akHandle = ?', 
				array($typeID, $attributeKeyHandle)
			);
			
			if(!$entityID) { 
				// Need to create a new entity.
				$db->Execute('insert into PermissionAccessEntities (petID) values(?)', array($typeID));
				Config::save('ACCESS_ENTITY_UPDATED', time());
				$entityID = $db->Insert_ID();
				$db->Execute('insert into '.self::$tableName.' (peID, akHandle) values (?, ?)', array($entityID, $attributeKeyHandle));
			}
			
			return PermissionAccessEntity::getByID($entityID);
		}
	}
	
	/**
	 * Set some properties necessary for using this access entity.
	 */
	public function load() {
		$db = Loader::db();
		$akHandle = $db->GetOne('select akHandle from '.self::$tableName.' where peID = ?', array($this->peID));
		if($akHandle) {
			$attributeKey = CollectionAttributeKey::getByHandle($akHandle);
			if(is_object($attributeKey)) {
				$this->akHandle = $attributeKey->getAttributeKeyHandle();
				$this->label = t('%s Attribute', $attributeKey->getAttributeKeyDisplayHandle());
			} else {
				$this->label = t('(Deleted User Attribute)');
			}
		}
	}
}
