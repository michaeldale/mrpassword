<?php
/**
 * 	Passwords Class
 *	Copyright Dalegroup Pty Ltd 2013
 *	support@dalegroup.net
 *
 *
 * @package     mrpassword
 * @author      Michael Dale <mdale@dalegroup.net>
 */

 
namespace mrpassword;

class passwords {

	function __construct() {

	}
	
	public function add($array) {
		global $db;
				
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$log			= &singleton::get(__NAMESPACE__ . '\log');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$auth 			= &singleton::get(__NAMESPACE__ . '\auth');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		$encryption 	= &singleton::get(__NAMESPACE__ . '\encryption');
		$storage 		= &singleton::get(__NAMESPACE__ . '\storage');

		$site_id	= SITE_ID;
		$date_added = datetime();

		$query = "INSERT INTO $tables->passwords (name, username, password, user_id, site_id, date_added, last_modified, encryption_level";
		
		if (isset($array['description'])) {
			$query .= ", description";
		}
		if (isset($array['category_id'])) {
			$query .= ", category_id";
		}
		if (isset($array['old'])) {
			$query .= ", old";
		}
		if (isset($array['parent_id'])) {
			$query .= ", parent_id";
		}		
		if (isset($array['url'])) {
			$query .= ", url";
		}	
		
		$query .= ") VALUES (:name, :username, :password, :user_id, :site_id, :date_added, :last_modified, :encryption_level";
	
		if (isset($array['description'])) {
			$query .= ", :description";
		}
		if (isset($array['category_id'])) {
			$query .= ", :category_id";
		}
		if (isset($array['old'])) {
			$query .= ", :old";
		}
		if (isset($array['parent_id'])) {
			$query .= ", :parent_id";
		}
		if (isset($array['url'])) {
			$query .= ", :url";
		}
		
		$query .= ")";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}
		
		$stmt->bindParam(':name', $array['name'], database::PARAM_STR);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		$stmt->bindParam(':date_added', $date_added, database::PARAM_STR);
		$stmt->bindParam(':last_modified', $date_added, database::PARAM_STR);

		$username = $array['username'];
		$stmt->bindParam(':username', $username, database::PARAM_STR);
			
		$password	= $encryption->encrypt($array['password']);
		$stmt->bindParam(':password', $password, database::PARAM_STR);

		if (isset($array['description'])) {
			$description = $array['description'];
			$stmt->bindParam(':description', $description, database::PARAM_STR);
		}
		if (isset($array['category_id'])) {
			$category_id = $array['category_id'];
			$stmt->bindParam(':category_id', $category_id, database::PARAM_INT);
		}
		if (isset($array['old'])) {
			$old = $array['old'];
			$stmt->bindParam(':old', $old, database::PARAM_INT);
		}
		if (isset($array['parent_id'])) {
			$parent_id = $array['parent_id'];
			$stmt->bindParam(':parent_id', $parent_id, database::PARAM_INT);
		}
		if (isset($array['url'])) {
			$url = $array['url'];
			$stmt->bindParam(':url', $url, database::PARAM_INT);
		}		
		
		$encryption_level = $config->get('encryption_level');
		$stmt->bindParam(':encryption_level', $encryption_level, database::PARAM_INT);			
		
		if (isset($array['user_id'])) {
			$user_id = (int) $array['user_id'];
		}
		else {
			$user_id = $auth->get('id');
		}
		
		$stmt->bindParam(':user_id', $user_id, database::PARAM_INT);
		
		
		try {
			$stmt->execute();
			$id = $db->lastInsertId();
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
				
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Password Added "<a href="'. $config->get('address') .'/passwords/view/'.(int)$id.'/">' . safe_output($array['name']) . '</a>"';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'add';
		$log_array['event_source'] = 'passwords';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
		
		if (isset($array['attach_file_ids']) && !empty($array['attach_file_ids'])) {
			foreach($array['attach_file_ids'] as $file_id) {
				if ($file_id !== false) {
					$storage->add_file_to_password(array('file_id' => (int) $file_id, 'password_id' => $id));
				}
			}
		}
				
		return $id;
		
	}
	
