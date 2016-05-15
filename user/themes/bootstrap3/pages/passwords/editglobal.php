<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Edit Global Password'));
$site->set_config('container-type', 'container');

$id = (int) $url->get_item();

if ($id == 0) {
	header('Location: ' . $config->get('address') . '/passwords/global/');
	exit;
}

$passwords_array = $passwords->get(array('id' => $id, 'shared_user_id' => $auth->get('id'), 'old' => 0, 'get_other_data' => true, 'global' => 1));

if (count($passwords_array) == 1) {
	$password = $passwords_array[0];
	if ($password['access_level'] != 2) {
		header('Location: ' . $config->get('address') . '/passwords/global/');
		exit;
	}
}
else {
	header('Location: ' . $config->get('address') . '/passwords/global/');
	exit;
}

if (isset($_POST['save'])) {
	if (!empty($_POST['name'])) {
		if (!empty($_POST['password'])) {
			$passwords->edit(
				array(
					'id'				=> $id,
					'name' 				=> $_POST['name'], 
					'username'			=> $_POST['username'],
					'password'			=> $_POST['password'],
					'url'				=> $_POST['url'],
					'description'		=> $_POST['description']
				)
			);
			
			$custom_fields->delete(array('password_id' => $id));
			
			$i = 0;
			foreach ($_POST['item_name'] as $name) {
				if (!empty($name)) {
					$item_array['name']			= $name;
					$item_array['value']		= $_POST['item_value'][$i];
					$item_array['password_id']	= $id;
					$custom_fields->add($item_array);
				}
				$i++;
			}
			
			header('Location: ' . $config->get('address') . '/passwords/viewglobal/' . $id . '/');
			exit;
		}
		else {
			$message = $language->get('Password Empty');
		}
	}
	else {
		$message = $language->get('Name Empty');
	}
}

if ((int)$password['category_id'] != 0) {
	$category_names 		= $categories->get(array('shared_user_id' => $auth->get('id'), 'get_other_data' => true, 'id' => $password['category_id'], 'limit' => 1, 'global' => 1));
}

if (!empty($category_names)) {
	$category_name = $category_names[0]['name'];
}


$fields = $custom_fields->get(array('password_id' => $id));

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<script type="text/javascript">
	$(document).ready(function () {
		$('.nav-tabs').button();
		$('.default-toggle').button('toggle');
	});
</script>
<div class="row">
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">
		
		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Global Password')); ?></h4>
				</div>
					
				<div class="pull-right">
					<button type="submit" name="save" class="btn btn-primary"><?php echo safe_output($language->get('Save')); ?></button>  
					<a href="<?php echo $config->get('address'); ?>/passwords/viewglobal/<?php echo (int) $password['id']; ?>/" class="btn btn-default">
					<?php echo safe_output($language->get('Cancel')); ?></a>
				</div>
				
				<div class="clearfix"></div>
				
				<br />
				
			</div>
			<div class="well well-sm">
				<h4><?php echo $language->get('Generate Settings'); ?></h4>
				
				<div class="btn-group" data-toggle="buttons">
					<label class="btn btn-primary btn-sm">
						<input type="radio" name="password_type" id="option1" value="1"> <?php echo $language->get('Easy'); ?>
					</label>
					<label class="btn btn-primary btn-sm">
						<input type="radio" name="password_type" id="option2" value="2"> <?php echo $language->get('Simple'); ?>
					</label>
					<label class="btn btn-primary btn-sm default-toggle">
						<input type="radio" name="password_type" id="option3" value="3" checked="checked"> <?php echo $language->get('Complex'); ?> 
					</label>
				</div>			
				
				<div class="clearfix"></div>

			</div>
		</div>

	<div class="col-md-9">
		<?php if (isset($message)) { ?>
			<div class="alert alert-danger">
				<?php echo html_output($message); ?>
			</div>
		<?php } ?>
		<div class="well well-sm">
			
			<p><?php echo safe_output($language->get('Name (aka Nickname)')); ?><br /><input type="text" name="name" value="<?php echo safe_output($password['name']); ?>" /></p>
			
			<p><?php echo safe_output($language->get('Category')); ?><br />
			<?php if (isset($category_name)) echo safe_output($category_name); ?>
			</p>
			
			<p><?php echo safe_output($language->get('Username (optional)')); ?><br /><input type="text" size="50" name="username" value="<?php echo safe_output($password['username']); ?>" /></p>
			<p><?php echo safe_output($language->get('Password')); ?><br /><input type="text" class="generate_field" size="50" autocomplete="off" name="password" value="<?php echo safe_output($encryption->decrypt($password['password'])); ?>" /> <a class="generate_password btn btn-info btn-sm" href="#"><?php echo safe_output($language->get('Generate')); ?></a></p>
			<p><?php echo safe_output($language->get('URL (optional)')); ?><br /><input type="text" size="50" name="url" value="<?php echo safe_output($password['url']); ?>" /></p>
			<p><?php echo safe_output($language->get('Description (optional)')); ?><br />
			<textarea name="description" cols="50" rows="6"><?php echo safe_output($password['description']); ?></textarea>
			</p>
					
			<h3 name="custom"><?php echo safe_output($language->get('Custom Fields')); ?> <a href="#custom" id="add_item" class="btn btn-default"><?php echo safe_output($language->get('Add')); ?></a></h3>
			<?php foreach ($fields as $field) { ?>
				<div class="current_custom_field">
					<p>
					<?php echo safe_output($language->get('Name')); ?> <input type="text" size="25" name="item_name[]" value="<?php echo safe_output($field['name']); ?>" />
					<?php echo safe_output($language->get('Value')); ?> <input type="text" size="25" name="item_value[]" value="<?php echo safe_output($encryption->decrypt($field['value'])); ?>" /> <a href="#custom" id="delete_item" class="btn btn-danger"><?php echo safe_output($language->get('Delete')); ?></a>
					</p>
				</div>
			<?php } ?>
			<br />
			
			<div class="custom_field">
				<p>
				<?php echo safe_output($language->get('Name')); ?> <input type="text" size="25" name="item_name[]" value="" />
				<?php echo safe_output($language->get('Value')); ?> <input type="text" size="25" name="item_value[]" value="" />
				</p>
			</div>
			
			<div class="extra_custom_field"></div>
			
			<br />
				
			<div class="clear"></div>

		</div>
	</div>
	</form>
</div>

<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>