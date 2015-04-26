<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Controller for the projects single page.
 */
Loader::library('clov_single_page_controller', 'clov');
class ClovProjectsController extends ClovSinglePageController {
	/**
	 * Set up page defaults.
	 */
	public function initializeDefaults() {
		$page = $this->getCollectionObject();
		Loader::helper('clov_page', 'clov');
		
		ClovPageHelper::addBlockByHandle($page, 'clov_project_list');
		ClovPageHelper::addBlockByHandle($page, 'clov_relative_link', array(
			'action' => 'clov/projects/-/add',
			'text' => t('Add A New Project'),
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
		
		ClovPermissionsHelper::setBaselinePermissions($page);
		
		// Project managers can add new projects.
		$clovGroups = Loader::package('clov')->getGroups();
		$page->assignPermissions($clovGroups[ClovPackage::PROJECT_MANAGERS], array('add_subpage'));
		
		// Only allow clov_project pages under this one.
		ClovPermissionsHelper::restrictSubpageType($page, CollectionType::getByHandle('clov_project'));
	}
	
	/**
	 * Action to allow a user to add a new project.
	 */
	public function add() {
		$entry = $this->getEntryToAdd('clov_project');
		
		// Add the logged-in user by default.
		$clovGroups = Loader::package('clov')->getGroups();
		$loggedInUser = new User;
		if($loggedInUser->inGroup($clovGroups[ClovPackage::PROJECT_MANAGERS])) {
			$entry->setAttribute('clov_project_managers', array($loggedInUser->getUserID()));
		} else if($loggedInUser->inGroup($clovGroups[ClovPackage::EMPLOYEES])) {
			$entry->setAttribute('clov_project_assignees', array($loggedInUser->getUserID()));
		}
		
		$this->set('entry', $entry);
		$this->render('clov/default/add');
	}
	
	/**
	 * Action to allow a user to edit an existing project.
	 */
	public function edit($cID) {
		$entry = $this->getEntryToEdit($cID);
		$this->set('entry', $entry);
		$this->render('clov/default/edit');
	}
	
	/**
	 * Save a project. This is the action that both the add & edit forms 
	 * submit to.
	 */
	public function save($cID) {
		$entry = $this->getEntryToSave($cID);
		if($this->saveEntry($entry)) {
			Loader::helper('clov_url', 'clov');
			
			// Redirect to the saved project.
			$this->redirect(ClovUrlHelper::getCollectionRoute($entry));
		} else {
			// Something went wrong. Show the edit view where errors can be 
			// displayed.
			$this->edit($entry->getCollectionID());
		}
	}
}
