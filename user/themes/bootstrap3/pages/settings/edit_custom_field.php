<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Custom Fields'));
$site->set_config('container-type', 'container');

if ($auth->get('user_level') != 2) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$id = (int) $url->get_item();

$custom_field_groups		= $password_custom_fields->get_groups(array('id' => $id));
	
if (empty($custom_field_groups)) {
	header('Location: ' . $config->get('address') . '/settings/passwords/#custom_fields');
	exit;
}

$custom_field = $custom_field_groups[0];

if (isset($_POST['delete'])) {
	$password_custom_fields->delete_group(array('id' => $custom_field['id']));
	header('Location: ' . $config->get('address') . '/settings/passwords/#custom_fields');
	exit;
}

if (isset($_POST['save'])) {
	if (!empty($_POST['name'])) {
		$add_array['type']				= $_POST['type'];
		$add_array['name']				= $_POST['name'];
		$add_array['client_modify']		= 1;
		$add_array['id']				= $id;
		$add_array['enabled']			= $_POST['enabled'] ? 1 : 0;

		$password_custom_fields->edit_group($add_array);
		
		//$ticket_custom_fields->delete_fields(array('ticket_field_group_id' => $id));
		
		if ($add_array['type'] == 'dropdown' || $add_array['type'] == 'checkbox') {
			foreach($_POST['dropdown_field'] as $index => $value){
				if (!empty($value)) {
					$password_custom_fields->add_field(array('password_field_group_id' => $id, 'value' => $value));
				}
			}
			
			//update existing fields
			foreach($_POST as $index => $value){
				if(strncasecmp($index, 'existing_field-', 15) === 0) {
					$field_index 					= explode('-', $index);
					if (!empty($value)) {
						$item_array['value']		= $value;
						$item_array['id']			= (int) $field_index[1];
						$password_custom_fields->edit_field($item_array);
						unset($item_array);
					}
					else {
						$item_array['id']			= (int) $field_index[1];
						$password_custom_fields->delete_field($item_array);
						unset($item_array);
					}
				}
			}
		}
		
		header('Location: ' . $config->get('address') . '/settings/passwords/#custom_fields');
		exit;
		//$message = $language->get('Saved');
		
	}
	else {
		$message = $language->get('Name empty');
	}
	$custom_field_groups		= $password_custom_fields->get_groups(array('id' => $id));
	$custom_field = $custom_field_groups[0];
}

$custom_fields = $password_custom_fields->get_fields(array('password_field_group_id' => $id));


include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<script type="text/javascript">
	$(document).ready(function () {
		$('#delete').click(function () {
			if (confirm("<?php echo safe_output($language->get('All data attached to this custom field will be deleted. Are you sure you wish to delete this Custom Field?')); ?>")){
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
					<h4><?php echo safe_output($language->get('Custom Fields')); ?></h4>
				</div>
			
				
				<div class="pull-right">
					<p>
						<button type="submit" name="save" class="btn btn-info btn-sm"><?php echo safe_output($language->get('Save')); ?></button>
						<a href="<?php echo $config->get('address'); ?>/settings/passwords/#custom_fields" class="btn btn-default btn-sm"><?php echo safe_output($language->get('Cancel')); ?></a>
					</p>
				</div>
					
				<div class="clearfix"></div>	

				<br />
				<div class="pull-right"><button type="submit" id="delete" name="delete" class="btn btn-danger"><?php echo safe_output($language->get('Delete')); ?></button></div>
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

				<div class="clearfix"></div>
				
				<p><?php echo $language->get('Name'); ?><br /><input type="text" name="name" value="<?php echo safe_output($custom_field['name']); ?>" size="30" /></p>
				
				<p><?php echo $language->get('Enabled'); ?><br />
					<select name="enabled">
						<option value="0"<?php if ($custom_field['enabled'] == '0') { echo ' selected="selected"'; } ?>><?php echo $language->get('No'); ?></option>
						<option value="1"<?php if ($custom_field['enabled'] == '1') { echo ' selected="selected"'; } ?>><?php echo $language->get('Yes'); ?></option>
					</select>
				</p>
				
				<p><?php echo $language->get('Input Type'); ?><br />
					<select name="type" id="custom_field_type">
						<option value="textinput"<?php if ($custom_field['type'] == 'textinput') { echo ' selected="selected"'; } ?>><?php echo $language->get('Text Input'); ?></option>
						<option value="textarea"<?php if ($custom_field['type'] == 'textarea') { echo ' selected="selected"'; } ?>><?php echo $language->get('Text Area'); ?></option>
						<option value="dropdown"<?php if ($custom_field['type'] == 'dropdown') { echo ' selected="selected"'; } ?>><?php echo $language->get('Drop Down'); ?></option>
						<option value="date"<?php if ($custom_field['type'] == 'date') { echo ' selected="selected"'; } ?>><?php echo $language->get('Date'); ?></option>
						<option value="datetime"<?php if ($custom_field['type'] == 'datetime') { echo ' selected="selected"'; } ?>><?php echo $language->get('Date & Time'); ?></option>
						<!--<option value="checkbox"<?php if ($custom_field['type'] == 'checkbox') { echo ' selected="selected"'; } ?>><?php echo $language->get('Check Box'); ?></option>-->
					</select>
				</p>

				<div id="dropdown_fields">
					<br />
					<h4><a name="add_dropdown"></a><?php echo $language->get('Fields'); ?> <a id="add_dropdown_field" href="#add_dropdown" class="btn btn-default"><?php echo $language->get('Add'); ?></a></h4>

					<?php if ($custom_field['type'] == 'dropdown' || $custom_field['type'] == 'checkbox') { ?>
						<?php foreach($custom_fields as $item) { ?>
							<div class="existing_dropdown_field">
								<p><?php echo $language->get('Option'); ?><br /><input type="text" name="existing_field-<?php echo (int) $item['id']; ?>" value="<?php echo safe_output($item['value']); ?>" size="30" /></p>
							</div>
						<?php } ?>
					<?php } ?>
				
					<div class="dropdown_field">
						<p><?php echo $language->get('Option'); ?><br /><input type="text" name="dropdown_field[]" value="" size="30" /></p>
					</div>
					<div class="extra_dropdown_field"></div>
				</div>

			</div>
				
			<div class="clearfix"></div>

		</div>

	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>