<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Users'));
$site->set_config('container-type', 'container');

if ($auth->get('user_level') != 2) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$get_array = array();

if (isset($_GET['filter'])) {
	if (isset($_GET['like_search']) && !empty($_GET['like_search'])) {
		$get_array['like_search'] 	= $_GET['like_search'];
		$like_search_temp			= $_GET['like_search'];
	}
	if (isset($_GET['user_level']) && !empty($_GET['user_level'])) {
		$get_array['user_level'] 	= (int) $_GET['user_level'];
		$user_level_temp			= $_GET['user_level'];
	}
}

$users_array = $users->get($get_array);

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">	
	<div class="col-md-3">
		<div class="well well-sm">
			<div class="pull-left">
				<h4><?php echo safe_output($language->get('Users')); ?></h4>
			</div>
			
			<div class="pull-right">
				<a href="<?php echo $config->get('address'); ?>/users/add/" class="btn btn-default"><?php echo safe_output($language->get('Add')); ?></a>
			</div>
			
			<div class="clearfix"></div>

			<label class="left-result"><?php echo $language->get('Users'); ?></label>
			<p class="right-result"><?php echo count($users_array); ?></p>
			<div class="clearfix"></div>
		
		</div>
		
		<div class="well well-sm">
			<form method="get" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">
				<input type="text" class="form-control" placeholder="<?php echo $language->get('Search'); ?>" name="like_search" value="<?php if (isset($like_search_temp)) echo safe_output($like_search_temp); ?>" size="15" />
				<div class="clearfix"></div>
				<br />		
				<label class="left-result"><?php echo $language->get('Permissions'); ?></label>
				<p class="right-result">
					<select name="user_level">
						<option value=""></option>
						<option value="1"<?php if (isset($user_level_temp) && $user_level_temp == 1) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('User')); ?></option>
						<option value="2"<?php if (isset($user_level_temp) && $user_level_temp == 2) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('Administrator')); ?></option>
					</select>
				</p>
				
				<div class="clearfix"></div>				
					
				<br />
				<div class="pull-right"><p><button type="submit" name="filter" class="btn btn-info"><?php echo safe_output($language->get('Filter')); ?></button> <a href="<?php echo safe_output($config->get('address')); ?>/users/" class="btn btn-default"><?php echo safe_output($language->get('Clear')); ?></a></p></div>
				<div class="clearfix"></div>
			</form>		
		</div>

	</div>
	
	<div class="col-md-9">
		<div class="table-responsive">
			<table class="table table-striped">
				<tr>
					<th><?php echo safe_output($language->get('Name')); ?></th>
					<th><?php echo safe_output($language->get('Username')); ?></th>
					<th><?php echo safe_output($language->get('Email')); ?></th>
					<th><?php echo safe_output($language->get('Permissions')); ?></th>
				</tr>
				<?php
					$i = 0;
					foreach ($users_array as $user) {
				?>
				<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
					<td class="centre"><a href="<?php echo $config->get('address'); ?>/users/view/<?php echo (int) $user['id']; ?>/"><?php echo safe_output($user['name']); ?></a></td>
					<td class="centre"><?php echo safe_output($user['username']); ?></td>
					<td class="centre"><?php echo safe_output($user['email']); ?></td>
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
					</td>
				</tr>
				<?php $i++; } ?>
			</table>
		</div>
		<div class="clearfix"></div>

	</div>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>