<?php
/**
 * 	Database Class
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */


namespace mrpassword;
use PDO, PDOException;

class database extends PDO {

	function __construct($bt_db_host, $bt_db_name, $bt_db_user, $bt_db_pass, $bt_db_type = 'mysql', $bt_db_charset = 'UTF8', $bt_db_port = 3306) {
		
		$error 	=	&singleton::get(__NAMESPACE__ . '\error');
		
		switch ($bt_db_type) {
			case 'mysql':
				
				if (array_search('mysql', parent::getAvailableDrivers()) === FALSE) {
					$error->create(array('type' => 'sql_type_unsupported', 'message' => 'Unable to find the PDO MySQL database driver, which is required with the current database settings.'));
				}
						
				try {
					$connection = parent::__construct('mysql:host=' . $bt_db_host . ';port='.$bt_db_port.';dbname=' . $bt_db_name, $bt_db_user, $bt_db_pass,
						array(PDO::ATTR_PERSISTENT => false));
				}
				catch (\PDOException $e) {
					$error->create(array('type' => 'sql_connect_error', 'message' => $e->getMessage()));
				}
				//set charset
				parent::exec('SET CHARACTER SET ' . $bt_db_charset);				
				parent::exec('SET NAMES ' . $bt_db_charset);
				
				return $connection;

			break;
			
			default:
				$error->create(array('type' => 'sql_type_unsupported', 'message' => 'Database "' . $bt_db_type . '" is unsupported.'));
		}
	}
	
	private $bt_num_query = 0;
	
	function query($query) {
		//echo $query . '<br />';
		$this->bt_num_query++;
		return parent::query($query);
	}
	
	function prepare($query, $options = NULL) {
		//echo $query . '<br />';
		$this->bt_num_query++;
		if (isset($options)) {
			return parent::prepare($query, $options);
		}
		else {
			return parent::prepare($query);
		}
	}
	
	function num_queries() {
		return $this->bt_num_query;
	}
	
}
?>