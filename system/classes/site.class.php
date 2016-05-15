<?php
/**
 * 	Site Class
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace mrpassword;

class site {
	var $config = NULL;
	
	function __construct() {
		$this->config['title'] = '';
	}
	
	function set_title($title) {
		$this->config['title'] = $title;
	}
	
	function get_title() {
		$config = &singleton::get(__NAMESPACE__  . '\config');
		
		return $config->get('name') . ' - ' . $this->config['title'];
	}
	
	public function set_config($name, $value) {
		$this->config[$name] = $value;
	}
	public function get_config($name) {
		return $this->config[$name];
	}
	
	function get_page_title() {
		$config = &singleton::get(__NAMESPACE__  . '\config');
		
		return $this->config['title'];
	}	
	
	function display_custom_field_forms() {
	
		$password_custom_fields 	= &singleton::get(__NAMESPACE__ . '\password_custom_fields');
		
		$custom_field_groups		= $password_custom_fields->get_groups(array('enabled' => 1));
		
		foreach($custom_field_groups as $custom_field_group) { ?>
			<p><?php echo safe_output($custom_field_group['name']); ?><br />
			<?php if ($custom_field_group['type'] == 'dropdown') { ?>
				<?php $fields = $password_custom_fields->get_fields(array('password_field_group_id' => $custom_field_group['id'])); ?>
				<select name="field-<?php echo safe_output($custom_field_group['id']); ?>">
				<?php foreach ($fields as $field) { ?>
					<option value="<?php echo safe_output($field['id']); ?>"<?php if (isset($_POST['field-' . safe_output($custom_field_group['id'])]) && $field['id'] == $_POST['field-' . safe_output($custom_field_group['id'])]) { echo ' selected="selected"'; } ?>><?php echo safe_output($field['value']); ?></option>
				<?php } ?>
				</select>
			<?php } else if ($custom_field_group['type'] == 'checkbox') { ?>
                <?php $fields = $password_custom_fields->get_fields(array('password_field_group_id' => $custom_field_group['id'])); ?>
                <?php foreach ($fields as $field) { ?>
                    <input type="checkbox" name="field-<?php echo safe_output($custom_field_group['id']); ?>[]" <?php if (isset($_POST['field-' . $custom_field_group['id']]) && in_array($field['id'], $_POST['field-' . $custom_field_group['id']])) { ?>checked="checked"<?php } ?> value="<?php echo (int) $field['id']; ?>"> <?php echo safe_output($field['value']); ?><br />
                <?php } ?>
			<?php } else if ($custom_field_group['type'] == 'textinput') { ?>
				<input type="text" name="field-<?php echo safe_output($custom_field_group['id']); ?>" value="<?php if (isset($_POST['field-' . safe_output($custom_field_group['id'])])) echo safe_output($_POST['field-' . safe_output($custom_field_group['id'])]); ?>" size="50" />	
			<?php } else if ($custom_field_group['type'] == 'textarea') { ?>
				<div id="no_underline">
					<textarea class="wysiwyg_enabled" name="field-<?php echo safe_output($custom_field_group['id']); ?>" cols="80" rows="12"><?php if (isset($_POST['field-' . safe_output($custom_field_group['id'])])) echo safe_output($_POST['field-' . safe_output($custom_field_group['id'])]); ?></textarea>
				</div>
			<?php } else if ($custom_field_group['type'] == 'date') { ?>
                <input type="text" class="form-control" data-date-format="YYYY-MM-DD" id="field-<?php echo safe_output($custom_field_group['id']); ?>" name="field-<?php echo safe_output($custom_field_group['id']); ?>" value="<?php if (isset($_POST['field-' . safe_output($custom_field_group['id'])])) echo safe_output($_POST['field-' . safe_output($custom_field_group['id'])]); ?>" size="50" />
                <script type="text/javascript">
                    $(function () {
                        $('#field-<?php echo safe_output($custom_field_group['id']); ?>').datetimepicker({
                            widgetPositioning: {
                            vertical: "bottom",
                            horizontal: "auto"
                        }
                        });
                    });
                </script>
            <?php } else if ($custom_field_group['type'] == 'datetime') { ?>
                <input type="text" class="form-control" data-date-format="YYYY-MM-DD HH:mm" id="field-<?php echo safe_output($custom_field_group['id']); ?>" name="field-<?php echo safe_output($custom_field_group['id']); ?>" value="<?php if (isset($_POST['field-' . safe_output($custom_field_group['id'])])) echo safe_output($_POST['field-' . safe_output($custom_field_group['id'])]); ?>" size="50" />
                <script type="text/javascript">
                    $(function () {
                        $('#field-<?php echo safe_output($custom_field_group['id']); ?>').datetimepicker({
                            widgetPositioning: {
                            vertical: "bottom",
                            horizontal: "auto"
                        }
                        });
                    });
                </script>
            <?php } ?>
			</p>
		<?php }
	}
	
	function view_custom_field_edit_form($array) {
	
		$password = $array['password'];
		
		$password_custom_fields 	= &singleton::get(__NAMESPACE__ . '\password_custom_fields');
	
		$custom_field_groups	= $password_custom_fields->get_groups(array('enabled' => 1));
		
		foreach($custom_field_groups as $custom_field_group) { ?>
			<?php $current_fields = $password_custom_fields->get_values(array('password_field_group_id' => $custom_field_group['id'], 'password_id' => (int) $password['id'])); ?>
			<p><?php echo safe_output($custom_field_group['name']); ?><br />
			<?php if ($custom_field_group['type'] == 'dropdown') { ?>
				<?php $fields = $password_custom_fields->get_fields(array('password_field_group_id' => $custom_field_group['id'])); ?>
				<select name="field-<?php echo safe_output($custom_field_group['id']); ?>">
				<?php foreach ($fields as $field) { ?>
					<option value="<?php echo safe_output($field['id']); ?>"<?php if (isset($current_fields[0]['value']) && ($current_fields[0]['value'] == $field['id'])) { echo ' selected="selected"'; } ?>><?php echo safe_output($field['value']); ?></option>
				<?php } ?>
				</select>
			<?php } else if ($custom_field_group['type'] == 'checkbox') {
                $values = array();
                if (isset($current_fields[0]['value'])) {
                    $values = json_decode($current_fields[0]['value'], true);
                }
                ?>
                <?php $fields = $password_custom_fields->get_fields(array('password_field_group_id' => $custom_field_group['id'])); ?>
                <?php foreach ($fields as $field) { ?>
                    <input type="checkbox" name="field-<?php echo safe_output($custom_field_group['id']); ?>[]" <?php if (in_array($field['id'], $values)) { ?>checked="checked"<?php } ?> value="<?php echo (int) $field['id']; ?>"> <?php echo safe_output($field['value']); ?><br />
            <?php } ?>
			<?php } else if ($custom_field_group['type'] == 'textinput') { ?>
				<input type="text" name="field-<?php echo safe_output($custom_field_group['id']); ?>" value="<?php if (isset($current_fields[0]['value'])) echo safe_output($current_fields[0]['value']); ?>" size="50" />	
			<?php } else if ($custom_field_group['type'] == 'textarea') { ?>
				<div id="no_underline">
					<textarea class="wysiwyg_enabled" name="field-<?php echo safe_output($custom_field_group['id']); ?>" cols="80" rows="12"><?php if (isset($current_fields[0]['value'])) echo safe_output($current_fields[0]['value']); ?></textarea>
				</div>
            <?php } else if ($custom_field_group['type'] == 'date') { ?>
                <input type="text" data-date-format="YYYY-MM-DD" class="form-control" id="field-<?php echo safe_output($custom_field_group['id']); ?>" name="field-<?php echo safe_output($custom_field_group['id']); ?>" value="<?php if (isset($current_fields[0]['value'])) echo safe_output($current_fields[0]['value']); ?>" size="50" />
                <script type="text/javascript">
                    $(function () {
                        $('#field-<?php echo safe_output($custom_field_group['id']); ?>').datetimepicker({
                            widgetPositioning: {
                            vertical: "bottom",
                            horizontal: "auto"
                        }
                        });
                    });
                </script>
            <?php } else if ($custom_field_group['type'] == 'datetime') { ?>
                <input type="text" data-date-format="YYYY-MM-DD HH:mm" class="form-control" id="field-<?php echo safe_output($custom_field_group['id']); ?>" name="field-<?php echo safe_output($custom_field_group['id']); ?>" value="<?php if (isset($current_fields[0]['value'])) echo safe_output($current_fields[0]['value']); ?>" size="50" />
                <script type="text/javascript">
                    $(function () {
                        $('#field-<?php echo safe_output($custom_field_group['id']); ?>').datetimepicker({
                            widgetPositioning: {
                            vertical: "bottom",
                            horizontal: "auto"
                        }
                        });
                    });
                </script>
            <?php } ?>
			</p>
		<?php }
	}
	
}

?>