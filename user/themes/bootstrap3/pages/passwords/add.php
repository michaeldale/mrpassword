<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Add Password'));
$site->set_config('container-type', 'container');

if (isset($_POST['add'])) {
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
							$files_upload[] 			= $storage->upload($file_array);
						}
					}
				}
			}
					
			if ($upload_file && empty($files_upload)) {
				$message = $language->get('File Upload Failed. Password Not Submitted.');
			}
			else {		
				if ($_POST['url'] == 'http://') $_POST['url'] = '';
				
				$id = $passwords->add(
					array(
						'name'				=> $_POST['name'],
						'username'			=> $_POST['username'],
						'password'			=> $_POST['password'],
						'description'		=> $_POST['description'],
						'url'				=> $_POST['url'],
						'category_id'		=> (int) $_POST['category_id'],
						'attach_file_ids'	=> $files_upload
					)
				);
				
				/*
					
				*/
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
				
				header('Location: ' . $config->get('address') . '/passwords/view/' . $id . '/');
			}
		}
		else {
			$message = $language->get('Password Empty');
		}
	}
	else {
		$message = $language->get('Name Empty');
	}
}

$cat_id = (int) $url->get_item();

$categories_array 		= $categories->get(array('user_id' => $auth->get('id'), 'get_other_data' => true, 'global' => 0));

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<script type="text/javascript">
	$(document).ready(function () {
		$('.nav-tabs').button();
		$('.default-toggle').button('toggle');
	});
</script>
<div class="row">
	<form method="post" enctype="multipart/form-data" action="<?php echo safe_output($config->get('address')); ?>/passwords/add/">
		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo $language->get('Add Password'); ?></h4>
				</div>

				<div class="pull-right">
					<p><button type="submit" name="add" class="btn btn-primary"><?php echo $language->get('Add'); ?></button></p>
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

			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="pull-left">
						<h4><?php echo safe_output($language->get('Password')); ?></h4>
					</div>

					<div class="clearfix"></div>

				</div>
				<div class="panel-body">

					<p><?php echo $language->get('Name (aka Nickname)'); ?><br /><input type="text" class="form-control" name="name" value="<?php if (isset($_POST['name'])) echo safe_output($_POST['name']); ?>" /></p>
					<p><?php echo $language->get('Category (optional)'); ?><br />
					<select name="category_id">
						<option value=""></option>
						<?php foreach ($categories_array as $category) { ?>
							<option value="<?php echo (int) $category['id']; ?>"
							<?php
							if (isset($_POST['category_id']) && ($_POST['category_id'] == $category['id'])) {
								echo ' selected="selected"'; 
							} else if ($cat_id == $category['id']) {
								echo ' selected="selected"'; 
							}
							?>
							>
							<?php echo safe_output($category['name'] . ' - ' . $category['share_count'] . ' Shares'); ?></option>
						<?php } ?>
					</select></p>

					<p><?php echo $language->get('Username (optional)'); ?><br /><input type="text" class="form-control" size="50" name="username" value="<?php if (isset($_POST['username'])) echo safe_output($_POST['username']); ?>" /></p>
					<p><?php echo $language->get('Password'); ?><br /><input type="text" class="form-control generate_field" size="50" name="password" value="<?php if (isset($_POST['password'])) echo safe_output($_POST['password']); ?>" autocomplete="off" /> <a class="btn btn-info btn-sm generate_password" href="#"><?php echo $language->get('Generate'); ?></a></p>
					<p><?php echo $language->get('URL (optional)'); ?><br /><input type="text" class="form-control" size="50" name="url" value="<?php if (isset($_POST['url'])) { echo safe_output($_POST['url']); } else { echo 'http://'; } ?>" /></p>
					<p><?php echo $language->get('Description (optional)'); ?><br />
					<textarea name="description" class="form-control" cols="50" rows="6"><?php if (isset($_POST['description'])) echo safe_output($_POST['description']); ?></textarea>
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
					
					<?php $site->display_custom_field_forms(); ?>
					
					<br />
					<h3 name="custom"><?php echo $language->get('Custom Fields'); ?> <a href="#custom" id="add_item" class="btn btn-default"><?php echo $language->get('Add'); ?></a></h3>
					<br />
					<div class="custom_field">
						<p>
						<?php echo $language->get('Name'); ?> <input type="text" class="form-control" size="25" name="item_name[]" value="" />
						<?php echo $language->get('Value'); ?> <input type="text" class="form-control" size="25" name="item_value[]" value="" />
						</p>
					</div>
					
					<div class="extra_custom_field"></div>
					
					<br />

					<div class="clearfix"></div>
				</div>
			</div>
			<br />
		</div>
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>