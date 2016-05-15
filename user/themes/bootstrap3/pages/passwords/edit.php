<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Edit Password'));
$site->set_config('container-type', 'container');

$id = (int) $url->get_item();

if ($id == 0) {
	header('Location: ' . $config->get('address') . '/passwords/');
	exit;
}

$passwords_array = $passwords->get(array('id' => $id, 'get_other_data' => true, 'user_id' => $auth->get('id'), 'global_or_null' => 0));

if (count($passwords_array) == 1) {
	$password = $passwords_array[0];
}
else {
	header('Location: ' . $config->get('address') . '/passwords/');
	exit;
}

if (isset($_POST['delete'])) {
	$del_passwords_array = $passwords->get(array('parent_id' => $id, 'user_id' => $auth->get('id')));
	
	foreach($del_passwords_array as $del) {
		$custom_fields->delete(array('password_id' => $del['id']));
	}

	$custom_fields->delete(array('password_id' => $id));
	$passwords->delete(array('id' => $id, 'user_id' => $auth->get('id')));
	$passwords->delete(array('parent_id' => $id, 'user_id' => $auth->get('id')));

	header('Location: ' . $config->get('address') . '/passwords/');
	exit;
}

if (isset($_POST['save'])) {
	if (!empty($_POST['name'])) {
		if (!empty($_POST['password'])) {
			$upload_file 	= false;
			$files_upload 	= array();
			
			if ($config->get('storage_enabled')) {
				if (isset($_FILES['file']) && is_array($_FILES['file'])) {
					$files_array = rearrange($_FILES['file']);	
					foreach($files_array as $file) {
						if ($file['size'] > 0) {
							$upload_file = true;
							$file_array['file']			= $file;
							$file_array['name']			= $file['name'];		
							$file_array['user_id']		= $auth->get('id');		
							$file_id 					= $storage->upload($file_array);		
							if ($file_id) {
								$files_upload[] 		= $file_id;
								$storage->add_file_to_password(
									array(
										'file_id' 		=> $file_id, 
										'password_id' 	=> (int) $password['id']
									)
								);
								unset($file_id);
							}
						}
					}
				}
			}
					
			$passwords->edit(
				array(
					'id'				=> $id,
					'name' 				=> $_POST['name'], 
					'username'			=> $_POST['username'],
					'password'			=> $_POST['password'],
					'description'		=> $_POST['description'],
					'url'				=> $_POST['url'],
					'category_id'		=> (int) $_POST['category_id'],
				)
			);
			
			$password_custom_fields->delete_value(array('password_id' => $id));
			
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
			
			header('Location: ' . $config->get('address') . '/passwords/view/' . $id . '/');
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

$categories_array 		= $categories->get(array('user_id' => $auth->get('id'), 'get_other_data' => true, 'global' => 0));

$fields = $custom_fields->get(array('password_id' => $id));

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>	
<script type="text/javascript">
	$(document).ready(function () {
		$('.nav-tabs').button();
		$('.default-toggle').button('toggle');

		$('#delete').click(function () {
			if (confirm("<?php echo $language->get('Are you sure you wish to delete this password and its history?'); ?>")){
				return true;
			}
			else{
				return false;
			}
		});
	});
</script>
<div class="row">

	<form method="post" enctype="multipart/form-data" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">
	
		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo $language->get('Password'); ?></h4>
				</div>
				<div class="pull-right">
					<p><button type="submit" name="save" class="btn btn-primary"><?php echo $language->get('Save'); ?></button> <a href="<?php echo $config->get('address'); ?>/passwords/view/<?php echo (int) $password['id']; ?>/" class="btn btn-default"><?php echo $language->get('Cancel'); ?></a></p>
				</div>
				<div class="clearfix"></div>
				<br />
				<div class="pull-right">
					<p class="seperator"><button type="submit" id="delete" name="delete" class="btn btn-danger"><?php echo $language->get('Delete'); ?></button></p>
				</div>
				<div class="clearfix"></div>

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
			
			<?php $files = $passwords->get_files(array('id' => $password['id'])); ?>
			<?php if (count($files) > 0) { ?>
				<div class="well well-sm">
					<h4><?php echo safe_output($language->get('Files')); ?></h4>
					<ul>
						<?php foreach ($files as $file) { ?>
						<li id="password_id-<?php echo (int) $password['id']; ?>"><a href="<?php echo $config->get('address'); ?>/files/download/<?php echo (int) $file['id']; ?>/?password_id=<?php echo (int) $password['id']; ?>"><?php echo safe_output($file['name']); ?></a> <a href="#" class="delete_existing_password_file" id="file_id-<?php echo (int) $file['id']; ?>"><img src="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/images/icons/delete.png" alt="Delete File" /></a></li>
						<?php } ?>
					</ul>
				</div>
			<?php } ?>
		</div>

		<div class="col-md-9">
			<?php if (isset($message)) { ?>
				<div class="alert alert-danger">
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>
			<div class="well well-sm">
				
				<p><?php echo $language->get('Name (aka Nickname)'); ?><br /><input type="text" name="name" value="<?php echo safe_output($password['name']); ?>" /></p>
				<p><?php echo $language->get('Category'); ?><br />
				<select name="category_id">
					<option value=""></option>
					<?php foreach ($categories_array as $category) { ?>
						<option value="<?php echo (int) $category['id']; ?>"<?php if ($password['category_id'] == $category['id']) echo ' selected="selected"'; ?>><?php echo safe_output($category['name'] . ' - ' . $category['share_count'] . ' Shares'); ?></option>
					<?php } ?>
				</select></p>
				<p><?php echo $language->get('Username (optional)'); ?><br /><input type="text" size="50" name="username" value="<?php echo safe_output($password['username']); ?>" /></p>
				<p><?php echo $language->get('Password'); ?><br /><input type="text" class="generate_field" size="50" autocomplete="off" name="password" value="<?php echo safe_output($encryption->decrypt($password['password'])); ?>" /> <a class="btn btn-info btn-sm generate_password" href="#"><?php echo $language->get('Generate'); ?></a></p>
				<p><?php echo $language->get('URL (optional)'); ?><br /><input type="text" size="50" name="url" value="<?php echo safe_output($password['url']); ?>" /></p>
				<p><?php echo $language->get('Description (optional)'); ?><br />
				<textarea name="description" cols="50" rows="6"><?php echo safe_output($password['description']); ?></textarea>
				</p>
				
				<?php if ($config->get('storage_enabled')) { ?>
					<p><?php echo safe_output($language->get('Attach File')); ?></p>
					
					<div class="form-group">		
						<div class="col-lg-4">								
							<div class="pull-left"><input name="file[]" type="file" /></div>
							<div class="pull-right"><a href="#" id="add_extra_file"><span class="glyphicon glyphicon-plus"></span></a></div>
							<div id="attach_file_area"></div>					
						</div>
					</div>
					
					<div class="clearfix"></div>
				<?php } ?>
				
				<?php $site->view_custom_field_edit_form(array('password' => $password)); ?>

				<br />
				<h3 name="custom"><?php echo $language->get('Custom Fields'); ?> <a href="#custom" id="add_item" class="btn btn-default"><?php echo $language->get('Add'); ?></a></h3>
				<?php foreach ($fields as $field) { ?>
					<div class="current_custom_field">
						<p>
						<?php echo $language->get('Name'); ?> <input type="text" size="25" name="item_name[]" value="<?php echo safe_output($field['name']); ?>" />
						<?php echo $language->get('Value'); ?> <input type="text" size="25" name="item_value[]" value="<?php echo safe_output($encryption->decrypt($field['value'])); ?>" /> <a href="#custom" id="delete_item" class="btn btn-danger"><?php echo $language->get('Delete'); ?></a>
						</p>
					</div>
				<?php } ?>
				<br />
				
				<div class="custom_field">
					<p>
					<?php echo $language->get('Name'); ?> <input type="text" size="25" name="item_name[]" value="" />
					<?php echo $language->get('Value'); ?> <input type="text" size="25" name="item_value[]" value="" />
					</p>
				</div>
				
				<div class="extra_custom_field"></div>
				
				<br />
				
				<div class="clearfix"></div>

			</div>
		</div>
	
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>