<?php
/**
 * 	Tables Class
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */
 
namespace mrpassword;

class tables {

	var $table_prefix;
	
	//original tables
	var $sessions;
	var $users;
	var $config;
	var $events;
	var $passwords;
	var $queue;
	var $storage;
	var $categories;
	var $shares;
	var $custom_fields;	
	var $password_field_group;
	var $password_fields;
	var $password_field_values;
	
	var $tables = array();

	function __construct($table_prefix) {

		$this->table_prefix = strtolower($table_prefix);
		
		$this->sessions 				= $this->table_prefix . 'sessions';
		$this->users 					= $this->table_prefix . 'users';
		$this->config 					= $this->table_prefix . 'config';
		$this->events 					= $this->table_prefix . 'events';
		$this->passwords 				= $this->table_prefix . 'passwords';
		$this->queue 					= $this->table_prefix . 'queue';
		$this->storage 					= $this->table_prefix . 'storage';
		$this->categories 				= $this->table_prefix . 'categories';
		$this->shares 					= $this->table_prefix . 'shares';
		$this->custom_fields 			= $this->table_prefix . 'custom_fields';
		
		$this->password_field_group 	= $this->table_prefix . 'password_field_group';
		$this->password_fields 			= $this->table_prefix . 'password_fields';
		$this->password_field_values 	= $this->table_prefix . 'password_field_values';

		$this->files_to_passwords 		= $this->table_prefix . 'files_to_passwords';
		

		$this->tables = array(
			'sessions' 					=> $this->table_prefix . 'sessions',
			'users'						=> $this->table_prefix . 'users',
			'config'					=> $this->table_prefix . 'config',
			'events'					=> $this->table_prefix . 'events',
			'passwords'					=> $this->table_prefix . 'passwords',
			'queue'						=> $this->table_prefix . 'queue',
			'storage'					=> $this->table_prefix . 'storage',
			'categories'				=> $this->table_prefix . 'categories',
			'shares'					=> $this->table_prefix . 'shares',
			'custom_fields'				=> $this->table_prefix . 'custom_fields',		
			'password_field_group'		=> $this->table_prefix . 'password_field_group',
			'password_fields'			=> $this->table_prefix . 'password_fields',
			'password_field_values'		=> $this->table_prefix . 'password_field_values',
			'files_to_passwords'		=> $this->table_prefix . 'files_to_passwords'
		);
	}
	
	function add_table($table_name) {

		//notice the extra $ ($this->_$_table_name) 
		$this->$table_name = $this->table_prefix . $table_name;
		$this->tables += array($table_name => $this->table_prefix . $table_name);
		
	}
	
	public function get() {
		return $this->tables;
	}

}

?>