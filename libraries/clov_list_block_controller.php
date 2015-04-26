<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Base class for blocks which display a list of Clov entities.
 */
Loader::library('clov_block_controller', 'clov');
abstract class ClovListBlockController extends ClovBlockController {
	// See BlockController for more behavior-changing properties like this.
	protected $btCacheBlockRecord = true;
	
	// Required database fields.
	public $approvedPages;
	public $activePages;
	public $num;
	public $sortBy;
	public $sortByDirection;
	
	/**
	 * Get a prepared PageList object to use with the block.
	 * 
	 * @return ClovPageList
	 */
	public function getPageList() {
		Loader::model('clov_page_list', 'clov');
		$pageList = new ClovPageList;
		$pageList->setNameSpace('b'.$this->bID);
		
		// Sort the page list based on the sortBy and sortByDirection fields 
		// (if they contain valid options). Note that without validation this 
		// could be a potential SQL injection route.
		$sortByOptions = $this->getSortByOptions();
		$sortByDirectionOptions = $this->getSortByDirectionOptions();
		if(isset($sortByOptions[$this->sortBy])) {
			if(isset($sortByDirectionOptions[$this->sortByDirection])) {
				$pageList->sortBy($this->sortBy, $this->sortByDirection);
			} else {
				$pageList->sortBy($this->sortBy);
			}
		}
		
		// $this->activePages can be null|true|false. If true, only include 
		// active pages (ClovPageList's default behavior).
		if(!isset($this->activePages)) {
			// If null, include both active and inactive pages.
			$pageList->includeInactivePages();
		} else if($this->activePages == false) {
			// If false, only include inactive pages.
			$pageList->includeInactivePages();
			$pageList->filter('p1.cIsActive', 0);
		}
		
		// $this->approvedPages can be null|true|false. If true, only include 
		// approved pages (ClovPageList's default behavior).
		if(!isset($this->approvedPages)) {
			// If null, include both approved and unapproved pages.
			$pageList->displayUnapprovedPages();
		} else if($this->approvedPages == false) {
			// If false, only include unapproved pages.
			$pageList->displayUnapprovedPages();
			$pageList->filter('cvIsApproved', 0);
		}
		
		// Limit the page list based on the num field (0 means unlimited).
		$num = (int) $this->num;
		if($num > 0) {
			$pageList->setItemsPerPage($num);
		}
		
		return $pageList;
	}
	
	/**
	 * Get an array of option value => text to use as options for the  
	 * approvedPages field.
	 * 
	 * @return array
	 */
	public function getActivePagesOptions() {
		return array(
			1 => t('only non-draft'),
			0 => t('only draft'),
			'NULL' => t('draft and non-draft'),
		);
	}
	
	/**
	 * Get an array of option value => text to use as options for the  
	 * approvedPages field.
	 * 
	 * @return array
	 */
	public function getApprovedPagesOptions() {
		return array(
			1 => t('only approved'),
			0 => t('only unapproved'),
			'NULL' => t('approved and unapproved'),
		);
	}
	
	/**
	 * Get an array of option value => text to use as options for the  
	 * sortByDirection field.
	 * 
	 * @return array
	 */
	public function getSortByDirectionOptions() {
		return array(
			'asc' => t('ascending'),
			'desc' => t('descending'),
		);
	}
	
	/**
	 * Get an array of option value => text to use as options for the  
	 * sortBy field. Contains some general values for all page lists which 
	 * should generally be augmented with specific page attributes in 
	 * subclasses.
	 * 
	 * @return array
	 */
	public function getSortByOptions() {
		// See PageList model for details about these keys.
		return array(
			'p1.cDisplayOrder' => t('sitemap order'),
			'cvName' => t('name'),
			// These are some other possibilities which probably aren't useful 
			// for Clov page types:
			// 'p1.cID' => t('collection ID'),
			// 'cvDatePublic' => t('public date'),
		);
	}
	
	/**
	 * A helper method to generate sort options for a list of attribute keys.
	 * 
	 * @return array
	 */
	public function getSortByOptionsForAttributeKeys($attributeKeys) {
		// Add sorting options for all attributes.
		$options = array();
		foreach($attributeKeys as $attributeKey) {
			$value = 'ak_'.$attributeKey->getAttributeKeyHandle();
			$text = strtolower($attributeKey->getAttributeKeyName());
			$options[$value] = $text;
		}
		return $options;
	}
	
	/**
	 * Normalize some arguments and defer to the parent's save method.
	 */
	public function save($arguments) {
		$arguments['num'] = ($arguments['num'] > 0) ? $arguments['num'] : 0;
		
		parent::save($arguments);
	}
}
