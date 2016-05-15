<?php
/**
 * 	Users Class
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */
 
namespace mrpassword;

class users {

	var $meta = NULL;

	function __construct() {

	}
		
	/**
	 * Gets an array of users from the system.
	 *
	 * Form the array like this (all optional):
	 * <code>
	 * $array = array(
	 *		'id'	=> 1,		//The id of the user you want to get (for a single user)
	 *		'limit'	=> 10		//Limit the number of returned users
	 *
	 * );
	 * </code>
	 *
	 * @param   array   $array 		The array explained above
	 * @return  array				The array of users.
	 */
	public function get($array = NULL) {
		global $db;
		
		$error 		=	&singleton::get(__NAMESPACE__ . '\error');
		$tables 	=	&singleton::get(__NAMESPACE__ . '\tables');
		$site_id	= SITE_ID;
		
		$query = "SELECT u.* FROM $tables->users u ";
		
		if (isset($array['department_id']) || isset($array['department_ids'])) {
			$query .= "LEFT JOIN $tables->users_to_departments utd";
			
			$query .= " ON u.id = utd.user_id";
			
		}
		
		$query .= " WHERE 1 = 1 AND u.site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND u.id = :id";
		}
		if (isset($array['group_id'])) {
			$query .= " AND u.group_id = :group_id";
		}
		if (isset($array['allow_login'])) {
			$query .= " AND u.allow_login = :allow_login";
		}
		if (isset($array['email'])) {
			$query .= " AND u.email = :email";
		}
		if (isset($array['username'])) {
			$query .= " AND u.username = :username";
		}
		if (isset($array['authentication_id'])) {
			$query .= " AND u.authentication_id = :authentication_id";
		}

		if (isset($array['user_level'])) {
			$query .= " AND u.user_level = :user_level";
		}

		if (isset($array['department_id'])) {
			$query .= " AND utd.site_id = :site_id AND utd.department_id = :department_id";
		}
		
		if (isset($array['department_ids'])) {				
			$return = ' AND utd.site_id = :site_id AND utd.department_id IN (';
			
			foreach ($array['department_ids'] as $index => $value) {
				$return .= ':department_id' . (int) $index . ',';
			}
			
			if(substr($return, -1) == ',') {	
				$return = substr($return, 0, strlen($return) - 1);
			}
			
			$return .= ')';
			
			$query .= $return;
		}
		
		if (isset($array['user_levels'])) {
				
			$return = ' AND u.user_level IN ( ';
			
			foreach ($array['user_levels'] as $index => $value) {
				$return .= ' :user_level' . (int) $index . ',' ;
			}
			
			if(substr($return, -1) == ',') {	
				$return = substr($return, 0, strlen($return) - 1);
			}
			
			$return .= ')';
			
			$query .= $return;
		}
		
		if (isset($array['ids'])) {
				
			$return = ' AND (u.id = ';
			
			foreach ($array['ids'] as $index => $value) {
				$return .= ' :ids' . (int) $index . ' OR u.id = ' ;
			}
			
			if(substr($return, -11) == ' OR u.id = ') {	
				$return = substr($return, 0, strlen($return) - 11);
			}
			
			$return .= ')';
			
			$query .= $return;
		}
		
		if (isset($array['like_search'])) {
			$query .= " AND (u.name LIKE :like_search OR u.username LIKE :like_search OR u.email LIKE :like_search)";
		}
		
		if (isset($array['name_search']) && !empty($array['name_search'])) {
			$query .= " AND u.name LIKE :name_search";
		}
	
		$query .= " GROUP BY u.id ORDER BY u.name";
		
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
		if (isset($array['group_id'])) {
			$stmt->bindParam(':group_id', $array['group_id'], database::PARAM_INT);
		}
		if (isset($array['allow_login'])) {
			$stmt->bindParam(':allow_login', $array['allow_login'], database::PARAM_INT);
		}
		if (isset($array['email'])) {
			$stmt->bindParam(':email', strtolower($array['email']), database::PARAM_STR);
		}
		if (isset($array['username'])) {
			$stmt->bindParam(':username', strtolower($array['username']), database::PARAM_STR);
		}
		if (isset($array['authentication_id'])) {
			$stmt->bindParam(':authentication_id', $array['authentication_id'], database::PARAM_INT);
		}
		if (isset($array['department_id'])) {
			$stmt->bindParam(':department_id', $array['department_id'], database::PARAM_INT);
		}
		if (isset($array['user_level'])) {
			$stmt->bindParam(':user_level', $array['user_level'], database::PARAM_INT);
		}

