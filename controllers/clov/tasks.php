<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Controller for the tasks single page.
 */
Loader::library('clov_single_page_controller', 'clov');
class ClovTasksController extends ClovSinglePageController {
	/**
	 * Set up page defaults.
	 */
	public function initializeDefaults() {
		$page = $this->getCollectionObject();
		Loader::helper('clov_page', 'clov');
		
		ClovPageHelper::addBlockByHandle($page, 'clov_task_list', array(
			'uID' => null, // null means "everyone"
		));
		ClovPageHelper::addBlockByHandle($page, 'clov_relative_link', array(
			'action' => 'clov/tasks/-/add',
			'text' => t('Add A New Task'),
			'class' => 'clov-action-link clov-add-link',
		));
		
		$page->setAttribute('exclude_nav', true);
		$page->setAttribute('exclude_sitemapxml', true);
		
		$this->initializePermissions();
	}
	
	/**
	 * Set up default permissions.
	 */
	public function initializePermissions() {
		Loader::helper('clov_permissions', 'clov');
		$page = $this->getCollectionObject();
		
		ClovPermissionsHelper::setBaselinePermissions($page);
		
		// Project managers and employees can add new tasks.
		$clovGroups = Loader::package('clov')->getGroups();
		$page->assignPermissions($clovGroups[ClovPackage::PROJECT_MANAGERS], array('add_subpage'));
		$page->assignPermissions($clovGroups[ClovPackage::EMPLOYEES], array('add_subpage'));
		
		// Only allow clov_task pages under this one.
		ClovPermissionsHelper::restrictSubpageType($page, CollectionType::getByHandle('clov_task'));
	}
	
	/**
	 * Action to allow a user to add a new task.
	 */
	public function add() {
		$entry = $this->getEntryToAdd('clov_task');
		
		// Default to the logged-in user.
		$loggedInUser = new User;
		$entry->setAttribute('clov_task_assignee', $loggedInUser->getUserID());
		
		$this->set('entry', $entry);
		$this->render('clov/default/add');
	}
	
	/**
	 * Action to allow a user to edit an existing task.
	 */
	public function edit($cID) {
		$entry = $this->getEntryToEdit($cID);
		$this->set('entry', $entry);
		$this->render('clov/default/edit');
	}
	
	/**
	 * Save a task. This is the action that both the add & edit forms 
	 * submit to.
	 */
	public function save($cID) {
		$entry = $this->getEntryToSave($cID);
		if($this->saveEntry($entry)) {
			Loader::helper('clov_url', 'clov');
			
			// Redirect to the main Clov page if the task is assigned to the 
			// logged in user (since there is a "your tasks" list there). 
			// Otherwise redirect to the generic tasks list page to make sure 
			// that the saved task is always visible.
			$loggedInUser = new User;
			if($loggedInUser->getUserID() == $entry->getAttribute('clov_task_assignee')) {
				$this->redirect('clov');
			} else {
				$this->redirect('clov/tasks');
			}
		} else {
			// Something went wrong. Show the edit view where errors can be 
			// displayed.
			$this->edit($entry->getCollectionID());
		}
	}
}