	public function count($array = NULL) {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error =	&singleton::get(__NAMESPACE__ . '\error');
		$site_id	= SITE_ID;
				
		$query = "SELECT count(*) AS `count` FROM $tables->passwords WHERE site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id = :id";
		}
		if (isset($array['user_id'])) {
			$query .= " AND user_id = :user_id";
		}
		if (isset($array['old'])) {
			$query .= " AND old = :old";
		}
		if (isset($array['parent_id'])) {
			$query .= " AND parent_id = :parent_id";
		}
		if (isset($array['encryption_level'])) {
			$query .= " AND encryption_level = :encryption_level";
		}		
		
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id);

		if (isset($array['id'])) {
			$id = $array['id'];
			$stmt->bindParam(':id', $id, database::PARAM_INT);
		}
		
		if (isset($array['user_id'])) {
			$user_id = $array['user_id'];
			$stmt->bindParam(':user_id', $user_id, database::PARAM_INT);
		}
		
		if (isset($array['old'])) {
			$old = $array['old'];
			$stmt->bindParam(':old', $old, database::PARAM_INT);
		}
		if (isset($array['parent_id'])) {
			$parent_id = $array['parent_id'];
			$stmt->bindParam(':parent_id', $parent_id, database::PARAM_INT);
		}
		if (isset($array['encryption_level'])) {
			$encryption_level = $array['encryption_level'];
			$stmt->bindParam(':encryption_level', $encryption_level, database::PARAM_INT);
		}
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		$count = $stmt->fetch(database::FETCH_ASSOC);
		
		return (int) $count['count'];
	}
	
	
	//stores the old password and custom fields for history
	private function store_old($array) {
	
		$custom_fields				= &singleton::get(__NAMESPACE__ . '\custom_fields');
		$password_custom_fields		= &singleton::get(__NAMESPACE__ . '\password_custom_fields');
		$encryption 				= &singleton::get(__NAMESPACE__ . '\encryption');

	
		//get password
		$password_array 	= $this->get(array('id' => $array['id']));
		
		if (count($password_array) == 1) {
			//store password
			$password = $password_array[0];
		
			foreach($password as $index => $value) {
				if ($index == 'password') {
					$add_password[$index]	= $encryption->decrypt($value);
				}
				else {
					$add_password[$index]	= $value;
				}
			}
		
			unset($add_password['id']);
			unset($add_password['site_id']);
			
			$add_password['old']		= 1;
			$add_password['parent_id']	= $password['id'];
			
			$new_id = $this->add($add_password);
				
			//get custom fields
			$field_array = $custom_fields->get(array('password_id' => $password['id']));
			
			//store custom fields
			if (!empty($field_array)) {
				foreach($field_array as $field) {
					$custom_fields->add(array('name' => $field['name'], 'value' => $encryption->decrypt($field['value']), 'password_id'	=> $new_id));	
				}
				unset($field);
			}
			
			//get global custom fields
			$custom_field_groups = $password_custom_fields->get_groups(array('enabled' => 1));
			
			if (!empty($custom_field_groups)) {
				foreach($custom_field_groups as $custom_field_group) {
					$fields = $password_custom_fields->get_values(array('password_field_group_id' => $custom_field_group['id'], 'password_id' => (int) $password['id'])); 
					if (!empty($fields) && !empty($fields[0]['value'])) {						
						if ($custom_field_group['type'] == 'textinput' || $custom_field_group['type'] == 'textarea') {
							$value = safe_output($fields[0]['value']);
						} else if ($custom_field_group['type'] == 'dropdown') { 
							$set_fields = $password_custom_fields->get_fields(array('password_field_group_id' => $custom_field_group['id']));

							foreach ($set_fields as $field) {
								if (isset($fields[0]['value']) && ($field['id'] == $fields[0]['value'])) {
									$value = safe_output($field['value']);
									continue;
								}
							}
						}
						
						$edit_array['password_field_group_id']	= $custom_field_group['id'];
						$edit_array['password_id']				= $new_id;
						$edit_array['value']					= $value;
					
						$password_custom_fields->add_value($edit_array);
						unset($value);
					}
				}	
			}
		}
	}
	
	public function edit($array) {
		global $db;
		
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$auth			= &singleton::get(__NAMESPACE__ . '\auth');
		$log			= &singleton::get(__NAMESPACE__ . '\log');
		$config			= &singleton::get(__NAMESPACE__ . '\config');
		$encryption 	= &singleton::get(__NAMESPACE__ . '\encryption');

		$site_id		= SITE_ID;
		$last_modified 	= datetime();
		
		if (!isset($array['store_old']) || $array['store_old'] == true) {
			$this->store_old(array('id' => $array['id']));
		}
		
		$query = "UPDATE $tables->passwords SET last_modified = :last_modified";

		if (isset($array['name'])) {
			$query .= ", name = :name";		
		}
		if (isset($array['username'])) {
			$query .= ", username = :username";
		}
		if (isset($array['password'])) {
			$query .= ", password = :password";
		}
		if (isset($array['description'])) {
			$query .= ", description = :description";
		}
		if (isset($array['category_id'])) {
			$query .= ", category_id = :category_id";
		}
		if (isset($array['old'])) {
			$query .= ", old = :old";
		}
		if (isset($array['parent_id'])) {
			$query .= ", parent_id = :parent_id";
		}
		if (isset($array['url'])) {
			$query .= ", url = :url";
		}		
		if (isset($array['encryption_level'])) {
			$query .= ", encryption_level = :encryption_level";
		}			
		
		$query .= " WHERE id = :id AND site_id = :site_id";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		$stmt->bindParam(':last_modified', $last_modified, database::PARAM_INT);

		if (isset($array['name'])) {
			$stmt->bindParam(':name', $array['name'], database::PARAM_STR);
		}			
		if (isset($array['username'])) {
			$stmt->bindParam(':username', $array['username'], database::PARAM_STR);
		}	
		if (isset($array['description'])) {
			$stmt->bindParam(':description', $array['description'], database::PARAM_STR);
		}	
		if (isset($array['category_id'])) {
			$stmt->bindParam(':category_id', $array['category_id'], database::PARAM_INT);
		}
		if (isset($array['password'])) {
			$password	= $encryption->encrypt($array['password']);
			$stmt->bindParam(':password', $password, database::PARAM_STR);
		}
		if (isset($array['parent_id'])) {
			$stmt->bindParam(':parent_id', $array['parent_id'], database::PARAM_INT);
		}
		if (isset($array['url'])) {
			$stmt->bindParam(':url', $array['url'], database::PARAM_INT);
		}		
		if (isset($array['encryption_level'])) {
			$stmt->bindParam(':encryption_level', $array['encryption_level'], database::PARAM_INT);
		}
		
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		if (isset($array['name'])) {
			$log_array['event_severity'] = 'notice';
			$log_array['event_number'] = E_USER_NOTICE;
			$log_array['event_description'] = 'Password Edited "<a href="'. $config->get('address') .'/passwords/view/'.(int)$array['id'] . '/">' . safe_output($array['name']) . '</a>"';
			$log_array['event_file'] = __FILE__;
			$log_array['event_file_line'] = __LINE__;
			$log_array['event_type'] = 'edit';
			$log_array['event_source'] = 'passwords';
			$log_array['event_version'] = '1';
			$log_array['log_backtrace'] = false;	
					
			$log->add($log_array);
		}
				
		
		return true;
	
	}
	public function get($array = NULL) {
		global $db;
		
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$site_id		= SITE_ID;
		$order_array 	= array('id', 'name', 'username', 'date_added', 'category');

		$query = "SELECT p.*";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$query .= ", s.access_level";
			$query .= ", c.name AS `category_name`";
		}
		
		$query .= " FROM $tables->passwords p";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$query .= " LEFT JOIN $tables->categories c ON (c.id = p.category_id)";
			$query .= " LEFT JOIN $tables->shares s ON (c.id = s.category_id)";
		}
		
		$query .= " WHERE 1 = 1 AND p.site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND p.id = :id";
		}
		if (isset($array['user_id'])) {
			$query .= " AND p.user_id = :user_id";
		}
		if (isset($array['category_id'])) {
			$query .= " AND p.category_id = :category_id";
		}
		
		if (isset($array['global'])) {
			$query .= " AND c.global = :global";
		}
		else if (isset($array['global_or_null'])) {
			$query .= " AND (c.global = :global_or_null OR c.global IS NULL)";		
		}
		
		if (isset($array['shared_user_id'])) {
			$query .= " AND s.shared_user_id = :shared_user_id";
		}
		if (isset($array['old'])) {
			$query .= " AND p.old = :old";
		}
		if (isset($array['parent_id'])) {
			$query .= " AND p.parent_id = :parent_id";
		}
		if (isset($array['encryption_level'])) {
			$query .= " AND p.encryption_level = :encryption_level";
		}		
		
		
		if (isset($array['like_search'])) {
			$query .= " AND (p.name LIKE :like_search OR p.username LIKE :like_search OR p.description LIKE :like_search OR p.url LIKE :like_search)";		
		}
		
		$query .= " GROUP BY p.id";
		
		if (isset($array['order_by']) && in_array($array['order_by'], $order_array)) {
			if ($array['order_by'] == 'category') {
				if (isset($array['order']) && $array['order'] == 'desc') {
					$query .= ' ORDER BY c.name DESC';
				}
				else {
					$query .= ' ORDER BY c.name';
				}				
			}
			else {
				if (isset($array['order']) && $array['order'] == 'desc') {
					$query .= ' ORDER BY p.' . $array['order_by'] . ' DESC';
				}
				else {
					$query .= ' ORDER BY p.' . $array['order_by'];
				}
			}			
		}
		else {
			if (isset($array['order']) && $array['order'] == 'desc') {
				$query .= ' ORDER BY p.name DESC';
			}
			else {
				$query .= " ORDER BY p.name";
			}	
		}
				
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
		if (isset($array['user_id'])) {
			$stmt->bindParam(':user_id', $array['user_id'], database::PARAM_INT);
		}
		if (isset($array['shared_user_id'])) {
			$stmt->bindParam(':shared_user_id', $array['shared_user_id'], database::PARAM_INT);
		}
		if (isset($array['category_id'])) {
			$stmt->bindParam(':category_id', $array['category_id'], database::PARAM_INT);
		}		
		if (isset($array['old'])) {
			$stmt->bindParam(':old', $array['old'], database::PARAM_INT);
		}
		if (isset($array['parent_id'])) {
			$stmt->bindParam(':parent_id', $array['parent_id'], database::PARAM_INT);
		}	
		if (isset($array['encryption_level'])) {
			$stmt->bindParam(':encryption_level', $array['encryption_level'], database::PARAM_INT);
		}		
		if (isset($array['global'])) {
			$stmt->bindParam(':global', $array['global'], database::PARAM_INT);
		}
		else if (isset($array['global_or_null'])) {
			$stmt->bindParam(':global_or_null', $array['global_or_null'], database::PARAM_INT);
		}
		if (isset($array['like_search'])) {
			$value = $array['like_search'];
			$value = "%{$value}%";
			$stmt->bindParam(':like_search', $value, database::PARAM_STR);
			unset($value);
		}		
		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$passwords = $stmt->fetchAll(database::FETCH_ASSOC);
		
		return $passwords;
	}
	
	function delete($array) {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error 	=	&singleton::get(__NAMESPACE__ . '\error');
		$log 	=	&singleton::get(__NAMESPACE__ . '\log');

		$site_id	= SITE_ID;

		
		//delete password
		$query 	= "DELETE FROM $tables->passwords WHERE site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id = :id";
		}
		if (isset($array['user_id'])) {
			$query .= " AND user_id = :user_id";
		}
		if (isset($array['parent_id'])) {
			$query .= " AND parent_id = :parent_id";
		}
		if (isset($array['old'])) {
			$query .= " AND old = :old";
		}		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

		if (isset($array['id'])) {
			$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		}
		if (isset($array['user_id'])) {
			$stmt->bindParam(':user_id', $array['user_id'], database::PARAM_INT);
		}
		if (isset($array['parent_id'])) {
			$stmt->bindParam(':parent_id', $array['parent_id'], database::PARAM_INT);
		}
		if (isset($array['old'])) {
			$stmt->bindParam(':old', $array['old'], database::PARAM_INT);
		}		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Passwords Deleted';
		if (isset($array['id'])) {
			$log_array['event_description'] = 'Password Deleted ID ' . safe_output($array['id']);
		}
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'delete';
		$log_array['event_source'] = 'passwords';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
		
	}
	
	public function get_files($array) {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error 	=	&singleton::get(__NAMESPACE__ . '\error');
		$log 	=	&singleton::get(__NAMESPACE__ . '\log');

		$site_id	= SITE_ID;
		
		$query = "SELECT $tables->storage.* FROM $tables->files_to_passwords LEFT JOIN $tables->storage ON $tables->files_to_passwords.file_id = $tables->storage.id WHERE 1 = 1 AND $tables->storage.site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND $tables->files_to_passwords.password_id = :id";
		}
		
		if (isset($array['file_id'])) {
			$query .= " AND $tables->files_to_passwords.file_id = :file_id";
		}

		if (isset($array['private'])) {
			$query .= " AND $tables->files_to_passwords.private = :private";
		}
		
		if (isset($array['password_ids'])) {
							
			$return = " AND $tables->files_to_passwords.password_id IN (";
			
			foreach ($array['password_ids'] as $index => $value) {
				$return .= ':password_ids' . (int) $index . ',';
			}
			
			if(substr($return, -1) == ',') {	
				$return = substr($return, 0, strlen($return) - 1);
			}
			
			$return .= ')';
			
			$query .= $return;

		}
		
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
		if (isset($array['file_id'])) {
			$stmt->bindParam(':file_id', $array['file_id'], database::PARAM_INT);
		}
		if (isset($array['private'])) {
			$stmt->bindParam(':private', $array['private'], database::PARAM_INT);
		}
		if (isset($array['password_ids'])) {	
			foreach ($array['password_ids'] as $index => $value) {
				$t_id = (int) $value;
				$stmt->bindParam(':password_ids' . (int) $index, $t_id, database::PARAM_INT);
				unset($t_id);
			}
		}	
	
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$files = $stmt->fetchAll(database::FETCH_ASSOC);
		
		return $files;
		
	}
}


?>