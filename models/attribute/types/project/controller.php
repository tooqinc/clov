<?php  defined('C5_EXECUTE') or die('Access Denied.');

Loader::model('attribute/types/default/controller');

class ProjectAttributeTypeController extends DefaultAttributeTypeController {
	/**
	 * Hilariously, we need a blank method overriding the parent 
	 * implementation just to get form.php to render.
	 */
	public function form() {}
	
	/**
	 * Get an array of value => text for the possible values for this 
	 * attribute type.
	 * 
	 * @return array
	 */
	public function getOptions() {
		// Skip the cache because otherwise sometimes these projects can end up 
		// corrupting permission checks performed after they are loaded.
		// FIXME: Is there a better way to solve this? What's the root 
		// cause? The bug was specifically noticed on timesheet entry code 
		// editing/setting (it occurred on one server but not another with 
		// identical cache settings, so it's somewhat magical).
		Loader::helper('clov_cache', 'clov');
		$projects = ClovCacheHelper::ignoreCache(function() {
			Loader::model('clov_page_list', 'clov');
			$pageList = new ClovPageList;
			$pageList->displayUnapprovedPages();
			$pageList->filterByCollectionTypeHandle('clov_project');
			return $pageList->get(INF);
		});
		
		$options = array(
			// The weird name here is used to be consistent with the built-in 
			// select attribute.
			'' => t('** None'),
		);
		foreach($projects as $project) {
			$options[$project->getCollectionID()] = $project->getCollectionName();
		}
		return $options;
	}
	
	/**
	 * Render as a link to the project.
	 * 
	 * @return string
	 */
	public function getDisplayValue() {
		$projectID = $this->getValue();
		if(!$projectID) {
			return t('No project');
		} else {
			$project = Page::getByID($projectID);
			Loader::helper('clov_page', 'clov');
			return ClovPageHelper::getPageAnchor($project);
		}
	}
	
	// TODO: Add a validateForm method.
}
