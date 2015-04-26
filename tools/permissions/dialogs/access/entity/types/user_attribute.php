<?php  defined('C5_EXECUTE') or die('Access Denied.');
	
	/**
	 * Contents of a dialog window to let the user choose an attribute key to 
	 * use with a user_attribute permission access entity.
	 */
	
	// Attribute types that can be used with the user_attribute permission 
	// access entity.
	$userAttributeTypes = array('user', 'users');
	
	// Need dashboard stylesheet for nice attribute list styles.
	$htmlHelper = Loader::helper('html');
	$view = View::getInstance();
	echo $htmlHelper->css('ccm.dashboard.css');
?>

<div id="ccm-list-wrapper">
	<?php 
		// Will iterate over all attribute keys and display ones that have 
		// types that can be used with the access entity.
		$collectionAttributeKeys = CollectionAttributeKey::getList();
	?>
	<?php  foreach($collectionAttributeKeys as $attributeKey): ?>
		<?php  $attributeTypeHandle = $attributeKey->getAttributeType()->getAttributeTypeHandle(); ?>
		<?php  if(in_array($attributeTypeHandle, $userAttributeTypes)): ?>
			<div class="ccm-attribute">
				<img class="ccm-attribute-icon" src="<?php  echo $attributeKey->getAttributeKeyIconSRC(); ?>" width="16" height="16" />
				<a href="javascript:void(0)" onclick="ccm_selectAttributeKey('<?php  echo $attributeKey->getAttributeKeyHandle(); ?>')">
					<?php  echo $attributeKey->getAttributeKeyDisplayHandle(); ?>
				</a>
			</div>
		<?php  endif; ?>
	<?php  endforeach; ?>
</div>

<?php 
	$toolURL = PermissionAccessEntityType::getByHandle('user_attribute')->getAccessEntityTypeToolsURL();
?>
<script>
	ccm_selectAttributeKey = function(akHandle) {
		jQuery.fn.dialog.showLoader();
		
		// Send the handle to the access entity tool which will respond with 
		// the entity ID.
		$.ajax('<?php  echo $toolURL; ?>', {
			dataType: 'json',
			type: 'POST',
			data: { akHandle: akHandle },
			success: function(data) {
				// Fill in the ID of the access entity so that the entity form 
				// knows what to assign.
				$('#ccm-permissions-access-entity-form input[name=peID]').val(data.peID);
				$('#ccm-permissions-access-entity-label').html('<div class="alert alert-info">' + data.label + '</div>');
			},
			error: function(response) {
				try {
					var message = $.parseJSON(response.responseText).label;
				} catch(error) {
					var message = undefined;
				}
				message = message || '<?php  echo t('Unknown Error'); ?>';
				$('#ccm-permissions-access-entity-label').html('<div class="alert alert-error">' + message + '</div>');
			},
			complete: function() {
				jQuery.fn.dialog.hideLoader();
				jQuery.fn.dialog.closeTop();
			}
		});
	};
</script>
