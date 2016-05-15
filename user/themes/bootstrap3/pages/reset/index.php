<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Reset Password'));
$site->set_config('container-type', 'container');

$reset_form	= false;

if (isset($_GET['key']) && !empty($_GET['key']) && isset($_GET['username']) && !empty($_GET['username'])) {

	$users_array = $users->get(array('username' => $_GET['username'], 'allow_login' => 1, 'authentication_id' => 1));

	if (count($users_array) == 1) {
		$user = $users_array[0];

		if (isset($user['reset_key']) && $_GET['key'] === $user['reset_key'] && datetime() < $user['reset_expiry']) {
			$reset_form	= true;
			
			if (isset($_POST['reset'])) {
				if (!empty($_POST['password']) && ($_POST['password'] == $_POST['password2'])) {
					$users->edit(
						array(
							'id'				=> $user['id'],
							'password'			=> $_POST['password'],
							'reset_expiry'		=> '',
							'reset_key'			=> ''
						)
					);
					
					$reset_form	= false;
					header('Location: ' . $config->get('address') . '/');
					exit;
				}
				else {
					$message = 'Passwords do not match.';
				}
			}
		}
	}
	
}
else {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">
		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Reset Password')); ?></h4>
				</div>
				
				<?php if ($reset_form) { ?>
					<div class="pull-right">
						<p><button type="submit" name="reset" class="btn btn-info btn-xs"><?php echo safe_output($language->get('Reset Password')); ?></button></p>
					</div>
				<?php } ?>
				
				<div class="clearfix"></div>	
			</div>
		</div>
		
		<div class="col-md-9">
			<?php if (isset($message)) { ?>
				<div class="alert alert-danger">
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>	
			<?php if ($reset_form) { ?>
				<div class="well well-sm">
					<p><?php echo safe_output($language->get('New Password')); ?><br /><input type="password" name="password" value="<?php if (isset($_POST['username'])) echo safe_output($_POST['username']); ?>" /></p>
					<p><?php echo safe_output($language->get('New Password Again')); ?><br /><input type="password" name="password2" value="<?php if (isset($_POST['username'])) echo safe_output($_POST['username']); ?>" /></p>
					<div class="clearfix"></div>
				</div>
			<?php } else { ?>
				<div class="alert alert-danger">
					<?php echo html_output($language->get('Sorry a reset request was not found or it has expired.')); ?>
				</div>			
			<?php } ?>
		</div>
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>