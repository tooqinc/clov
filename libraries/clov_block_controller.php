<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A slightly smarter version of BlockController.
 */
abstract class ClovBlockController extends BlockController {
	/**
	 * Make sure default values are properly set if the record has not been 
	 * saved yet.
	 */
	public function save($arguments) {
		// Make sure that unset args use field defaults instead of nulls.
		// This should only happen the first time the block is saved, since if 
		// the block was previously in the DB $this->record will be preloaded 
		// with existing field values which should not be overwritten with 
		// defaults, even if they are omitted from $args.
		if(!$this->isInDB()) {
			$arguments = $this->fillDefaults($arguments);
		}
		
		parent::save($arguments);
	}
	
	/**
	 * Fill a field array with default values for missing keys based on the 
	 * table definition. Note that this only occurs when $this->record does 
	 * not contain a value for that field (because it's assumed that this will
	 * be most useful during save(), which relies on $this->record state).
	 * 
	 * @return array
	 */
	// FIXME: Is the $this->record check really necessary given the isInDB() 
	// safeguard in save? I guess it allows setting $this->record->whatever 
	// manually from code and keeping that from being overwritten when 
	// $fieldArray['whatever'] is missing...
	public function fillDefaults($fieldArray) {
		$tableInfo = $this->record->TableInfo();
		$fieldInfo = $tableInfo->flds;
		foreach($fieldInfo as $name => $field) {
			// Only use the default value if there is one to use, if the record 
			// does not already have a value for that field, and if there is 
			// not already a value present in $fieldArray.
			// TODO? Could fill $fieldArray with $this->record->{$name} if 
			// something is set there, but then "fillDefaults" wouldn't really 
			// be a good name for this method anymore.
			if($field->has_default && !isset($this->record->{$name}) && !array_key_exists($name, $fieldArray)) {
				$fieldArray[$name] = $field->default_value;
			}
		}
		return $fieldArray;
	}
	
	/**
	 * Check if this block has already been saved to the database.
	 * 
	 * @return boolean
	 */
	public function isInDB() {
		$db = Loader::db();
		return (boolean) $db->getOne('select count(*) from '.$this->btTable.' where bID = ?', array($this->bID));
	}
}