		if (isset($array['department_ids'])) {	
			foreach ($array['department_ids'] as $index => $value) {
				$d_id = (int) $value;
				$stmt->bindParam(':department_id' . (int) $index, $d_id, database::PARAM_INT);
				unset($d_id);
			}
		}
			
		if (isset($array['user_levels'])) {	
			foreach ($array['user_levels'] as $index => $value) {
				$t_id = (int) $value;
				$stmt->bindParam(':user_level' . (int) $index, $t_id, database::PARAM_INT);
				unset($t_id);
			}
		}
		
		if (isset($array['ids'])) {	
			foreach ($array['ids'] as $index => $value) {
				$id_s = (int) $value;
				$stmt->bindParam(':ids' . (int) $index, $id_s, database::PARAM_INT);
				unset($id_s);
			}
		}
		
		if (isset($array['like_search'])) {
			$value = $array['like_search'];
			$value = "%{$value}%";
			$stmt->bindParam(':like_search', $value, database::PARAM_STR);
			unset($value);
		}
		if (isset($array['name_search']) && !empty($array['name_search'])) {
			$value1 = $array['name_search'];
			$value1 = "%{$value1}%";
			$stmt->bindParam(':name_search', $value1);
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
		
		$users = $stmt->fetchAll(database::FETCH_ASSOC);
		
		return $users;
	}

	/**
	 * Adds a user into the system.
	 *
	 * Form the array like this:
	 * <code>
	 * $array = array(
	 *			'name' 				=> 'Test User', 		// The users Full Name 	(required)
	 *			'email' 			=> 'user@example.com',	// The email address	(optional)
	 *			'authentication_id' => 1,					// 1 (local database) or 2 (active directory)
	 *			'allow_login'		=> 1,					// Should always be 1
	 *			'username'			=> 'test',				// Username all lowercase
	 *			'password'			=> '1234',				// Plain text password
	 *			'user_level'		=> 1,					// 1,2,3,4 or 5
	 * );
	 * </code>
	 *
	 * @param   array   $array 		The array explained above
	 * @return  int					The id of the created user.
	 */
	public function add($array) {
		global $db;
		
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		$log		= &singleton::get(__NAMESPACE__ . '\log');
		$config		= &singleton::get(__NAMESPACE__ . '\config');
		$auth		= &singleton::get(__NAMESPACE__ . '\auth');
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$notifications 	= &singleton::get(__NAMESPACE__ . '\notifications');

		$site_id	= SITE_ID;
		$salt 		= $auth->generate_user_salt();

		$query = "INSERT INTO $tables->users (name, site_id, salt";

		//used for import
		if (isset($array['id'])) {
			$query .= ", id";
		}

		if (isset($array['email'])) {
			$query .= ", email";
		}
		if (isset($array['authentication_id'])) {
			$query .= ", authentication_id";
		}
		if (isset($array['group_id'])) {
			$query .= ", group_id";
		}
		if (isset($array['allow_login'])) {
			$query .= ", allow_login";
		}
		if (isset($array['username'])) {
			$query .= ", username";
		}
		if (isset($array['password'])) {
			$query .= ", password";
		}
		if (isset($array['user_level'])) {
			$query .= ", user_level";
		}
		if (isset($array['address'])) {
			$query .= ", address";
		}
		if (isset($array['phone_number'])) {
			$query .= ", phone_number";
		}
		if (isset($array['pushover_key'])) {
			$query .= ", pushover_key";
		}
		if (isset($array['email_notifications'])) {
			$query .= ", email_notifications";
		}		
		
		$query .= ") VALUES (:name, :site_id, :salt";
		
		//used for import
		if (isset($array['id'])) {
			$query .= ", :id";
		}
		
		if (isset($array['email'])) {
			$query .= ", :email";
		}
		if (isset($array['authentication_id'])) {
			$query .= ", :authentication_id";
		}
		if (isset($array['group_id'])) {
			$query .= ", :group_id";
		}
		if (isset($array['allow_login'])) {
			$query .= ", :allow_login";
		}
		if (isset($array['username'])) {
			$query .= ", :username";
		}
		if (isset($array['password'])) {
			$query .= ", :password";
		}
		if (isset($array['user_level'])) {
			$query .= ", :user_level";
		}
		if (isset($array['address'])) {
			$query .= ", :address";
		}
		if (isset($array['phone_number'])) {
			$query .= ", :phone_number";
		}
		if (isset($array['pushover_key'])) {
			$query .= ", :pushover_key";
		}
		if (isset($array['email_notifications'])) {
			$query .= ", :email_notifications";
		}
		
		$query .= ")";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}
		
