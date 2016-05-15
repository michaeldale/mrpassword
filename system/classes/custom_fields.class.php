<?php
namespace mrpassword;

class custom_fields {

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

		$site_id	= SITE_ID;

		$query = "INSERT INTO $tables->custom_fields (name, value, password_id, site_id, encryption_level";
		
		$query .= ") VALUES (:name, :value, :password_id, :site_id, :encryption_level";
		
		$query .= ")";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}
		
		$stmt->bindParam(':name', $array['name'], database::PARAM_STR);
		
		$value	= $encryption->encrypt($array['value']);
		$stmt->bindParam(':value', $value, database::PARAM_STR);
		$stmt->bindParam(':password_id', $array['password_id'], database::PARAM_INT);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		$encryption_level = $config->get('encryption_level');
		$stmt->bindParam(':encryption_level', $encryption_level, database::PARAM_INT);			
		
		
		try {
			$stmt->execute();
			$id = $db->lastInsertId();
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
				
		return $id;
		
	}
	
	public function count($array = NULL) {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error =	&singleton::get(__NAMESPACE__ . '\error');
		$site_id	= SITE_ID;
				
		$query = "SELECT count(*) AS `count` FROM $tables->custom_fields WHERE site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id = :id";
		}
		if (isset($array['password_id'])) {
			$query .= " AND password_id = :password_id";
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
		
		if (isset($array['password_id'])) {
			$password_id = $array['password_id'];
			$stmt->bindParam(':password_id', $password_id, database::PARAM_INT);
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
	
	public function edit($array) {
		global $db;
		
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$auth			= &singleton::get(__NAMESPACE__ . '\auth');
		$log			= &singleton::get(__NAMESPACE__ . '\log');
		$config			= &singleton::get(__NAMESPACE__ . '\config');
		$encryption 	= &singleton::get(__NAMESPACE__ . '\encryption');

		$site_id	= SITE_ID;

		
		$query = "UPDATE $tables->custom_fields SET name = :name";

		if (isset($array['value'])) {
			$query .= ", value = :value";
		}
		if (isset($array['password_id'])) {
			$query .= ", password_id = :password_id";
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
		
		$stmt->bindParam(':name', $array['name'], database::PARAM_STR);
		$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

		if (isset($array['username'])) {
			$stmt->bindParam(':username', $array['username'], database::PARAM_STR);
		}	
		if (isset($array['value'])) {
			$value	= $encryption->encrypt($array['value']);
			$stmt->bindParam(':value', $value, database::PARAM_STR);
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
		
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Password Field Edited "<a href="'. $config->get('address') .'/passwords/view/'.(int)$array['id'] . '/">' . safe_output($array['name']) . '</a>"';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'edit';
		$log_array['event_source'] = 'custom_fields';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		//$log->add($log_array);
				
		
		return true;
	
	}
	public function get($array = NULL) {
		global $db;
		
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$site_id	= SITE_ID;

		$query = "SELECT * FROM $tables->custom_fields WHERE 1 = 1 AND site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id = :id";
		}
		if (isset($array['password_id'])) {
			$query .= " AND password_id = :password_id";
		}
		if (isset($array['encryption_level'])) {
			$query .= " AND encryption_level = :encryption_level";
		}
		
		$query .= " ORDER BY id";
		
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
		if (isset($array['password_id'])) {
			$stmt->bindParam(':password_id', $array['password_id'], database::PARAM_INT);
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
		
		$fields = $stmt->fetchAll(database::FETCH_ASSOC);
		
		return $fields;
	}
	
	function delete($array) {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error 	=	&singleton::get(__NAMESPACE__ . '\error');
		$log 	=	&singleton::get(__NAMESPACE__ . '\log');

		$site_id	= SITE_ID;

		
		//delete password
		$query 	= "DELETE FROM $tables->custom_fields WHERE site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id = :id";
		}
		if (isset($array['password_id'])) {
			$query .= " AND password_id = :password_id";
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
		if (isset($array['password_id'])) {
			$stmt->bindParam(':password_id', $array['password_id'], database::PARAM_INT);
		}
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Password Field Deleted';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'delete';
		$log_array['event_source'] = 'custom_fields';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		//$log->add($log_array);
		
	}
}


?>