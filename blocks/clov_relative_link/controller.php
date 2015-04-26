<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Produces a relative link. Most of the action for this block is in db.xml, 
 * form.php, and view.php.
 */
Loader::library('clov_block_controller', 'clov');
class ClovRelativeLinkBlockController extends ClovBlockController {
	protected $btTable = 'btClovRelativeLink';
	
	public function getBlockTypeDescription() {
		return t('Creates a relative link (generated at view time).');
	}
	
	public function getBlockTypeName() {
		return t('Relative Link');
	}
}
