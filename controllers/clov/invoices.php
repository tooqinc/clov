<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Controller for the invoices single page.
 */
Loader::library('clov_single_page_controller', 'clov');
class ClovInvoicesController extends ClovSinglePageController {
	/**
	 * Set up page defaults.
	 */
	public function initializeDefaults() {
		$page = $this->getCollectionObject();
		Loader::helper('clov_page', 'clov');
		
		ClovPageHelper::addBlockByHandle($page, 'clov_invoice_list');
		ClovPageHelper::addBlockByHandle($page, 'clov_relative_link', array(
			'action' => 'clov/invoices/-/add',
			'text' => t('Add A New Invoice'),
			'class' => 'clov-action-link clov-add-link',
		));
		
		$this->initializePermissions();
	}
	
	/**
	 * Set up default permissions.
	 */
	public function initializePermissions() {
		Loader::helper('clov_permissions', 'clov');
		$page = $this->getCollectionObject();
		
		// Employees can't even view invoices.
		ClovPermissionsHelper::setBaselinePermissions($page, array(ClovPackage::ADMINISTRATORS, ClovPackage::PROJECT_MANAGERS));
		
		// Project managers can add new invoices.
		$clovGroups = Loader::package('clov')->getGroups();
		$page->assignPermissions($clovGroups[ClovPackage::PROJECT_MANAGERS], array('add_subpage'));
		
		// Only allow clov_invoice pages under this one.
		ClovPermissionsHelper::restrictSubpageType($page, CollectionType::getByHandle('clov_invoice'));
	}
	
	/**
	 * Action to allow a user to add a new invoice.
	 */
	public function add() {
		$entry = $this->getEntryToAdd('clov_invoice');
		
		// If the user got here from a project page, pre-fill the project 
		// attribute.
		Loader::helper('clov_url', 'clov');
		if($project = ClovUrlHelper::loadReferrerPage('clov_project')) {
			$entry->setAttribute('clov_invoice_project', $project->getCollectionID());
		}
		
		$this->set('entry', $entry);
		$this->render('clov/default/add');
	}
	
	/**
	 * Action to allow a user to edit an existing invoice.
	 */
	public function edit($cID) {
		$entry = $this->getEntryToEdit($cID);
		$this->set('entry', $entry);
		$this->render('clov/default/edit');
	}
	
	/**
	 * Save an invoice. This is the action that both the add & edit forms 
	 * submit to.
	 */
	public function save($cID) {
		$entry = $this->getEntryToSave($cID);
		if($this->saveEntry($entry)) {
			Loader::helper('clov_url', 'clov');
			
			// Redirect to the associated project or the generic list page.
			if($projectID = $entry->getAttribute('clov_invoice_project')) {
				$project = Page::getByID($projectID);
				$this->redirect(ClovUrlHelper::getCollectionRoute($project));
			} else {
				$this->redirect('clov/invoices');
			}
		} else {
			// Something went wrong. Show the edit view where errors can be 
			// displayed.
			$this->edit($entry->getCollectionID());
		}
	}
}
