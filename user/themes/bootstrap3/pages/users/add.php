<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Add User'));
$site->set_config('container-type', 'container');

if ($auth->get('user_level') != 2) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if (isset($_POST['add'])) {
	if (!empty($_POST['name'])) {
		if (!empty($_POST['username'])) {
			if (!$users->check_username_taken(array('username' => $_POST['username']))) {
				if (empty($_POST['email']) || check_email_address($_POST['email'])) {
					if (!empty($_POST['password']) && (int) $_POST['authentication_id'] == 1) {
						if ($_POST['password'] == $_POST['password2']) {
							$add_array = array(
									'name' 					=> $_POST['name'], 
									'email' 				=> $_POST['email'],
									'authentication_id'		=> 1,
									'allow_login'			=> 1,
									'username'				=> $_POST['username'],
									'password'				=> $_POST['password'],
									'user_level'			=> (int) $_POST['user_level'],
									'welcome_email'			=> $_POST['welcome_email'] ? 1 : 0
							);
														
							$id = $users->add($add_array);
														
							header('Location: ' . $config->get('address') . '/users/view/' . $id . '/');
							exit;
						}
						else {
							$message = $language->get('Passwords Do Not Match');
						}
					}
					else if((int) $_POST['authentication_id'] == 2 || (int) $_POST['authentication_id'] == 3 || (int) $_POST['authentication_id'] == 4) {
						$add_array = 
							array(
								'name' 					=> $_POST['name'], 
								'email' 				=> $_POST['email'],
								'authentication_id' 	=> (int) $_POST['authentication_id'],
								'allow_login'			=> 1,
								'username'				=> $_POST['username'],
								'user_level'			=> (int) $_POST['user_level'],
								'welcome_email'			=> $_POST['welcome_email'] ? 1 : 0
							);

						$id = $users->add($add_array);
						
						header('Location: ' . $config->get('address') . '/users/view/' . $id . '/');
						exit;		
					}
					else {
						$message = $language->get('Password Empty');
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

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('New User')); ?></h4>
				</div>
				
				<div class="pull-right">
					<p><button type="submit" name="add" class="btn btn-primary"><?php echo safe_output($language->get('Add')); ?></button></p>
				</div>
				
				<div class="clearfix"></div>
				
				<p><?php echo safe_output($language->get('This page allows you to add a new user to the system.')); ?></p>
				<p><?php echo safe_output($language->get('Users can only view their own passwords.')); ?></p>
				
				<h4><?php echo safe_output($language->get('Authentication Type')); ?></h4>
				
				<p><?php echo safe_output($language->get('Local: The password is stored in the local database.')); ?></p>
				<p><?php echo safe_output($language->get('Active Directory: The password is stored in Active Directory, password fields are ignored.')); ?></p>
				<p><?php echo safe_output($language->get('Note: Active Directory must be enabled and connected to an Active Directory server in the settings page.')); ?></p>
				
				<h4><?php echo safe_output($language->get('Permissions')); ?></h4>
				<p><?php echo safe_output($language->get('Users: Can create, view and share their own passwords.')); ?></p>
				<p><?php echo safe_output($language->get('Administrators: Can create, view and share their own passwords. Add Users, view Logs and change System Settings.')); ?></p>

			</div>
		</div>

		<div class="col-md-9">
		
			<?php if (isset($message)) { ?>
				<div class="alert alert-danger">
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>
			
			<div class="well well-sm">

				<p><?php echo safe_output($language->get('Name')); ?><br /><input type="text" name="name" size="20" value="<?php if (isset($_POST['name'])) { echo safe_output($_POST['name']); } else if (isset($_GET['name'])) { echo safe_output($_GET['name']); } ?>" /></p>
				<p><?php echo safe_output($language->get('Username')); ?><br /><input type="text" name="username" value="<?php if (isset($_POST['username'])) echo safe_output($_POST['username']); ?>" /></p>
				<p><?php echo safe_output($language->get('Email (recommended)')); ?><br /><input type="text" name="email" size="30" value="<?php if (isset($_POST['email'])) { echo safe_output($_POST['email']); } else if (isset($_GET['email'])) { echo safe_output($_GET['email']); } ?>" /></p>

				<p><?php echo safe_output($language->get('Password')); ?><br /><input type="password" name="password" value="" autocomplete="off" /></p>
				<p><?php echo safe_output($language->get('Password Again')); ?><br /><input type="password" name="password2" value="" autocomplete="off" /></p>
				
				<p><?php echo safe_output($language->get('Authentication Type')); ?><br />
				<select name="authentication_id">
					<option value="1"><?php echo safe_output($language->get('Local')); ?></option>
					<option value="2"<?php if (isset($_POST['authentication_id']) && ($_POST['authentication_id'] == 2)) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('Active Directory')); ?></option>
					<option value="3"<?php if (isset($_POST['authentication_id']) && ($_POST['authentication_id'] == 3)) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('LDAP')); ?></option>
					<option value="4"<?php if (isset($_POST['authentication_id']) && ($_POST['authentication_id'] == 4)) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('JSON')); ?></option>
				</select></p>

				<p><?php echo safe_output($language->get('Permissions')); ?><br />
				<select name="user_level">
					<option value="1"<?php if (isset($_POST['user_level']) && ($_POST['user_level'] == 1)) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('User')); ?></option>
					<option value="2"<?php if (isset($_POST['user_level']) && ($_POST['user_level'] == 2)) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('Administrator')); ?></option>
				</select></p>

				<p><?php echo safe_output($language->get('Send Welcome Email')); ?><br />
				<select name="welcome_email">
					<option value="1"<?php if (isset($_POST['welcome_email']) && ($_POST['welcome_email'] == 1)) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					<option value="0"><?php echo safe_output($language->get('No')); ?></option>
				</select>		
				</p>				
					
				<div class="clear"></div>

			</div>
		</div>
	</form>

</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>