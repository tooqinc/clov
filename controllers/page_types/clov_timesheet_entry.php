<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A controller for the "clov_timesheet_entry" page type.
 */
Loader::library('clov_page_type_controller', 'clov');
class ClovTimesheetEntryPageTypeController extends ClovPageTypeController {
	/**
	 * Set up page defaults.
	 */
	public function initializeDefaults() {
		$page = $this->getCollectionObject();
		Loader::helper('clov_composer', 'clov');
		ClovComposerHelper::addToComposer('clov_timesheet_entry', '/clov/timesheets');
		
		$page->setAttribute('exclude_nav', true);
		$page->setAttribute('exclude_sitemapxml', true);
		
		// Since SelectAttributeTypeController doesn't easily allow adding 
		// options via saveKey we do it manually here instead of in the 
		// package controller.
		$codeAttributeKey = CollectionAttributeKey::getByHandle('clov_timesheet_entry_code');
		$defaultCodes = unserialize(Loader::package('clov')->config('DEFAULT_TIME_CODES'));
		foreach($defaultCodes as $defaultCode) {
			SelectAttributeTypeOption::add($codeAttributeKey, $defaultCode);
		}
		
		$this->initializePermissions();
	}
	
	/**
	 * Set up default permissions.
	 */
	public function initializePermissions() {
		$page = $this->getCollectionObject();
		
		Loader::helper('clov_permissions', 'clov');
		ClovPermissionsHelper::setBaselinePermissions($page, array(ClovPackage::ADMINISTRATORS, ClovPackage::PROJECT_MANAGERS));
		
		$clovGroups = Loader::package('clov')->getGroups();
		
		// Project managers can view, create, edit & approve timesheet entries 
		// without restriction (view is set in baseline).
		$page->assignPermissions($clovGroups[ClovPackage::PROJECT_MANAGERS], array('edit_page_contents', 'edit_page_properties', 'approve_page_versions'));
		
		// Employees can create timesheet entries ('edit_page_contents' is 
		// needed to be able to create composer drafts).
		$page->assignPermissions($clovGroups[ClovPackage::EMPLOYEES], array('edit_page_contents'));
		
		// The worker ("employee" attribute; not to be confused with the 
		// Employees group) can view the timesheet entry and make edits but 
		// cannot change the "employee" attribute (it should default to the 
		// logged in user when creating a new timesheet entry).
		$employee = UserAttributePermissionAccessEntity::getOrCreate('clov_timesheet_entry_employee');
		ClovPermissionsHelper::assignPermissions($page, $employee, array('view_page', 'view_page_versions', 'edit_page_properties'));
		ClovPermissionsHelper::disallowEditingAttributes($page, array('clov_timesheet_entry_employee'), $employee);
	}
}
