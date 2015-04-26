<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Displays a list of Clov tasks.
 */
Loader::library('clov_list_block_controller', 'clov');
class ClovTaskListBlockController extends ClovListBlockController {
	protected $btTable = 'btClovTaskList';
	
	public function getBlockTypeDescription() {
		return t('List Clov tasks.');
	}
	
	public function getBlockTypeName() {
		return t('Clov Task List');
	}
	
	/**
	 * Get a pre-filtered PageList object to use with the block.
	 * 
	 * @return PageList
	 */
	public function getPageList() {
		$pageList = parent::getPageList();
		
		// Filter by assignee.
		$assignee = $this->getAssignee();
		if($assignee) {
			$pageList->filterByAttribute('clov_task_assignee', $assignee->getUserID());
		}
		
		// Only show clov_task pages.
		$pageList->filterByCollectionTypeHandle('clov_task');
		
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
			return ClovMessageHelper::generateListBlockTitle('Tasks', array(
				'approvedPages' => $this->approvedPages,
				'activePages' => $this->activePages,
				'userRelation' => $this->getAssignee(),
			));
		}
	}
	
	/**
	 * Get the assigned user that is used to filter this list, if any.
	 * 
	 * return null|User
	 */
	public function getAssignee() {
		$assignee = null;
		if(isset($this->uID)) {
			if($this->uID == 0) {
				// 0 means to filter by the logged in user.
				$assignee = new User;
			} else {
				$assignee = User::getByUserID($this->uID);
			}
		}
		return $assignee;
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
	 * Add page type specific sorting options to the default choices.
	 * 
	 * @return array
	 */
	public function getSortByOptions() {
		$pageTypeAttributeKeys = CollectionType::getByHandle('clov_task')->getAvailableAttributeKeys();
		$sortByAttributeOptions = $this->getSortByOptionsForAttributeKeys($pageTypeAttributeKeys);
		return array_merge(parent::getSortByOptions(), $sortByAttributeOptions);
	}
}