		$stmt->bindParam(':salt', $salt, database::PARAM_STR);
		$stmt->bindParam(':name', $array['name'], database::PARAM_STR);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		//used for import
		if (isset($array['id'])) {
			$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		}

		if (isset($array['email'])) {
			$email = strtolower($array['email']);
			$stmt->bindParam(':email', $email, database::PARAM_STR);
		}
		if (isset($array['authentication_id'])) {
			$stmt->bindParam(':authentication_id', $array['authentication_id'], database::PARAM_INT);
		}
		if (isset($array['group_id'])) {
			$stmt->bindParam(':group_id', $array['group_id'], database::PARAM_INT);
		}
		if (isset($array['allow_login'])) {
			$stmt->bindParam(':allow_login', $array['allow_login'], database::PARAM_INT);
		}
		if (isset($array['username'])) {
			$stmt->bindParam(':username', $array['username'], database::PARAM_STR);
		}
		if (isset($array['password'])) {
			$password	= $auth->hash_password($array['password'], $salt);
			$stmt->bindParam(':password', $password, database::PARAM_STR);
		}
		if (isset($array['user_level'])) {
			$stmt->bindParam(':user_level', $array['user_level'], database::PARAM_INT);
		}
		if (isset($array['address'])) {
			$stmt->bindParam(':address', $array['address'], database::PARAM_STR);
		}
		if (isset($array['phone_number'])) {
			$stmt->bindParam(':phone_number', $array['phone_number'], database::PARAM_STR);
		}
		if (isset($array['pushover_key'])) {
			$stmt->bindParam(':pushover_key', $array['pushover_key'], database::PARAM_STR);
		}
		if (isset($array['email_notifications'])) {
			$stmt->bindParam(':email_notifications', $array['email_notifications'], database::PARAM_INT);
		}
		
		try {
			$stmt->execute();
			$id = $db->lastInsertId();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		if (isset($array['welcome_email']) && ($array['welcome_email'] == 1)) {
			$array['id']	= $id;
			$notifications->new_user($array);
		}
				
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'User Added "<a href="' . $config->get('address') . '/users/view/' . (int)$id . '/">' . safe_output($array['name']) . '</a>"';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'add';
		$log_array['event_source'] = 'users';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
				
		return $id;
	}
	
