<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Settings'));
$site->set_config('container-type', 'container');

if ($auth->get('user_level') != 2) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if (isset($_POST['save'])) {

	if (!DEMO_MODE) {
		$config->set('name', $_POST['name']);
		$config->set('domain', $_POST['domain']);
		$config->set('script_path', $_POST['script_path']);
		$config->set('port_number', (int) $_POST['port_number']);
		$config->set('https', $_POST['https'] ? 1 : 0);
		$config->set('storage_enabled', $_POST['storage_enabled'] ? 1 : 0);
		$config->set('storage_path', $_POST['storage_path']);
		$config->set('login_message', $_POST['login_message']);
		
		$config->set('registration_enabled', $_POST['registration_enabled'] ? 1 : 0);
		$config->set('captcha_enabled', $_POST['captcha_enabled'] ? 1 : 0);
		$config->set('captcha_extra_enabled', $_POST['captcha_extra_enabled'] ? 1 : 0);
	
		$config->set('default_timezone', $_POST['default_timezone']);
		$config->set('default_language', $_POST['default_language']);
		$config->set('default_theme', $_POST['default_theme']);

		if (isset($_POST['default_sub_theme'])) {
			$config->set('default_theme_sub', $_POST['default_sub_theme']);
		}
		
	}
		
	$log_array['event_severity'] = 'notice';
	$log_array['event_number'] = E_USER_NOTICE;
	$log_array['event_description'] = 'Settings Edited';
	$log_array['event_file'] = __FILE__;
	$log_array['event_file_line'] = __LINE__;
	$log_array['event_type'] = 'edit';
	$log_array['event_source'] = 'settings';
	$log_array['event_version'] = '1';
	$log_array['log_backtrace'] = false;	
			
	$log->add($log_array);
	
	$message = $language->get('Settings Saved');
}

$upgrade 		= new upgrade();
$langs 			= $language->get_system_languages();
$user_langs	 	= $language->get_user_languages();
$timezones		= get_timezones();
$themes_array 	= $themes->get();


include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/settings.js"></script>

