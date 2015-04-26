<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Helper functions for building messages to display to users.
 */
class ClovMessageHelper {
	/**
	 * List block titles follow a standard format. For example:
	 * 	- "Expenses" (all expenses in Clov)
	 * 	- "Your Unapproved Drafted Expenses For Project A"
	 * 	- "Joe's Expenses"
	 * Titles are context-aware and will not include the pageRelation name if 
	 * it is the current page.
	 * 
	 * @return string
	 */
	public static function generateListBlockTitle($itemsListed, $options = array()) {
		$titleParts = array();
		
		if(isset($options['userRelation'])) {
			$loggedInUser = new User;
			if($options['userRelation']->getUserID() == $loggedInUser->getUserID()) {
				$titleParts[] = t('Your');
			} else {
				// This looks awkward; it's meant to allow people to translate 
				// the possessive.
				// TODO? Could link-ify the user name, but probably only 
				// if this isn't on the user's profile page.
				$titleParts[] = t('%s\'s', $options['userRelation']->getUserName());
			}
		}
		
		// If unset, list is showing both approved and unapproved.
		if(isset($options['approvedPages'])) {
			if($options['approvedPages'] == false) {
				$titleParts[] = t('Unapproved');
			} else {
				$titleParts[] = t('Approved');
			}
		}
		
		// If false, list is only showing inactive pages (which in Clov are 
		// used for drafts).
		if(isset($options['activePages']) && $options['activePages'] == false) {
			$titleParts[] = t('Drafted');
		}
		
		$titleParts[] = t($itemsListed);
		
		if(isset($options['pageRelation']) && $options['pageRelation']->getCollectionID() != Page::getCurrentPage()->getCollectionID()) {
			// TODO? Could link-ify the page name.
			$titleParts[] = t('For %s', $options['pageRelation']->getCollectionName());
		}
		
		return ucfirst(implode(' ', $titleParts));
	}
}
