<?php
/**
 * 	Table Access Class
 *	Copyright Dalegroup Pty Ltd 2014
 *	support@dalegroup.net
 *
 *  This class allows basic Add/Edit/Delete functions for any generic table
 *
 * @package     dgx
 * @author      Michael Dale <support@dalegroup.net>
 */

namespace mrpassword;

class table_access {

	private 	$table_name 			= NULL;
	private 	$base_table_name 		= NULL;
	private 	$allowed_columns 		= NULL;
	private 	$custom_fields 			= array('enabled' => false, 'fields' => array(), 'settings_url' => '', 'name' => '');
	private 	$current				= array();
	protected 	$args					= array();
	
	function __construct() {
		//don't use
	}
	
	public function set_table($table_name) {
		if (empty($table_name)) return false;
		
		$this->base_table_name	= $table_name;
	
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');

		$tables->add_table($table_name);
	
		$this->table_name = $tables->$table_name;	
		
	}
	
	private function load_custom_fields() {
		$table_access_cf		= &singleton::get(__NAMESPACE__ . '\table_access_cf');
		
		$custom_fields = $table_access_cf->get(array('where' => array('table_name' => $this->base_table_name)));
		
		if (!empty($custom_fields)) {
			foreach($custom_fields as $item) {
				$this->custom_fields['fields'][$item['id']] 	= array(
					'column_name' 	=> 'cf_' . $item['id'], 
					'name' 			=> $item['name'], 
					'index_display' => $item['index_display'],
					'type'			=> $item['type'],
					'id'			=> $item['id'],
					'client_modify'	=> $item['client_modify'],
					'enabled'		=> $item['enabled']
				);
				$this->set_allowed_column('cf_' . $item['id']);
			}
		}
	
	}
	
	public function get_table() {
		return $this->table_name;
	}
	
	private function set_allowed_column($name) {
		if (!in_array($name, $this->allowed_columns)) {
			$this->allowed_columns[] = $name;
		}
	}
	
	public function allowed_columns($array) {
		$this->allowed_columns = $array;
	}
	public function get_allowed_columns() {
		return $this->allowed_columns;
	}
	
	public function enable_cf() {
		$this->custom_fields['enabled'] = true;

		$this->load_custom_fields();	
	}
	
	public function set_cf_settings_url($url) {
		$this->custom_fields['settings_url'] = $url;
	}

	public function set($name, $value) {
		$this->custom_fields[$name] = $value;		
	}
	
	public function get_cf_settings_url() {
		return $this->custom_fields['settings_url'];
	}
	
	public function is_cf_enabled() {
		return $this->custom_fields['enabled'];
	}
	
	public function get_cf() {
		return $this->custom_fields;
	}
	
	public function add_cf($array) {
		global $db;
		
		if (!$this->custom_fields['enabled']) return false;
		
		$error 				= &singleton::get(__NAMESPACE__ . '\error');
		$log				= &singleton::get(__NAMESPACE__ . '\log');
		$config 			= &singleton::get(__NAMESPACE__ . '\config');
		$table_access_cf	= &singleton::get(__NAMESPACE__ . '\table_access_cf');

		$site_id		= SITE_ID;
				
		$add_array				 	= $array['columns'];		
		$add_array['table_name']	= $this->base_table_name;

		$id = (int) $table_access_cf->add(
			array('columns' => 
				$add_array
			)
		);
		
		switch ($array['columns']['type']) {
		
			case 'dropdown':
				//not yet supported
				$query = false;
			break;
		
			case 'textinput':
				$query = "ALTER TABLE $this->table_name ADD `cf_{$id}` VARCHAR(255) NULL";
			break;

			case 'textarea':
				$query = "ALTER TABLE $this->table_name ADD `cf_{$id}` LONGTEXT NULL";
			break;

			case 'date':
				$query = "ALTER TABLE $this->table_name ADD `cf_{$id}` DATE NULL";
			break;

			case 'datetime':
				$query = "ALTER TABLE $this->table_name ADD `cf_{$id}` DATETIME NULL";
			break;
			
			default:
				$query = false;
			break;
		
		}
		
		if ($query !== false) {
			try {
				$stmt = $db->prepare($query);
			}
			catch (\Exception $e) {
				$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
			}
				
			try {
				$stmt->execute();
			}
			catch (\Exception $e) {
				$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
			}
			
			$this->custom_fields['enabled'] 		= true;
			$this->custom_fields['fields'][$id] 	= array('column_name' => 'cf_' . $id);
			$this->set_allowed_column('cf_' . $id);

			return $id;
			
		}
		else {
			return false;
		}
		
	}
	
