<?php
/**
 * 	Session Class
 *	Copyright Dalegroup Pty Ltd 2015
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <support@dalegroup.net>
 */

namespace mrpassword;
use PDO;

class sessions {

	private $db;
	private $tb;
	private $life_time;
	private $site_id;
	
	//setup the session
	function __construct($array) {
	
		$config 			= &singleton::get(__NAMESPACE__ . '\config');
		$this->db 			= &$array['database'];
		$this->tb 			= $array['table_name'];
		$this->site_id 		= SITE_ID;
		$this->life_time 	= 3600; //1 hr
		
		if ($config->get('session_life_time')) {
			$this->life_time = (int) $config->get('session_life_time');
		}
				
		session_name($config->get('cookie_name') . '_sid');
		ini_set('session.use_only_cookies', 1);
		
		if ($config->get('domain') == 'localhost') {
			$domain = '';
		}
		else {
			$domain = $config->get('domain');
		}
		
		if ($config->get('https')) {
			session_set_cookie_params(0, $config->get('script_path') . '/', $domain, true, true);
		}
		else {
			session_set_cookie_params(0, $config->get('script_path') . '/', $domain, false, true);
		}
		
		if (isset($array['session_id']) && !empty($array['session_id'])) {	
			session_id($array['session_id']);
		}
		
		session_set_save_handler(
			array(&$this, 'open'),
			array(&$this, 'close'),
			array(&$this, 'read'),
			array(&$this, 'write'),
			array(&$this, 'destroy'),
			array(&$this, 'gc')
		);
		
		session_start();
		
		return true;
	}           

	public function open($save_path, $id) {	
		return true;
	}
	public function close() {
		return true;
	}
	
	//reading the array from the session
	public function read($session_id) {
		
		$error 	=	&singleton::get(__NAMESPACE__ . '\error');

		$query = "SELECT session_data FROM $this->tb WHERE session_id = :session_id AND `site_id` = :site_id LIMIT 1";
		$stmt = $this->db->prepare($query);
		$stmt->bindParam(':session_id', $session_id);	
		$stmt->bindParam(':site_id', $this->site_id, database::PARAM_INT);
				
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		if ($array = $stmt->fetch(database::FETCH_ASSOC)) {
			return $array['session_data'];
		}
		else {
			//$this->create();
			return '';
		}
	}
	
	//writes an array to the session
	public function write($session_id, $data) {

		$error 	=	&singleton::get(__NAMESPACE__ . '\error');
		
		$ip_address = ip_address();
		
		$query = "REPLACE INTO $this->tb
		(session_id, session_start, session_start_utc, session_expire, session_expire_utc, session_data, ip_address, `site_id`)
		VALUES
		(:session_id, :session_start, :session_start_utc, :session_expire, :session_expire_utc, :session_data, :ip_address, :site_id)
		";
		$datetime = datetime();
		$datetime_utc = datetime_utc();
		$expire = datetime($this->life_time);
		$expire_utc = datetime_utc($this->life_time);
		$stmt = $this->db->prepare($query);
		$stmt->bindParam(':session_data', $data);
		$stmt->bindParam(':session_start', $datetime);
		$stmt->bindParam(':session_start_utc', $datetime_utc);
		$stmt->bindParam(':session_expire', $expire);
		$stmt->bindParam(':session_expire_utc', $expire_utc);
		$stmt->bindParam(':session_id', $session_id);
		$stmt->bindParam(':ip_address', $ip_address, database::PARAM_STR);
		$stmt->bindParam(':site_id', $this->site_id, database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		return true;
	}
	
	//destroys a session id
	public function destroy($session_id) {
	
		$error 	=	&singleton::get(__NAMESPACE__ . '\error');

		$query = "DELETE FROM $this->tb WHERE session_id = :session_id AND `site_id` = :site_id";
		
		$stmt = $this->db->prepare($query);
		
		$stmt->bindParam(':session_id', $session_id);
		$stmt->bindParam(':site_id', $this->site_id, database::PARAM_INT);

		
		try {
			$stmt->execute();
			return true;
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
	}
	//garbage collection
	public function gc() {
	
		$error 	=	&singleton::get(__NAMESPACE__ . '\error');
	
		$now = datetime();
		
		$query = "DELETE FROM $this->tb WHERE session_expire <= :expire AND `site_id` = :site_id";
		$stmt = $this->db->prepare($query);
		$stmt->bindParam(':expire', $now);
		$stmt->bindParam(':site_id', $this->site_id, database::PARAM_INT);

		
		try {
			$stmt->execute();
			return true;
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
	}
	
	//regenerate session id in database and deletes old session
	public function regenerate_id() {
	
		$error 	=	&singleton::get(__NAMESPACE__ . '\error');

		$old_id = session_id();
		session_regenerate_id();
		$new_id = session_id();
		
		$query = "UPDATE $this->tb SET session_id = :new_id WHERE session_id = :old_id AND `site_id` = :site_id";
		
		$stmt = $this->db->prepare($query);
		$stmt->bindParam(':new_id', $new_id);
		$stmt->bindParam(':old_id', $old_id);
		$stmt->bindParam(':site_id', $this->site_id, database::PARAM_INT);
		
		try {
			$stmt->execute();
			return true;
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
	}
	
	//create session in database here
	private function create() {
	
		$error 	=	&singleton::get(__NAMESPACE__ . '\error');
		
		$query = "INSERT INTO $this->tb
		(session_id, session_start, session_start_utc, session_expire, session_expire_utc, ip_address, `site_id`)
		VALUES
		(:session_id, :session_start, :session_start_utc, :session_expire, :session_expire_utc, :ip_address, :site_id)
		";
		
		$ip_address 				= ip_address();
		$session_id					= session_id();
		$datetime					= datetime();
		$datetime_utc				= datetime_utc();
		$datetime_expire			= datetime($this->life_time);
		$datetime_expire_utc		= datetime_utc($this->life_time);

		
		$stmt = $this->db->prepare($query);
		$stmt->bindParam(':session_id', $session_id);
		$stmt->bindParam(':session_start', $datetime);
		$stmt->bindParam(':session_start_utc', $datetime_utc);
		$stmt->bindParam(':session_expire', $datetime_expire);
		$stmt->bindParam(':session_expire_utc', $datetime_expire_utc);
		$stmt->bindParam(':ip_address', $ip_address, database::PARAM_STR);
		$stmt->bindParam(':site_id', $this->site_id, database::PARAM_INT);


		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		return true;
	}
}

?>