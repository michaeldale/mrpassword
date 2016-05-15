<?php
include('includes/header.php');

include('includes/html-header.php');

?>

<div id="page-body">
	<div id="sidebar">
		<div id="help" class="widget">
			<h2>Help</h2>
			<p>This section will include tips that will be useful during the install.</p>
		</div>
	<div class="clear"></div>
	</div>
	
	<div id="box">
		<div id="content">
			<h2>Welcome</h2>
			<p>Welcome to the Dalegroup Password Manager installer.</p>
			<p>This installer is designed to setup a new copy of Mr Password (MrP). It will create the configuration file (config.php), the MySQL database structure and the hypertext access file (.htaccess).</p>
			<br />
			<p>Please make sure you have read the documentation and agreed to the license before you start.</p>
			<p><a href="../documentation/" class="button">Documentation &amp; License</a></p>
			<br />
			<div class="message">Although there is no License Key this application is not free. <b>You may install a single copy for each purchase from Codecanyon.</b> Please do not share this application.</div>
			<br />
			<div class="message">Using HTTPs/SSL will increase the security of this system (you can setup in the Settings page after install).</div>
			
			<div class="clear"></div>
			<br />
			<div class="right">
				<p><a href="check_system.php" class="button">Next</a></p>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="clear"></div>
</div>

<?php
include('includes/html-footer.php');
?>