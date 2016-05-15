<?php
/*
	Copyright Dalegroup Pty Ltd 2013
*/
class install {

	var $db;
	private $tasks;
	private $config;

	function __construct() {
		$this->config['version']	= '3.1';
		$this->config['db_version']	= 23;
	}
	
	function form_data($form_name, $default_value = NULL) {
		if (isset($_POST[$form_name])) {
			return $_POST[$form_name];
		}
		else if (isset($_SESSION['install_data']['config'][$form_name])) {
			return $_SESSION['install_data']['config'][$form_name];
		}
		else if (!empty($default_value)) {
			return $default_value;
		}
		else {
			return '';
		}
	}
	
	public function get_config($name) {
		if (isset($this->config[$name])) {
			return $this->config[$name];
		}
		else {
			return false;
		}
	}
	
	function set_form($form_name, $form_data) {
		$_SESSION['install_data']['config'][$form_name] = $form_data;
	}
	
	function session_data($form_name) {
		if (isset($_SESSION['install_data']['config'][$form_name])) {
			return $_SESSION['install_data']['config'][$form_name];
		}
		else {
			return false;
		}
	}
	
	function connect_db() {
	
		//start database connection here
		$ipm_db = new PDO('mysql:host=' . $this->session_data('dbhost') . ';dbname=' . $this->session_data('dbname'), $this->session_data('dbusername'), $this->session_data('dbpassword'));
	
		$ipm_db->exec('SET NAMES UTF8');
	
		$ipm_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$ipm_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		
		$this->db = $ipm_db;
	}
	
	private function install_db_structure() {
			
		$query = "CREATE TABLE IF NOT EXISTS `config` (
					  `config_name` varchar(255) NOT NULL,
					  `config_value` LONGTEXT NOT NULL,
					  `site_id` int(11) unsigned NOT NULL,
					  KEY `site_id` (`site_id`)
				) DEFAULT CHARSET=utf8";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}

