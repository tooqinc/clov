<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Package controller which handles install/uninstall, events, and other 
 * package-wide features.
 */
class ClovPackage extends Package {
	protected $pkgHandle = 'clov';
	protected $appVersionRequired = '5.6.0.2';
	protected $pkgVersion = '1.0.1';
	
	const ADMINISTRATORS = 'Administrators';
	const PROJECT_MANAGERS = 'Project Managers';
	const EMPLOYEES = 'Employees';
	
	public function getPackageName() {
		return t('Clov');
	}
	
	public function getPackageDescription() {
		return t('Simple project management.');
	}
	
	/**
	 * Set up package-wide behavior.
	 */
	public function on_start() {
		// Access entities need to be autoloadable.
		Loader::registerAutoload(array(
			'UserAttributePermissionAccessEntity' => array('model', 'permission/access/entity/types/user_attribute', 'clov'),
		));
		
		// FIXME: This is bad for performance, but it's the only way I can 
		// figure out to avoid the following error:
		// 	"The script tried to execute a method or access a property of an 
		// 	incomplete object."
		// Which is caused by UserAttributePermissionAccessEntity being 
		// unserialized (thanks to session_start) before the autoloader knows 
		// how to find its class.
		register_shutdown_function(function() {
			unset($_SESSION['accessEntities']);
		});
		
		// Allow all page type controllers to respond to on_page_add events.
		$pageTypes = $this->getPageTypeSpecifications();
		foreach($pageTypes as $pageTypeHandle => $attributes) {
			Events::extendPageType($this->addPrefixToHandle($pageTypeHandle), 'on_page_add');
		}
		
		// Add the package stylesheet.
		$htmlHelper = Loader::helper('html');
		$view = View::getInstance();
		$stylsheets = unserialize($this->config('STYLESHEETS'));
		if(is_array($stylsheets)) {
			foreach($stylsheets as $stylesheet) {
				$view->addHeaderItem($htmlHelper->css($stylesheet, 'clov'));
			}
		}
	}
	
	/**
	 * Install the package.
	 * 
	 * @return Package
	 */
	public function install() {
		$this->on_start();
		$installedPackage = parent::install();
		
		// The local cache can cause some incorrect behavior during install. 
		// Notably it will make Stack::getByName return the wrong value when 
		// reinstalling (as of 5.6.0.2).
		// FIXME: Is the cache only an issue when I uninstall & reinstall
		// during one request (or one execution environment)? If yes, maybe 
		// disabling the cache is unnecessary.
		Cache::disableLocalCache();
		
		$userAttributeEntity = PermissionAccessEntityType::add('user_attribute', 'User Attribute', $installedPackage);
		PermissionKeyCategory::getByHandle('page')->associateAccessEntityType($userAttributeEntity);
		
		$installedPackage->initializeConfiguration();
		$installedPackage->createGroups();
		$installedPackage->createPagesAndBlocks();
		
		Log::addEntry(t('Clov package installed successfully.'), 'clov');
		
		return $installedPackage;
	}
	
	/**
	 * Perform upgrade tasks.
	 */
	public function upgrade() {
		parent::upgrade();
		
		// 1.0.0 -> 1.0.1
		Loader::helper('upgrade/avoid_url_collisions', 'clov');
		ClovUpgradeAvoidUrlCollisionsHelper::upgrade();
	}
	
	/**
	 * Clean up some things that Concrete5 does not take care of automatically
	 * (pretty much anything which isn't explicitly tied to the package). 
	 * Note that Clov-created groups are intentionally left to hang around.
	 */
	public function uninstall() {
		parent::uninstall();
		
		$clovNavigationStack = Stack::getByName('Clov Navigation');
		if($clovNavigationStack) {
			$clovNavigationStack->delete();
		}
		
		$db = Loader::db();
		
		// Clean up tables added by the package's db.xml.
		$db->Execute('drop table if exists atUserSettings');
		$db->Execute('drop table if exists PermissionAccessEntityUserAttributes');
		
		Cache::flush();
		
		Log::addEntry(t('Clov package uninstalled successfully.'), 'clov');
	}
	
