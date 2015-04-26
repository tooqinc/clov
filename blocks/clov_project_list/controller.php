<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Displays a list of Clov projects.
 */
// TODO? Could abstract user stuff into a ClovListFilteredByUserBlockController 
// (quite the name!) to share code between this and similar blocks. Or use 
// composition.
Loader::library('clov_list_block_controller', 'clov');
class ClovProjectListBlockController extends ClovListBlockController {
	protected $btTable = 'btClovProjectList';
	
	public function getBlockTypeDescription() {
		return t('List Clov projects.');
	}
	
	public function getBlockTypeName() {
		return t('Clov Project List');
	}
	
	/**
	 * Get a pre-filtered PageList object to use with the block.
	 * 
	 * @return PageList
	 */
	public function getPageList() {
		$pageList = parent::getPageList();
		
		// Filter by user (in assignees list or managers list).
		$user = $this->getUser();
		if($user) {
			$assigneesAttribute = CollectionAttributeKey::getByHandle('clov_project_assignees');
			$userIsAssigneeCondition = $assigneesAttribute->getController()->getUserSearchCondition($user);
			
			$managersAttribute = CollectionAttributeKey::getByHandle('clov_project_managers');
			$userIsManagerCondition = $managersAttribute->getController()->getUserSearchCondition($user);
			
			$pageList->filter(false, '('.$userIsAssigneeCondition.' or '.$userIsManagerCondition.')');
		}
		
		// Only show clov_project pages.
		$pageList->filterByCollectionTypeHandle('clov_project');
		
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
			return ClovMessageHelper::generateListBlockTitle('Projects', array(
				'approvedPages' => $this->approvedPages,
				'activePages' => $this->activePages,
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
	 * Add page type specific sorting options to the default choices.
	 * 
	 * @return array
	 */
	public function getSortByOptions() {
		$pageTypeAttributeKeys = CollectionType::getByHandle('clov_project')->getAvailableAttributeKeys();
		$sortByAttributeOptions = $this->getSortByOptionsForAttributeKeys($pageTypeAttributeKeys);
		return array_merge(parent::getSortByOptions(), $sortByAttributeOptions);
	}
}
