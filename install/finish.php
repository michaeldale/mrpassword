<?php
include('includes/header.php');

if (!isset($_SESSION['install_data']) || ($_SESSION['install_data']['stage'] < 5)) {
	header('Location: index.php');
}

$ipm_install->connect_db();

include('includes/html-header.php');

?>

<div id="page-body">
	<div id="sidebar">
		<div id="help" class="widget">
			<h2>Help</h2>
			<p>The install process may take 0-1 minutes, please do not leave this page until the install is completed.</p>
		</div>

	</div>
	<div id="box">
		<div id="content">
			<h2>Step 7 - Installing</h2>
			<?php if (!$ipm_install->is_installed()) { ?>
				<?php $ipm_install->install_db(); ?>
				<p>The database install has been completed.</p>
				<br />
				<?php if (false == $config_result = $ipm_install->write_htaccess()) { ?>
					<div class="message">Unable to write .htaccess file.</div>
					<br />
				<?php } else { ?>
					<p>The .htaccess file has been written successfully.</p>
				<?php } ?>
				
				<?php if (false == $config_result = $ipm_install->write_config()) { ?>
					<?php echo $config_result; ?>
					<div class="message">Unable to write config file.</div>
					<br />
				<?php } else { ?>
					<p>The config file has been written successfully.</p>
					<br />
					<div class="message">The install has been completed.</div>
				<?php } ?>
				<?php session_destroy(); ?>
				<br /><br />
				<p>You should now delete the install/ folder, this folder is only required for the first install.</p>
				<br /><br />
				<p><a href="../" class="button">Login</a></p>
			<?php } else { ?>
				<p>The database selected is not empty and cannot be used.</p>
			<?php } ?>
		</div>
	</div>
	<div class="clear"></div>
</div>

<?php
include('includes/html-footer.php');
?>