<?php
include('includes/header.php');

if (!isset($_SESSION['install_data']) || ($_SESSION['install_data']['stage'] < 3)) {
	header('Location: index.php');
}

if (isset($_POST['next'])) {
	
	if (!empty($_POST['site_name'])) {
		if (!empty($_POST['domain'])) {
	
			$ipm_install->set_form('site_name', 		$_POST['site_name']);
			$ipm_install->set_form('domain', 			strtolower($_POST['domain']));
			$ipm_install->set_form('script_path', 		ipm_remove_end_slash($_POST['script_path']));
			$ipm_install->set_form('port', 				(int) $_POST['port']);
			$ipm_install->set_form('https', 			$_POST['https'] ? 1 : 0);
			$ipm_install->set_form('default_timezone', 	$_POST['default_timezone']);
			
			$_SESSION['install_data']['stage'] = 4;
			header('Location: user.php');
		}
		else {
			$message = 'Domain Empty';
		}

	}
	else {
		$message = 'Site Name Empty';
	}
}

$timezones		= get_timezones();

include('includes/html-header.php');
?>

<div id="page-body">
	<div id="sidebar">
		<div id="help" class="widget">
			<h2>Help</h2>
			<p>All the settings on this page can be changed after install.</p>
			<br />
			<p><b>Site Name</b>: The name of the website.</p>
			<br />
			<p><b>Domain Name</b>: The domain or IP address that this site will be accessed at, e.g mrp.example.net</p>
			<br />
			<p><b>Script Path</b>: The path to the website if you're not using a subdomain e.g for www.example.net/mrp you would type /mrp. Leave blank if not required.</p>
			<br />
			<p><b>Port Number</b>: Normally 80 for HTTP or 443 for HTTPs/SSL.</p>
			<br />
			<p><b>Secure HTTP</b>: Turn this on if you are using HTTPs/SSL.</p>
		</div>

	</div>
	<div id="box">
		<div id="content">
			<h2>Step 3 - Server Details</h2>
			
			<?php if (isset($message)) echo '<div class="message">' . ipm_htmlentities($message) . '</div>'; ?>
			<form method="post" action="<?php echo ipm_htmlentities($_SERVER['PHP_SELF']); ?>">
				<p>Site Name<br /><input type="text" name="site_name" value="<?php echo ipm_htmlentities($ipm_install->form_data('site_name')); ?>" size="50" /></p>		
				<p>Domain Name<br /><input type="text" name="domain" value="<?php echo ipm_htmlentities($ipm_install->form_data('domain', strtolower($_SERVER['SERVER_NAME']))); ?>" size="50" /></p>		
				<p>Script Path<br /><input type="text" name="script_path" value="<?php echo ipm_htmlentities($ipm_install->form_data('script_path', str_replace('/install/site.php', '',  strtolower($_SERVER['PHP_SELF'])))); ?>" size="50" /></p>		
				<p>Port Number<br /><input type="text" name="port" value="<?php echo ipm_htmlentities($ipm_install->form_data('port', (int) $_SERVER['SERVER_PORT'])); ?>" size="10" /></p>		
				<p>Secure HTTP (recommended, requires SSL certificate)<br />
				<select name="https">
					<option value="0">No</option>
					<option value="1"<?php if ($ipm_install->form_data('https') == 1) { echo ' selected="selected"'; } ?>>Yes</option>
				</select></p>
				
				<p>Timezone<br />
				<select name="default_timezone">
					<option value="Australia/Sydney">Australia/Sydney</option>
					<?php foreach ($timezones as $timezone) { ?>
					<option value="<?php echo ipm_htmlentities($timezone); ?>"<?php if ($ipm_install->form_data('default_timezone') == $timezone) { echo ' selected="selected"'; } ?>><?php echo ipm_htmlentities($timezone); ?></option>
					<?php } ?>
				</select>
				</p>
				
				<div class="right">
					<p class="seperator"><button type="submit" name="next">Next</button></p>
				</div>
			</form>
			
			<br />
			<p><a href="database.php" class="button">Back</a></p>
			
		</div>
	</div>
	<div class="clear"></div>
</div>

<?php
include('includes/html-footer.php');
?>