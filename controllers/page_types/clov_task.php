<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A controller for the "clov_task" page type.
 */
Loader::library('clov_page_type_controller', 'clov');
class ClovTaskPageTypeController extends ClovPageTypeController {
	/**
	 * Set up page defaults.
	 */
	public function initializeDefaults() {
		$page = $this->getCollectionObject();
		Loader::helper('clov_composer', 'clov');
		ClovComposerHelper::addToComposer('clov_task', '/clov/tasks');
		
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
		
		// Project managers can view, create, edit & approve taks without 
		// restriction (view is set in baseline).
		$page->assignPermissions($clovGroups[ClovPackage::PROJECT_MANAGERS], array('edit_page_contents', 'edit_page_properties', 'approve_page_versions'));
		
		// Employees can create tasks ('edit_page_contents' is needed to be 
		// able to create composer drafts).
		$page->assignPermissions($clovGroups[ClovPackage::EMPLOYEES], array('edit_page_contents'));
		
		// The assignee can view the task and make edits but cannot change the 
		// "assignee" attribute (it should default to the logged in user when 
		// creating a new task). They can also approve edits.
		$assignee = UserAttributePermissionAccessEntity::getOrCreate('clov_task_assignee');
		ClovPermissionsHelper::assignPermissions($page, $assignee, array('view_page', 'view_page_versions', 'edit_page_properties', 'approve_page_versions'));
		ClovPermissionsHelper::disallowEditingAttributes($page, array('clov_task_assignee'), $assignee);
	}
	
	/**
	 * Action to mark a task as completed.
	 */
	public function complete() {
		if($this->isPost()) {
			$page = $this->getCollectionObject();
			$pagePermissions = new Permissions($page);
			$completed = CollectionAttributeKey::getByHandle('clov_task_completed');
			if($pagePermissions->canEditPageProperties($completed)) {
				$page->setAttribute($completed, true);
				
				// FIXME: This seems brittle, is there a better way?
				// Probably better to redirect back to referrer, since it'd 
				// be nice to be able to mark tasks as completed from different 
				// pages.
				$this->redirect('clov/tasks/'.$this->getView());
			} else {
				throw new Exception(t('You are not allowed to mark this task completed.'));
			}
		}
	}
}
