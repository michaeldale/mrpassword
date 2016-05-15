<?php
include('includes/header.php');

if (!isset($_SESSION['install_data']) || ($_SESSION['install_data']['stage'] < 1)) {
	header('Location: index.php');
}

$system_check = array();

$system_check['php_version']	= PHP_VERSION;
$system_check['pass']			= true;

$system_check['php']			= true;
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
	$system_check['php']		= false;
	$system_check['pass']		= false;
}

$system_check['php_pdo']		= true;
if (!extension_loaded('pdo')) {
	$system_check['php_pdo']	= false;
	$system_check['pass']		= false;
}

$system_check['php_pdo_mysql']		= true;
if (!extension_loaded('pdo_mysql')) {
	$system_check['php_pdo_mysql']	= false;
	$system_check['pass']		= false;
}

$system_check['php_mcrypt']		= true;
if (!extension_loaded('mcrypt')) {
	$system_check['php_mcrypt']	= false;
	$system_check['pass']		= false;
}

$system_check['file_write']				= true;
$system_check['config_file_write']		= true;

if (isset($_GET['file_check']) && ($_GET['file_check'] == 'skip')) {

}
else {
	if (!$ipm_install->test_write()) {
		$system_check['file_write']			= false;
		$system_check['pass']				= false;
	}
	if (!$ipm_install->test_write_config()) {
		$system_check['config_file_write']	= false;
		$system_check['pass']				= false;
	}
}

$system_check['php_ldap']		= false;
if (extension_loaded('ldap')) {
	$system_check['php_ldap']	= true;
}

$system_check['php_gd']		= false;
if (extension_loaded('gd')) {
	$system_check['php_gd']	= true;
}

$system_check['php_openssl']	= false;
if (extension_loaded('openssl')) {
	$system_check['php_openssl']	= true;
}

if (file_exists(ROOT . '/user/settings/config.php')) {
	$system_check['pass'] = false;
}

$system_check['no_htaccess']			= true;
if (file_exists(ROOT . '/.htaccess')) {
	$system_check['no_htaccess']		= false;
}

if ($system_check['pass']) {
	if (isset($_POST['next'])) {
		$_SESSION['install_data']['stage'] = 2;
		header('Location: database.php');
	}
}


include('includes/html-header.php');

?>
<script type="text/javascript">
$(document).ready(function () {
	$.ajax({
		type: "GET",
		url:  "update_check.php",
		success: function(html){
			$('#latest_installer').html(html);
		}
	 });
});
</script>

<div id="page-body">
	<div id="sidebar">
		<div id="help" class="widget">
			<h2>Help</h2>
			<p>If your system is missing any Optional Components you can install them later.</p>
		</div>

	</div>
	<div id="box">
		<div id="content">
			<div class="message">If you get a 404 or 500 error after the install please check to ensure the Apache Mod Rewrite module is enabled.</div>
		</div>
		<div id="content">
			<h2>Step 1 - System Check</h2>
			<?php if (file_exists(ROOT . '/user/settings/config.php')) { ?>
				<div class="message">The config file "config.php" already exists, the install cannot continue.</div>
				<br />
				<div class="message">You must delete this file if you wish to start a new install.</div>
			<?php } else { ?>
			
				<div id="update_check"></div>

				<h3>Required Components</h3>
				<table class="data-table">
					<thead>
						<tr>
							<th>Item</th>
							<th>Pass/Fail</th>
							<th>Info</th>
						</tr>
					</thead>
					<?php $i = 0; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre">PHP Version 5.3.0+</td>
						<td class="centre"><?php if ($system_check['php']) { echo 'Pass'; } else { echo 'Fail'; } ?></td>
						<td class="centre">Required for Base System (Found <?php echo $system_check['php_version']; ?>)</td>
					</tr>
					<?php $i++; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre">PHP PDO Extension</td>
						<td class="centre"><?php if ($system_check['php_pdo']) { echo 'Pass'; } else { echo 'Fail'; } ?></td>
						<td class="centre">Required for Database System</td>
					</tr>
					<?php $i++; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre">PHP PDO MySQL Extension</td>
						<td class="centre"><?php if ($system_check['php_pdo_mysql']) { echo 'Pass'; } else { echo 'Fail'; } ?></td>
						<td class="centre">Required for Database System</td>
					</tr>
					<?php $i++; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre">PHP Mcrypt Extension</td>
						<td class="centre"><?php if ($system_check['php_mcrypt']) { echo 'Pass'; } else { echo 'Fail'; } ?></td>
						<td class="centre">Required for Security System</td>
					</tr>
					<?php $i++; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre">File Write Access 1</td>
						<td class="centre"><?php if ($system_check['file_write']) { echo 'Pass'; } else { echo 'Fail'; } ?></td>
						<td class="centre">Required to Write .htaccess File</td>
					</tr>
					<?php $i++; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre">File Write Access 2</td>
						<td class="centre"><?php if ($system_check['config_file_write']) { echo 'Pass'; } else { echo 'Fail'; } ?></td>
						<td class="centre">Required to write to user/settings/ folder</td>
					</tr>
				</table>
				
				<br />
				<h3>Optional Components</h3>
				<table class="data-table">
					<thead>
						<tr>
							<th>Item</th>
							<th>Pass/Fail</th>
							<th>Info</th>
						</tr>
					</thead>
					<?php $i = 0; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre">Latest Installer</td>
						<td class="centre" id="latest_installer">Checking...</td>
						<td class="centre">Installing the Latest Version is Recommended</td>
					</tr>
					<?php $i++; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre">PHP LDAP Extension</td>
						<td class="centre"><?php if ($system_check['php_ldap']) { echo 'Pass'; } else { echo 'Fail'; } ?></td>
						<td class="centre">Required for Active Directory Authentication</td>
					</tr>
					<?php $i++; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre">No .htaccess File</td>
						<td class="centre"><?php if ($system_check['no_htaccess']) { echo 'Pass'; } else { echo 'Fail'; } ?></td>
						<td class="centre">The installer will create the required .htaccess file.</td>
					</tr>
				</table>
				
				<?php if ($system_check['pass']) { ?>
					<br />
					<div class="right">
						<form method="post" action="<?php echo ipm_htmlentities($_SERVER['REQUEST_URI']); ?>">
							<p class="seperator"><button type="submit" name="next">Next</button></p>
						</form>	
					</div>
				<?php } else { ?>
					<br />
					<div class="message">Sorry you cannot install MrP on this server, please check your system.</div>
				<?php } ?>
			
			
			<?php } ?>
			
			<br />
			<p><a href="index.php" class="button">Back</a></p>
			
		</div>
	</div>
	<div class="clear"></div>
	<br />
</div>

<?php
include('includes/html-footer.php');
?>