		$query = "CREATE TABLE IF NOT EXISTS `events` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Event Primary Key',
			  `event_number` int(11) NOT NULL,
			  `user_id` int(11) unsigned NOT NULL COMMENT 'User ID',
			  `server_id` int(11) unsigned DEFAULT NULL COMMENT 'The ID of the remote log client',
			  `remote_id` int(11) unsigned DEFAULT NULL COMMENT 'The Event Primary Key from the remote client',
			  `event_date` datetime NOT NULL COMMENT 'Event Datetime in local timezone',
			  `event_date_utc` datetime NOT NULL COMMENT 'Event Datetime in UTC timezone',
			  `event_type` varchar(255) NOT NULL COMMENT 'The type of event',
			  `event_source` varchar(255) NOT NULL COMMENT 'Text description of the source of the event',
			  `event_severity` varchar(255) NOT NULL COMMENT 'Notice, Warning etc',
			  `event_file` text NOT NULL COMMENT 'The full file location of the source of the event',
			  `event_file_line` int(11) NOT NULL COMMENT 'The line in the file that triggered the event',
			  `event_ip_address` varchar(255) NOT NULL COMMENT 'IP Address of the user that triggered the event',
			  `event_summary` varchar(255) NULL COMMENT 'A summary of the description',
			  `event_description` text NOT NULL COMMENT 'Full description of the event',
			  `event_trace` text NULL COMMENT 'Full PHP trace',
			  `event_synced` int(1) unsigned DEFAULT '0',
			  `site_id` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `event_type` (`event_type`),
			  KEY `event_source` (`event_source`),
			  KEY `user_id` (`user_id`),
			  KEY `server_id` (`server_id`),
			  KEY `event_date` (`event_date`),
			  KEY `site_id` (`site_id`)
			) DEFAULT CHARSET=utf8";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
					
		$query = "CREATE TABLE IF NOT EXISTS `passwords` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) NOT NULL,
			  `username` varchar(255) NOT NULL,
			  `password` TEXT NOT NULL,
			  `description` text NOT NULL,
			  `date_added` datetime NOT NULL,
			  `user_id` int(11) unsigned NOT NULL,
			  `site_id` int(11) unsigned NOT NULL,
			  `category_id` int(11) unsigned DEFAULT NULL,
			  `last_modified` datetime NOT NULL,
			  `old` INT( 1 ) UNSIGNED NOT NULL DEFAULT 0,
			  `parent_id` INT( 11 ) UNSIGNED NULL,
			  `url` TEXT NULL,
			  `encryption_level` int(11) UNSIGNED NOT NULL DEFAULT 1,
			  PRIMARY KEY (`id`),
			  KEY `user_id` (`user_id`),
			  KEY `site_id` (`site_id`),
			  KEY `category_id` (`category_id`),
			  KEY `old` ( `old` ),
			  KEY `parent_id` ( `parent_id` )
			) DEFAULT CHARSET=utf8";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `shares` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `user_id` int(11) unsigned NOT NULL,
			  `shared_user_id` int(11) unsigned NOT NULL,
			  `category_id` int(11) unsigned NOT NULL,
			  `site_id` int(11) unsigned NOT NULL,
			  `access_level` INT( 11 ) UNSIGNED NOT NULL DEFAULT 1,
			  PRIMARY KEY (`id`),
			  KEY `user_id` (`user_id`),
			  KEY `shared_user_id` (`shared_user_id`),
			  KEY `category_id` (`category_id`),
			  KEY `site_id` (`site_id`)
			) DEFAULT CHARSET=utf8";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		
		$query = "CREATE TABLE IF NOT EXISTS `categories` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) NOT NULL,
			  `user_id` int(11) unsigned NOT NULL,
			  `site_id` int(11) unsigned NOT NULL,
			  `global` int(1) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`),
			  KEY `user_id` (`user_id`),
			  KEY `site_id` (`site_id`),
			  KEY `global` (`global`)
			) DEFAULT CHARSET=utf8";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `sessions` (
			  `session_id` varchar(32) NOT NULL DEFAULT '',
			  `session_start` datetime NOT NULL,
			  `session_start_utc` datetime NOT NULL,
			  `session_expire` datetime NOT NULL,
			  `session_expire_utc` datetime NOT NULL,
			  `session_data` text,
			  `session_active_key` varchar(32) DEFAULT NULL,
			  `ip_address` varchar(100) DEFAULT NULL,
			  `site_id` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`session_id`),
			  KEY `session_expire` (`session_expire`),
			  KEY `site_id` (`site_id`)
			) DEFAULT CHARSET=utf8";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
				
		$query = "CREATE TABLE IF NOT EXISTS `users` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) DEFAULT NULL,
		  `username` varchar(255) NOT NULL,
		  `password` varchar(255) NULL,
		  `salt` varchar(255) NOT NULL,
		  `email` varchar(255) NULL,
		  `authentication_id` int(11) unsigned NOT NULL DEFAULT '0',
		  `group_id` int(11) unsigned NOT NULL DEFAULT '0',
		  `user_level` int(11) unsigned NOT NULL DEFAULT '1',
		  `allow_login` int(1) unsigned NOT NULL DEFAULT '0',
		  `site_id` int(11) unsigned NOT NULL,
		  `failed_logins` INT( 11 ) UNSIGNED NULL,
		  `fail_expires` DATETIME NULL,
		  `reset_key` VARCHAR(255) NULL,
		  `reset_expiry` DATETIME NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `username` (`username`),
		  KEY `site_id` (`site_id`)
		) DEFAULT CHARSET=utf8";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
	
	
		$query = "
		CREATE TABLE IF NOT EXISTS `custom_fields` (
			`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`password_id` int(11) UNSIGNED NOT NULL,
			`name` VARCHAR( 255 ) NOT NULL,
			`value` TEXT NOT NULL ,
			`site_id` int(11) unsigned NOT NULL,
			`encryption_level` int(11) UNSIGNED NOT NULL DEFAULT 1,
			INDEX (`password_id`),
			INDEX (`site_id`)
			) DEFAULT CHARSET=utf8";
	
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `password_field_group` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`site_id` INT( 11 ) UNSIGNED NOT NULL ,
			`name` VARCHAR( 255 ) NULL ,
			`type` VARCHAR( 255 ) NOT NULL ,
			`list_view` int( 1 ) NOT NULL DEFAULT 0 ,
			`enabled` int( 1 ) NOT NULL ,
			`default_field_id` int(11) unsigned NULL,
			PRIMARY KEY (`id`),
			KEY `site_id` (`site_id`),
			KEY `list_view` (`list_view`),
			KEY `enabled` (`enabled`)
			) DEFAULT CHARSET=utf8;
		";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `password_fields` (
			`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`site_id` INT( 11 ) UNSIGNED NOT NULL ,
			`value` VARCHAR( 255 ) NOT NULL,
			`password_field_group_id` int( 11 ) unsigned NOT NULL,
			PRIMARY KEY (`id`),
			KEY `site_id` (`site_id`),
			KEY `password_field_group_id` (`password_field_group_id`)
			) DEFAULT CHARSET=utf8;
		";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `password_field_values` (
			`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`site_id` INT( 11 ) UNSIGNED NOT NULL ,
			`password_id` int( 11 ) unsigned NOT NULL,
			`password_field_group_id` int( 11 ) unsigned NOT NULL,
			`value` LONGTEXT NOT NULL,
			PRIMARY KEY (`id`),
			KEY `site_id` (`site_id`),
			KEY `password_id` (`password_id`),
			KEY `password_field_group_id` (`password_field_group_id`)
			) DEFAULT CHARSET=utf8;
		";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
			//queue
		$query = "CREATE TABLE IF NOT EXISTS `queue` (
					  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					  `data` longtext NOT NULL,
					  `type` varchar(255) NOT NULL,
					  `start_date` datetime DEFAULT NULL,
					  `date` datetime NOT NULL,
					  `retry` int(11) unsigned DEFAULT '0',
					  `site_id` int(11) unsigned NOT NULL,
					  PRIMARY KEY (`id`),
					  KEY `site_id` (`site_id`) 
				)  DEFAULT CHARSET=utf8;
				";
				
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		//storage
		
		$query = "
		CREATE TABLE IF NOT EXISTS `storage` (
			`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`name` VARCHAR( 255 ) NULL ,
			`uuid` VARCHAR( 255 ) NOT NULL ,
			`date_added` DATETIME NOT NULL ,
			`extension` VARCHAR( 255 ) NULL ,
			`description` TEXT NULL ,
			`type` VARCHAR( 255 ) NULL ,
			`category_id` INT( 11 ) UNSIGNED NULL ,
			`user_id` INT( 11 ) UNSIGNED NULL ,
			`site_id` INT( 11 ) UNSIGNED NOT NULL ,
			UNIQUE (
				`uuid`
			)
		) DEFAULT CHARSET=utf8;		
		";
			
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		$query = "
		CREATE TABLE IF NOT EXISTS `files_to_passwords` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `password_id` int(11) unsigned NOT NULL,
		  `file_id` int(11) unsigned NOT NULL,
		  `site_id` int(11) unsigned NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `password_id` (`password_id`),
		  KEY `file_id` (`file_id`),
		  KEY `site_id` (`site_id`)
		) DEFAULT CHARSET=utf8;		
		";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
	}
	
	private function install_db_data() {	
	
		$stmt = $this->db->prepare("INSERT INTO `config` (`config_name`, `config_value`, `site_id`) VALUES
			('domain', :domain, 1),
			('script_path', :path, 1),
			('https', :https, 1),
			('port_number', :port, 1),
			('name', :name, 1),
			('cookie_name', 'mrp', 1),
			('encryption_key', :key, 1),
			('database_version', :db_version, 1),
			('program_version', :program, 1),
			('ad_server', '', 1),
			('ad_account_suffix', '', 1),
			('ad_base_dn', '', 1),
			('ad_create_accounts', 0, 1),
			('ad_enabled', 0, 1),
			('lockout_enabled', 1, 1),
			('login_message', '', 1),
			('registration_enabled', 0, 1),
			('smtp_enabled', 0, 1),
			('smtp_email_address', '', 1),
			('smtp_server', '', 1),
			('smtp_auth', 0, 1),
			('smtp_username', '', 1),
			('smtp_password', '', 1),
			('last_update_response', '', 1),
			('cron_intervals', :cron_intervals, 1),
			('plugin_data', :plugin_data, 1),
			('plugin_update_data', :plugin_update_data, 1),
			('smtp_tls', 0, 1),
			('smtp_port', 25, 1),
			('default_language', 'english_aus', 1),
			('default_theme', 'bootstrap3', 1),
			('default_timezone', :timezone, 1),
			('log_limit', 100000, 1),
			('license_key', '', 1),
			('notification_new_user_subject', :notification_new_user_subject, 1),
			('notification_new_user_body', :notification_new_user_body, 1),
			('ldap_server', '', 1),
			('ldap_account_suffix', '', 1),
			('ldap_base_dn', '', 1),
			('ldap_create_accounts', 0, 1),
			('ldap_enabled', 0, 1),
			('captcha_enabled', 0, 1),
			('application_id', 2, 1),
			('auto_update_cache', :auto_update_cache, 1),
			('utc_date_installed', :utc_date_installed, 1),
			('encryption_level', 2, 1),
			('captcha_extra_enabled', 0, 1),
			('default_theme_sub', 'default', 1),
			('auth_json_url', '', 1),
			('auth_json_key', '', 1),
			('auth_json_enabled', 0, 1),
			('auth_json_site_id', '', 1),
			('auth_json_create_accounts', 0, 1),
			('storage_enabled', :storage_enabled, 1),
			('storage_path', :storage_path, 1)
		");		
				
		$domain 		= $this->session_data('domain');
		$name 			= $this->session_data('site_name');
		$script_path 	= $this->session_data('script_path');
		$port 			= (int)$this->session_data('port');
		$https 			= (int)$this->session_data('https');
		$key 			= $this->session_data('encryption_key');
		$db_version		= $this->get_config('db_version');
		$program		= $this->get_config('version');
		$timezone		= $this->session_data('default_timezone');
		$storage_enabled	= (int) $this->session_data('storage_enabled');
		$storage_path		= $this->session_data('storage_path');
		
				
		$cron_intervals = array(
			array('name' => 'every_two_minutes', 'description' => 'Every Two Minutes', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '120'),
			array('name' => 'every_five_minutes', 'description' => 'Every Five Minutes', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '300'),
			array('name' => 'every_fifteen_minutes', 'description' => 'Every Fifteen Minutes', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '900'),
			array('name' => 'every_hour', 'description' => 'Every Hour', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '3600'),
			array('name' => 'every_day', 'description' => 'Every Day', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '86400'),
			array('name' => 'every_week', 'description' => 'Every Week', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '604800'),
			array('name' => 'every_month', 'description' => 'Every Month', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '2592000'),
			);
		
		$cron_intervals			= serialize($cron_intervals);
		$plugin_data			= serialize(array());
		$plugin_update_data		= serialize(array());	
		$auto_update_cache		= serialize(array());	
		
		date_default_timezone_set($timezone);
		
		$datetime_utc			= datetime_utc();
		
		$stmt->bindParam(':domain', $domain, PDO::PARAM_STR);
		$stmt->bindParam(':name', $name, PDO::PARAM_STR);
		$stmt->bindParam(':path', $script_path, PDO::PARAM_STR);
		$stmt->bindParam(':port', $port, PDO::PARAM_INT);
		$stmt->bindParam(':https', $https, PDO::PARAM_INT);
		$stmt->bindParam(':key', $key, PDO::PARAM_STR);
		$stmt->bindParam(':db_version', $db_version, PDO::PARAM_STR);
		$stmt->bindParam(':program', $program, PDO::PARAM_STR);
		$stmt->bindParam(':cron_intervals', $cron_intervals, PDO::PARAM_STR);
		$stmt->bindParam(':timezone', $timezone, PDO::PARAM_STR);
		$stmt->bindParam(':plugin_data', $plugin_data, PDO::PARAM_STR);
		$stmt->bindParam(':plugin_update_data', $plugin_update_data, PDO::PARAM_STR);
		$stmt->bindParam(':auto_update_cache', $auto_update_cache, PDO::PARAM_STR);
		$stmt->bindParam(':storage_enabled', $storage_enabled, PDO::PARAM_INT);
		$stmt->bindParam(':storage_path', $storage_path, PDO::PARAM_STR);
		$stmt->bindParam(':utc_date_installed', $datetime_utc, PDO::PARAM_STR);

		
$notification_new_user_subject = '#SITE_NAME# - New Account';
$notification_new_user_body = '
Hi #USER_FULLNAME#,
<br /><br />
A user account has been created for you at #SITE_NAME#.
<br /><br />
URL: 		#SITE_ADDRESS#<br />
Name:		#USER_FULLNAME#<br />
Username:	#USER_NAME#<br />
Password:	#USER_PASSWORD#';
		
		$stmt->bindParam(':notification_new_user_subject', $notification_new_user_subject, PDO::PARAM_STR);
		$stmt->bindParam(':notification_new_user_body', $notification_new_user_body, PDO::PARAM_STR);

		
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
	}
	
	private function install_admin_user() {
	
		$user_salt 			= ipm_rand_str(64);
		$password			= $this->session_data('admin_password');
		$salt				= $this->session_data('site_salt');
		$hashed_password	= hash_hmac('sha512', $password . $user_salt, $salt);
	

		$stmt = $this->db->prepare("INSERT INTO `users` (
			`name`, `username`, `password`, `salt`, `email`, `authentication_id`, `user_level`, `allow_login`, `site_id`
			) VALUES (:name, :username, :password, :salt, :email, 1, 2, 1, 1)");
		
		$name 			= $this->session_data('admin_name');
		$username 		= $this->session_data('admin_username');
		$email 			= $this->session_data('admin_email');

		
		$stmt->bindParam(':name', $name, PDO::PARAM_STR);
		$stmt->bindParam(':username', $username, PDO::PARAM_STR);
		$stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
		$stmt->bindParam(':salt', $user_salt, PDO::PARAM_STR);
		$stmt->bindParam(':email', $email, PDO::PARAM_STR);


		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}

	}
	
	
	
	public function install_db() {
	
		//max 2 minutes processing time
		set_time_limit(120); 
	
		$this->install_db_structure();
		$this->install_db_data();
		$this->install_admin_user();

	}

	public function test_is_installed($database_connection) {
		
		$stmt = $database_connection->prepare("SHOW TABLES LIKE 'config'");
		
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			ipm_die($e->getMessage());
		}
		
		$array = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		if (!isset($array[0])) return false;
		
		return true;	
	}
	
	public function is_installed() {
		
		$stmt = $this->db->prepare("SHOW TABLES LIKE 'config'");
		
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			ipm_die($e->getMessage());
		}
		
		$array = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		if (!isset($array[0])) return false;
		
		return true;
	}
	
	public function test_write() {
	
		$filename 		= dirname(__FILE__) . '/../../.mrp_test_file';
		
		$data = 'This is a test file for MrP and can be deleted.';
		
		if (@file_put_contents($filename, $data)) { 
			return true;
		}
		else {
			return false;
		}
  
	}
	
	public function test_write_config() {
	
		$filename 		= dirname(__FILE__) . '/../../user/settings/.mrp_test_file';
		
		$data = 'This is a test file for MrP and can be deleted.';
		
		if (@file_put_contents($filename, $data)) { 
			return true;
		}
		else {
			return false;
		}
  
	}

	public function write_htaccess() {
	
		$data 			= array(
								'script_path' => $this->session_data('script_path')							
							);
	
		$filename 		= dirname(__FILE__) . '/../../.htaccess';
		$config_data	= $this->create_htaccess($data);
		
		if ($handle = @fopen($filename, 'x')) { 
  
			if (fwrite($handle, $config_data)) {   
				fclose($handle);
				return true;
			}
			else {
				$return['message'] = 'Unable to write "' . ipm_htmlentities($filename) . '". Please make sure you create it yourself.';
				$return['success'] = false;
				fclose($handle);
				return $return;
			}
		}
		else {
			$return['message'] = '"' . ipm_htmlentities($filename) . '" already seems to exist or PHP doesn\'t have write access to its location. Please make sure this file has been created with the correct details.';
			$return['success'] = false;
		
			return false;
		}
	}
	
	public function write_config() {
	
		$data 			= array(
								'db_hostname' 		=> $this->session_data('dbhost'),
								'db_username' 		=> $this->session_data('dbusername'),
								'db_password' 		=> $this->session_data('dbpassword'),
								'db_name' 			=> $this->session_data('dbname'),
								'salt' 				=> $this->session_data('site_salt'),	
								'encryption_key' 	=> $this->session_data('file_encryption_key')								
							);
	
		$filename 		= dirname(__FILE__) . '/../../user/settings/config.php';
		$config_data	= $this->create_mysql_config($data);
		
		if ($handle = fopen($filename, 'x')) { 
  
			if (fwrite($handle, $config_data)) {   
				fclose($handle);
				return true;
			}
			else {
				$return['message'] = 'Unable to write "' . ipm_htmlentities($filename) . '". Please make sure you create it yourself.';
				$return['success'] = false;
				fclose($handle);
				return $return;
			}
		}
		else {
			$return['message'] = '"' . ipm_htmlentities($filename) . '" already seems to exist or PHP doesn\'t have write access to its location. Please make sure this file has been created with the correct details.';
			$return['success'] = false;
		
			return false;
		}
	}
	
	private function create_htaccess($array) {
	
	$array['script_path'] = str_replace(' ', '%20', $array['script_path']);
	
	$config_data = '<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteBase ' .$array['script_path'] . '/

	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^(.*)$ index.php?url=$1 [L,QSA]
</IfModule>	';
		return $config_data;
	}
	
	private function create_mysql_config($config_array)  {
	
	$config_data = '<?php
namespace mrpassword;

/*
	MrP Config File
	
	You should only need to change: database_hostname, database_username, database_password, database_name and salt
	
	Database Charset should always be UTF8
	Database Table Prefix should always be empty
	SITE ID should always be 1	
*/
if (!defined(__NAMESPACE__ . \'\SEC_DB_LOADED\')) {
	$config =
		array(
			\'database_hostname\'		=> \'' . $config_array['db_hostname'] . '\',
			\'database_username\'		=> \'' . $config_array['db_username'] . '\',
			\'database_password\'		=> \'' . $config_array['db_password'] . '\',
			\'database_name\'			=> \'' . $config_array['db_name'] . '\',
			\'database_type\'			=> \'mysql\',
			\'database_charset\'		=> \'UTF8\',
			\'database_table_prefix\'	=> \'\',
			\'site_id\'					=> 1,
			\'salt\'					=> \'' . $config_array['salt'] . '\',
			\'encryption_key\'			=> \'' . $config_array['encryption_key'] . '\'
		);
}
?>';
	return $config_data;
	}
	
	public function storage_path() {
		$root_path = substr(__DIR__, 0, -16);
		$root_path .= 'user/files/';

		if (is_dir($root_path)) {
			return $root_path;
		}
		else {
			return '';
		}
	}

}
	
?>