	/**
	 * Adds a value into the database table
	 *
	 * Form the array like this:
	 * <code>
	 * $array = array(
	 * 	'columns' => array( 	
	 *		'username'    	=> 'admin',
	 *   	'password'   	=> '1234'
	 *	)
	 * );
	 * 
	 * </code>
	 *
	 * @param   array   $array 			The array explained above
	 * @return  int						The ID of the added value
	 */
	public function add($array) {
		global $db;
		
		// Custom Fields
		if ($this->custom_fields['enabled']) {
			foreach($this->custom_fields['fields'] as $field) {
				if ($field['enabled']) {
					if (isset($_POST) && isset($_POST['cf-' . $field['id']])) {
						if (!isset($array['columns'][$field['column_name']])) {
							$array['columns'][$field['column_name']] = $_POST['cf-' . $field['id']];
						}
					}
				}
			}
		}
		
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$log			= &singleton::get(__NAMESPACE__ . '\log');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');

		$site_id		= SITE_ID;
		
		$query = "INSERT INTO $this->table_name (site_id";

		if (isset($array['columns'])) {
			foreach($array['columns'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$query .= ', `' . $index . '`';
					unset($index);
					unset($value);
				}
			}
		}
		
		$query .= ") VALUES (:site_id";
	
		if (isset($array['columns'])) {
			foreach($array['columns'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$query .= ', :' . $index;
					unset($index);
					unset($value);
				}
			}
		}
	
		$query .= ")";
		
		//echo $query;
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

		if (isset($array['columns'])) {
			foreach($array['columns'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$stmt->bindParam(':' . $index, $value);
					unset($index);
					unset($value);
				}
			}
		}	
		
		try {
			$stmt->execute();
			$id = $db->lastInsertId();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
				
		return $id;
		
	}
	
	
	public function edit($array) {
		global $db;
		
		// Custom Fields
		if ($this->custom_fields['enabled']) {
			foreach($this->custom_fields['fields'] as $field) {
				if ($field['enabled']) {
					if (isset($_POST) && isset($_POST['cf-' . $field['id']])) {
						if (!isset($array['columns'][$field['column_name']])) {
							$array['columns'][$field['column_name']] = $_POST['cf-' . $field['id']];
						}
					}
				}
			}
		}
		

		$error 		= &singleton::get(__NAMESPACE__ . '\error');		
		$log		= &singleton::get(__NAMESPACE__ . '\log');
		$config		= &singleton::get(__NAMESPACE__ . '\config');
		
		$site_id		= SITE_ID;


		$query = "UPDATE $this->table_name SET site_id = :site_id";

		if (isset($array['columns'])) {
			foreach($array['columns'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$query .= ', `'.$index.'` = :'.$index;
					unset($index);
					unset($value);
				}
			}
		}	
		
		$query .= " WHERE site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id = :id";
		}
		
		if (isset($array['where'])) {
			foreach($array['where'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$query .= ' AND `'.$index.'` = :w1'.$index;
					unset($index);
					unset($value);
				}
			}
		}
		
		//echo $query;
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

		if (isset($array['id'])) {
			$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		}
		
		if (isset($array['columns'])) {
			foreach($array['columns'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$stmt->bindParam(':' . $index, $value);
					unset($index);
					unset($value);
				}
			}
		}
		
		if (isset($array['where'])) {
			foreach($array['where'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$stmt->bindParam(':w1' . $index, $value);
					unset($index);
					unset($value);
				}
			}
		}
		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}				
		
