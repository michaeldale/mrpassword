<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Forgot Password'));
$site->set_config('container-type', 'container');

if (isset($_POST['reset'])) {
	if ($config->get('captcha_extra_enabled') && (empty($_POST['captcha_input']) || strtoupper($_POST['captcha_input']) !== strtoupper($_SESSION['captcha_text']))) {
		$message = $language->get('Anti-Spam Text Incorrect');
	}
	else {
		if (!empty($_POST['username'])) {
			$users_array = $users->get(array('username' => $_POST['username'], 'allow_login' => 1, 'authentication_id' => 1));
			
			if (count($users_array) == 1) {
				$user = $users_array[0];
				
				if (!empty($user['email'])) {
				
					$users->create_reset_key(array('username' => $_POST['username']));
				
					$message = $language->get('An email with a reset link has been sent to your account.');
				}
				else {
					$message = $language->get('Unable to reset password.');
				}
			}
			else {
				$message = $language->get('Unable to reset password.');
			}
		}
		else {
			$message = $language->get('Unable to reset password.');
		}
	}
}
$_SESSION['captcha_text'] = $captcha->get_random_text();

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Forgot Password')); ?></h4>
				</div>
				<div class="pull-right">
					<p><button type="submit" name="reset" class="btn btn-primary btn-xs"><?php echo safe_output($language->get('Request Reset')); ?></button></p>
				</div>
				
				<div class="clearfix"></div>
	
				<p><?php echo safe_output($language->get('If you have forgotten your password you can reset it here.')); ?></p>
				<p><?php echo safe_output($language->get('An email will be sent to your account with a reset password link. Please follow this link to complete the password reset process.')); ?></p>
			</div>
		</div>
		
		<div class="col-md-9">
			<?php if (isset($message)) { ?>
			<div class="alert alert-danger">
				<?php echo html_output($message); ?>
				<div class="clearfix"></div>
			</div>
			<?php } ?>
		
			<div class="well well-sm">
				<p><?php echo safe_output($language->get('Username')); ?><br /><input type="text" name="username" value="<?php if (isset($_POST['username'])) echo safe_output($_POST['username']); ?>" /></p>
				<?php if ($config->get('captcha_extra_enabled')) { ?>
					<p><?php echo safe_output($language->get('Anti-Spam Image')); ?><br /><img src="<?php echo safe_output($config->get('address')); ?>/captcha/" alt="captcha_image" /></p>
					<p><?php echo safe_output($language->get('Anti-Spam Text')); ?><br /><input type="text" name="captcha_input" value="" autocomplete="off" /></p>
				<?php } ?>			
				<div class="clearfix"></div>

			</div>
		</div>
	</form>
</div>

<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>