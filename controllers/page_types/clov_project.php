<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A controller for the "clov_project" page type.
 */
Loader::library('clov_page_type_controller', 'clov');
class ClovProjectPageTypeController extends ClovPageTypeController {
	/**
	 * Set up page defaults.
	 */
	public function initializeDefaults() {
		$page = $this->getCollectionObject();
		Loader::helper('clov_composer', 'clov');
		Loader::helper('clov_page', 'clov');
		
		ClovComposerHelper::addToComposer('clov_project', '/clov/projects');
		
		$page->setAttribute('exclude_nav', true);
		$page->setAttribute('exclude_sitemapxml', true);
		
		ClovPageHelper::addBlockByHandle($page, 'clov_invoice_list', array(
			'projectID' => '0', // '0' means "the current project" (whatever project page the list is on).
		));
		ClovPageHelper::addBlockByHandle($page, 'clov_relative_link', array(
			'action' => 'clov/invoices/-/add',
			'text' => t('Add A New Invoice'),
			'class' => 'clov-action-link clov-add-link',
		));
		
		ClovPageHelper::addBlockByHandle($page, 'clov_expense_list', array(
			'projectID' => '0',
			'approvedPages' => '1',
		));
		ClovPageHelper::addBlockByHandle($page, 'clov_expense_list', array(
			'projectID' => '0',
			'approvedPages' => '0',
		));
		ClovPageHelper::addBlockByHandle($page, 'clov_relative_link', array(
			'action' => 'clov/expenses/-/add',
			'text' => t('Add A New Expense'),
			'class' => 'clov-action-link clov-add-link',
		));
		
		ClovPageHelper::addBlockByHandle($page, 'clov_timesheet_entry_list', array(
			'projectID' => '0',
			'approvedPages' => '1',
		));
		ClovPageHelper::addBlockByHandle($page, 'clov_timesheet_entry_list', array(
			'projectID' => '0',
			'approvedPages' => '0',
		));
		ClovPageHelper::addBlockByHandle($page, 'clov_relative_link', array(
			'action' => 'clov/timesheets/-/add',
			'text' => t('Add A New Timesheet Entry'),
			'class' => 'clov-action-link clov-add-link',
		));
		
		$this->initializePermissions();
	}
	
	/**
	 * Set up default permissions.
	 */
	public function initializePermissions() {
		$page = $this->getCollectionObject();
		
		Loader::helper('clov_permissions', 'clov');
		ClovPermissionsHelper::setBaselinePermissions($page);
		
		$clovGroups = Loader::package('clov')->getGroups();
		
		// Project managers can create projects and approve them 
		// ('edit_page_contents' is needed to create composer drafts).
		$page->assignPermissions($clovGroups[ClovPackage::PROJECT_MANAGERS], array('edit_page_contents', 'approve_page_versions'));
		
		// Only managers of the specific project can edit its properties.
		$managersEntity = UserAttributePermissionAccessEntity::getOrCreate('clov_project_managers');
		ClovPermissionsHelper::assignPermissions($page, $managersEntity, array('edit_page_properties'));
		
		// Hide invoice blocks from employees.
		$blocks = $page->getBlocks();
		$projectManagersGroupEntity = GroupPermissionAccessEntity::getOrCreate($clovGroups[ClovPackage::PROJECT_MANAGERS]);
		$administratorsGroupEntity = GroupPermissionAccessEntity::getOrCreate($clovGroups[ClovPackage::ADMINISTRATORS]);
		foreach($blocks as $block) {
			$isInvoiceList = $block->getBlockTypeHandle() == 'clov_invoice_list';
			$isAddInvoiceLink = $block->getBlockTypeHandle() == 'clov_relative_link' && $block->getController()->get('action') == 'clov/invoices/-/add';
			if($isInvoiceList || $isAddInvoiceLink) {
				$block->doOverrideAreaPermissions();
				
				$viewBlockKey = PermissionKey::getByHandle('view_block');
				$viewBlockKey->setPermissionObject($block);
				$viewBlockAssignment = $viewBlockKey->getPermissionAssignmentObject();
				
				// Invoice blocks are only visible to project managers and 
				// administrators.
				$viewInvoiceBlockAccess = PermissionAccess::create($viewBlockKey);
				$viewInvoiceBlockAccess->addListItem($projectManagersGroupEntity);
				$viewInvoiceBlockAccess->addListItem($administratorsGroupEntity);
				
				// Replace the old access rules with new ones (the old rules 
				// may be shared with the page/area [even after overriding], 
				// so we can't just change them in place).
				$viewBlockAssignment->clearPermissionAssignment();
				$viewBlockAssignment->assignPermissionAccess($viewInvoiceBlockAccess);
			}
		}
	}
	
	/**
	 * Get a list of all attribute keys that specify the project's budget.
	 * 
	 * @return array
	 */
	public function getBudgetAttributeKeys() {
		$projectType = CollectionType::getByHandle('clov_project');
		$attributeKeys = $projectType->getAvailableAttributeKeys();
		return array_filter($attributeKeys, function($attributeKey) {
			// Include the attribute key if it starts with 
			// 'clov_project_budget_for'.
			return strpos($attributeKey->getAttributeKeyHandle(), 'clov_project_budget_for') === 0;
		});
	}
	
	/**
	 * Sum up all budgets to generate a total for the project.
	 * 
	 * @return numeric
	 */
	public function getTotalBudget() {
		$project = $this->getCollectionObject();
		$budgets = $this->getBudgetAttributeKeys();
		$total = 0;
		foreach($budgets as $budget) {
			$budgetValue = $project->getAttributeValueObject($budget);
			if(is_object($budgetValue)) {
				$total += $budgetValue->getValue();
			}
		}
		return $total;
	}
	
	/**
	 * Return the total project budget in a nicely-formatted way.
	 * 
	 * @return string
	 */
	public function getTotalBudgetDisplayValue() {
		return MoneyAmountAttributeTypeController::formatMoneyAmount($this->getTotalBudget());
	}
	
	/**
	 * Sum up the amounts of all invoices for this project.
	 * 
	 * @return numeric
	 */
	public function getTotalInvoicedAmount() {
		$project = $this->getCollectionObject();
		
		Loader::model('clov_page_list', 'clov');
		$invoiceList = new ClovPageList;
		$invoiceList->ignorePermissions();
		$invoiceList->filterByCollectionTypeHandle('clov_invoice');
		$invoiceList->filterByAttribute('clov_invoice_project', $project->getCollectionID());
		
		$invoices = $invoiceList->get(INF);
		$invoiceAmountKey = CollectionAttributeKey::getByHandle('clov_invoice_amount');
		
		$total = 0;
		foreach($invoices as $invoice) {
			$invoiceAmount = $invoice->getAttributeValueObject($invoiceAmountKey);
			$total += $invoiceAmount->getValue();
		}
		return $total;
	}
	
	/**
	 * Return the total invoiced amount in a nicely-formatted way.
	 * 
	 * @return string
	 */
	public function getTotalInvoicedAmountDisplayValue() {
		return MoneyAmountAttributeTypeController::formatMoneyAmount($this->getTotalInvoicedAmount());
	}
}
