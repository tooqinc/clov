<?php  defined('C5_EXECUTE') or die('Access Denied.');

Loader::model('attribute/types/default/controller');

/**
 * An attribute type whose value is a registered user.
 */
class UserAttributeTypeController extends DefaultAttributeTypeController {
	/**
	 * Table used for storing attribute type settings (for configuring 
	 * attribute keys of this type).
	 */
	protected $atSettingsTable = 'atUserSettings';
	
	/**
	 * Hilariously, we need a blank method overriding the parent 
	 * implementation just to get form.php to render.
	 */
	public function form() {}
	
	/**
	 * Get an array of value => text for the possible values for this 
	 * attribute type.
	 * 
	 * @return array
	 */
	public function getValueOptions() {
		Loader::model('user_list');
		$userList = new UserList;
		$gID = $this->getGID();
		if(isset($gID)) {
			$userList->filterByGroupID($gID);
		}
		$allUsers = $userList->get(INF);
		
		$options = array();
		foreach($allUsers as $user) {
			$options[$user->getUserID()] = $user->getUserName();
		}
		return $options;
	}
	
	/**
	 * Forgivingly load the user and render them. Also do some ghetto error 
	 * handling.
	 * 
	 * @return string
	 */
	public function getDisplayValue($uID = null) {
		if(!isset($uID)) {
			$uID = $this->getValue();
		}
		if(empty($uID)) {
			return t('Nobody');
		} else {
			$user = User::getByUserID($uID);
			if(!$user->isRegistered()) {
				// Check if the value is a username. The form for this should 
				// try to make sure it's a uID, but since validation is iffy, 
				// this could help people out.
				// TODO: Should probably remove this and only allow user ID.
				$userInfo = UserInfo::getByUserName($uID);
				if(is_object($userInfo)) {
					$user = $userInfo->getUserObject();
				}
			}
			if(empty($user) || !$user->isRegistered()) {
				// TODO: Better error handling? It seems that validateForm 
				// doesn't actually get called anywhere useful.
				return '<span class="clov-error">Invalid value "'.Loader::helper('text')->entities($uID).'".</span>';
			} else {
				Loader::helper('clov_page', 'clov');
				return ClovPageHelper::getRenderedUser($user);
			}
		}
	}
	
	/**
	 * Check if this attribute contains a specified user.
	 * 
	 * @return boolean
	 */
	public function containsUser($user) {
		return $user->getUserID() == $this->getValue();
	}
	
	/**
	 * Make sure the value is a valid registered user ID. If a group ID is set 
	 * also validate that the user is a member of the group.
	 * 
	 * @return boolean
	 */
	public function validateForm($data) {
		$user = User::getByUserID($data['value']);
		if(!$user->isRegistered()) {
			return false;
		} else {
			$gID = $this->getGID();
			if(isset($gID)) {
				$group = Group::getByID($gID);
				if(!$user->inGroup($group)) {
					return false;
				}
			}
		}
		
		return parent::validateForm($data);
	}
	
	/**
	 * Get options for the gID argument. This is used on the type form, not 
	 * the value form.
	 * 
	 * @return array
	 */
	public function getGIDOptions() {
		$groupSearch = new GroupSearch;
		$groupSearch->filter('gID', REGISTERED_GROUP_ID, '>'); // Only include "actual" groups (not "guest" or "registered users").
		$allGroups = $groupSearch->get(INF);
		
		$options = array(
			'NULL' => t('All Registered Users'),
		);
		foreach($allGroups as $group) {
			$options[$group['gID']] = $group['gName'];
		}
		return $options;
	}
	
	/**
	 * Get the gID setting for the current attribute key.
	 * 
	 * @return null|numeric
	 */
	public function getGID() {
		$attributeKey = $this->getAttributeKey();
		if(!isset($attributeKey)) {
			// When creating a new key of this type this will initially be null.
			return null;
		} else {
			$db = Loader::db();
			$row = $db->GetRow('select akGID from '.$this->atSettingsTable.' where akID = ?', $attributeKey->getAttributeKeyID());
			return isset($row['akGID']) ? $row['akGID'] : null;
		}
	}
	
	/**
	 * Set the gID setting for the current attribute key.
	 */
	public function setGID($gID) {
		$db = Loader::db();
		$attributeKey = $this->getAttributeKey();
		$db->Replace($this->atSettingsTable, array(
			'akID' => $attributeKey->getAttributeKeyID(), 
			'akGID' => $gID,
		), array('akID'), true);
	}
	
	/**
	 * Save the user ID.
	 */
	public function saveValue($uID) {
		$allowedValues = $this->getValueOptions();
		if((empty($allowedValues) && !isset($uID)) || isset($allowedValues[$uID])) {
			parent::saveValue($uID);
		} else {
			throw new Exception(t('Non-allowed user ID (%s).', $uID));
		}
	}
	
	/**
	 * Save attribute key settings.
	 */
	public function saveKey($data) {
		if(isset($data['akGID'])) {
			$this->setGID($data['akGID']);
		} else {
			$this->setGID(null);
		}
	}
	
	/**
	 * Delete an attribute key of this type.
	 */
	public function deleteKey() {
		$attributeKey = $this->getAttributeKey();
		$db = Loader::db();
		$db->Execute('delete from '.$this->atSettingsTable.' where akID = ?', $attributeKey->getAttributeKeyID());
	}
}
