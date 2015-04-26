<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Controller for the dashboard single page.
 */
class ClovController extends Controller {
	/**
	 * Set up page defaults.
	 */
	public function initializeDefaults() {
		$page = $this->getCollectionObject();
		Loader::helper('clov_page', 'clov');
		
		$page->update(array(
			'cName' => t('Dashboard'),
		));
		
		ClovPageHelper::addBlockByHandle($page, 'clov_task_list', array(
			'uID' => '0', // '0' means "logged in user"
		));
		ClovPageHelper::addBlockByHandle($page, 'clov_relative_link', array(
			'action' => 'clov/tasks/-/add',
			'text' => t('Add A New Task'),
			'class' => 'clov-action-link clov-add-link',
		));
		
		ClovPageHelper::addBlockByHandle($page, 'clov_project_list', array(
			'uID' => '0', // '0' means "logged in user"
		));
		ClovPageHelper::addBlockByHandle($page, 'clov_relative_link', array(
			'action' => 'clov/projects/-/add',
			'text' => t('Add A New Project'),
			'class' => 'clov-action-link clov-add-link',
		));
		
		// List draft timesheet entries & expenses.
		ClovPageHelper::addBlockByHandle($page, 'clov_timesheet_entry_list', array(
			'uID' => '0',
			'activePages' => '0',
		));
		ClovPageHelper::addBlockByHandle($page, 'clov_expense_list', array(
			'uID' => '0',
			'activePages' => '0',
		));
		
		$this->initializePermissions();
	}
	
	/**
	 * Set up default permissions.
	 */
	public function initializePermissions() {
		Loader::helper('clov_permissions', 'clov');
		ClovPermissionsHelper::setBaselinePermissions($this->getCollectionObject());
	}
}
