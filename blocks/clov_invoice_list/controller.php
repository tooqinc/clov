<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Displays a list of Clov invoices.
 */
Loader::library('clov_list_block_controller', 'clov');
class ClovInvoiceListBlockController extends ClovListBlockController {
	protected $btTable = 'btClovInvoiceList';
	
	public function getBlockTypeDescription() {
		return t('List Clov invoices.');
	}
	
	public function getBlockTypeName() {
		return t('Clov Invoice List');
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
			$pageList->filterByAttribute('clov_invoice_project', $project->getCollectionID());
		}
		
		// Only show clov_invoice pages.
		$pageList->filterByCollectionTypeHandle('clov_invoice');
		
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
			return ClovMessageHelper::generateListBlockTitle('Invoices', array(
				'approvedPages' => $this->approvedPages,
				'activePages' => $this->activePages,
				'pageRelation' => $this->getProject(),
			));
		}
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
		$pageTypeAttributeKeys = CollectionType::getByHandle('clov_invoice')->getAvailableAttributeKeys();
		$sortByAttributeOptions = $this->getSortByOptionsForAttributeKeys($pageTypeAttributeKeys);
		return array_merge(parent::getSortByOptions(), $sortByAttributeOptions);
	}
}
