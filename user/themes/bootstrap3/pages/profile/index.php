<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Profile'));
$site->set_config('container-type', 'container');

if (isset($_POST['save'])) {		
	$message = $language->get('Profile Updated');
	if ($auth->get('authentication_id') == 1) {
		if (!empty($_POST['current_password']) && !empty($_POST['password']) && !empty($_POST['password2'])) {
			if ($auth->test_password(array('id' => $auth->get('id'), 'password' => $_POST['current_password']))) {	
				if ($_POST['password'] === $_POST['password2']) {
					$users->edit(
						array(
							'id'				=> $auth->get('id'),
							'password'			=> $_POST['password'],
						)
					);
					$message = $language->get('Password Changed');
				}
				else {
					$message = $language->get('New Passwords Do Not Match');
				}
			}
			else {
				$message = $language->get('Current Password Incorrect');
			}
		}
	}
}

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">
		
		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Profile')); ?></h4>
				</div>
				<div class="pull-right">
					<p><button type="submit" name="save" class="btn btn-primary"><?php echo safe_output($language->get('Save')); ?></button></p>
				</div>		
			
				<div class="clearfix"></div>
				
				<label class="left-result"><?php echo safe_output($language->get('Name')); ?></label>
				<p class="right-result">
					<?php echo safe_output(ucwords($auth->get('name'))); ?>
				</p>		
				<div class="clearfix"></div>
				
				<label class="left-result"><?php echo safe_output($language->get('Username')); ?></label>
				<p class="right-result">
					<?php echo safe_output($auth->get('username')); ?>
				</p>					
				<div class="clearfix"></div>
				<label class="left-result"><?php echo safe_output($language->get('Email')); ?></label>
				<p class="right-result">
					<?php echo safe_output($auth->get('email')); ?>
				</p>	

				<div class="clearfix"></div>
			
			</div>
		</div>
		
		<div class="col-md-9">
		
			<?php if (isset($message)) { ?>
				<div class="alert alert-success">
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>

			<div class="well well-sm">
				<h4><?php echo safe_output($language->get('Change Password')); ?></h4>			
				<?php if ($auth->get('authentication_id') == 1) { ?>
					<p><?php echo safe_output($language->get('Current Password')); ?><br /><input type="password" name="current_password" value="" autocomplete="off" /></p>
					<p><?php echo safe_output($language->get('New Password')); ?><br /><input type="password" name="password" value="" autocomplete="off" /></p>
					<p><?php echo safe_output($language->get('New Password Again')); ?><br /><input type="password" name="password2" value="" autocomplete="off" /></p>
				<?php } else { ?>
					<div class="alert alert-warning">
						<?php echo safe_output($language->get('You cannot change the password for this account.')); ?>
					</div>
				<?php } ?>
				
				<div class="clearfix"></div>

			</div>
		</div>
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>