	/**
	 * Set default package configuration values from config/package.php.
	 */
	public function initializeConfiguration() {
		// The configuration file should define a constant corresponding to 
		// each config option.
		$constantsBeforeConfig = get_defined_constants(true);
		require('config/package.php');
		$constantsAfterConfig = get_defined_constants(true);
		
		// Save the constants which are newly-defined by the file.
		$clovConfigOptions = array_diff_key($constantsAfterConfig['user'], $constantsBeforeConfig['user']);
		foreach($clovConfigOptions as $option => $value) {
			$this->saveConfig($option, $value);
		}
	}
	
	/**
	 * Get an array of page type handle => attribute specification array. Note 
	 * that these handles are all unprefixed.
	 * 
	 * @return array
	 */
	public function getPageTypeSpecifications() {
		$clovGroups = $this->getGroups();
		$specifications = array(
			'task' => array(
				'assignee' => array(
					'type' => 'user',
				),
				'completed' => array(
					'type' => 'boolean',
					'arguments' => array(
						'akName' => t('Mark As Completed'),
					),
				),
			),
			'project' => array(
				'reference_number' => array(
					'type' => 'text',
				),
				'start_date' => array(
					'type' => 'date_time',
					'arguments' => array(
						'akDateDisplayMode' => 'date',
					),
				),
				'expected_hours' => array(
					'type' => 'number', // TODO: Duration attribute type?
				),
				'managers' => array(
					'type' => 'users',
					'arguments' => array(
						'akGID' => is_object($clovGroups[self::PROJECT_MANAGERS]) ? $clovGroups[self::PROJECT_MANAGERS]->getGroupID() : null,
					),
				),
				'assignees' => array(
					'type' => 'users',
					'arguments' => array(
						'akGID' => is_object($clovGroups[self::EMPLOYEES]) ? $clovGroups[self::EMPLOYEES]->getGroupID() : null,
					),
				),
			),
			'expense' => array(
				'date' => array(
					'type' => 'date_time',
					'arguments' => array(
						'akDateDisplayMode' => 'date',
					),
				),
				'amount' => array(
					'type' => 'money_amount',
				),
				'location' => array(
					'type' => 'text',
				),
				'payer' => array(
					'type' => 'user',
				),
				'project' => array(
					'type' => 'project',
				),
			),
			'invoice' => array(
				'reference_number' => array(
					'type' => 'text',
				),
				'amount' => array(
					'type' => 'money_amount',
				),
				'date' => array(
					'type' => 'date_time',
					'arguments' => array(
						'akDateDisplayMode' => 'date',
					),
				),
				'project' => array(
					'type' => 'project',
				),
			),
			'timesheet_entry' => array(
				'start' => array(
					'type' => 'date_time',
				),
				'hours' => array(
					'type' => 'number', // TODO: Duration attribute type?
				),
				'employee' => array(
					'type' => 'user',
				),
				'project' => array(
					'type' => 'project',
				),
				'code' => array(
					'type' => 'select',
					// Note that because SelectAttributeTypeController::saveKey 
					// is lame and reads value options directly from $_POST, 
					// we can't add options here via 'arguments'. See 
					// ClovTimesheetEntryPageTypeController::initializeDefaults 
					// for default options.
				),
			),
		);
		
		$textHelper = Loader::helper('text');
		
		// Add a project budget attribute for each time code.
		$timeCodes = unserialize($this->config('DEFAULT_TIME_CODES'));
		if(is_array($timeCodes)) {
			foreach($timeCodes as $timeCode) {
				$handleSafeTimeCode = $textHelper->uncamelcase($textHelper->alphanum($timeCode));
				$attributeHandle = 'budget_for_'.$handleSafeTimeCode;
				$specifications['project'][$attributeHandle] = array('type' => 'money_amount');
			}
		}
		
		return $specifications;
	}
	
	/**
	 * Returns an array of all the group names that Clov makes use of.
	 * 
	 * @return array
	 */
	public function getGroupNames() {
		return array(
			self::ADMINISTRATORS,
			self::PROJECT_MANAGERS,
			self::EMPLOYEES,
		);
	}
	
	/**
	 * Returns an array of Group objects that Clov makes use of.
	 * 
	 * @return array
	 */
	public function getGroups($groupNames = null) {
		if(!isset($groupNames)) {
			// All Clov groups.
			$groupNames = $this->getGroupNames();
		}
		// Return an array of name => Group.
		$groups = array();
		foreach($groupNames as $groupName) {
			$groups[$groupName] = Group::getByName($groupName);
		}
		return $groups;
	}
	
