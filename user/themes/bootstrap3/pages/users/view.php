<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('View User'));
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

$password_count = $passwords->count(array('user_id' => $user_id));

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<div class="col-md-3">
		<div class="well well-sm">
			<div class="pull-left">
				<h4><?php echo safe_output($language->get('User')); ?></h4>
			</div>
			<div class="pull-right">
				<p><a href="<?php echo $config->get('address'); ?>/users/edit/<?php echo (int) $user['id']; ?>/" class="btn btn-default"><?php echo safe_output($language->get('Edit')); ?></a></p>
			</div>	
			
			<div class="clearfix"></div>
		
		</div>
		
		<?php $plugins->run('view_user_sidebar_finish'); ?>

	</div>
	
	<div class="col-md-9">
		<div id="content">		

			<div class="table-responsive">
				<table class="table table-striped">
					<tr>
						<th><?php echo safe_output($language->get('Item')); ?></th>
						<th><?php echo safe_output($language->get('Value')); ?></th>
					</tr>
					<?php $i = 0; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre"><?php echo safe_output($language->get('Name')); ?></td>
						<td class="centre"><?php echo safe_output($user['name']); ?></td>
					</tr>
					<?php $i++; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre"><?php echo safe_output($language->get('Username')); ?></td>
						<td class="centre"><?php echo safe_output($user['username']); ?></td>
					</tr>
					<?php $i++; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre"><?php echo safe_output($language->get('Email')); ?></td>
						<td class="centre"><?php echo safe_output($user['email']); ?></td>
					</tr>
					<?php $i++; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre"><?php echo safe_output($language->get('Authentication Type')); ?></td>
						<td class="centre">
						<?php
							switch ($user['authentication_id']) {		
								case 2:
									echo $language->get('Active Directory');
								break;
								
								case 3:
									echo $language->get('LDAP');
								break;

								case 4:
									echo $language->get('JSON');
								break;
								
								default:
									echo $language->get('Local');
								break;
							}
						?>
						</td>
					</tr>
					<?php $i++; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre"><?php echo safe_output($language->get('Permissions')); ?></td>
						<td class="centre">
						<?php switch($user['user_level']) {
						
							case 1:
								echo safe_output($language->get('User'));
							break;
							case 2:
								echo safe_output($language->get('Administrator'));
							break;					
						}
						?>
					</tr>
					<?php $i++; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre"><?php echo safe_output($language->get('Password Count')); ?></td>
						<td class="centre"><?php echo safe_output($password_count); ?></td>
					</tr>
				</table>
			</div>

			<div class="clearfix"></div>
			<?php $plugins->run('view_user_details_finish'); ?>

		</div>
		
		<?php $plugins->run('view_user_content_finish'); ?>
	</div>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>