<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Authentication'));
$site->set_config('container-type', 'container');

if ($auth->get('user_level') != 2) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if (isset($_POST['save'])) {

	$config->set('lockout_enabled', $_POST['lockout_enabled'] ? 1 : 0);

	$config->set('ad_server', $_POST['ad_server']);
	$config->set('ad_account_suffix', $_POST['ad_account_suffix']);
	$config->set('ad_base_dn', $_POST['ad_base_dn']);
	$config->set('ad_create_accounts', $_POST['ad_create_accounts'] ? 1 : 0);
	$config->set('ad_enabled', $_POST['ad_enabled'] ? 1 : 0);

	$config->set('ldap_server', $_POST['ldap_server']);
	$config->set('ldap_base_dn', $_POST['ldap_base_dn']);
	$config->set('ldap_create_accounts', $_POST['ldap_create_accounts'] ? 1 : 0);
	$config->set('ldap_enabled', $_POST['ldap_enabled'] ? 1 : 0);
	
	$config->set('auth_json_url', $_POST['auth_json_url']);
	$config->set('auth_json_site_id', (int) $_POST['auth_json_site_id']);
	$config->set('auth_json_key', $_POST['auth_json_key']);
	$config->set('auth_json_create_accounts', $_POST['auth_json_create_accounts'] ? 1 : 0);
	$config->set('auth_json_enabled', $_POST['auth_json_enabled'] ? 1 : 0);	
	
	$config->set('session_life_time', (int) $_POST['session_life_time']);


	$log_array['event_severity'] = 'notice';
	$log_array['event_number'] = E_USER_NOTICE;
	$log_array['event_description'] = 'Authentication Settings Edited';
	$log_array['event_file'] = __FILE__;
	$log_array['event_file_line'] = __LINE__;
	$log_array['event_type'] = 'edit';
	$log_array['event_source'] = 'authentication_settings';
	$log_array['event_version'] = '1';
	$log_array['log_backtrace'] = false;	
			
	$log->add($log_array);
	
	$message = $language->get('Settings Saved');
}


include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-right">
					<p><button type="submit" name="save" class="btn btn-primary"><?php echo safe_output($language->get('Save')); ?></button></p>
				</div>
				
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Authentication')); ?></h4>
				</div>
				

				<div class="clearfix"></div>

			</div>
		</div>
		
		<div class="col-md-9">
			
			<?php if (!extension_loaded('ldap')) { ?>
				<div class="alert alert-danger">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<?php echo html_output($language->get('Note: The PHP LDAP extension is required for Active Directory and LDAP.')); ?>
				</div>
			<?php } ?>
		
			<?php if (isset($message)) { ?>
				<div class="alert alert-success">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>
			
			<div class="well well-sm">
				<div class="col-lg-8">								

					<h3><?php echo safe_output($language->get('Authentication Settings')); ?></h3>
					
					<p><?php echo safe_output($language->get('Account Protection (user accounts are locked for 15 minutes after 5 failed logins)')); ?><br />
					<select name="lockout_enabled">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if ($config->get('lockout_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>
					
					<p><?php echo safe_output($language->get('Session Expiration Timeout')); ?><br />
					<select name="session_life_time">
						<option value="3600"><?php echo safe_output($language->get('1 Hour')); ?></option>
						<option value="7200"<?php if ($config->get('session_life_time') == 7200) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('2 Hours')); ?></option>
						<option value="14400"<?php if ($config->get('session_life_time') == 14400) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('4 Hours')); ?></option>
						<option value="21600"<?php if ($config->get('session_life_time') == 21600) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('6 Hours')); ?></option>
					</select></p>
						
				</div>
				<div class="clearfix"></div>
			</div>
		
			<div class="well well-sm">

				<div class="col-lg-6">								
				
					<h3><?php echo safe_output($language->get('Active Directory')); ?></h3>

					<p><?php echo safe_output($language->get('Enabled')); ?><br />
					<select name="ad_enabled">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if ($config->get('ad_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>
					<p><?php echo safe_output($language->get('Server (e.g. dc.example.local or 192.168.1.1)')); ?><br /><input type="text" name="ad_server" size="30" value="<?php echo safe_output($config->get('ad_server')); ?>" /></p>				
					<p><?php echo safe_output($language->get('Account Suffix (e.g. @example.local)')); ?><br /><input type="text" name="ad_account_suffix" size="30" value="<?php echo safe_output($config->get('ad_account_suffix')); ?>" /></p>
					<p><?php echo safe_output($language->get('Base DN (e.g. DC=example,DC=local)')); ?><br /><input type="text" name="ad_base_dn" size="50" value="<?php echo safe_output($config->get('ad_base_dn')); ?>" /></p>

					<p><?php echo safe_output($language->get('Create user on valid login')); ?><br />
					<select name="ad_create_accounts">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if ($config->get('ad_create_accounts') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>				
										
					<div class="clearfix"></div>
				</div>
				<div class="clearfix"></div>

			</div>
			
			<div class="well well-sm">	
				<div class="col-lg-6">								

					<h3><?php echo safe_output($language->get('LDAP')); ?></h3>

					<p><?php echo safe_output($language->get('Enabled')); ?><br />
					<select name="ldap_enabled">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if ($config->get('ldap_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>
					<p><?php echo safe_output($language->get('Server (e.g. dc.example.local or 192.168.1.1)')); ?><br /><input type="text" name="ldap_server" size="30" value="<?php echo safe_output($config->get('ldap_server')); ?>" /></p>				
					<p><?php echo safe_output($language->get('Base DN (e.g. OU=sydney,DC=example,DC=local)')); ?><br /><input type="text" name="ldap_base_dn" size="50" value="<?php echo safe_output($config->get('ldap_base_dn')); ?>" /></p>

					<p><?php echo safe_output($language->get('Create user on valid login')); ?><br />
					<select name="ldap_create_accounts">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if ($config->get('ldap_create_accounts') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>				
										
				</div>
				<div class="clearfix"></div>
			</div>


			<div class="well well-sm">	
				<div class="col-lg-6">								
		
					<h3><?php echo safe_output($language->get('JSON')); ?></h3>

					<p><?php echo safe_output($language->get('Enabled')); ?><br />
					<select name="auth_json_enabled">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if ($config->get('auth_json_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>
					<p><?php echo safe_output($language->get('URL (SSL Recommended)')); ?><br /><input type="text" class="input-xlarge" name="auth_json_url" size="30" value="<?php echo safe_output($config->get('auth_json_url')); ?>" /></p>				
					<p><?php echo safe_output($language->get('Site ID')); ?><br /><input type="text" name="auth_json_site_id" class="input-small" size="30" value="<?php echo safe_output($config->get('auth_json_site_id')); ?>" /></p>				
					
					<p><?php echo safe_output($language->get('Security Key')); ?><br /><input type="text" class="input-xlarge" name="auth_json_key" size="50" value="<?php echo safe_output($config->get('auth_json_key')); ?>" /></p>

					<p><?php echo safe_output($language->get('Create user on valid login')); ?><br />
					<select name="auth_json_create_accounts">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if ($config->get('auth_json_create_accounts') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>				
				</div>						
				<div class="clearfix"></div>

			</div>			

		</div>
	</form>
</div>

<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>