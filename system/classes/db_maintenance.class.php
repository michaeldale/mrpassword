<?php
/**
 * 	Database Maintenance Class
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */
namespace mrpassword;

class db_maintenance {

	function __construct() {		
	
	}

	public function optimise_tables() {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$log 	=	&singleton::get(__NAMESPACE__ . '\log');
			
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Optimising Tables';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'optimise_tables';
		$log_array['event_source'] = 'db_maintenance';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);

		$optimise_tables = '';
		
		foreach ($tables->get() as $value => $index) {
			$optimise_tables .= $index . ',';
		}
		
		$optimise_tables = substr($optimise_tables, 0, strlen($optimise_tables) - 1);
		
		$query = 'OPTIMIZE TABLE ' . $optimise_tables;		
		
		foreach ($db->query($query, database::FETCH_ASSOC) as $row) {
			if ($row['Msg_type'] == 'error') {
				$number	= E_USER_WARNING;
				$type 	= 'warning';
			}
			else {
				$number	= E_USER_NOTICE;
				$type 	= 'notice';
			}
						
			$log_array['event_severity'] 		= $type;
			$log_array['event_number'] 			= $number;
			$log_array['event_description'] 	= 'Table "' . $row['Table']  . '"<br />Message "' . $row['Msg_text'] . '"';
			$log_array['event_file'] 			= __FILE__;
			$log_array['event_file_line'] 		= __LINE__;
			$log_array['event_type'] 			= 'optimise_tables';
			$log_array['event_source'] 			= 'db_maintenance';
			$log_array['event_version'] 		= '1';
			$log_array['log_backtrace'] 		= false;	
					
			$log->add($log_array);			
		}

		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Optimising Tables Complete';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'optimise_tables';
		$log_array['event_source'] = 'db_maintenance';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
		
	}

}

?>