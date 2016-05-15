<?php
namespace mrpassword;

class categories {
	
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

		$query = "INSERT INTO $tables->categories (name, user_id, site_id";
		
			if (isset($array['parent_id'])) {
				$query .= ", parent_id";
			}
			if (isset($array['global'])) {
				$query .= ", `global`";
			}
		
		$query .= ") VALUES (:name, :user_id, :site_id";
			
			if (isset($array['parent_id'])) {
				$query .= ", :parent_id";
			}
			if (isset($array['global'])) {
				$query .= ", :global";
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
		$user_id = $auth->get('id');
		$stmt->bindParam(':user_id', $user_id, database::PARAM_INT);
		if (isset($array['global'])) {
			$global = (int) $array['global'];
			$stmt->bindParam(':global', $global, database::PARAM_INT);
		}
		
		try {
			$stmt->execute();
			$id = $db->lastInsertId();
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
				
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Category Added "<a href="'. $config->get('address') .'/categories/view/'.(int)$id.'/">' . safe_output($array['name']) . '</a>"';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'add';
		$log_array['event_source'] = 'categories';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
				
		return $id;
		
	}
	
	function get($array = NULL) {
		global $db;
		
		$error 		=	&singleton::get(__NAMESPACE__ . '\error');
		$tables 	=	&singleton::get(__NAMESPACE__ . '\tables');
		$site_id	= SITE_ID;

		$query = "SELECT c.*";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$query .= ", count(distinct p.id) AS `password_count`, count(distinct s.id) AS `share_count`, s.access_level AS `access_level`";
			$query .= ", u.name AS `owner_name`";
		}
		
		$query .= " FROM $tables->categories c";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$query .= " LEFT JOIN $tables->passwords p ON (c.id = p.category_id AND c.user_id = p.user_id AND p.old = 0)";
			$query .= " LEFT JOIN $tables->shares s ON (c.id = s.category_id)";
			$query .= " LEFT JOIN $tables->users u ON (u.id = c.user_id)";
		}

		$query .= " WHERE 1 = 1 AND c.site_id = :site_id";

		
		if (isset($array['id'])) {
			$query .= " AND c.id = :id";
		}
		
		if (isset($array['user_id'])) {
			$query .= " AND c.user_id = :user_id";
		}

		if (isset($array['shared_user_id'])) {
			$query .= " AND s.shared_user_id = :shared_user_id";
		}
		if (isset($array['global'])) {
			$query .= " AND c.global = :global";
		}				
		
		$query .= " GROUP BY c.id ";

		
		$query .=" ORDER BY c.name";
		
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
		if (isset($array['global'])) {
			$stmt->bindParam(':global', $array['global'], database::PARAM_INT);
		}		
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$categories = $stmt->fetchAll(database::FETCH_ASSOC);
		
		return $categories;
	}
	
	function edit($array) {
		global $db;
		
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$auth		= &singleton::get(__NAMESPACE__ . '\auth');
		$log		= &singleton::get(__NAMESPACE__ . '\log');
		$config		= &singleton::get(__NAMESPACE__ . '\config');

		$site_id	= SITE_ID;
		
		$query = "UPDATE $tables->categories SET name = :name";
		
		$query .= " WHERE id = :id AND site_id = :site_id";
		
		if (isset($array['global'])) {
			$query .= " AND `global` = :global";
		}
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':name', $array['name'], database::PARAM_STR);
		$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		if (isset($array['global'])) {
			$stmt->bindParam(':global', $array['global'], database::PARAM_INT);
		}	
		
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Category Edited "<a href="'. $config->get('address') .'/categories/view/'.(int)$array['id'] . '/">' . safe_output($array['name']) . '</a>"';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'edit';
		$log_array['event_source'] = 'categories';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
				
		
		return true;
	}
	
	function delete($array) {
		global $db;
		
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		$log		= &singleton::get(__NAMESPACE__ . '\log');
		$passwords	= &singleton::get(__NAMESPACE__ . '\passwords');
		$shares		= &singleton::get(__NAMESPACE__ . '\shares');

		
		$site_id	= SITE_ID;
		
		//update passwords
		if (isset($array['id'])) {
			$query 	= "UPDATE $tables->passwords SET category_id = NULL WHERE category_id = :category_id AND site_id = :site_id";
			
			try {
				$stmt = $db->prepare($query);
			}
			catch (\PDOException $e) {
				$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
			}
			
			$stmt->bindParam(':category_id', $array['id'], database::PARAM_INT);
			$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
			
			try {
				$stmt->execute();
			}
			catch (\PDOException $e) {
				$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
			}
		}
		
		//delete shares
		if (isset($array['id'])) {
			$shares->delete(array('category_id' => $array['id']));
		}
		
		//delete category
		$query 	= "DELETE FROM $tables->categories WHERE site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id = :id";
		}
		if (isset($array['user_id'])) {
			$query .= " AND user_id = :user_id";
		}
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		if (isset($array['id'])) {
			$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		}
		if (isset($array['user_id'])) {
			$stmt->bindParam(':user_id', $array['user_id'], database::PARAM_INT);
		}
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Category Deleted ID ' . safe_output($array['id']);
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'delete';
		$log_array['event_source'] = 'categories';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
	}
	
	function count($array = NULL) {
	
	}


}

?>