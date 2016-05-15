<?php
/**
 * 	Upgrade Class
 *	Copyright Dalegroup Pty Ltd 2015
 *	support@dalegroup.net
 *
 *
 * @package     mrpassword
 * @author      Michael Dale <support@dalegroup.net>
 */

namespace mrpassword;

class upgrade {

	private $db_version 		= '27';
	private $program_version 	= '4.0';

	function __construct() {
	
	}
	public function get_db_version() {
		return $this->db_version;
	}
	public function get_program_version() {
		return $this->program_version;
	}
	
	public function version_info() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');
		$log		= &singleton::get(__NAMESPACE__ . '\log');

		$update_message = $config->get('last_update_response');
		
		$return['code_database_version']			= $this->db_version;
		$return['code_program_version']				= $this->program_version;
		$return['installed_program_version']		= $config->get('program_version');
		$return['installed_database_version']		= $config->get('database_version');
		$return['latest_program_version']			= '';
		$return['latest_database_version']			= '';
		
		if (!empty($update_message)) {
								
			if (isset($update_message['version'])) {
				$return['latest_program_version']			= (string) $update_message['version'];
			}		
			
		}	
		
		return $return;
	}
	
	public function get_update_info() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');

		$update_array = $config->get('last_update_response');
		
		if (is_array($update_array)) {
			return $update_array;
		}
		else {
			return array();
		}
	}
	
	public function update_available() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');

		$update_array = $config->get('last_update_response');
		
		$update = false;
		
		if (!empty($update_array)) {
			if (isset($update_array['version'])) {
				$version = $config->get('program_version');
				$version = explode('-', $version);
				if (version_compare($version[0], $update_array['version'], '<')) {
					$update = true;
				}
			}
		}
		
		return $update;
	}
	
	public function do_upgrade($array = NULL) {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');
		$log		= &singleton::get(__NAMESPACE__ . '\log');

		if (!ini_get('safe_mode')) {
			//ooh we can process for sooo long
			set_time_limit(280); 
		}		
		
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Database Upgrade Started (Program Version "' . safe_output($config->get('program_version')) . '", Database Version "'. safe_output($config->get('database_version')) .'")';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'upgrade';
		$log_array['event_source'] = 'upgrade';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
		
		for ($i = $config->get('database_version') + 1; $i <= $this->get_db_version(); $i++) {
			if (method_exists($this, 'dbsv_' . $i)) {
				call_user_func(array($this, 'dbsv_' . $i));		
			}
			
			if (method_exists($this, 'dbdv_' . $i)) {
				call_user_func(array($this, 'dbdv_' . $i));
			}
		}
		
		
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Database Upgrade Finished (Program Version "' . safe_output($config->get('program_version')) . '", Database Version "'. safe_output($config->get('database_version')) .'")';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'upgrade';
		$log_array['event_source'] = 'upgrade';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
		
		return true;
	}
	
	private function dbdv_2() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');

		$config->add('ad_server', '');
		$config->add('ad_account_suffix', '');
		$config->add('ad_base_dn', '');
		$config->add('ad_create_accounts', 0);
		$config->add('ad_enabled', 0);

		$config->set('database_version', 2);
		$config->set('program_version', '1.1');

	}
	
	private function dbsv_3() {
		global $db;
		
		$config 	= &singleton::get(__NAMESPACE__ . '\config');
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		
		//custom fields
		$query = "CREATE TABLE IF NOT EXISTS `$tables->custom_fields` (
			`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`password_id` int(11) UNSIGNED NOT NULL,
			`name` VARCHAR( 255 ) NOT NULL,
			`value` VARCHAR( 255 ) NOT NULL ,
			`site_id` int(11) unsigned NOT NULL,
			INDEX (`password_id`),
			INDEX (`site_id`)
			) DEFAULT CHARSET=utf8
		";
		
		try {
			$db->query($query);
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$config->set('database_version', 3);
		$config->set('program_version', '1.2');
	
	}
	
	private function dbsv_4() {
		global $db;
		
		$config 	= &singleton::get(__NAMESPACE__ . '\config');
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$query = "ALTER TABLE `$tables->users` ADD `failed_logins` INT( 11 ) UNSIGNED NULL";
		
		try {
			$db->query($query);
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->users` ADD `fail_expires` DATETIME NULL";
		
		try {
			$db->query($query);
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		
		$config->add('lockout_enabled', 0);

		$config->set('database_version', 4);
		
	}
	
	private function dbsv_5() {
		global $db;
		
		$config 	= &singleton::get(__NAMESPACE__ . '\config');
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');

		$query = "ALTER TABLE `$tables->shares` ADD `access_level` INT( 11 ) UNSIGNED NOT NULL DEFAULT 1";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	
		
		$config->set('database_version', 5);
		$config->set('program_version', '1.3');
	}
	
	private function dbdv_6() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');

		$config->add('login_message', '');

		$config->set('database_version', 6);
	}
	
	private function dbdv_7() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');

		$config->add('registration_enabled', 0);

		$config->set('database_version', 7);
		$config->set('program_version', '1.4');

	}
	
	private function dbsv_8() {
		global $db;
		
		$config 	= &singleton::get(__NAMESPACE__ . '\config');
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');

		$query = "ALTER TABLE `$tables->passwords` ADD `last_modified` datetime NOT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	
		
		$query = "ALTER TABLE `$tables->passwords` ADD `old` INT( 1 ) UNSIGNED NOT NULL DEFAULT 0";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->passwords` ADD INDEX `old` ( `old` )";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->passwords` ADD `parent_id` INT( 11 ) UNSIGNED NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->passwords` ADD INDEX `parent_id` ( `parent_id` )";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->passwords` ADD `url` TEXT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		$query = "ALTER TABLE `$tables->users` ADD `reset_key` VARCHAR(255) NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->users` ADD `reset_expiry` DATETIME NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$config->add('smtp_enabled', 0);
		$config->add('smtp_email_address', '');
		$config->add('smtp_server', '');
		$config->add('smtp_auth', 0);
		$config->add('smtp_username', '');
		$config->add('smtp_password', '');

		
		$config->set('database_version', 8);
		$config->set('program_version', '1.5');
	}
	
	private function dbdv_9() {
		global $db;
		
		$config 	= &singleton::get(__NAMESPACE__ . '\config');
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		$cron 		= &singleton::get(__NAMESPACE__ . '\cron');
		
		
		$cron_intervals = array(
			array('name' => 'every_five_minutes', 'description' => 'Every Five Minutes', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '300'),
			array('name' => 'every_fifteen_minutes', 'description' => 'Every Fifteen Minutes', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '900'),
			array('name' => 'every_hour', 'description' => 'Every Hour', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '3600'),
			array('name' => 'every_day', 'description' => 'Every Day', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '86400'),
			array('name' => 'every_week', 'description' => 'Every Week', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '604800'),
			array('name' => 'every_month', 'description' => 'Every Month', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '2592000'),
		);
		
		$cron_intervals			= serialize($cron_intervals);
		$plugin_data			= array();
		$plugin_update_data		= array();
		
		$config->add('last_update_response', '');
		$config->add('cron_intervals', $cron_intervals);
		$config->add('plugin_data', $plugin_data);
		$config->add('plugin_update_data', $plugin_update_data);
			
		$config->set('database_version', 9);
		$config->set('program_version', '1.6');
		
		$cron->update_check();
		
	}
	
	private function dbdv_10() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');

		$config->add('smtp_tls', 0);
		$config->add('smtp_port', 25);
	
		$config->set('database_version', 10);
		$config->set('program_version', '1.7');

	}
	
	private function dbsv_11() {
		global $db;
		
		$config 	= &singleton::get(__NAMESPACE__ . '\config');
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');

		$query = "ALTER TABLE `$tables->users` CHANGE `group_id` `group_id` int(11) unsigned NOT NULL DEFAULT '0'";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->events` CHANGE `event_summary` `event_summary` varchar(255) NULL COMMENT 'A summary of the description'";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->events` CHANGE `event_trace` `event_trace` TEXT NULL COMMENT 'Full PHP trace'";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	
		
		$config->set('database_version', 11);
		$config->set('program_version', '1.7.1');
	}
	
		
	private function dbsv_12() {
		global $db;
		
		$config 			= &singleton::get(__NAMESPACE__ . '\config');
		$tables 			= &singleton::get(__NAMESPACE__ . '\tables');
		$error 				= &singleton::get(__NAMESPACE__ . '\error');
		$notifications 		= &singleton::get(__NAMESPACE__ . '\notifications');
		
		$query = "ALTER TABLE `$tables->users` CHANGE `password` `password` varchar(255) NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	

		$query = "ALTER TABLE `$tables->users` CHANGE `email` `email` varchar(255) NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	
		
				
		$query = "CREATE TABLE IF NOT EXISTS `$tables->password_field_group` (
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
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `$tables->password_fields` (
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
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `$tables->password_field_values` (
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
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->config` MODIFY `config_value` LONGTEXT NOT NULL";
	
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	
		
		//queue
		$query = "CREATE TABLE IF NOT EXISTS `$tables->queue` (
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
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	

		$cron_intervals = $config->get('cron_intervals');		
		$cron_intervals[] = array('name' => 'every_two_minutes', 'description' => 'Every Two Minutes', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '120');
		$config->set('cron_intervals', $cron_intervals);
		
		$config->add('notification_new_user_subject', '');
		$config->add('notification_new_user_body', '');		
			
		$notifications->reset_new_user_notification();
		
		$config->add('default_language', 'english_aus');
		$config->add('default_theme', 'standard');
		$config->add('default_timezone', 'Australia/Sydney');
		$config->add('log_limit', '100000');
		$config->add('license_key', '');

		$config->set('database_version', 12);
		$config->set('program_version', '1.8');		
	}
	
	private function dbsv_13() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');

		$config->add('captcha_enabled', 0);

		$config->add('ldap_server', '');
		$config->add('ldap_account_suffix', '');
		$config->add('ldap_base_dn', '');
		$config->add('ldap_create_accounts', 0);
		$config->add('ldap_enabled', 0);

		$config->set('database_version', 13);
		$config->set('program_version', '2.0');	
	}

	private function dbsv_14() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');

		$config->set('database_version', 14);
		$config->set('program_version', '2.0.1');	
	}	
	
	private function dbdv_15() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');

		$config->set('database_version', 15);
	}	
	
	private function dbdv_16() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$query = "ALTER TABLE `$tables->categories` ADD `global` int(1) unsigned DEFAULT '0'";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}		
		
		$query = "ALTER TABLE `$tables->categories` ADD INDEX `global` ( `global` )";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$config->add('application_id' , 2);
		$config->add('auto_update_cache', array());
		$config->add('utc_date_installed', datetime_utc());
		$config->add('encryption_level', 1);
		
		$config->set('program_version', '2.1-beta-1');			
		$config->set('database_version', 16);
				
	}	
	
	private function dbsv_17() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$query = "ALTER TABLE `$tables->passwords` ADD `encryption_level` int(11) UNSIGNED NOT NULL DEFAULT 1";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	
		
		$query = "ALTER TABLE `$tables->passwords` MODIFY `password` TEXT NOT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	
		
		$config->add('captcha_extra_enabled', 0);

		$config->set('database_version', 17);
		
	}
	
	private function dbsv_18() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$query = "ALTER TABLE `$tables->custom_fields` ADD `encryption_level` int(11) UNSIGNED NOT NULL DEFAULT 1";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	
		
		$query = "ALTER TABLE `$tables->custom_fields` MODIFY `value` TEXT NOT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	
	
		$config->set('program_version', '2.1');				
		$config->set('database_version', 18);
		
	}
	
		
	private function dbdv_19() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');

		$config->add('default_theme_sub', 'default');			
		
		$config->add('auth_json_url', '');
		$config->add('auth_json_key', '');
		$config->add('auth_json_enabled', 0);
		$config->add('auth_json_site_id', '');
		$config->add('auth_json_create_accounts', 0);

		$config->set('program_version', '3.0-alpha-1');				
		$config->set('database_version', 19);
	}
	
	private function dbdv_20() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');

		$config->set('program_version', '3.0-beta-1');				
		$config->set('database_version', 20);
	}	

	private function dbdv_21() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$config->set('default_theme', 'bootstrap3');
		$config->set('program_version', '3.0');				
		$config->set('database_version', 21);
	}
	
	private function dbsv_22() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');

		$query = "
		CREATE TABLE IF NOT EXISTS `$tables->storage` (
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
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	
		
		$config->add('storage_enabled', 0);				
		$config->add('storage_path', USER . '/files/');

		$config->set('program_version', '3.1');				
		$config->set('database_version', 22);
		
	}
	
	private function dbsv_23() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');

		$query = "
		CREATE TABLE IF NOT EXISTS `$tables->files_to_passwords` (
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
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	
		$config->set('database_version', 23);
	
	}
	
	private function dbdv_24() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$config->set('default_theme', 'bootstrap3');
		$config->set('default_theme_sub', 'v5');			
		$config->set('program_version', '4.0-alpha-1');				
		$config->set('database_version', 24);
	}
	
	private function dbsv_25() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		
		$query = "ALTER TABLE `$tables->sessions` MODIFY `session_id` varchar(255) NOT NULL DEFAULT ''";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
					
		$config->set('database_version', 25);
	
	}
	
	private function dbdv_26() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$config->set('default_theme', 'bootstrap3');
		$config->set('default_theme_sub', 'dark');			
		$config->set('program_version', '4.0-alpha-2');				
		$config->set('database_version', 26);
	}
	
	private function dbdv_27() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');

		$config->add('session_life_time', 3600);		
		
		$config->set('program_version', '4.0');
		$config->set('database_version', 27);		
	}
	
}

?>