	/**
	 * Create the user groups relevant to Clov if they don't already exist.
	 */
	protected function createGroups() {
		array_walk($this->getGroupNames(), 'self::createGroupIfNonexistent');
	}
	
	/**
	 * Check by name if a group already exists and create it if not.
	 * 
	 * @return Group
	 */
	protected static function createGroupIfNonexistent($name) {
		if(!($group = Group::getByName($name))) {
			$group = Group::add($name, t('Added by Clov.'));
		}
		return $group;
	}
	
	/**
	 * Create and initialize Clov's single pages, page types, and blocks.
	 * Clov uses pages with custom attributes for most of its data storage.
	 */
	protected function createPagesAndBlocks() {
		// This will contain pages/page types for initializePageDefaults.
		$pagesToInitialize = array();
		
		// Add custom attribute types.
		// These custom attribute types need to exist before trying to add 
		// them to page types.
		$this->createAttributeType('user', 'collection');
		$this->createAttributeType('users', 'collection');
		$this->createAttributeType('project', 'collection');
		$this->createAttributeType('money_amount', 'collection');
		
		// Add page types and assign attributes to them.
		// createPageTypeWithAttributes will automatically add prefixes to 
		// handles (e.g. "clov_task" and "clov_task_assignee").
		$pageTypeSpecifications = $this->getPageTypeSpecifications();
		foreach($pageTypeSpecifications as $pageTypeHandle => $attributes) {
			$pagesToInitialize[] = $this->createPageTypeWithAttributes($pageTypeHandle, $attributes);
		}
		
		// Add single pages.
		$pagesToInitialize[] = SinglePage::add('/clov', $this);
		$pagesToInitialize[] = SinglePage::add('/clov/tasks', $this);
		$pagesToInitialize[] = SinglePage::add('/clov/projects', $this);
		$pagesToInitialize[] = SinglePage::add('/clov/expenses', $this);
		$pagesToInitialize[] = SinglePage::add('/clov/invoices', $this);
		$pagesToInitialize[] = SinglePage::add('/clov/timesheets', $this);
		
		$this->installBlockType('clov_task_list');
		$this->installBlockType('clov_project_list');
		$this->installBlockType('clov_expense_list');
		$this->installBlockType('clov_invoice_list');
		$this->installBlockType('clov_timesheet_entry_list');
		$this->installBlockType('clov_relative_link');
		
		self::initializeGlobalAreas();
		
		// Initialize all Clov-created pages.
		array_walk($pagesToInitialize, 'self::initializePageDefaults');
	}
	
	/**
	 * Helper function to add a package prefix to a handle.
	 * 
	 * @return string
	 */
	protected function addPrefixToHandle($handle) {
		return $this->getPackageHandle().'_'.$handle;
	}
	
	/**
	 * Make block installation a bit nicer.
	 */
	protected function installBlockType($handle) {
		// Improve error handling.
		$errorMessage = BlockType::installBlockTypeFromPackage($handle, $this);
		if($errorMessage) {
			throw new Exception($errorMessage);
		}
	}
	
	/**
	 * Create a custom attribute type and associate it with the attribute 
	 * category specified by $categoryHandle.
	 * 
	 * @return AttributeType
	 */
	protected function createAttributeType($handle, $categoryHandle) {
		// Create attribute type (if it doesn't already exist).
		$attributeType = AttributeType::getByHandle($handle);
		if(!is_object($attributeType) || !intval($attributeType->getAttributeTypeID())) {
			$attributeType = AttributeType::add($handle, Loader::helper('text')->unhandle($handle), $this);
		}
		
		// Associate attribute type with its category (if not already 
		// associated).
		$category = AttributeKeyCategory::getByHandle($categoryHandle);
		$associationExists = self::checkAttributeTypeAssociation($attributeType, $category);
		if(!$associationExists) {
			$category->associateAttributeKeyType($attributeType);
		}
		
		return $attributeType;
	}
	
	/**
	 * Check if an attribute type is already associated with a certain 
	 * attribute category.
	 * 
	 * @return boolean
	 */
	protected static function checkAttributeTypeAssociation($attributeType, $attributeKeyCategory) {
		$db = Loader::db();
		$sql = 'SELECT count(*) FROM AttributeTypeCategories WHERE atID = ? AND akCategoryID = ?';
		$values = array($attributeType->getAttributeTypeID(), $attributeKeyCategory->getAttributeKeyCategoryID());
		return (boolean) $db->getOne($sql, $values);
	}
	
