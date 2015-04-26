<?php  defined('C5_EXECUTE') or die('Access Denied.');

Loader::model('attribute/types/default/controller');

/**
 * Similar to UserAttributeTypeController, but allows multiple selections.
 */
class UsersAttributeTypeController extends UserAttributeTypeController {
	
	/**
	 * Used to separate individual user values in the search index. Newline is 
	 * chosen to be consistent with how the built in (multi-)select attribute 
	 * type works.
	 */
	const SEARCH_INDEX_DELIMITER = "\n";
	
	/**
	 * Render the users, offloading most of the heavy listing to the superclass.
	 * 
	 * @return string
	 */
	public function getDisplayValue() {
		$uIDs = $this->getValue();
		if(empty($uIDs)) {
			return t('Nobody');
		} else {
			// Use the parent implementation to render and return as a list 
			// separated by commas.
			$displayValues = array_map('parent::getDisplayValue', $uIDs);
			return implode(', ', $displayValues);
		}
	}
	
	/**
	 * Check if this attribute contains a specified user.
	 * 
	 * @return boolean
	 */
	public function containsUser($user) {
		return in_array($user->getUserID(), $this->getValue());
	}
	
	/**
	 * How the value is stored for search indexing. Like the (multiple) select 
	 * attribute type, this is indexed as a delimited list (with extra 
	 * delimiters at the beginning and end). Note that when filtering database 
	 * item lists by this attribute, this format is what should be expected 
	 * (database item lists use the search index for attribute filters).
	 * 
	 * @return string
	 */
	public function getSearchIndexValue() {
		return self::SEARCH_INDEX_DELIMITER.implode(self::SEARCH_INDEX_DELIMITER, $this->getValue()).self::SEARCH_INDEX_DELIMITER;
	}
	
	/**
	 * Make sure the value is a user ID.
	 * 
	 * @return boolean
	 */
	// TODO: Also check if the value is in $this->getValueOptions()
	public function validateForm($data) {
		if(!is_array($data['value'])) {
			return false;
		} else {
			foreach($data['value'] as $uID) {
				$user = User::getByUserID($data['value']);
				if(!$user->isRegistered()) {
					return false;
				}
			}
			return parent::validateForm($data);
		}
	}
	
	/**
	 * Return an SQL condition to check if the search index for the 
	 * currently-loaded attribute key instance contains the specified user. 
	 * Useful to add to page list filters, etc.
	 * 
	 * @return string
	 */
	public function getUserSearchCondition($userOrUID) {
		if(is_numeric($userOrUID)) {
			$uID = $userOrUID;
		} else if(is_object($userOrUID)) {
			$uID = $userOrUID->getUserID();
		} else {
			throw new Exception('Value for condition must be a user or user ID (was "'.Loader::helper('text')->entities($userOrUID).'").');
		}
		
		// Since this is stored in the search index as string containing a 
		// delimited list of uIDs, we can use searchKeywords() to generate a 
		// usable LIKE condition.
		return $this->searchKeywords(self::SEARCH_INDEX_DELIMITER.$uID.self::SEARCH_INDEX_DELIMITER);
	}
	
	/**
	 * Save the user list. Should be passed an array of user IDs.
	 */
	// FIXME: The current storage mechanism (encode to JSON) may not scale 
	// well, but it's dead simple. If it needs to be rethought in the future, 
	// saveValue() and getValue() can hopefully keep the same signatures and 
	// just do some extra work internally (deleteKey(), deleteValue(), and 
	// searchForm() may also need to be overridden).
	public function saveValue($uIDs) {
		if(empty($uIDs)) {
			$uIDs = array();
		}
		
		if(!is_array($uIDs)) { // Alternately could use $this->validateForm().
			throw new Exception('Value must be an array of user IDs.');
		} else {
			$allowedValues = $this->getValueOptions();
			foreach($uIDs as $uID) {
				if(!isset($allowedValues[$uID])) {
					throw new Exception('Non-allowed user ID ('.$uID.').');
				}
			}
			
			// Make sure the array elements are encoded as strings.
			// FIXME: This isn't strictly necessary anymore, but it should be 
			// normalized to something. Would numbers be better?
			$uIDs = array_map('strval', $uIDs);
			$jsonHelper = Loader::helper('json');
			
			$db = Loader::db();
			$db->Replace('atDefault', array(
				'avID' => $this->getAttributeValueID(),
				'value' => $jsonHelper->encode($uIDs),
			), 'avID', true);
		}
	}
	
	/**
	 * Abstract away the storage mechanism and return an array of user IDs.
	 * 
	 * @return array
	 */
	public function getValue() {
		$db = Loader::db();
		$value = $db->GetOne('select value from atDefault where avID = ?', array($this->getAttributeValueID()));
		
		$jsonHelper = Loader::helper('json');
		return $jsonHelper->decode($value);
	}
}
