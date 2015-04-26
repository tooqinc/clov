<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Displays a list of Clov timesheet entries.
 */
Loader::library('clov_list_block_controller', 'clov');
class ClovTimesheetEntryListBlockController extends ClovListBlockController {
	protected $btTable = 'btClovTimesheetEntryList';
	
	public function getBlockTypeDescription() {
		return t('List Clov timesheet entries.');
	}
	
	public function getBlockTypeName() {
		return t('Clov Timesheet Entry List');
	}
	
	/**
	 * Get a pre-filtered PageList object to use with the block.
	 * 
	 * @return PageList
	 */
	public function getPageList() {
		$pageList = parent::getPageList();
		
		// Filter by project.
		$project = $this->getProject();
		if($project) {
			$pageList->filterByAttribute('clov_timesheet_entry_project', $project->getCollectionID());
		}
		
		// Filter by employee.
		$user = $this->getUser();
		if($user) {
			$pageList->filterByAttribute('clov_timesheet_entry_employee', $user->getUserID());
		}
		
		// Only show clov_timesheet entry pages.
		$pageList->filterByCollectionTypeHandle('clov_timesheet_entry');
		
		return $pageList;
	}
	
	/**
	 * Generate a meaningful title if the block instance doesn't have its own 
	 * name.
	 * 
	 * @return string
	 */
	public function getTitle() {
		$blockName = $this->getBlockObject()->getBlockName();
		if(!empty($blockName)) {
			return $blockName;
		} else {
			Loader::helper('clov_message', 'clov');
			return ClovMessageHelper::generateListBlockTitle('Timesheet Entries', array(
				'approvedPages' => $this->approvedPages,
				'activePages' => $this->activePages,
				'pageRelation' => $this->getProject(),
				'userRelation' => $this->getUser(),
			));
		}
	}
	
	/**
	 * Get the user that is used to filter this list, if any.
	 * 
	 * return null|User
	 */
	public function getUser() {
		$user = null;
		if(isset($this->uID)) {
			if($this->uID == 0) {
				// 0 means to filter by the logged in user.
				$user = new User;
			} else {
				$user = User::getByUserID($this->uID);
			}
		}
		return $user;
	}
	
	/**
	 * Get an array of Users to include as filtering options.
	 * 
	 * @return User[]
	 */
	protected function getUsers() {
		$userList = new UserList;
		return $userList->get(INF);
	}
	
	/**
	 * Get an array of option value => text to use as options for the uID 
	 * field. This includes special values for "logged in user" and "any user".
	 * 
	 * @return array
	 */
	public function getUIDOptions() {
		$userOptions = array(
			0 => t('logged in user'),
			'NULL' => t('any user'),
		);
		$users = $this->getUsers();
		foreach($users as $user) {
			$userOptions[$user->uID] = $user->getUserName();
		}
		return $userOptions;
	}
	
	/**
	 * Get the project that is used to filter this list, if any.
	 * 
	 * return null|Page
	 */
	public function getProject() {
		$project = null;
		if(isset($this->projectID)) {
			if($this->projectID == 0) {
				// Grab the page this block is on and make sure it is a project 
				// page.
				$project = $this->getCollectionObject();
				if($project->getCollectionTypeHandle() != 'clov_project') {
					$projectIDOptions = $this->getProjectIDOptions();
					$this->set('error', t('%s block must be on a project page when "%s" option is selected.', $this->getBlockTypeName(), $projectIDOptions[0]));
					$project = false;
				}
			} else {
				$project = Page::getByID($this->projectID);
			}
		}
		return $project;
	}
	
	/**
	 * Get an array of option value => text to use as options for the projectID 
	 * field. This includes special values for "the current project" and "all 
	 * projects".
	 * 
	 * @return array
	 */
	public function getProjectIDOptions() {
		$projectOptions = array(
			0 => t('the current project'),
			'NULL' => t('all projects'),
		);
		$projects = CollectionType::getByHandle('clov_project')->getPages();
		foreach($projects as $project) {
			$projectOptions[$project->getCollectionID()] = $project->getCollectionName();
		}
		return $projectOptions;
	}
	
	/**
	 * Add page type specific sorting options to the default choices.
	 * 
	 * @return array
	 */
	public function getSortByOptions() {
		$pageTypeAttributeKeys = CollectionType::getByHandle('clov_timesheet_entry')->getAvailableAttributeKeys();
		$sortByAttributeOptions = $this->getSortByOptionsForAttributeKeys($pageTypeAttributeKeys);
		return array_merge(parent::getSortByOptions(), $sortByAttributeOptions);
	}
}
