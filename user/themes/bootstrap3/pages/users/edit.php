<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Edit User'));
$site->set_config('container-type', 'container');

if ($auth->get('user_level') != 2) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$user_id = (int) $url->get_item();

if ($user_id == 0) {
	header('Location: ' . $config->get('address') . '/users/');
	exit;
}

$users_array = $users->get(array('id' => $user_id));

if (count($users_array) == 1) {
	$user = $users_array[0];
}
else {
	header('Location: ' . $config->get('address') . '/users/');
	exit;
}

if (isset($_POST['save'])) {
	if (!empty($_POST['name'])) {
		if (!empty($_POST['username'])) {
			if (!$users->check_username_taken(array('username' => $_POST['username'], 'id' => $user_id))) {
				if (empty($_POST['email']) || check_email_address($_POST['email'])) {
					if (!empty($_POST['password']) && ((int) $_POST['authentication_id'] == 1)) {
						if ($_POST['password'] == $_POST['password2']) {
						
							$edit_array =
								array(
									'id'					=> $user_id,
									'name' 					=> $_POST['name'], 
									'email' 				=> $_POST['email'],
									'authentication_id' 	=> 1,
									'allow_login'			=> 1,
									'username'				=> $_POST['username'],
									'password'				=> $_POST['password'],
									'user_level'			=> (int) $_POST['user_level'],
								);
															
							$users->edit($edit_array);
							
							header('Location: ' . $config->get('address') . '/users/view/' . $user_id . '/');
							exit;
						}
						else {
							$message = $language->get('Passwords Do Not Match');
						}
					}
					else {
					
						$edit_array = 
							array(
								'id'					=> $user_id,
								'name' 					=> $_POST['name'], 
								'email' 				=> $_POST['email'],
								'authentication_id' 	=> (int) $_POST['authentication_id'],
								'allow_login'			=> 1,
								'username'				=> $_POST['username'],
								'user_level'			=> (int) $_POST['user_level']
							);

						$users->edit($edit_array);
						
						header('Location: ' . $config->get('address') . '/users/view/' . $user_id . '/');
						exit;
					}
				}
				else {
					$message = $language->get('Email Address Invalid');
				}
			}
			else {
				$message = $language->get('Username In Use');
			}
		}
		else {
			$message = $language->get('Username Empty');
		}
	}
	else {
		$message = $language->get('Name Empty');
	}
}

if (isset($_POST['delete'])) {
	if ($auth->get('id') !== $user['id']) {
		$users->delete(array('id' => $user_id));
		header('Location: ' . $config->get('address') . '/users/');
		exit;
	}
}

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>

	<script type="text/javascript">
		$(document).ready(function () {
			$('#delete').click(function () {			
				if (confirm("<?php echo safe_output($language->get('Are you sure you wish to delete this user and their passwords?')); ?>")){
					return true;
				}
				else{
					return false;
				}
			});
		});
	</script>
<div class="row">

	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">	
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('User')); ?></h4>
				</div>
				<div class="pull-right">
					<p>
						<button type="submit" name="save" class="btn btn-info"><?php echo safe_output($language->get('Save')); ?></button>
						<a href="<?php echo $config->get('address'); ?>/users/view/<?php echo (int) $user['id']; ?>/" class="btn btn-default"><?php echo safe_output($language->get('Cancel')); ?></a>
					</p>
				</div>
				
				<div class="clearfix"></div>
			
				<div class="pull-right">
					<?php if ($auth->get('id') == $user['id']) { ?>
						<p><?php echo safe_output($language->get('You cannot delete yourself.')); ?></p>
					<?php } else { ?>
						<p class="seperator"><button type="submit" id="delete" name="delete" class="btn btn-danger"><?php echo safe_output($language->get('Delete')); ?></button></p>		
					<?php } ?>
				</div>
				
				<div class="clearfix"></div>

			</div>
		</div>

		<div class="col-md-9">	
			<div class="well well-sm">
				
				<?php if (isset($message)) echo message($message); ?>

				<p><?php echo safe_output($language->get('Name')); ?><br /><input type="text" name="name" size="20" value="<?php echo safe_output($user['name']); ?>" /></p>
				<p><?php echo safe_output($language->get('Username')); ?><br /><input type="text" name="username" value="<?php echo safe_output($user['username']); ?>" /></p>
				<p><?php echo safe_output($language->get('Email (recommended)')); ?><br /><input type="text" name="email" size="30" value="<?php echo safe_output($user['email']); ?>" /></p>
				
				<p><?php echo safe_output($language->get('Password (if blank the password is not changed)')); ?><br /><input type="password" name="password" value="" autocomplete="off" /></p>
				<p><?php echo safe_output($language->get('Password Again')); ?><br /><input type="password" name="password2" value="" autocomplete="off" /></p>
				
				<p><?php echo safe_output($language->get('Authentication Type')); ?><br />
				<select name="authentication_id">
					<option value="1"><?php echo safe_output($language->get('Local')); ?></option>
					<option value="2"<?php if ($user['authentication_id'] == 2) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('Active Directory')); ?></option>
					<option value="3"<?php if ($user['authentication_id'] == 3) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('LDAP')); ?></option>
					<option value="4"<?php if ($user['authentication_id'] == 4) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('JSON')); ?></option>
				</select></p>
				
				<p><?php echo safe_output($language->get('Permissions')); ?><br />
				<select name="user_level">
					<option value="1"<?php if ($user['user_level'] == 1) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('User')); ?></option>
					<option value="2"<?php if ($user['user_level'] == 2) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('Administrator')); ?></option>
				</select></p>

				<div class="clear"></div>

			</div>
		</div>
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>