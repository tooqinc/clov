<?php  defined('C5_EXECUTE') or die('Access Denied.');
	
	// Accept a $error in a bunch of different forms.
	if(!empty($error)) {
		if($error instanceof ValidationErrorHelper) { 
			$error = $error->getList();
		} else if($error instanceof Exception) {
			$error = array($error->getMessage());
		} else if(!is_array($error)) {
			$error = array($error);
		}
	}
	
	// $info and $success should be either arrays or single messages.
	if(!empty($info)) {
		if(!is_array($info)) {
			$info = array($info);
		}
	}
	if(!empty($success)) {
		if(!is_array($success)) {
			$success = array($success);
		}
	}
?>

<?php  if(!empty($success)): ?>
	<ul class="clov-alert clov-alert-success">
		<?php  foreach($success as $successMessage): ?>
			<li><?php  echo $successMessage; ?></li>
		<?php  endforeach; ?>
	</ul>
<?php  endif; ?>

<?php  if(!empty($info)): ?>
	<ul class="clov-alert clov-alert-info">
		<?php  foreach($info as $infoMessage): ?>
			<li><?php  echo $infoMessage; ?></li>
		<?php  endforeach; ?>
	</ul>
<?php  endif; ?>

<?php  if(!empty($error)): ?>
	<ul class="clov-alert clov-alert-error">
		<?php  foreach($error as $errorMessage): ?>
			<li><?php  echo $errorMessage; ?></li>
		<?php  endforeach; ?>
	</ul>
<?php  endif; ?>