	/**
	 * Create a custom page type and assign attributes to it. The page type 
	 * has its own attribute set.
	 * 
	 * @return CollectionType
	 */
	protected function createPageTypeWithAttributes($pageTypeHandle, $attributes) {
		$textHelper = Loader::helper('text');
		
		// TODO: Could also include ctIcon.
		$pageType = CollectionType::add(array(
			'ctHandle' => $this->addPrefixToHandle($pageTypeHandle), // e.g. "clov_task".
			'ctName' => $textHelper->unhandle($pageTypeHandle), // Not prefixed.
		), $this);
		
		// Create a new attribute set with the same handle as the page type.
		$collectionKeyCategory = AttributeKeyCategory::getByHandle('collection');
		$attributeSetName = $textHelper->unhandle($this->addPrefixToHandle($pageTypeHandle)).' Attributes';
		$pageTypeAttributeSet = $collectionKeyCategory->addSet($this->addPrefixToHandle($pageTypeHandle), $attributeSetName, $this);
		
		// Add attributes for the page type.
		// FIXME: Would it be better for each page type controller to add 
		// their own attributes?
		foreach($attributes as $attributeHandle => $attributeDefinition) {
			$arguments = array(
				'akHandle' => $this->addPrefixToHandle($pageTypeHandle.'_'.$attributeHandle), // e.g. "clov_task_assignee".
				'akName' => $textHelper->unhandle($attributeHandle), // Not prefixed.
			);
			// Allow specifying extra arguments.
			// These can include generic arguments like akIsSearchable, 
			// akIsSearchableIndexed, akIsAutoCreated, akIsEditable, and 
			// akIsInternal in addition to attribute type-specific arguments 
			// which get passed to the attribute type controller's saveKey() 
			// method.
			if(isset($attributeDefinition['arguments'])) {
				$arguments = array_merge($arguments, $attributeDefinition['arguments']);
			}
			$attributeKey = CollectionAttributeKey::add($attributeDefinition['type'], $arguments, $this);
			
			$attributeKey->setAttributeSet($pageTypeAttributeSet);
			
			// Add the new attribute to the new page type.
			$pageType->assignCollectionAttribute($attributeKey);
		}
		
		return $pageType;
	}
	
	/**
	 * Tell page controllers to initialize defaults for their pages. This is 
	 * used for both page types and single pages. Its argument can be either 
	 * a page type object or something that Loader::controller() can accept.
	 */
	public static function initializePageDefaults($pageTypeOrLoadableItem) {
		if($pageTypeOrLoadableItem instanceof CollectionType) {
			// For CollectionTypes, load the controller via the master template.
			$loadableItem = $pageTypeOrLoadableItem->getMasterTemplate();
		} else if($pageTypeOrLoadableItem instanceof Page) {
			// Reload the page since the passed in version may be stale 
			// (not reflect changes caused by previous initializations, etc).
			$loadableItem = Page::getByID($pageTypeOrLoadableItem->getCollectionID());
		} else {
			$loadableItem = $pageTypeOrLoadableItem;
		}
		
		$controller = Loader::controller($loadableItem);
		if(method_exists($controller, 'initializeDefaults')) {
			$controller->initializeDefaults();
		}
	}
	
	/**
	 * Add Clov-wide blocks to global areas.
	 */
	public static function initializeGlobalAreas() {
		$dashboard = Page::getByPath('/clov');
		
		// Make sure the global area is created during install (normally it 
		// wouldn't be created until the first time someone accesses a page 
		// which displays it). This will also automatically create a stack for 
		// the area.
		$navigationArea = Area::getOrCreate($dashboard, 'Clov Navigation', 1/* Indicates a global area. */);
		
		$navigationStack = Stack::getByName('Clov Navigation');
		$navigationBlockType = BlockType::getByHandle('autonav');
		// Note that even though the global area which contains this stack is 
		// named "Clov Navigation", the stack itself (which is a subclass of 
		// Page) uses an area named "Main".
		$navigationStack->addBlock($navigationBlockType, 'Main', array(
			'displayPages' => 'custom',
			'displayPagesCID' => $dashboard->cID, // Clov pages live under the dashboard in the sitemap.
			'orderBy' => 'display_asc',
		));
	}
}
