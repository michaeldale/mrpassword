<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Add Shared Password'));
$site->set_config('container-type', 'container');

$cat_id = (int) $url->get_item();

$categories_array 		= $categories->get(array('shared_user_id' => $auth->get('id'), 'id' => $cat_id, 'limit' => 1, 'get_other_data' => true, 'global' => 1));

$access_level	= 1;
if (!empty($categories_array)) {
	$category_name 	= $categories_array[0]['name'];
	$access_level	= (int) $categories_array[0]['access_level'];
	$user_id		= (int) $categories_array[0]['user_id'];
}

if ($access_level != 2) {
	header('Location: ' . $config->get('address') . '/passwords/global/');
	exit;
}

if (isset($_POST['add'])) {
	if (!empty($_POST['name'])) {
		if (!empty($_POST['password'])) {
			if ($_POST['url'] == 'http://') $_POST['url'] = '';
			$id = $passwords->add(
				array(
					'name'			=> $_POST['name'],
					'username'		=> $_POST['username'],
					'password'		=> $_POST['password'],
					'description'	=> $_POST['description'],
					'url'			=> $_POST['url'],
					'category_id'	=> $cat_id,
					'user_id'		=> $user_id
				)
			);
			
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
			
			/*
				Global Custom Fields
			*/
			foreach($_POST as $index => $value){
				if(strncasecmp($index, 'field-', 6) === 0) {
					$group_index = explode('-', $index);
					$group_id = (int) $group_index[1];
					if ($group_id !== 0) {
						$edit_array['password_field_group_id']	= $group_id;
						$edit_array['password_id']				= $id;
						
						if (is_array($value)) {
							$values = array();
							foreach($value as $check_index => $check_item) {
								$values[] = $check_item;
							}
							$edit_array['value']					= json_encode($value);								
						}
						else {
							$edit_array['value']					= $value;
						}
					
						$password_custom_fields->add_value($edit_array);
						unset($edit_array);
					}
				}			
			}
			
			header('Location: ' . $config->get('address') . '/passwords/viewglobal/' . $id . '/');
		}
		else {
			$message = $language->get('Password Empty');
		}
	}
	else {
		$message = $language->get('Name Empty');
	}
}

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
					<h4><?php echo $language->get('Add Global Password'); ?></h4>
				</div>
				<div class="pull-right">
					<p><button type="submit" name="add" class="btn btn-primary"><?php echo $language->get('Add'); ?></button></p>
				</div>
				
				<div class="clearfix"></div>

				<p><?php echo $language->get('This page allows you to add a new password in to the global category.'); ?></p>
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
		
				<p><?php echo $language->get('Name (aka Nickname)'); ?><br /><input type="text" name="name" value="<?php if (isset($_POST['name'])) echo safe_output($_POST['name']); ?>" /></p>
				<p><?php echo $language->get('Category'); ?><br />
				<?php echo safe_output($category_name); ?>
				</p>

				<p><?php echo $language->get('Username (optional)'); ?><br /><input type="text" size="50" name="username" value="<?php if (isset($_POST['username'])) echo safe_output($_POST['username']); ?>" /></p>
				<p><?php echo $language->get('Password'); ?><br /><input type="text" class="generate_field" size="50" name="password" value="<?php if (isset($_POST['password'])) echo safe_output($_POST['password']); ?>" autocomplete="off" /> <a class="generate_password btn btn-info btn-sm" href="#"><?php echo $language->get('Generate'); ?></a></p>
				<p><?php echo $language->get('URL (optional)'); ?><br /><input type="text" size="50" name="url" value="<?php if (isset($_POST['url'])) { echo safe_output($_POST['url']); } else { echo 'http://'; } ?>" /></p>
				<p><?php echo $language->get('Description (optional)'); ?><br />
				<textarea name="description" cols="50" rows="6"><?php if (isset($_POST['description'])) echo safe_output($_POST['description']); ?></textarea>
				</p>
				
				<?php $site->display_custom_field_forms(); ?>
				
				<h3 name="custom"><?php echo $language->get('Custom Fields'); ?> <a href="#custom" id="add_item" class="btn btn-default"><?php echo $language->get('Add'); ?></a></h3>
				
				<div class="custom_field">
					<p>
					<?php echo $language->get('Name'); ?> <input type="text" size="25" name="item_name[]" value="" />
					<?php echo $language->get('Value'); ?> <input type="text" size="25" name="item_value[]" value="" />
					</p>
				</div>
				
				<div class="extra_custom_field"></div>
				
				<div class="clear"></div>

			</div>
		</div>
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>