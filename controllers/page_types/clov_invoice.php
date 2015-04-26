<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A controller for the "clov_invoice" page type.
 */
Loader::library('clov_page_type_controller', 'clov');
class ClovInvoicePageTypeController extends ClovPageTypeController {
	/**
	 * Set up page defaults.
	 */
	public function initializeDefaults() {
		$page = $this->getCollectionObject();
		Loader::helper('clov_composer', 'clov');
		ClovComposerHelper::addToComposer('clov_invoice', '/clov/invoices');
		
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
		// Employees can't even view invoices.
		ClovPermissionsHelper::setBaselinePermissions($page, array(ClovPackage::ADMINISTRATORS, ClovPackage::PROJECT_MANAGERS));
		
		$clovGroups = Loader::package('clov')->getGroups();
		
		// Project managers can add, edit, and approve invoices.
		$page->assignPermissions($clovGroups[ClovPackage::PROJECT_MANAGERS], array('edit_page_contents', 'edit_page_properties', 'approve_page_versions'));
	}
}
