<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Email Settings'));
$site->set_config('container-type', 'container');

if ($auth->get('user_level') != 2) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if (isset($_POST['save'])) {

	$config->set('smtp_enabled', 		$_POST['smtp_enabled'] ? 1 : 0);
	$config->set('smtp_email_address', 	$_POST['smtp_email_address']);
	$config->set('smtp_server', 		$_POST['smtp_server']);
	$config->set('smtp_auth', 			$_POST['smtp_auth'] ? 1 : 0);
	$config->set('smtp_username', 		$_POST['smtp_username']);
	$config->set('smtp_password', 		$_POST['smtp_password']);
	$config->set('smtp_tls', 			$_POST['smtp_tls'] ? 1 : 0);
	$config->set('smtp_port', 			(int) $_POST['smtp_port']);
	$config->set('notification_new_user_subject', $_POST['notification_new_user_subject']);
	$config->set('notification_new_user_body', $_POST['notification_new_user_body']);

	
	$log_array['event_severity'] = 'notice';
	$log_array['event_number'] = E_USER_NOTICE;
	$log_array['event_description'] = 'Email Settings Edited';
	$log_array['event_file'] = __FILE__;
	$log_array['event_file_line'] = __LINE__;
	$log_array['event_type'] = 'edit';
	$log_array['event_source'] = 'email_settings';
	$log_array['event_version'] = '1';
	$log_array['log_backtrace'] = false;	
			
	$log->add($log_array);
	
	$message = $language->get('Settings Saved');
}

if (isset($_POST['test_cron'])) {
	$cron->run();
	
	$message = $language->get('Cron has been run.');
}

if (isset($_POST['test'])) {
	$smtp_server_address = $config->get('smtp_server');
	if (!empty($smtp_server_address)) {
		if (!empty($_POST['test_email'])) {
			$test_array['subject']			= $config->get('name') . ' - Test Email';
			$test_array['body']				= 'This is a test email.';
			$test_array['to']['to']			= $_POST['test_email'];
			$test_array['to']['to_name']	= 'Test';
				
			if ($mailer->send_email($test_array)) {
				$message =  $language->get('Test Email Sent');
			}
			else {
				$message =  $language->get('Test Email Failed. View the logs for more details.');
			}
		}
		else {
			$message =  $language->get('Test Email Failed. Email address was empty.');
		}
	}
	else {
		$message =  $language->get('Test Email Failed. SMTP server not set.');
	}
}


include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Email Settings')); ?></h4>
				</div>
				
				<div class="pull-right">
					<p><button type="submit" name="save" class="btn btn-primary"><?php echo safe_output($language->get('Save')); ?></button></p>
				</div>
				
				<div class="clearfix"></div>
				<br />
				<p><?php echo safe_output($language->get('Please ensure that you have the cron system setup, otherwise emails will not be sent.')); ?></p>
				<div class="pull-right">
					<p><button type="submit" name="test_cron" class="btn btn-info"><?php echo safe_output($language->get('Run Cron Manually')); ?></button></p>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Test Email')); ?></h4>
				</div>
				
				<div class="pull-right">
					<p><button type="submit" name="test" class="btn btn-info"><?php echo safe_output($language->get('Send Test')); ?></button></p>
				</div>
				
				<div class="clearfix"></div>
				
				<p><?php echo safe_output($language->get('Email Address')); ?><br /><input type="text" name="test_email" size="20" value="" /></p>		
				
				<div class="clearfix"></div>

			</div>
		</div>

		<div class="col-md-9">
			<?php if (isset($message)) { ?>
				<div class="alert alert-success">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>
			<div class="well well-sm">
			
				<p><?php echo safe_output($language->get('SMTP Enabled')); ?><br />
				<select name="smtp_enabled">
					<option value="0"><?php echo safe_output($language->get('No')); ?></option>
					<option value="1"<?php if ($config->get('smtp_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
				</select></p>
				
				<p><?php echo safe_output($language->get('Hostname (i.e mail.example.com)')); ?><br /><input type="text" name="smtp_server" size="30" value="<?php echo safe_output($config->get('smtp_server')); ?>" /></p>
				
				<p><?php echo safe_output($language->get('Port (default 25)')); ?><br /><input type="text" name="smtp_port" size="5" value="<?php echo safe_output($config->get('smtp_port')); ?>" /></p>

				<p><?php echo safe_output($language->get('TLS (required for gmail and other servers that use SSL)')); ?><br />
				<select name="smtp_tls">
					<option value="0"><?php echo safe_output($language->get('No')); ?></option>
					<option value="1"<?php if ($config->get('smtp_tls') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
				</select></p>

				<p><?php echo safe_output($language->get('Email Address')); ?><br /><input type="text" name="smtp_email_address" size="30" value="<?php echo safe_output($config->get('smtp_email_address')); ?>" /></p>

				<p><?php echo safe_output($language->get('Authentication')); ?><br />
				<select name="smtp_auth">
					<option value="0"><?php echo safe_output($language->get('No')); ?></option>
					<option value="1"<?php if ($config->get('smtp_auth') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
				</select></p>
						
				<p><?php echo safe_output($language->get('Username')); ?><br /><input type="text" name="smtp_username" size="30" value="<?php echo safe_output($config->get('smtp_username')); ?>" /></p>
				<p><?php echo safe_output($language->get('Password')); ?><br /><input type="password" name="smtp_password" size="30" value="<?php echo safe_output($config->get('smtp_password')); ?>" /></p>			
							
				<div class="clear"></div>

			</div>
			
			<div class="well well-sm">
				<h4><?php echo safe_output($language->get('Email Notification Templates')); ?></h4>
				<br />
							
				<p><b><?php echo safe_output($language->get('New User (Welcome Email)')); ?></b></p>
				
				<p><?php echo safe_output($language->get('Subject')); ?><br /><input type="text" name="notification_new_user_subject" size="50" value="<?php echo safe_output($config->get('notification_new_user_subject')); ?>" /></p>
				
				<div id="no_underline">
					<p><?php echo safe_output($language->get('Body')); ?><br />
						<textarea class="wysiwyg_enabled" name="notification_new_user_body" cols="80" rows="12"><?php echo safe_output($config->get('notification_new_user_body')); ?></textarea>
					</p>
				</div>
			</div>
		</div>
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>