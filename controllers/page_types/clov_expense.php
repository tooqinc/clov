<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A controller for the "clov_expense" page type.
 */
Loader::library('clov_page_type_controller', 'clov');
class ClovExpensePageTypeController extends ClovPageTypeController {
	/**
	 * Set up page defaults.
	 */
	public function initializeDefaults() {
		$page = $this->getCollectionObject();
		Loader::helper('clov_composer', 'clov');
		ClovComposerHelper::addToComposer('clov_expense', '/clov/expenses');
		
		$page->setAttribute('exclude_nav', true);
		$page->setAttribute('exclude_sitemapxml', true);
		
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
		
		// Project managers can view, create, edit & approve expenses without 
		// restriction (view is set in baseline).
		$page->assignPermissions($clovGroups[ClovPackage::PROJECT_MANAGERS], array('edit_page_contents', 'edit_page_properties', 'approve_page_versions'));
		
		// Employees can create expenses ('edit_page_contents' is needed to be 
		// able to create composer drafts).
		$page->assignPermissions($clovGroups[ClovPackage::EMPLOYEES], array('edit_page_contents'));
		
		// The payer can view the expense and make edits but cannot change the 
		// "payer" attribute (it should default to the logged in user when 
		// creating a new expense).
		$payer = UserAttributePermissionAccessEntity::getOrCreate('clov_expense_payer');
		ClovPermissionsHelper::assignPermissions($page, $payer, array('view_page', 'view_page_versions', 'edit_page_properties'));
		ClovPermissionsHelper::disallowEditingAttributes($page, array('clov_expense_payer'), $payer);
	}
}
