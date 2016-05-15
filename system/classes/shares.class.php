<?php
namespace mrpassword;

class shares {


	function __construct() {

	}
		
		
	function add($array) {
		global $db;
				
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		$log		= &singleton::get(__NAMESPACE__ . '\log');
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$auth 		= &singleton::get(__NAMESPACE__ . '\auth');
		$config 	= &singleton::get(__NAMESPACE__ . '\config');

		$site_id	= SITE_ID;
		$date_added = datetime();

		$query = "INSERT INTO $tables->shares (user_id, shared_user_id, category_id, site_id";
		
		if (isset($array['access_level'])) {
			$query .= ", access_level";
		}
		
		$query .= ") VALUES (:user_id, :shared_user_id, :category_id, :site_id";
		
		if (isset($array['access_level'])) {
			$query .= ", :access_level";
		}
	
		$query .= ")";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}
		
		$stmt->bindParam(':category_id', $array['category_id'], database::PARAM_INT);
		$stmt->bindParam(':shared_user_id', $array['shared_user_id'], database::PARAM_INT);
		
		if (isset($array['access_level'])) {
			$stmt->bindParam(':access_level', $array['access_level'], database::PARAM_INT);
		}

		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		$user_id = $auth->get('id');
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
		$log_array['event_description'] = 'Share Added';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'add';
		$log_array['event_source'] = 'shares';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
				
		return $id;
	}
	public function count($array = NULL) {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error =	&singleton::get(__NAMESPACE__ . '\error');
		$site_id	= SITE_ID;
				
		$query = "SELECT count(*) AS `count` FROM $tables->shares WHERE site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id = :id";
		}
		if (isset($array['user_id'])) {
			$query .= " AND user_id = :user_id";
		}
		if (isset($array['user_id'])) {
			$query .= " AND user_id = :user_id";
		}
		if (isset($array['shared_user_id'])) {
			$query .= " AND shared_user_id = :shared_user_id";
		}		
		if (isset($array['category_id'])) {
			$query .= " AND category_id = :category_id";
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
		if (isset($array['shared_user_id'])) {
			$shared_user_id = $array['shared_user_id'];
			$stmt->bindParam(':shared_user_id', $shared_user_id, database::PARAM_INT);
		}
		if (isset($array['category_id'])) {
			$category_id = $array['category_id'];
			$stmt->bindParam(':category_id', $category_id, database::PARAM_INT);
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
	function delete($array) {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error 	=	&singleton::get(__NAMESPACE__ . '\error');
		$log 	=	&singleton::get(__NAMESPACE__ . '\log');

		$site_id	= SITE_ID;

		
		//delete shares
		$query 	= "DELETE FROM $tables->shares WHERE site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id = :id";
		}
		if (isset($array['user_id'])) {
			$query .= " AND user_id = :user_id";
		}
		if (isset($array['category_id'])) {
			$query .= " AND category_id = :category_id";
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
		if (isset($array['category_id'])) {
			$stmt->bindParam(':category_id', $array['category_id'], database::PARAM_INT);
		}
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Shares Deleted';
		if (isset($array['id'])) {
			$log_array['event_description'] = 'Share Deleted ID ' . safe_output($array['id']);
		}
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'delete';
		$log_array['event_source'] = 'shares';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
	}
	function get($array = NULL) {
		global $db;
		
		$error 		=	&singleton::get(__NAMESPACE__ . '\error');
		$tables 	=	&singleton::get(__NAMESPACE__ . '\tables');
		$site_id	= SITE_ID;

		$query = "SELECT s.*";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$query .= ", u.name AS `owner_name`, u2.name AS `client_name`, u2.username AS `client_username`";
			//$query .= ", c.name AS `category_name`";
		}
		
		$query .= " FROM $tables->shares s";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$query .= " LEFT JOIN $tables->users u ON (u.id = s.user_id)";
			$query .= " LEFT JOIN $tables->users u2 ON (u2.id = s.shared_user_id)";
			//$query .= " LEFT JOIN $tables->categories c ON (c.id = s.category_id)";
		}


		$query .= " WHERE 1 = 1 AND s.site_id = :site_id";

		
		if (isset($array['id'])) {
			$query .= " AND s.id = :id";
		}
		
		if (isset($array['user_id'])) {
			$query .= " AND s.user_id = :user_id";
		}
		if (isset($array['shared_user_id'])) {
			$query .= " AND s.shared_user_id = :shared_user_id";
		}		
		if (isset($array['category_id'])) {
			$query .= " AND s.category_id = :category_id";
		}	
		
		$query .= " GROUP BY s.id ORDER BY s.id DESC";
		
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
		if (isset($array['user_id'])) {
			$stmt->bindParam(':user_id', $array['user_id'], database::PARAM_INT);
		}
		if (isset($array['shared_user_id'])) {
			$stmt->bindParam(':shared_user_id', $array['shared_user_id'], database::PARAM_INT);
		}	
		if (isset($array['category_id'])) {
			$stmt->bindParam(':category_id', $array['category_id'], database::PARAM_INT);
		}		
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$shares = $stmt->fetchAll(database::FETCH_ASSOC);
		
		return $shares;
	}
}