	public function edit($array) {
		global $db;
		
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$auth		= &singleton::get(__NAMESPACE__ . '\auth');
		$log		= &singleton::get(__NAMESPACE__ . '\log');
		$config		= &singleton::get(__NAMESPACE__ . '\config');
		$error		= &singleton::get(__NAMESPACE__ . '\error');

		$site_id	= SITE_ID;
		$salt 		= $auth->generate_user_salt();

		
		$query = "UPDATE $tables->users SET site_id = :site_id";

		if (isset($array['name'])) {
			$query .= ", name = :name";
		}
		if (isset($array['group_id'])) {
			$query .= ", group_id = :group_id";
		}
		if (isset($array['authentication_id'])) {
			$query .= ", authentication_id = :authentication_id";
		}		
		if (isset($array['email'])) {
			$query .= ", email = :email";
		}
		if (isset($array['allow_login'])) {
			$query .= ", allow_login = :allow_login";
		}
		if (isset($array['username'])) {
			$query .= ", username = :username";
		}
		if (isset($array['password'])) {
			$query .= ", salt = :salt";
			$query .= ", password = :password";
		}
		if (isset($array['user_level'])) {
			$query .= ", user_level = :user_level";
		}
		if (isset($array['fail_expires'])) {
			$query .= ", fail_expires = :fail_expires";
		}
		if (isset($array['failed_logins'])) {
			$query .= ", failed_logins = :failed_logins";
		}		
		if (isset($array['email_notifications'])) {
			$query .= ", email_notifications = :email_notifications";
		}
		if (isset($array['reset_key'])) {
			$query .= ", reset_key = :reset_key";
		}
		if (isset($array['reset_expiry'])) {
			$query .= ", reset_expiry = :reset_expiry";
		}			
		if (isset($array['address'])) {
			$query .= ", address = :address";
		}
		if (isset($array['phone_number'])) {
			$query .= ", phone_number = :phone_number";
		}
		if (isset($array['pushover_key'])) {
			$query .= ", pushover_key = :pushover_key";
		}
		$query .= " WHERE id = :id AND site_id = :site_id";
		
		//echo $query;
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		if (isset($array['name'])) {
			$stmt->bindParam(':name', $array['name'], database::PARAM_STR);
		}
		if (isset($array['group_id'])) {
			$stmt->bindParam(':group_id', $array['group_id'], database::PARAM_INT);
		}
		if (isset($array['authentication_id'])) {
			$stmt->bindParam(':authentication_id', $array['authentication_id'], database::PARAM_INT);
		}
		if (isset($array['email'])) {
			$stmt->bindParam(':email', $array['email'], database::PARAM_STR);
		}
		if (isset($array['allow_login'])) {
			$stmt->bindParam(':allow_login', $array['allow_login'], database::PARAM_INT);
		}
		if (isset($array['username'])) {
			$stmt->bindParam(':username', $array['username'], database::PARAM_STR);
		}	
		if (isset($array['password'])) {
			$stmt->bindParam(':salt', $salt, database::PARAM_STR);
			$password	= $auth->hash_password($array['password'], $salt);
			$stmt->bindParam(':password', $password, database::PARAM_STR);
		}
		if (isset($array['user_level'])) {
			$stmt->bindParam(':user_level', $array['user_level'], database::PARAM_INT);
		}
		if (isset($array['fail_expires'])) {
			$stmt->bindParam(':fail_expires', $array['fail_expires'], database::PARAM_STR);
		}	
		if (isset($array['failed_logins'])) {
			$stmt->bindParam(':failed_logins', $array['failed_logins'], database::PARAM_INT);
		}	
		if (isset($array['email_notifications'])) {
			$stmt->bindParam(':email_notifications', $array['email_notifications'], database::PARAM_INT);
		}	
		if (isset($array['reset_key'])) {
			$stmt->bindParam(':reset_key', $array['reset_key'], database::PARAM_STR);
		}
		if (isset($array['reset_expiry'])) {
			$stmt->bindParam(':reset_expiry', $array['reset_expiry'], database::PARAM_STR);
		}	
		if (isset($array['address'])) {
			$stmt->bindParam(':address', $array['address'], database::PARAM_STR);
		}
		if (isset($array['phone_number'])) {
			$stmt->bindParam(':phone_number', $array['phone_number'], database::PARAM_STR);
		}	
		if (isset($array['pushover_key'])) {
			$stmt->bindParam(':pushover_key', $array['pushover_key'], database::PARAM_STR);
		}		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		if (isset($array['name'])) {
			$log_array['event_severity'] = 'notice';
			$log_array['event_number'] = E_USER_NOTICE;
			$log_array['event_description'] = 'User Edited "<a href="' . $config->get('address') . '/users/view/' . (int)$array['id'] . '/">' . safe_output($array['name']) . '</a>"';
			$log_array['event_file'] = __FILE__;
			$log_array['event_file_line'] = __LINE__;
			$log_array['event_type'] = 'edit';
			$log_array['event_source'] = 'users';
			$log_array['event_version'] = '1';
			$log_array['log_backtrace'] = false;	
					
			$log->add($log_array);
		}
		
		return true;
	
	}
	
	public function count($array = NULL) {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error =	&singleton::get(__NAMESPACE__ . '\error');
		$site_id	= SITE_ID;
				
		$query = "SELECT count(*) AS `count` FROM $tables->users WHERE site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id = :id";
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
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		$count = $stmt->fetch(database::FETCH_ASSOC);
		
		return (int) $count['count'];
	}
	