<div class="row">
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Settings')); ?></h4>
				</div>

				<div class="pull-right">
					<p><button type="submit" name="save" class="btn btn-primary"><?php echo safe_output($language->get('Save')); ?></button></p>
				</div>
				
				<div class="clearfix"></div>
				
				<label class="left-result"><?php echo safe_output($language->get('Program Version')); ?></label>
				<p class="right-result">
					<?php echo safe_output($config->get('program_version')); ?>
				</p>
				<div class="clearfix"></div>

				<label class="left-result"><?php echo safe_output($language->get('Database Version')); ?></label>
				<p class="right-result">
					<?php echo safe_output($config->get('database_version')); ?>
				</p>
				<div class="clearfix"></div>
				<br />
				<div class="pull-right">
					<p><a href="<?php echo $config->get('address'); ?>/update/" class="btn btn-default"><?php echo safe_output($language->get('Check for updates')); ?></a></p>
				</div>				
				<div class="clearfix"></div>	
			</div>
		</div>

		<div class="col-md-9">
			<?php if (($config->get('database_version') !== $upgrade->get_db_version()) || ($config->get('program_version') !== $upgrade->get_program_version())) { ?>
				<div class="alert alert-warning">
					<?php echo html_output($language->get('The database needs upgrading before you continue.')); ?>
					<br />
					<div class="pull-right">
						<p><a href="<?php echo safe_output($config->get('address')); ?>/upgrade/" class="btn btn-default"><?php echo safe_output($language->get('Upgrade')); ?></a></p>
					</div>
					<div class="clearfix"></div>
				</div>
			<?php } ?>
			<?php if ($config->get('encryption_level') == 1) { ?>
				<div class="alert alert-warning">
					<?php echo html_output($language->get('The security of this system can be improved.')); ?>
					<br />
					<div class="pull-right">
						<p><a href="http://portal.dalegroup.net/public/kb_view/21/" class="btn btn-default"><?php echo safe_output($language->get('More Information')); ?></a></p>
					</div>
					<div class="clearfix"></div>
				</div>
			<?php } ?>
			<?php if ($upgrade->update_available()) { ?>
				<div class="alert alert-success">
					<?php echo html_output($language->get('There is a software update available.')); ?>
					<br />
					<div class="pull-right">
						<p><a href="<?php echo safe_output($config->get('address')); ?>/update/info/" class="btn btn-default"><?php echo safe_output($language->get('More Information')); ?></a></p>
					</div>
					<div class="clearfix"></div>
				</div>
			<?php } ?>
			
			<?php if (DEMO_MODE) { ?>
				<div class="alert alert-warning">
					<?php echo message('Demo Mode: Changing the settings on this page is disabled.'); ?>
				</div>
			<?php } ?>
			
			<?php if (isset($message)) { ?>
				<div class="alert alert-success">
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>
			
			<div class="well well-sm">
				
				<h3><?php echo safe_output($language->get('Site Settings')); ?></h3>
				
				<p><?php echo safe_output($language->get('Site Name')); ?><br /><input type="text" name="name" value="<?php echo safe_output($config->get('name')); ?>" /></p>
					
				<p><?php echo safe_output($language->get('Domain Name (e.g example.com)')); ?><br /><input type="text" size="30" name="domain" value="<?php echo safe_output($config->get('domain')); ?>" /></p>
				<p><?php echo safe_output($language->get('Script Path (e.g /mrp)')); ?><br /><input type="text" name="script_path" value="<?php echo safe_output($config->get('script_path')); ?>" /></p>
				<p><?php echo safe_output($language->get('Port Number (80 for HTTP and 443 for Secure HTTP are the norm)')); ?><br /><input type="text" name="port_number" value="<?php echo (int)($config->get('port_number')); ?>" /></p>
				<p><?php echo safe_output($language->get('Secure HTTP (recommended, requires SSL certificate)')); ?><br />
				<select name="https">
					<option value="0"><?php echo safe_output($language->get('No')); ?></option>
					<option value="1"<?php if ($config->get('https') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
				</select></p>
				
											
				<p><?php echo safe_output($language->get('Default Timezone')); ?><br />
				<select name="default_timezone">
					<?php foreach ($timezones as $timezone) { ?>
					<option value="<?php echo safe_output($timezone); ?>"<?php if ($config->get('default_timezone') == $timezone) { echo ' selected="selected"'; } ?>><?php echo safe_output($timezone); ?></option>
					<?php } ?>
				</select>
				</p>
				
				<p><?php echo safe_output($language->get('Default Language')); ?><br />
				<select name="default_language">
					<optgroup label="System Languages">
						<?php foreach ($langs as $lang) { ?>
						<option value="<?php echo safe_output($lang['name']); ?>"<?php if ($config->get('default_language') == $lang['name']) { echo ' selected="selected"'; } ?>><?php echo safe_output($lang['nice_name']); ?></option>
						<?php } ?>
					</optgroup>
					<optgroup label="User Languages">
						<?php foreach ($user_langs as $lang) { ?>
						<option value="<?php echo safe_output($lang['name']); ?>"<?php if ($config->get('default_language') == $lang['name']) { echo ' selected="selected"'; } ?>><?php echo safe_output($lang['nice_name']); ?></option>
						<?php } ?>
					</optgroup>
				</select>
				</p>		
				
				<p><?php echo safe_output($language->get('Default Theme')); ?><br />
				<select name="default_theme" id="default_theme" data-sub-theme="<?php echo safe_output($config->get('default_theme_sub')); ?>">
					<?php foreach ($themes_array as $theme) { ?>
						<option value="<?php echo safe_output($theme['file_name']); ?>"<?php if ($config->get('default_theme') == $theme['file_name']) { echo ' selected="selected"'; } ?>><?php echo safe_output($theme['name']); ?></option>
					<?php } ?>
				</select>
				</p>
				
				<div id="default_sub_theme_area"></div>				
				
			</div>
			
			<div class="well well-sm">
			
				<h3><?php echo safe_output($language->get('Site Options')); ?></h3>

				<p><?php echo safe_output($language->get('Login Message')); ?><br /><input type="text" name="login_message" size="50" value="<?php echo safe_output($config->get('login_message')); ?>" /></p>

				<p><?php echo safe_output($language->get('Account Registration Enabled')); ?><br />
				<select name="registration_enabled">
					<option value="0"><?php echo safe_output($language->get('No')); ?></option>
					<option value="1"<?php if ($config->get('registration_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
				</select></p>
				
				<p><?php echo safe_output($language->get('Anti-Spam Captcha Enabled (helps protect the user registration page from bots)')); ?><br />
				<select name="captcha_enabled">
					<option value="0"><?php echo safe_output($language->get('No')); ?></option>
					<option value="1"<?php if ($config->get('captcha_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
				</select></p>
				
				<p><?php echo safe_output($language->get('Login Anti-Spam Captcha Enabled (helps protect the login and forgot password page from bots)')); ?><br />
				<select name="captcha_extra_enabled">
					<option value="0"><?php echo safe_output($language->get('No')); ?></option>
					<option value="1"<?php if ($config->get('captcha_extra_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
				</select></p>
				
				<?php if (!SAAS_MODE) { ?>
					<p><?php echo safe_output($language->get('File Storage Enabled (for file attachments, files are not encrypted)')); ?><br />
					<select name="storage_enabled">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if ($config->get('storage_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>
				
					<p><?php echo safe_output($language->get('File Storage Path')); ?><br /><input type="text" class="form-control" name="storage_path" size="50" value="<?php echo safe_output($config->get('storage_path')); ?>" /></p>
				<?php } ?>
								
								
				<div class="clearfix"></div>
			</div>
		</div>
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>