		return true;
	
	}
	public function get($array = NULL) {
		global $db;
		
		$this->args = $array;
		
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$plugins 		= &singleton::get(__NAMESPACE__ . '\plugins');

		$site_id		= SITE_ID;


		$query = "SELECT tan.* ";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$plugins->run('query_table_access_' . $this->base_table_name . '_get_other_data_columns', $query);			
		}
		
		$query .= " FROM $this->table_name tan";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$plugins->run('query_table_access_' . $this->base_table_name . '_get_other_data_join', $query);
		}
		
		$query .= " WHERE 1 = 1 AND tan.site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND tan.id = :id";
		}
				
		if (isset($array['ids'])) {				
			$return = ' AND tan.id IN (';
			
			foreach ($array['ids'] as $index => $value) {
				$return .= ':id' . (int) $index . ',';
			}
			
			if(substr($return, -1) == ',') {	
				$return = substr($return, 0, strlen($return) - 1);
			}
			
			$return .= ')';
			
			$query .= $return;
		}
		
		if (isset($array['where'])) {
			foreach($array['where'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$query .= ' AND tan.' . $index . ' = :'.$index;
					unset($index);
					unset($value);
				}
			}
			$plugins->run('query_table_access_' . $this->base_table_name . '_get_where', $query);
		}
		
		if (isset($array['where_min'])) {
			foreach($array['where_min'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$query .= ' AND tan.' . $index . ' >= :where_min'.$index;
					unset($index);
					unset($value);
				}
			}
			$plugins->run('query_table_access_' . $this->base_table_name . '_get_where_min', $query);
		}
		
		if (isset($array['where_max'])) {
			foreach($array['where_max'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$query .= ' AND tan.' . $index . ' <= :where_max'.$index;
					unset($index);
					unset($value);
				}
			}
			$plugins->run('query_table_access_' . $this->base_table_name . '_get_where_max', $query);
		}
		
		if (isset($array['like'])) {
			$query .= ' AND (';
			foreach($array['like'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$query .= 'tan.' . $index . ' LIKE :'.$index . ' OR ';
					unset($index);
					unset($value);
				}
			}
			
			if(substr($query, -4) == ' OR ') {	
				$query = substr($query, 0, strlen($query) - 4);
			}
			
			$query .= ')';
		}
		
		$query .= " GROUP BY tan.id";

		if (isset($array['order_by']) && in_array($array['order_by'], $this->allowed_columns)) {
			if (isset($array['order']) && $array['order'] == 'desc') {
				$query .= ' ORDER BY tan.' . $array['order_by'] . ' DESC';
			}
			else {
				$query .= ' ORDER BY tan.' . $array['order_by'] . '';
			}			
		}
		else {
			if (isset($array['order']) && $array['order'] == 'asc') {
				$query .= ' ORDER BY tan.id';
			}
			else {
				$query .= " ORDER BY tan.id DESC";
			}	
		}
		
		
		if (isset($array['limit'])) {
			$query .= " LIMIT :limit";
			if (isset($array['offset'])) {
				$query .= " OFFSET :offset";
			}
		}
			
		//echo $query;
			
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
				
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

	
		if (isset($array['id'])) {
			$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		}
		if (isset($array['ids'])) {	
			foreach ($array['ids'] as $index => $value) {
				$r_id = (int) $value;
				$stmt->bindParam(':id' . (int) $index, $r_id, database::PARAM_INT);
				unset($r_id);
			}
		}
		
		if (isset($array['where'])) {
			foreach($array['where'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$stmt->bindParam(':' . $index, $value);
					unset($index);
					unset($value);
				}
			}
			$plugins->run('query_table_access_' . $this->base_table_name . '_get_where_bind', $stmt);
		}
		
		if (isset($array['where_min'])) {
			foreach($array['where_min'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$stmt->bindParam(':where_min' . $index, $value);
					unset($index);
					unset($value);
				}
			}
			$plugins->run('query_table_access_' . $this->base_table_name . '_get_where_min_bind', $stmt);
		}
		
		if (isset($array['where_max'])) {
			foreach($array['where_max'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$stmt->bindParam(':where_max' . $index, $value);
					unset($index);
					unset($value);
				}
			}
			$plugins->run('query_table_access_' . $this->base_table_name . '_get_where_min_bind', $stmt);
		}

		if (isset($array['like'])) {
			foreach($array['like'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$value = "%{$value}%";
					$stmt->bindParam(':' . $index, $value);
					unset($value);
					unset($index);
				}
			}
		}
		
		if (isset($array['limit'])) {
			$limit = (int) $array['limit'];
			if ($limit < 0) $limit = 0;
			$stmt->bindParam(':limit', $limit, database::PARAM_INT);
			if (isset($array['offset'])) {
				$offset = (int) $array['offset'];
				$stmt->bindParam(':offset', $offset, database::PARAM_INT);					
			}
		}	
	
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$items = $stmt->fetchAll(database::FETCH_ASSOC);
		
		$this->current = $items;
		
		return $items;
	}
	
	function delete($array) {
		global $db;
		
		$this->args = $array;
	
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error 	=	&singleton::get(__NAMESPACE__ . '\error');

		$site_id	= SITE_ID;
				
		//delete ticket
		$query 	= "DELETE FROM $this->table_name WHERE site_id = :site_id";
		
		//this really should be a where and not a columns array, SHOULD FIX!
		if (isset($array['columns'])) {
			foreach($array['columns'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$query .= ' AND `'.$index.'` = :'.$index;
					unset($index);
					unset($value);
				}
			}
		}
		
		if (isset($array['where'])) {
			foreach($array['where'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$query .= ' AND `'.$index.'` = :'.$index;
					unset($index);
					unset($value);
				}
			}
		}
		
		if (isset($array['id'])) {
			$query .= " AND id = :id";
		}
		
		//echo $query;
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

		if (isset($array['id'])) {
			$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		}
		
		if (isset($array['columns'])) {
			foreach($array['columns'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$stmt->bindParam(':' . $index, $value);
					unset($index);
					unset($value);
				}
			}
		}

		if (isset($array['where'])) {
			foreach($array['where'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$stmt->bindParam(':' . $index, $value);
					unset($index);
					unset($value);
				}
			}
		}
		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

	}
	
	public function count($array = NULL) {
		global $db;
		
		$this->args = $array;
		
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
	
		$site_id		= SITE_ID;


		$query = "SELECT count(*) AS `count` ";
				
		$query .= " FROM $this->table_name";
		
		$query .= " WHERE 1 = 1 AND site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id = :id";
		}
		
		if (isset($array['where'])) {
			foreach($array['where'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$query .= ' AND `'.$index.'` = :'.$index;
					unset($index);
					unset($value);
				}
			}
		}
		
		if (isset($array['like'])) {
			$query .= ' AND (';
			foreach($array['like'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$query .= '`' . $index . '` LIKE :'.$index . ' OR ';
					unset($index);
					unset($value);
				}
			}
			
			if(substr($query, -4) == ' OR ') {	
				$query = substr($query, 0, strlen($query) - 4);
			}
			
			$query .= ')';
		}
			
		//echo $query;
			
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

	
		if (isset($array['id'])) {
			$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		}
		
		if (isset($array['where'])) {
			foreach($array['where'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$stmt->bindParam(':' . $index, $value);
					unset($index);
					unset($value);
				}
			}
		}

		if (isset($array['like'])) {
			foreach($array['like'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$value = "%{$value}%";
					$stmt->bindParam(':' . $index, $value);
					unset($value);
					unset($index);
				}
			}
		}
	
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$count = $stmt->fetch(database::FETCH_ASSOC);
		
		return (int) $count['count'];

	}
	
	public function display_cf_add() {
		
		if (!$this->custom_fields['enabled']) return false;
		
		foreach($this->custom_fields['fields'] as $custom_field_group) { ?>	
			<?php if (!$custom_field_group['enabled']) continue; ?>
			<div class="clearfix"></div>
			<div class="col-lg-8">	
				<p><?php echo safe_output($custom_field_group['name']); ?><br />
				<?php if ($custom_field_group['type'] == 'dropdown') { ?>
					<!-- Not Yet Supported -->
					<?php $fields = $ticket_custom_fields->get_fields(array('ticket_field_group_id' => $custom_field_group['id'])); ?>
					<select name="cf-<?php echo safe_output($custom_field_group['id']); ?>">
					<?php foreach ($fields as $field) { ?>
						<option value="<?php echo safe_output($field['id']); ?>"<?php if (isset($_POST['cf-' . safe_output($custom_field_group['id'])]) && $field['id'] == $_POST['cf-' . safe_output($custom_field_group['id'])]) { echo ' selected="selected"'; } ?>><?php echo safe_output($field['value']); ?></option>
					<?php } ?>
					</select>
				<?php } else if ($custom_field_group['type'] == 'textinput') { ?>
					<input type="text" class="form-control" name="cf-<?php echo safe_output($custom_field_group['id']); ?>" value="<?php if (isset($_POST['cf-' . safe_output($custom_field_group['id'])])) echo safe_output($_POST['cf-' . safe_output($custom_field_group['id'])]); ?>" size="50" />	
				<?php } else if ($custom_field_group['type'] == 'textarea') { ?>
					<div id="no_underline">
						<textarea class="wysiwyg_enabled" name="cf-<?php echo safe_output($custom_field_group['id']); ?>" cols="80" rows="12"><?php if (isset($_POST['cf-' . safe_output($custom_field_group['id'])])) echo safe_output($_POST['cf-' . safe_output($custom_field_group['id'])]); ?></textarea>
					</div>
				<?php } else if ($custom_field_group['type'] == 'date') { ?>
					<input type="text" class="form-control" data-date-format="YYYY-MM-DD" id="cf-<?php echo safe_output($custom_field_group['id']); ?>" name="cf-<?php echo safe_output($custom_field_group['id']); ?>" value="<?php if (isset($_POST['cf-' . safe_output($custom_field_group['id'])])) echo safe_output($_POST['cf-' . safe_output($custom_field_group['id'])]); ?>" size="50" />	
					<script type="text/javascript">
						$(function () {
							$('#cf-<?php echo safe_output($custom_field_group['id']); ?>').datetimepicker({
								pickTime: false,
								pick12HourFormat: false
							});
						});
					</script>	
				<?php } else if ($custom_field_group['type'] == 'datetime') { ?>
					<input type="text" class="form-control" data-date-format="YYYY-MM-DD HH:mm" id="cf-<?php echo safe_output($custom_field_group['id']); ?>" name="cf-<?php echo safe_output($custom_field_group['id']); ?>" value="<?php if (isset($_POST['cf-' . safe_output($custom_field_group['id'])])) echo safe_output($_POST['cf-' . safe_output($custom_field_group['id'])]); ?>" size="50" />	
					<script type="text/javascript">
						$(function () {
							$('#cf-<?php echo safe_output($custom_field_group['id']); ?>').datetimepicker({
								pickTime: true,
								pick12HourFormat: false
							});
						});
					</script>
				<?php } ?>		
				</p>
			</div>
		<?php }	
	}
	public function display_cf_edit() {

		if (!$this->custom_fields['enabled']) return false;
				
		foreach($this->custom_fields['fields'] as $custom_field_group) { ?>		
			<?php if (!$custom_field_group['enabled']) continue; ?>
			<div class="clearfix"></div>
			<div class="col-lg-8">	
				<p><?php echo safe_output($custom_field_group['name']); ?><br />
				<?php if ($custom_field_group['type'] == 'dropdown') { ?>
					<!-- Not Yet Supported -->
					<?php $fields = $ticket_custom_fields->get_fields(array('ticket_field_group_id' => $custom_field_group['id'])); ?>
					<select name="cf-<?php echo safe_output($custom_field_group['id']); ?>">
					<?php foreach ($fields as $field) { ?>
						<option value="<?php echo safe_output($field['id']); ?>"<?php if (isset($current_fields[0]['value']) && ($current_fields[0]['value'] == $field['id'])) { echo ' selected="selected"'; } ?>><?php echo safe_output($field['value']); ?></option>
					<?php } ?>
					</select>
				<?php } else if ($custom_field_group['type'] == 'textinput') { ?>
					<input type="text" class="form-control" name="cf-<?php echo safe_output($custom_field_group['id']); ?>" value="<?php if (isset($this->current[0][$custom_field_group['column_name']])) echo safe_output($this->current[0][$custom_field_group['column_name']]); ?>" size="50" />	
				<?php } else if ($custom_field_group['type'] == 'textarea') { ?>
					<textarea class="wysiwyg_enabled" name="cf-<?php echo safe_output($custom_field_group['id']); ?>" cols="80" rows="12"><?php if (isset($this->current[0][$custom_field_group['column_name']])) echo safe_output($this->current[0][$custom_field_group['column_name']]); ?></textarea>
				<?php } else if ($custom_field_group['type'] == 'date') { ?>
					<input type="text" data-date-format="YYYY-MM-DD" class="form-control" id="cf-<?php echo safe_output($custom_field_group['id']); ?>" name="cf-<?php echo safe_output($custom_field_group['id']); ?>" value="<?php if (isset($this->current[0][$custom_field_group['column_name']])) echo safe_output($this->current[0][$custom_field_group['column_name']]); ?>" size="50" />	
					<script type="text/javascript">
						$(function () {
							$('#cf-<?php echo safe_output($custom_field_group['id']); ?>').datetimepicker({
								pickTime: false,
								pick12HourFormat: false
							});
						});
					</script>			
				<?php } else if ($custom_field_group['type'] == 'datetime') { ?>
					<input type="text" data-date-format="YYYY-MM-DD HH:mm" class="form-control" id="cf-<?php echo safe_output($custom_field_group['id']); ?>" name="cf-<?php echo safe_output($custom_field_group['id']); ?>" value="<?php if (isset($this->current[0][$custom_field_group['column_name']])) echo safe_output($this->current[0][$custom_field_group['column_name']]); ?>" size="50" />	
					<script type="text/javascript">
						$(function () {
							$('#cf-<?php echo safe_output($custom_field_group['id']); ?>').datetimepicker({
								pickTime: true,
								pick12HourFormat: false
							});
						});
					</script>
				<?php } ?>	
				</p>
			</div>
		<?php }	
	}
	public function display_cf_view() {
		if (!$this->custom_fields['enabled']) return false;
				
		foreach($this->custom_fields['fields'] as $custom_field_group) { ?>	
			<?php if (!$custom_field_group['enabled']) continue; ?>
			<div class="clearfix"></div>
			<?php if (isset($this->current[0][$custom_field_group['column_name']]) && !empty($this->current[0][$custom_field_group['column_name']])) { ?>
				<h5><?php echo safe_output($custom_field_group['name']); ?></h5>
				<?php if ($custom_field_group['type'] == 'textinput') { ?>
					<p><?php echo safe_output($this->current[0][$custom_field_group['column_name']]); ?></p>
				<?php } else if ($custom_field_group['type'] == 'textarea') { ?>
					<?php echo html_output($this->current[0][$custom_field_group['column_name']]); ?>
				<?php } else if ($custom_field_group['type'] == 'dropdown') { 
					$set_fields = $ticket_custom_fields->get_fields(array('ticket_field_group_id' => $custom_field_group['id']));
					?>
					<!-- No Yet Supported -->
					<?php foreach ($set_fields as $field) { ?>
						<?php if (isset($fields[0]['value']) && ($field['id'] == $fields[0]['value'])) { ?>
						<p><?php echo safe_output($field['value']); ?></p>
						<?php }?>
					<?php } ?>
				<?php } else if ($custom_field_group['type'] == 'date') { ?> 
					<p><?php echo safe_output(nice_date($this->current[0][$custom_field_group['column_name']])); ?></p>	
				<?php } else if ($custom_field_group['type'] == 'datetime') { ?> 
					<p><?php echo safe_output(nice_datetime($this->current[0][$custom_field_group['column_name']])); ?></p>	
				<?php } ?>
			<?php } ?>
		<?php }
	}
	
	public function display_cf_settings() {
	
		$language 			= &singleton::get(__NAMESPACE__ . '\language');
		$config 			= &singleton::get(__NAMESPACE__ . '\config');
	
		?>										
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="pull-left">
					<?php if (!empty($this->custom_fields['name'])) { ?>
						<h1 class="panel-title"><?php echo safe_output($this->custom_fields['name']); ?> - <?php echo safe_output($language->get('Custom Fields')); ?></h1>
					<?php } else { ?>
						<h1 class="panel-title"><?php echo safe_output($language->get('Custom Fields')); ?></h1>					
					<?php } ?>
				</div>
				<div class="pull-right">
					<p><a href="<?php echo safe_output($config->get('address')); ?>/settings/add_cf/?table_access=<?php echo safe_output(get_called_class()); ?>" class="btn btn-default btn-sm"><?php echo $language->get('Add'); ?></a></p>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="panel-body">
				<section id="no-more-tables">				
					<table class="table table-striped table-bordered">
						<thead>
							<tr>
								<th><?php echo $language->get('Name'); ?></th>
								<th><?php echo $language->get('Type'); ?></th>
								<th><?php echo $language->get('Enabled'); ?></th>
							</tr>
						</thead>
						
						<tbody>

							<?php $i = 0; 
								foreach($this->custom_fields['fields'] as $custom_field_group) { ?>
								<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
									<td data-title="Name" class="centre"><a href="<?php echo safe_output($config->get('address')); ?>/settings/edit_cf/<?php echo (int) $custom_field_group['id']; ?>/?table_access=<?php echo safe_output(get_called_class()); ?>"><?php echo safe_output($custom_field_group['name']); ?></a></td>
									<td data-title="Type" class="centre">
									<?php
										switch($custom_field_group['type']) {
											case 'textinput':
												echo $language->get('Text Input');
											break;
											
											case 'textarea':
												echo $language->get('Text Area');
											break;
											
											case 'dropdown':
												echo $language->get('Drop Down');
											break;
											
											case 'date':
												echo $language->get('Date');
											break;
											
											case 'datetime':
												echo $language->get('Date & Time');
											break;
										}
									?>						
									</td>
									<td data-title="Enabled" class="centre"><?php if ($custom_field_group['enabled'] == '0') { echo $language->get('No'); } else { echo $language->get('Yes'); } ?></td>
								</tr>
							<?php $i++; } ?>
						</tbody>
					</table>
				</section>
			
			</div>
		</div>	
	<?php
	}
}


?>