	public function check_username_taken($array) {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error =	&singleton::get(__NAMESPACE__ . '\error');
		$site_id	= SITE_ID;
		
		$username = $array['username'];
		
		$query = "SELECT count(*) FROM $tables->users WHERE username = :username AND site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id != :id";
		}
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':username', $username);
		$stmt->bindParam(':site_id', $site_id);

		if (isset($array['id'])) {
			$id = $array['id'];
			$stmt->bindParam(':id', $id, database::PARAM_INT);
		}
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		$count = $stmt->fetch(database::FETCH_ASSOC);
		if ($count['count(*)'] != 0) {
			//already in list
			return true;
		}
		else {
			return false;
		}
	}
	
	public function check_email_address_taken($array) {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error =	&singleton::get(__NAMESPACE__ . '\error');
		$site_id	= SITE_ID;
		
		$query = "SELECT count(*) FROM $tables->users WHERE email = :email AND site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id != :id ";
		}
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		
		$stmt->bindParam(':email', strtolower($array['email']), database::PARAM_STR);
		$stmt->bindParam(':site_id', $site_id);
		
		if (isset($array['id'])) {
			$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		}
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		$count = $stmt->fetch(database::FETCH_ASSOC);
		if ($count['count(*)'] != 0) {
			//already in list
			return true;
		}
		else {
			return false;
		}

	}
	
	public function sanitize_user_name($user_name) {

		//converts username to lowercase.
		$user_name = strtolower($user_name);
		
		//only allow a-z, 0-9 - and _ characters.
		$user_name = preg_replace('([^a-z0-9_-])', '', $user_name);
		
		return $user_name;

	}
	
	public function is_user($user_name) {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error =	&singleton::get(__NAMESPACE__ . '\error');
		$site_id	= SITE_ID;

		$query = "SELECT count(*) as `count` FROM $tables->users WHERE username = :username AND site_id = :site_id";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':username', $user_name);
		$stmt->bindParam(':site_id', $site_id);

		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$users = $stmt->fetch(database::FETCH_ASSOC);
		
		if ($users['count'] == 0) {
			return false;
		}
		else {
			return true;
		}			
	}
	
	function delete($array) {
		global $db;
		
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		$log		= &singleton::get(__NAMESPACE__ . '\log');
		$passwords	= &singleton::get(__NAMESPACE__ . '\passwords');
		$shares		= &singleton::get(__NAMESPACE__ . '\shares');
		$categories	= &singleton::get(__NAMESPACE__ . '\categories');
		$custom_fields	= &singleton::get(__NAMESPACE__ . '\custom_fields');

		
		$site_id	= SITE_ID;
		
		//delete password custom fields
		//$custom_fields->delete(array('password_id' => $id));
		
		//delete passwords
		$passwords->delete(array('user_id' => $array['id']));
		
		//delete categories
		$categories->delete(array('user_id' => $array['id']));

		//delete shares
		$shares->delete(array('user_id' => $array['id']));
		
		//delete user
		$query 	= "DELETE FROM $tables->users WHERE id = :id AND site_id = :site_id";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'User Deleted ID ' . safe_output($array['id']);
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'delete';
		$log_array['event_source'] = 'users';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
		
	}
	
	
	function create_reset_key($array) {
		global $db;
		
		$notifications	= &singleton::get(__NAMESPACE__ . '\notifications');
		
		if (isset($array['username'])) {
			$users_array = $this->get(array('username' => $array['username'], 'authentication_id' => 1, 'allow_login' => 1));
		}
		else {
			return false;
		}
		
		if (count($users_array) == 1) {
			$id 			= (int) $users_array[0]['id'];
									
			$reset_key			= rand_str();
			//lasts 12 hours
			$reset_expiry		= datetime(43200);
			
			$this->edit(array('id' => $id, 'reset_key' => $reset_key, 'reset_expiry' => $reset_expiry));
			
			$notif_array['reset_key']		= $reset_key;
			$notif_array['reset_expiry']	= $reset_expiry;
			$notif_array['user']			= $users_array[0];
			
			$notifications->password_reset($notif_array);

			
			return $reset_key;
		}
		else {
			return false;
		}
	}
}
?>