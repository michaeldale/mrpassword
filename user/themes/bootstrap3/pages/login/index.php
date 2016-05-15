<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Login'));
$site->set_config('container-type', 'container');

if (isset($_POST['submit'])) {
	if ($config->get('captcha_extra_enabled') && (empty($_POST['captcha_input']) || strtoupper($_POST['captcha_input']) !== strtoupper($_SESSION['captcha_text']))) {
		$message = $language->get('Anti-Spam Text Incorrect');
	}
	else {
		if ($auth->login(array('username' => $_POST['username'], 'password' => $_POST['password']))) {
			if (isset($_SESSION['page'])) {
				header('Location: ' . safe_output($_SESSION['page']));
			}
			else {
				header('Location: ' . $config->get('address') . '/');
			}
			exit;
		}
		else {
			$message = $language->get('Login Failed');
		}
	}
}
$_SESSION['captcha_text'] = $captcha->get_random_text();

$login_message = $config->get('login_message');

include(ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3 col-md-offset-1">	
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo safe_output($config->get('name')); ?> - <?php echo safe_output($language->get('Login')); ?></h4>				
				</div>
				<div class="pull-right">
							
				</div>
				<div class="clearfix"></div>

				<br />
				<div class="clearfix"></div>

				<p><a href="<?php echo safe_output($config->get('address')) . '/forgot/'; ?>" class="btn btn-default"><?php echo safe_output($language->get('Forgot Password')); ?></a></p>
				<?php if ($config->get('registration_enabled')) { ?>
					<p><a href="<?php echo safe_output($config->get('address')) . '/register/'; ?>" class="btn btn-default"><?php echo safe_output($language->get('Create Account')); ?></a></p>
				<?php } ?>	
				<div class="clearfix"></div>

			</div>
			<div class="alert alert-warning"><?php echo safe_output($language->get('All login attempts are logged.')); ?></div>

		</div>
		
		<div class="col-md-6">
			<?php if (!empty($login_message)) { ?>
				<div class="alert alert-success">
					<?php echo html_output($login_message); ?>
				</div>
			<?php } ?>
			
			<?php if (isset($message)) { ?>
				<div class="alert alert-danger">
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>
			<div class="well well-sm">
				<div class="col-lg-4">
					<p><input type="text" class="form-control" name="username" placeholder="<?php echo safe_output($language->get('Username')); ?>"></p>
					<p><input type="password" class="form-control" name="password" placeholder="<?php echo safe_output($language->get('Password')); ?>" /></p>
					<?php if ($config->get('captcha_extra_enabled')) { ?>
						<p><?php echo safe_output($language->get('Anti-Spam Image')); ?><br /><img src="<?php echo safe_output($config->get('address')); ?>/captcha/" alt="captcha_image" /></p>
						<p><input type="text" class="form-control" 	name="captcha_input" value="" autocomplete="off" placeholder="<?php echo safe_output($language->get('Anti-Spam Text')); ?>" /></p>
					<?php } ?>
					<p><button type="submit" name="submit" class="btn btn-primary"><?php echo safe_output($language->get('Login')); ?></button></p>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>
	</form>
</div>
<?php include(ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>