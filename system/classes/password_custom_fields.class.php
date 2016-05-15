<?php
/*
	Copyright Dalegroup Pty Ltd 2012
*/
namespace mrpassword;

class password_custom_fields {

	function add_field($array) {
		global $db;
		
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$log			= &singleton::get(__NAMESPACE__ . '\log');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$auth 			= &singleton::get(__NAMESPACE__ . '\auth');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');

		$site_id		= SITE_ID;
		
		$query = "INSERT INTO $tables->password_fields (password_field_group_id, site_id";
		
		if (isset($array['value'])) {
			$query .= ", value";
		}

		$query .= ") VALUES (:password_field_group_id, :site_id";
		
		if (isset($array['value'])) {
			$query .= ", :value";
		}
		
		$query .= ")";
	
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}

		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);		
		$stmt->bindParam(':password_field_group_id', $array['password_field_group_id'], database::PARAM_INT);

		if (isset($array['value'])) {
			$stmt->bindParam(':value', $array['value'], database::PARAM_STR);
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
	
	function edit_field($array) {
		global $db;
		
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$log			= &singleton::get(__NAMESPACE__ . '\log');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$auth 			= &singleton::get(__NAMESPACE__ . '\auth');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');

		$site_id		= SITE_ID;
		
		$query = "UPDATE $tables->password_fields SET value = :value";

	
		$query .= " WHERE id = :id AND site_id = :site_id";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		$stmt->bindParam(':value', $array['value'], database::PARAM_STR);
								
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
	}
	
	function get_values($array) {
		global $db;
		
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$log			= &singleton::get(__NAMESPACE__ . '\log');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$auth 			= &singleton::get(__NAMESPACE__ . '\auth');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');

		$site_id		= SITE_ID;

		
		$query = "SELECT * FROM $tables->password_field_values WHERE site_id = :site_id";
		
		
		if (isset($array['password_field_group_id'])) {
			$query .= " AND password_field_group_id = :password_field_group_id";
		}
		if (isset($array['password_id'])) {
			$query .= " AND password_id = :password_id";
		}	
		if (isset($array['id'])) {
			$query .= " AND id = :id";
		}	
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}

		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

		if (isset($array['password_field_group_id'])) {
			$stmt->bindParam(':password_field_group_id', $array['password_field_group_id'], database::PARAM_INT);
		}
		if (isset($array['password_id'])) {
			$stmt->bindParam(':password_id', $array['password_id'], database::PARAM_INT);
		}
		if (isset($array['id'])) {
			$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		}	
		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$fields = $stmt->fetchAll(database::FETCH_ASSOC);
		
		return $fields;
		
	}
	
	function add_value($array) {
		global $db;
			
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$log			= &singleton::get(__NAMESPACE__ . '\log');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$auth 			= &singleton::get(__NAMESPACE__ . '\auth');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');

		$site_id		= SITE_ID;
				
		$query = "INSERT INTO $tables->password_field_values (site_id, password_id, password_field_group_id, value";
		
	
		
		$query .= ") VALUES (:site_id, :password_id, :password_field_group_id, :value";
		

		
		$query .= ")";
	
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}

		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);		
		$stmt->bindParam(':password_id', $array['password_id'], database::PARAM_INT);
		$stmt->bindParam(':password_field_group_id', $array['password_field_group_id'], database::PARAM_INT);
		$stmt->bindParam(':value', $array['value'], database::PARAM_STR);

		try {
			$stmt->execute();
			$id = $db->lastInsertId();

		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
	
		return $id;	
	}
	
	function add_group($array) {
		global $db;
			
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$log			= &singleton::get(__NAMESPACE__ . '\log');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$auth 			= &singleton::get(__NAMESPACE__ . '\auth');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');

		$site_id		= SITE_ID;
	
			
		$query = "INSERT INTO $tables->password_field_group (type, site_id";
		
		if (isset($array['name'])) {
			$query .= ", name";
		}
		if (isset($array['list_view'])) {
			$query .= ", list_view";
		}
		if (isset($array['enabled'])) {
			$query .= ", enabled";
		}
		if (isset($array['default_field_id'])) {
			$query .= ", default_field_id";
		}
		
		$query .= ") VALUES (:type, :site_id";
		
		if (isset($array['name'])) {
			$query .= ", :name";
		}
		if (isset($array['list_view'])) {
			$query .= ", :list_view";
		}
		if (isset($array['enabled'])) {
			$query .= ", :enabled";
		}
		if (isset($array['default_field_id'])) {
			$query .= ", :default_field_id";
		}
		
		$query .= ")";
	
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}

		$stmt->bindParam(':site_id', $site_id, database::PARAM_STR);		
		$stmt->bindParam(':type', $array['type'], database::PARAM_STR);

		if (isset($array['name'])) {
			$stmt->bindParam(':name', $array['name'], database::PARAM_STR);
		}
		if (isset($array['list_view'])) {
			$stmt->bindParam(':list_view', $array['list_view'], database::PARAM_INT);
		}
		if (isset($array['enabled'])) {
			$stmt->bindParam(':enabled', $array['enabled'], database::PARAM_INT);
		}
		if (isset($array['default_field_id'])) {
			$stmt->bindParam(':default_field_id', $array['default_field_id'], database::PARAM_INT);
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
	
	function delete_value($array) {
		global $db;
			
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$log			= &singleton::get(__NAMESPACE__ . '\log');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$auth 			= &singleton::get(__NAMESPACE__ . '\auth');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');

		$site_id		= SITE_ID;
				
		$query = "DELETE FROM $tables->password_field_values WHERE site_id = :site_id AND password_id = :password_id";
			
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}

		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);		
		$stmt->bindParam(':password_id', $array['password_id'], database::PARAM_INT);

		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
	
	}
	
	
	function edit_group($array) {
		global $db;
			
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$log			= &singleton::get(__NAMESPACE__ . '\log');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$auth 			= &singleton::get(__NAMESPACE__ . '\auth');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');

		$site_id		= SITE_ID;		
		
		
		$query = "UPDATE $tables->password_field_group SET type = :type";

		if (isset($array['name'])) {
			$query .= ", name = :name";
		}
		if (isset($array['list_view'])) {
			$query .= ", list_view = :list_view";
		}
		if (isset($array['enabled'])) {
			$query .= ", enabled = :enabled";
		}
		
		if (isset($array['default_field_id'])) {
			$query .= ", default_field_id = :default_field_id";
		}
		
		$query .= " WHERE id = :id AND site_id = :site_id";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}
		
		$stmt->bindParam(':type', $array['type'], database::PARAM_STR);
		$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

		
		if (isset($array['name'])) {
			$stmt->bindParam(':name', $array['name'], database::PARAM_STR);
		}
		if (isset($array['list_view'])) {
			$stmt->bindParam(':list_view', $array['list_view'], database::PARAM_INT);
		}
		if (isset($array['enabled'])) {
			$stmt->bindParam(':enabled', $array['enabled'], database::PARAM_INT);
		}
		if (isset($array['default_field_id'])) {
			$stmt->bindParam(':default_field_id', $array['default_field_id'], database::PARAM_INT);
		}
						
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
	}
	

	
	
	function get_groups($array = NULL) {
		global $db;
		
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$log			= &singleton::get(__NAMESPACE__ . '\log');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$auth 			= &singleton::get(__NAMESPACE__ . '\auth');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');

		$site_id		= SITE_ID;
		
		$query = "SELECT * FROM $tables->password_field_group WHERE site_id = :site_id";
		
		if (isset($array['list_view'])) {
			$query .= " AND list_view = :list_view";
		}

		if (isset($array['enabled'])) {
			$query .= " AND enabled = :enabled";
		}
		
		if (isset($array['default_field_id'])) {
			$query .= " AND default_field_id = :default_field_id";
		}
		if (isset($array['id'])) {
			$query .= " AND id = :id";
		}	
		
		$query .= " ORDER BY id DESC";

		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}

		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

		if (isset($array['enabled'])) {
			$stmt->bindParam(':enabled', $array['enabled'], database::PARAM_INT);
		}
		if (isset($array['default_field_id'])) {
			$stmt->bindParam(':default_field_id', $array['default_field_id'], database::PARAM_INT);
		}
		if (isset($array['id'])) {
			$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		}
	
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$fields = $stmt->fetchAll(database::FETCH_ASSOC);
		
		return $fields;
	}
	
	public function get_fields($array = NULL) {
		global $db;
		
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$log			= &singleton::get(__NAMESPACE__ . '\log');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$auth 			= &singleton::get(__NAMESPACE__ . '\auth');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');

		$site_id		= SITE_ID;

		
		$query = "SELECT * FROM $tables->password_fields WHERE site_id = :site_id";
		
		if (isset($array['password_field_group_id'])) {
			$query .= " AND password_field_group_id = :password_field_group_id";
		}
		
		$query .= " ORDER BY id";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

			
		if (isset($array['password_field_group_id'])) {
			$stmt->bindParam(':password_field_group_id', $array['password_field_group_id'], database::PARAM_INT);
		}			
	
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$fields = $stmt->fetchAll(database::FETCH_ASSOC);
		
		return $fields;
	}
	
	function delete_field($array = NULL) {
		global $db;
		
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$log			= &singleton::get(__NAMESPACE__ . '\log');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$auth 			= &singleton::get(__NAMESPACE__ . '\auth');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');

		$site_id		= SITE_ID;
		
		$query = "DELETE FROM $tables->password_fields WHERE site_id = :site_id";

		if (isset($array['id'])) {
			$query .= " AND id = :id";
		}
		if (isset($array['password_field_group_id'])) {
			$query .= " AND password_field_group_id = :password_field_group_id";
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
		if (isset($array['password_field_group_id'])) {
			$stmt->bindParam(':password_field_group_id', $array['password_field_group_id'], database::PARAM_INT);
		}
		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
	}
	
	function delete_group($array = NULL) {
		global $db;
		
		if (!isset($array['id'])) return false;
		
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$log			= &singleton::get(__NAMESPACE__ . '\log');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$auth 			= &singleton::get(__NAMESPACE__ . '\auth');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');

		$site_id		= SITE_ID;
		
		$query = "DELETE FROM $tables->password_field_values WHERE site_id = :site_id";

		
		if (isset($array['id'])) {
			$query .= " AND password_field_group_id = :id";
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
		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		
		$query = "DELETE FROM $tables->password_fields WHERE site_id = :site_id";

		
		if (isset($array['id'])) {
			$query .= " AND password_field_group_id = :id";
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
		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "DELETE FROM $tables->password_field_group WHERE site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id = :id";
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
				
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
	}
	
}

?>