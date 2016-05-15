<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Share Category'));
$site->set_config('container-type', 'container');

$id = (int) $url->get_item();

if ($id == 0) {
	header('Location: ' . $config->get('address') . '/categories/');
	exit;
}

$categories_array = $categories->get(array('user_id' => $auth->get('id'), 'get_other_data' => true, 'id' => $id, 'global' => 0));

if (count($categories_array) == 1) {
	$category = $categories_array[0];
}
else {
	header('Location: ' . $config->get('address') . '/categories/');
	exit;
}

if (isset($_POST['share'])) {
	if (!empty($_POST['user_id'])) {
		if ($auth->get('id') != (int) $_POST['user_id']) {
			if ($shares->count(array('user_id' => $auth->get('id'), 'shared_user_id' => (int) $_POST['user_id'], 'category_id' => $id)) == 0) {
				$share_id = $shares->add(
					array(
						'shared_user_id' 	=> (int) $_POST['user_id'],
						'category_id' 		=> $id,
						'access_level'		=> (int) $_POST['access_level']
					)
				);
				header('Location: ' . $config->get('address') . '/categories/view/' . $id . '/');

				exit;
			}
			else {
				$message = $language->get('Share already exists.');
			}
		}
		else {
			$message = $language->get('You cannot share to yourself.');
		}
	}
	else {
		$message = $language->get('Please Select a User');
	}
}

$users_array = $users->get();

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo $language->get('Share Category'); ?></h4>
				</div>
				<div class="pull-right">
					<p><button type="submit" name="share" class="btn btn-primary"><?php echo $language->get('Share'); ?></button></p>
				</div>
				<div class="clearfix"></div>

				<p><?php echo $language->get('Sharing a Category allows another user to access all the passwords within that Category.'); ?></p>
				
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

				<h4><?php echo safe_output($category['name']); ?></h4>
			
				<p><?php echo $language->get('User'); ?><br />
				<select name="user_id">
					<option value=""></option>
					<?php foreach ($users_array as $user) { ?>
						<option value="<?php echo (int)$user['id']; ?>"<?php if (isset($_POST['user_id']) && ($user['id'] == $_POST['user_id'])) echo ' selected="selected"'; ?>><?php echo safe_output($user['name']); ?> (<?php echo safe_output($user['username']); ?>)</option>
					<?php } ?>
				</select></p>
				<p><?php echo $language->get('Access'); ?><br />
				<select name="access_level">
					<option value="1"<?php if (isset($_POST['access_level']) && ($_POST['access_level'] == 1)) echo ' selected="selected"'; ?>><?php echo $language->get('View Only'); ?></option>
					<option value="2"<?php if (isset($_POST['access_level']) && ($_POST['access_level'] == 2)) echo ' selected="selected"'; ?>><?php echo $language->get('View, Edit and Add'); ?></option>
				</select></p>
				<br />

			</div>
		</div>
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>