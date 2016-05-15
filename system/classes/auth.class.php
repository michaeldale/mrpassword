<?php
/**
 * 	Authentication Class
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace mrpassword;

class auth {

	private $current_user;
	private $salt;


	function __construct() {
	
	}
	
	public function set_salt($salt) {
		$this->salt = $salt;
		return true;
	}
	
	public function get($name) {
		if (isset($this->current_user[$name])) {
			return $this->current_user[$name];
		}
		else {
			return false;
		}
	}
	
	public function logged_in() {
	
		$plugins 						= &singleton::get(__NAMESPACE__ . '\plugins');	
		$plugins->run('auth_logged_in_start');
	
		if (isset($this->current_user['id']) && $this->current_user['id'] !== 0) {
			return true;
		}
		else {
			return false;
		}
	}
		
	/**
	 * Load the user stored in the session.
	 *
	 */
	public function load() {
	
		$plugins 						= &singleton::get(__NAMESPACE__ . '\plugins');	
		$plugins->run('auth_load_start');
	
		if (isset($_SESSION['user_data'])) {
			//load from database
			$array['id']			= (int) $_SESSION['user_data']['id'];
			$array['allow_login']	= 1;
			
			$user 	= &singleton::get(__NAMESPACE__ . '\users');
			
			$users = $user->get($array);
			
			if (count($users) == 1) {
				$this->current_user = array(
					'id' 					=> $users[0]['id'],
					'name' 					=> $users[0]['name'],
					'username' 				=> $users[0]['username'],
					'email'					=> $users[0]['email'],
					'group_id'				=> $users[0]['group_id'],
					'user_level'			=> $users[0]['user_level'],
					'authentication_id' 	=> $users[0]['authentication_id']
				);
			}
			else {
				$this->current_user =	array(
					'id' 					=> 0,
					'name' 					=> 'guest',
					'username' 				=> '',
					'email'					=> '',
					'group_id'				=> 0,
					'user_level'			=> 0,
					'authentication_id' 	=> 0
				);
			}		
		}
		else {
			$this->current_user =	array(
				'id' 					=> 0,
				'name' 					=> 'guest',
				'username' 				=> '',
				'email'					=> '',
				'group_id'				=> 0,
				'user_level'			=> 0,
				'authentication_id' 	=> 0
			);
		}
		
		return true;
	}
	
	//only tests local passwords
	public function test_password($array) {
		global $db;
		
		$config 						= &singleton::get(__NAMESPACE__ . '\config');
		$tables 						= &singleton::get(__NAMESPACE__ . '\tables');
		$error 							= &singleton::get(__NAMESPACE__ . '\error');
		$log							= &singleton::get(__NAMESPACE__ . '\log');
	
		$site_id						= SITE_ID;
		
		$id 							= (int) $array['id'];
		$password						= $array['password'];
		
		if (empty($id)) return false;
		if (empty($password)) return false;
		
		//look for user in db
		$query = "SELECT * FROM $tables->users WHERE `id` = :id AND `site_id` = :site_id LIMIT 1";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':id', $id, database::PARAM_INT);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$users = $stmt->fetchAll(database::FETCH_ASSOC);
		
		if (count($users) == 1) {
			$user = $users[0];
			
			if ($user['authentication_id'] == 1) {
				if ($user['password'] === $this->hash_password($password, $user['salt'])) {
					return true;
				}
			}
		}

		return false;
	
	}
	
	/**
	 * Login in the user and creates a session.
	 *
	 * Form the array like this:
	 * <code>
	 * $array = array(
	 *   'username'    	=> 'admin',       // the username (will be converted to lowercase).
	 *   'password'   	=> '1234',     	  // the plaintext password
	 * );
	 * 
	 * </code>
	 *
	 * @param   array   $array 			The array explained above
	 * @return  bool					TRUE if successful or FALSE if not.
	 */
	public function login($array) {
		global $db;
		
		$plugins 						= &singleton::get(__NAMESPACE__ . '\plugins');	
		$plugins->run('auth_login_start', $array);
				
		$config 						= &singleton::get(__NAMESPACE__ . '\config');
		$tables 						= &singleton::get(__NAMESPACE__ . '\tables');
		$error 							= &singleton::get(__NAMESPACE__ . '\error');
		$log							= &singleton::get(__NAMESPACE__ . '\log');

		$site_id						= SITE_ID;
		
		$username 						= strtolower($array['username']);
		$password						= $array['password'];
		
		if (empty($username)) return false;
		if (empty($password)) return false;
		
		//look for user in db
		$query = "SELECT * FROM $tables->users WHERE `username` = :username AND `site_id` = :site_id LIMIT 1";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':username', $array['username'], database::PARAM_STR);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$users = $stmt->fetchAll(database::FETCH_ASSOC);
		
		//ad stuff
		$options['domain_controllers']	= array($config->get('ad_server'));
		$options['account_suffix']		= $config->get('ad_account_suffix');
		$options['base_dn']				= $config->get('ad_base_dn');
				
		//if found
		if (count($users) == 1) {
			$user = $users[0];
			if ($user['allow_login'] == 1) {
				
				//account lockout check.
				if (isset($user['failed_logins']) && ((int) $user['failed_logins'] > 4) && isset($user['fail_expires']) && ($user['fail_expires'] > datetime())) {
					if ($config->get('lockout_enabled')) {							
						$log_array['event_severity'] = 'warning';
						$log_array['event_number'] = E_USER_WARNING;
						$log_array['event_description'] = 'Login Failed "<a href="'.$config->get('address').'/users/view/'.(int)$user['id'].'/">' . safe_output($user['name']) . '</a>" - Account Temporarily Locked.';
						$log_array['event_file'] = __FILE__;
						$log_array['event_file_line'] = __LINE__;
						$log_array['event_type'] = 'login_failed_account_lockout';
						$log_array['event_source'] = 'auth';
						$log_array['event_version'] = '1';
						$log_array['log_backtrace'] = false;	
								
						$log->add($log_array);

						return false;
					}
				}
				
				//if AD account
				if ($user['authentication_id'] == 2) {
					if ($config->get('ad_enabled')) {
						
						try {
							$ad 		= &singleton::get('adLDAP', $options);
						}
						catch (\adLDAPException $e) {
							$log_array['event_severity'] = 'error';
							$log_array['event_number'] = E_USER_ERROR;
							$log_array['event_description'] = 'Active Directory could not start "' . $e->getMessage() . '"';
							$log_array['event_file'] = __FILE__;
							$log_array['event_file_line'] = __LINE__;
							$log_array['event_type'] = 'ad_login_failed';
							$log_array['event_source'] = 'auth';
							$log_array['event_version'] = '1';
							$log_array['log_backtrace'] = true;	
									
							$log->add($log_array);
	
							return false;
						}
						
						//login via ad
						if ($ad->user()->authenticate($user['username'], $password) == true){			
						
							$this->login_session($user);
							
							$log_array['event_severity'] = 'notice';
							$log_array['event_number'] = E_USER_NOTICE;
							$log_array['event_description'] = 'Active Directory Login Successful "<a href="'.$config->get('address').'/users/view/'.(int)$user['id'].'/">' . safe_output($user['name']) . '</a>"';
							$log_array['event_file'] = __FILE__;
							$log_array['event_file_line'] = __LINE__;
							$log_array['event_type'] = 'ad_login_successful';
							$log_array['event_source'] = 'auth';
							$log_array['event_version'] = '1';
							$log_array['log_backtrace'] = false;	
									
							$log->add($log_array);
							
							$this->clear_failed_login($user);

							return true;
						}
						else {
							$log_array['event_severity'] = 'warning';
							$log_array['event_number'] = E_USER_WARNING;
							$log_array['event_description'] = 'Active Directory Login Failed "<a href="'.$config->get('address').'/users/view/'.(int)$user['id'].'/">' . safe_output($user['name']) . '</a>" - Incorrect Password';
							$log_array['event_file'] = __FILE__;
							$log_array['event_file_line'] = __LINE__;
							$log_array['event_type'] = 'ad_login_failed';
							$log_array['event_source'] = 'auth';
							$log_array['event_version'] = '1';
							$log_array['log_backtrace'] = false;	
									
							$log->add($log_array);
							
							$this->increment_failed_login($user);
							
							return false;
						}
					}
					else {
					
						$log_array['event_severity'] = 'warning';
						$log_array['event_number'] = E_USER_WARNING;
						$log_array['event_description'] = 'Active Directory Login Failed "<a href="'.$config->get('address').'/users/view/'.(int)$user['id'].'/">' . safe_output($user['name']) . '</a>" - Auth Type Disabled';
						$log_array['event_file'] = __FILE__;
						$log_array['event_file_line'] = __LINE__;
						$log_array['event_type'] = 'ad_login_failed';
						$log_array['event_source'] = 'auth';
						$log_array['event_version'] = '1';
						$log_array['log_backtrace'] = false;	
								
						$log->add($log_array);
						
						return false;
					}
				}
				else if ($user['authentication_id'] == 3) {
					if ($config->get('ldap_enabled')) {
						$ldap 		= &singleton::get(__NAMESPACE__ . '\auth_ldap');

						if ($ldap->authenticate($user['username'], $password)) {
							
							$this->login_session($user);
							
							$log_array['event_severity'] 		= 'notice';
							$log_array['event_number'] 			= E_USER_NOTICE;
							$log_array['event_description'] 	= 'LDAP Login Successful "<a href="'.$config->get('address').'/users/view/'.(int)$user['id'].'/">' . safe_output($user['name']) . '</a>"';
							$log_array['event_file'] 			= __FILE__;
							$log_array['event_file_line'] 		= __LINE__;
							$log_array['event_type'] 			= 'ldap_login_successful';
							$log_array['event_source'] 			= 'auth';
							$log_array['event_version'] 		= '1';
							$log_array['log_backtrace'] 		= false;	
									
							$log->add($log_array);
							
							$this->clear_failed_login($user);

							return true;							
						}
						else {
							$log_array['event_severity'] = 'warning';
							$log_array['event_number'] = E_USER_WARNING;
							$log_array['event_description'] = 'LDAP Login Failed "<a href="'.$config->get('address').'/users/view/'.(int)$user['id'].'/">' . safe_output($user['name']) . '</a>" - Incorrect Password';
							$log_array['event_file'] = __FILE__;
							$log_array['event_file_line'] = __LINE__;
							$log_array['event_type'] = 'ldap_login_failed';
							$log_array['event_source'] = 'auth';
							$log_array['event_version'] = '1';
							$log_array['log_backtrace'] = false;	
									
							$log->add($log_array);
						
							$this->increment_failed_login($user);
						}
					}
					else {
					
						$log_array['event_severity'] = 'warning';
						$log_array['event_number'] = E_USER_WARNING;
						$log_array['event_description'] = 'LDAP Login Failed "<a href="'.$config->get('address').'/users/view/'.(int)$user['id'].'/">' . safe_output($user['name']) . '</a>" - Auth Type Disabled';
						$log_array['event_file'] = __FILE__;
						$log_array['event_file_line'] = __LINE__;
						$log_array['event_type'] = 'ldap_login_failed';
						$log_array['event_source'] = 'auth';
						$log_array['event_version'] = '1';
						$log_array['log_backtrace'] = false;	
								
						$log->add($log_array);
						
						return false;
					}
				}
				//Custom JSON Authentication
				else if ($user['authentication_id'] == 4) {
					if ($config->get('auth_json_enabled')) {
						$json 		= &singleton::get(__NAMESPACE__ . '\auth_json');

						if ($json->authenticate($user['username'], $password)) {
							
							$this->login_session($user);
							
							$log_array['event_severity'] 		= 'notice';
							$log_array['event_number'] 			= E_USER_NOTICE;
							$log_array['event_description'] 	= 'JSON Login Successful "<a href="'.$config->get('address').'/users/view/'.(int)$user['id'].'/">' . safe_output($user['name']) . '</a>"';
							$log_array['event_file'] 			= __FILE__;
							$log_array['event_file_line'] 		= __LINE__;
							$log_array['event_type'] 			= 'json_login_successful';
							$log_array['event_source'] 			= 'auth';
							$log_array['event_version'] 		= '1';
							$log_array['log_backtrace'] 		= false;	
									
							$log->add($log_array);
							
							$this->clear_failed_login($user);

							return true;							
						}
						else {
							$log_array['event_severity'] = 'warning';
							$log_array['event_number'] = E_USER_WARNING;
							$log_array['event_description'] = 'JSON Login Failed "<a href="'.$config->get('address').'/users/view/'.(int)$user['id'].'/">' . safe_output($user['name']) . '</a>" - Incorrect Password';
							$log_array['event_file'] = __FILE__;
							$log_array['event_file_line'] = __LINE__;
							$log_array['event_type'] = 'json_login_failed';
							$log_array['event_source'] = 'auth';
							$log_array['event_version'] = '1';
							$log_array['log_backtrace'] = false;	
									
							$log->add($log_array);
						
							$this->increment_failed_login($user);
						}
					}
					else {					
						$log_array['event_severity'] = 'warning';
						$log_array['event_number'] = E_USER_WARNING;
						$log_array['event_description'] = 'JSON Login Failed "<a href="'.$config->get('address').'/users/view/'.(int)$user['id'].'/">' . safe_output($user['name']) . '</a>" - Auth Type Disabled';
						$log_array['event_file'] = __FILE__;
						$log_array['event_file_line'] = __LINE__;
						$log_array['event_type'] = 'json_login_failed';
						$log_array['event_source'] = 'auth';
						$log_array['event_version'] = '1';
						$log_array['log_backtrace'] = false;	
								
						$log->add($log_array);
						
						return false;
					}				
				}				
				else {
					if ($user['password'] === $this->hash_password($password, $user['salt'])) {
						
						$this->login_session($user);
						
						$log_array['event_severity'] = 'notice';
						$log_array['event_number'] = E_USER_NOTICE;
						$log_array['event_description'] = 'Local Login Successful "<a href="'.$config->get('address').'/users/view/'.(int)$user['id'].'/">' . safe_output($user['name']) . '</a>"';
						$log_array['event_file'] = __FILE__;
						$log_array['event_file_line'] = __LINE__;
						$log_array['event_type'] = 'local_login_successful';
						$log_array['event_source'] = 'auth';
						$log_array['event_version'] = '1';
						$log_array['log_backtrace'] = false;	
								
						$log->add($log_array);
						
						$this->clear_failed_login($user);
						
						return true;
					}
					else {
						$log_array['event_severity'] = 'warning';
						$log_array['event_number'] = E_USER_WARNING;
						$log_array['event_description'] = 'Local Login Failed "<a href="'.$config->get('address').'/users/view/'.(int)$user['id'].'/">' . safe_output($user['name']) . '</a>"';
						$log_array['event_file'] = __FILE__;
						$log_array['event_file_line'] = __LINE__;
						$log_array['event_type'] = 'local_login_failed';
						$log_array['event_source'] = 'auth';
						$log_array['event_version'] = '1';
						$log_array['log_backtrace'] = false;	
								
						$log->add($log_array);
						
						$this->increment_failed_login($user);
						
						return false;
					}
				}
			}
			else {
				$log_array['event_severity'] = 'warning';
				$log_array['event_number'] = E_USER_WARNING;
				$log_array['event_description'] = 'Local Login Failed "<a href="'.$config->get('address').'/users/view/'.(int)$user['id'].'/">' . safe_output($user['name']) . '</a>"';
				$log_array['event_file'] = __FILE__;
				$log_array['event_file_line'] = __LINE__;
				$log_array['event_type'] = 'local_login_failed';
				$log_array['event_source'] = 'auth';
				$log_array['event_version'] = '1';
				$log_array['log_backtrace'] = false;	
						
				$log->add($log_array);
						
				return false;
			}
		}
		else {
			//if (Allow any valid AD user to login)
			if ($config->get('ad_enabled') && $config->get('ad_create_accounts')) {
				try {
					$ad 		= &singleton::get('adLDAP', $options);
				}
				catch (\adLDAPException $e) {
					return false;
				}

				if ($ad->user()->authenticate($username, $password) == true){
					$user 	= &singleton::get(__NAMESPACE__ . '\users');

					if (!$user->check_username_taken(array('username' => $username))) {
						
						//create user
						$user_info = $ad->user()->infoCollection($username, array('displayname', 'mail'));

						
						$client_array['name']				= $user_info->displayName;
						$client_array['username']			= $username;
						$client_array['email']				= strtolower($user_info->mail);
						$client_array['authentication_id']	= 2;
						$client_array['allow_login']		= 1;
						$client_array['user_level']			= 1;
						
						$id = $user->add($client_array);
						
						$client_array['id']			= $id;
						
						$log	= &singleton::get(__NAMESPACE__ . '\log');

						$log_array['event_severity'] = 'notice';
						$log_array['event_number'] = E_USER_NOTICE;
						$log_array['event_description'] = 'New Active Directory Login Successful "<a href="'.$config->get('address').'/users/view/'.(int)$id.'/">' . safe_output($client_array['name']) . '</a>"';
						$log_array['event_file'] = __FILE__;
						$log_array['event_file_line'] = __LINE__;
						$log_array['event_type'] = 'local_login_successful';
						$log_array['event_source'] = 'auth';
						$log_array['event_version'] = '1';
						$log_array['log_backtrace'] = false;	
								
						$log->add($log_array);
						
						$this->login_session($client_array);
						
						return true;
					}
					else {
						return false;
					}
				}
				else {
				
					$log_array['event_severity'] = 'warning';
					$log_array['event_number'] = E_USER_WARNING;
					$log_array['event_description'] = 'AD Login Failed "' . safe_output($username) . '" - Unknown Account';
					$log_array['event_file'] = __FILE__;
					$log_array['event_file_line'] = __LINE__;
					$log_array['event_type'] = 'unknown_user';
					$log_array['event_source'] = 'auth';
					$log_array['event_version'] = '1';
					$log_array['log_backtrace'] = false;	
							
					$log->add($log_array);
					return false;
				}
			}
			else if ($config->get('ldap_enabled') && $config->get('ldap_create_accounts')) {
				$ldap 		= &singleton::get(__NAMESPACE__ . '\auth_ldap');

				if ($ldap->authenticate($username, $password)){
					$user 	= &singleton::get(__NAMESPACE__ . '\users');

					if (!$user->check_username_taken(array('username' => $username))) {
						
						$ldap_user 							= $ldap->get_user();
						
						$client_array['name']				= $ldap_user['name'];
						$client_array['username']			= $username;
						$client_array['email']				= $ldap_user['email'];
						$client_array['authentication_id']	= 3;
						$client_array['allow_login']		= 1;
						$client_array['user_level']			= 1;
						
						$id = $user->add($client_array);
						
						$client_array['id']			= $id;
						
						$log	= &singleton::get(__NAMESPACE__ . '\log');

						$log_array['event_severity'] = 'notice';
						$log_array['event_number'] = E_USER_NOTICE;
						$log_array['event_description'] = 'New LDAP Login Successful "<a href="'.$config->get('address').'/users/view/'.(int)$id.'/">' . safe_output($client_array['name']) . '</a>"';
						$log_array['event_file'] = __FILE__;
						$log_array['event_file_line'] = __LINE__;
						$log_array['event_type'] = 'ldap_login_successful';
						$log_array['event_source'] = 'auth';
						$log_array['event_version'] = '1';
						$log_array['log_backtrace'] = false;	
								
						$log->add($log_array);
						
						$this->login_session($client_array);
						
						return true;
					}
					else {
						return false;
					}
				}
				else {
				
					$log_array['event_severity'] = 'warning';
					$log_array['event_number'] = E_USER_WARNING;
					$log_array['event_description'] = 'LDAP Login Failed "' . safe_output($username) . '" - Unknown Account';
					$log_array['event_file'] = __FILE__;
					$log_array['event_file_line'] = __LINE__;
					$log_array['event_type'] = 'unknown_user';
					$log_array['event_source'] = 'auth';
					$log_array['event_version'] = '1';
					$log_array['log_backtrace'] = false;	
							
					$log->add($log_array);
					return false;
				}				
			}
			else if ($config->get('auth_json_enabled') && $config->get('auth_json_create_accounts')) {
				$json 		= &singleton::get(__NAMESPACE__ . '\auth_json');

				if ($json->authenticate($username, $password)){
					$user 	= &singleton::get(__NAMESPACE__ . '\users');

					if (!$user->check_username_taken(array('username' => $username))) {
						
						$json_user 							= $json->get_user();
						
						$client_array['name']				= $json_user['name'];
						$client_array['username']			= $username;
						$client_array['email']				= $json_user['email'];
						$client_array['authentication_id']	= 4;
						$client_array['allow_login']		= 1;
						$client_array['user_level']			= 1;
						
						$id = $user->add($client_array);
						
						$client_array['id']			= $id;
						
						$log	= &singleton::get(__NAMESPACE__ . '\log');

						$log_array['event_severity'] = 'notice';
						$log_array['event_number'] = E_USER_NOTICE;
						$log_array['event_description'] = 'New JSON Login Successful "<a href="'.$config->get('address').'/users/view/'.(int)$id.'/">' . safe_output($client_array['name']) . '</a>"';
						$log_array['event_file'] = __FILE__;
						$log_array['event_file_line'] = __LINE__;
						$log_array['event_type'] = 'json_login_successful';
						$log_array['event_source'] = 'auth';
						$log_array['event_version'] = '1';
						$log_array['log_backtrace'] = false;	
								
						$log->add($log_array);
						
						$this->login_session($client_array);
						
						return true;
					}
					else {
						return false;
					}
				}
				else {
				
					$log_array['event_severity'] = 'warning';
					$log_array['event_number'] = E_USER_WARNING;
					$log_array['event_description'] = 'JSON Login Failed "' . safe_output($username) . '" - Unknown Account';
					$log_array['event_file'] = __FILE__;
					$log_array['event_file_line'] = __LINE__;
					$log_array['event_type'] = 'unknown_user';
					$log_array['event_source'] = 'auth';
					$log_array['event_version'] = '1';
					$log_array['log_backtrace'] = false;	
							
					$log->add($log_array);
					return false;
				}			
			}			
			else {
				$log_array['event_severity'] = 'warning';
				$log_array['event_number'] = E_USER_WARNING;
				$log_array['event_description'] = 'Local Login Failed "' . safe_output($array['username']) . '" - Unknown Account';
				$log_array['event_file'] = __FILE__;
				$log_array['event_file_line'] = __LINE__;
				$log_array['event_type'] = 'unknown_user';
				$log_array['event_source'] = 'auth';
				$log_array['event_version'] = '1';
				$log_array['log_backtrace'] = false;	
						
				$log->add($log_array);
						
				return false;
			}
		}	
	}
	
	private function clear_failed_login($user) {
		global $db;
		
		$users = &singleton::get(__NAMESPACE__  . '\users');

		$users->edit(array('id' => $user['id'], 'failed_logins' => 0, 'fail_expires' => datetime()));
		
		return true;
	}
	
	private function increment_failed_login($user) {
		global $db;
		
		$users = &singleton::get(__NAMESPACE__  . '\users');
		
		$users->edit(array('id' => $user['id'], 'failed_logins' => $user['failed_logins'] + 1, 'fail_expires' => datetime(900)));
		
		return true;
	}
	
	//creates user data in session	
	private function login_session($user) {
	
		$session = &singleton::get(__NAMESPACE__  . '\sessions');
		
		//improves security
		$session->regenerate_id();
		
		$user_array = array(
			'id' 					=> $user['id'],
			'name' 					=> $user['name'],
			'username' 				=> $user['username'],
			'email'	   				=> $user['email'],
			'user_level'	 		=> $user['user_level'],
			'group_id'	 			=> 0,
			'authentication_id'	 	=> $user['authentication_id']
		);
		
		if (isset($user['group_id'])) {
			$user_array['group_id'] = $user['group_id'];
		}

		$_SESSION['user_data'] 		= $user_array;
		$this->current_user			= $user_array;

		
		return $user_array;
	}
	
	/**
	 * Logs out the current user.
	 */
	public function logout() {
	
		$plugins 						= &singleton::get(__NAMESPACE__ . '\plugins');	
		$plugins->run('auth_logout_start');
		
		unset($this->current_user);
		session_destroy();
	}
	
	public function generate_user_salt() {
		return rand_str(64);
	}
	
	public function hash_password($password, $user_salt) {
		return hash_hmac('sha512', $password . $user_salt, $this->salt);
	}
}

?>