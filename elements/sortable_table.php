<?php  defined('C5_EXECUTE') or die('Access Denied.');
	
	/**
	 * A sortable table built using an ItemList (or one of its subclasses).
	 */
	
	// Ensure necessary arguments are passed in or set to default values.
	
	// Optional arguments:
	$caption = isset($caption) ? $caption : false;
	$tableClass = isset($tableClass) ? $tableClass : 'clov-sortable-table';
	$thDescClass = isset($thDescClass) ? $thDescClass : 'clov-sortable-table-active-sort-desc';
	$thAscClass = isset($thAscClass) ? $thAscClass : 'clov-sortable-table-active-sort-asc';
	$trAttributes = isset($trAttributes) ? $trAttributes : null;
	
	// Required arguments:
	if(!isset($itemList) || !($itemList instanceof ItemList)) {
		throw new Exception('An $itemList argument is required.');
	}
	if(!isset($columns) || !is_array($columns)) {
		// $columns must be an array containing:
		// 	- Sub-arrays with keys for 'name', 'value' and optionally 'column'.
		// 		- 'name' is a string to use as the column header.
		// 		- 'value' is a callback that will called once for each item 
		// 		  (passing the item object as a parameter). It must return 
		// 		  either a string or something that can be converted into a 
		// 		  string.
		// 		- 'column' is the column to sort by (see ItemList::sortBy(), 
		// 		  DatabaseItemList::sortBy(), and descendant classes for 
		// 		  details). If 'column' is left out the table column will not 
		// 		  be sortable.
		// 	- AttributeKey objects. The name will be the attribute key name and 
		// 	  and the values will be generated using 
		// 	  ClovPageHelper::getRenderedAttributeValueObject for each item.
		// 
		// For example:
		// 		$columns = array(
		// 			array(
		// 				'column' => 'cvName',
		// 				'name' => 'Page Name',
		// 				'value' => function($page) {
		// 					return $page->getCollectionName();
		// 				},
		// 			),
		// 			CollectionAttributeKey::getByHandle('attribute_key_handle'),
		// 			array(
		// 				// Not sortable because 'column' is missing.
		// 				'name' => 'Page Path',
		// 				'value' => function($page) {
		// 					return $page->getCollectionPath();
		// 				},
		// 			),
		// 		);
		throw new Exception('A $columns argument (array) is required.');
	} else {
		// Normalize $columns.
		foreach($columns as $key => &$value) {
			if($value instanceof AttributeKey) {
				// Allow attribute keys in $columns as a shortcut.
				$attributeKey = $value;
				$value = array(
					'column' => 'ak_'.$attributeKey->getAttributeKeyHandle(),
					'name' => $attributeKey->getAttributeKeyName(),
					'value' => function($item) use ($attributeKey) {
						return ClovPageHelper::getRenderedAttributeValueObject($item->getAttributeValueObject($attributeKey));
					}
				);
			}
		}
	}
	
	Loader::helper('clov_page', 'clov');
	Loader::helper('clov_version', 'clov');
	Loader::helper('clov_html', 'clov');
	
	$items = $itemList->getPage();
	
	// A few helper functions to keep the HTML below somewhat sane.
	$getThAttributes = function($spec) use ($itemList, $thDescClass, $thAscClass) {
		$attributes = array();
		if($itemList->isActiveSortColumn($spec['column'])) {
			$sortDirection = $itemList->getActiveSortDirection();
			if($sortDirection == 'desc') {
				$attributes['class'] = $thDescClass;
			} else if($sortDirection == 'asc') {
				$attributes['class'] = $thAscClass;
			}
		}
		return ClovHtmlHelper::buildAttributeString($attributes);
	};
	$getTrAttributes = function($item) use ($trAttributes) {
		if(is_callable($trAttributes)) {
			$trAttributes = $trAttributes($item);
		}
		return ClovHtmlHelper::buildAttributeString($trAttributes);
	};
?>

<table<?php  if(!empty($tableClass)) echo ' class="'.$tableClass.'"'; ?>>
	<?php  if($caption): ?>
		<caption>
			<?php  echo $caption; ?>
		</caption>
	<?php  endif; ?>
	<thead>
		<tr>
			<?php  foreach($columns as $spec): ?>
				<?php  if(isset($spec['column'])): ?>
					<?php  // Sorting link and a class to indicate current sort. ?>
					<th scope="col"<?php  echo $getThAttributes($spec); ?>>
						<a href="<?php  $itemList->getSortByURL($spec['column']); ?>"><?php  echo $spec['name']; ?></a>
					</th>
				<?php  else: ?>
					<?php  // No sorting, just the name. ?>
					<th scope="col"<?php  echo $getThAttributes($spec); ?>>
						<?php  echo $spec['name']; ?>
					</th>
				<?php  endif; ?>
			<?php  endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php  foreach($items as $item): ?>
			<tr<?php  echo $getTrAttributes($item); ?>>
				<?php  foreach($columns as $spec): ?>
					<td><?php  echo $spec['value']($item); ?></td>
				<?php  endforeach; ?>
			</tr>
		<?php  endforeach; ?>
	</tbody>
</table>
