<?php
/**
 * 	Configuration Class
 *	Copyright Dalegroup Pty Ltd 2015
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <support@dalegroup.net>
 */

namespace mrpassword;

class config {

	private $config 		= NULL;
	private $hard_config 	= NULL;

	/**
	 * Loads the configuration from the database into an array for use
	 */
	public function __construct() {

	}

	public function load() {
		global $db;
		
		$error 		= &singleton::get(__NAMESPACE__  . '\error');
		$tables		= &singleton::get(__NAMESPACE__  . '\tables');
		
		$site_id 	= SITE_ID;
	
		try {
			$query = "SELECT config_name, config_value FROM $tables->config WHERE `site_id` = :site_id";
			
			try {
				$stmt = $db->prepare($query);
			}
			catch (Exception $e) {
				$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
			}
				
			$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

			try {
				$stmt->execute();
			}
			catch (Exception $e) {
				$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
			}
			
			$config = $stmt->fetchAll(database::FETCH_ASSOC);
			
			foreach ($config as $item) {
				if (isset($this->hard_config[$item['config_name']])) {
					$this->config[$item['config_name']] = $this->hard_config[$item['config_name']];
				}			
				else {
					$this->config[$item['config_name']] = $item['config_value'];
				}
			}
		} catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		//generate site address, used for most things
		$port_number = '';
		if ($this->config['https']) {
			if ($this->config['port_number'] != 443) {
				$port_number = ':' . $this->config['port_number'];
			}
			$this->config['address'] = 'https://' . $this->config['domain'] . $port_number . $this->config['script_path'];
		}
		else {
			if ($this->config['port_number'] != 80) {
				$port_number = ':' . $this->config['port_number'];
			}
			$this->config['address'] = 'http://' . $this->config['domain'] . $port_number . $this->config['script_path'];
		}
				
		$this->hard_config['address']			= $this->config['address'];
			
		return true;	
	}
	
	/**
	 * Get a config value out of the configuration database
	 *
	 * @param   string   $config_name 		The configuration item you want to get
	 * @param   bool   	 $unserialize 		If true serialized data will be converted to an array
	 * @return  string						The value of the configuration item or FALSE if the item does not exist
	 */
	public function get($config_name, $unserialize = true, $array = NULL) {
		if (isset($this->config[$config_name])) {	
			if ($unserialize == true) {
				$str = 's';
				$array = 'a';
				$integer = 'i';
				$any = '[^}]*?';
				$count = '\d+';
				$content = '"(?:\\\";|.)*?";';
				$open_tag = '\{';
				$close_tag = '\}';
				$parameter = "($str|$array|$integer|$any):($count)" . "(?:[:]($open_tag|$content)|[;])";           
				$preg = "/$parameter|($close_tag)/";
				if(!preg_match_all($preg, $this->config[$config_name], $matches)) {           
					return $this->config[$config_name];
				}
				else {
					return unserialize($this->config[$config_name]);
				}
			}
			else {
				return $this->config[$config_name];
			}
		}
		else {
			return false;
		}
	}
	
	/**
	 * Create a new config value to be stored in the database
	 *
	 * @param   string   $config_name 		The configuration item you want to create
	 * @param   string   $config_value 		The value to store
	 * @return  bool						TRUE if created, FALSE if the value already exists
	 */
	public function add($config_name, $config_value) {
		global $db;
		
		$error 		= &singleton::get(__NAMESPACE__  . '\error');
		$tables 	= &singleton::get(__NAMESPACE__  . '\tables');
		$site_id 	= SITE_ID;

		if (!isset($this->config[$config_name])) {
			if (is_array($config_value)) {
				$this->config[$config_name] = serialize($config_value);
			}
			else {
				$this->config[$config_name] = $config_value;
			}
			try {
				$stmt = $db->prepare("INSERT INTO $tables->config (config_value, config_name, `site_id`) VALUES (:value, :name, :site_id)");
			}
			catch (Exception $e) {
				$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
			}
			
			$stmt->bindParam(':value', $this->config[$config_name]);
			$stmt->bindParam(':name', $config_name);
			$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
			
			try {
				$stmt->execute();
			}
			catch (Exception $e) {
				$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
			}
		}
		else {
			return false;
		}
	}
	
	public function hard_set($config_name, $config_value) {
		if (is_array($config_value)) {
			$this->hard_config[$config_name] = serialize($config_value);
		}
		else {
			$this->hard_config[$config_name] = $config_value;
		}
	}
	
	/**
	 * Updates a value in the configuration database
	 *
	 * @param   string   $config_name 		The configuration item you want to set
	 * @param   string   $config_value 		The value to store
	 */
	public function set($config_name, $config_value) {
		global $db;
		
		$error 		= &singleton::get(__NAMESPACE__  . '\error');
		$tables 	= &singleton::get(__NAMESPACE__  . '\tables');
		$site_id 	= SITE_ID;

		if (isset($this->hard_config[$config_name])) return false;
		
		if (is_array($config_value)) {
			$this->config[$config_name] = serialize($config_value);
		}
		else {
			$this->config[$config_name] = $config_value;
		}
	
		$stmt = $db->prepare("UPDATE $tables->config SET config_value = :value WHERE config_name = :name AND `site_id` = :site_id");
		
		$stmt->bindParam(':value', $this->config[$config_name], database::PARAM_STR);
		$stmt->bindParam(':name', $config_name, database::PARAM_STR);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		return true;
	}
	
	/**
	 * Deletes a config value from the database
	 *
	 * @param   string   $config_name 		The configuration item you want to delete
	 * @return  bool						TRUE if deleted, FALSE if the value does not exists
	 */
	public function delete($config_name) {
		global $db;
		
		$error = &singleton::get(__NAMESPACE__  . '\error');
		$tables = &singleton::get(__NAMESPACE__  . '\tables');
		$site_id 	= SITE_ID;

		if (isset($this->hard_config[$config_name])) return false;
	
		if (isset($this->config[$config_name])) {	
			$stmt = $db->prepare("DELETE FROM $tables->config WHERE config_name = :name AND `site_id` = :site_id");
			$stmt->bindParam(':name', $config_name);
			$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

			try {
				$stmt->execute();
			}
			catch (Exception $e) {
				$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
			}
			
			unset($this->config[$config_name]);
			
			return true;
		}
		else {
			return false;
		}
	}
}
?>