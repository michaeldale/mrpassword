<?php
include('includes/header.php');

if (!isset($_SESSION['install_data']) || ($_SESSION['install_data']['stage'] < 4)) {
	header('Location: index.php');
}

if (isset($_POST['next'])) {
	
	if (!empty($_POST['admin_name'])) {
		if (!empty($_POST['admin_username'])) {
			if (!empty($_POST['password']) && !empty($_POST['password'])) {
				if ($_POST['password'] === $_POST['password2']) {
					$ipm_install->set_form('admin_name', $_POST['admin_name']);
					$ipm_install->set_form('admin_email', $_POST['admin_email']);
					
					$ipm_install->set_form('admin_username', $_POST['admin_username']);
					$ipm_install->set_form('admin_password', $_POST['password']);
				
					
					$_SESSION['install_data']['stage'] = 5;
					header('Location: finish.php');
				}
				else {
					$message = 'Passwords do not match';
				}
			}
			else {
				$message = 'Password Missing';
			}
		}
		else {
			$message = 'Username Empty';
		}
	}
	else {
		$message = 'Name Empty';
	}	
}

include('includes/html-header.php');

?>

<div id="page-body">
	<div id="sidebar">
		<div id="help" class="widget">
			<h2>Help</h2>
			<p><b>Admin Details</b>: This is the primary admin account.</p>
		</div>

	</div>
	<div id="box">
		<div id="content">
			<h2>Step 4 - Administrator Account Details</h2>
			
			<?php if (isset($message)) echo '<div class="message">' . ipm_htmlentities($message) . '</div>'; ?>
			<form method="post" action="<?php echo ipm_htmlentities($_SERVER['PHP_SELF']); ?>">
				<p>Name<br /><input type="text" name="admin_name" value="<?php echo ipm_htmlentities($ipm_install->form_data('admin_name')); ?>" size="50" /></p>		
				<p>Email Address (optional)<br /><input type="text" name="admin_email" value="<?php echo ipm_htmlentities($ipm_install->form_data('admin_email')); ?>" size="50" /></p>		

				
				<p>Username<br /><input type="text" name="admin_username" value="<?php echo ipm_htmlentities($ipm_install->form_data('admin_username')); ?>" size="50" /></p>		
				<p>Password<br /><input autocomplete="off" type="password" name="password" value="" size="50" /></p>		
				<p>Password Again<br /><input autocomplete="off" type="password" name="password2" value="" size="50" /></p>		
				
				<div class="right">
					<p class="seperator"><button type="submit" name="next">Install</button></p>
				</div>
			</form>
			
			<br />
			<p><a href="site.php" class="button">Back</a></p>
			
		</div>
	</div>
	<div class="clear"></div>
</div>

<?php
include('includes/html-footer.php');
?>