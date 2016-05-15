<?php
/**
 * 	Log Class
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */
namespace mrpassword;

class log {
	
	private $event_table = '';
	
	function __construct() {

	}

	//add an event to the event log
	function add($event_array) {
		global $db;
		
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$site_id		= SITE_ID;
		
		//print_r($event_array);
		
		if (defined('DEBUG') && (DEBUG == TRUE)) {
			echo '<br />' . safe_output($event_array['event_number']) . ': ' . safe_output($event_array['event_description']) .  ' in <b>' . safe_output($event_array['event_file']) . '</b> on line <b>' . safe_output($event_array['event_file_line']) . '</b>';
		}
	
		$query = "
			INSERT INTO `$tables->events` 
			(user_id, event_date, event_date_utc, event_file, event_file_line, 
			event_type, event_number, event_source, event_severity, event_ip_address, event_description, site_id";
		
		if (isset($event_array['event_trace'])) {
			$query .= ', event_trace';
		}
		else if (isset($event_array['log_backtrace']) && ($event_array['log_backtrace'] == true)) {
			/*
			Get the backtrace here
			*/
			ob_start();
			debug_print_backtrace();
			$event_trace = ob_get_contents();
			ob_end_clean();
			$query .= ', event_trace';
		}
		if (isset($event_array['server_id'])) {
			$query .= ', server_id';
		}
		if (isset($event_array['event_id'])) {
			$query .= ', remote_id';
		}
		
		$query .= ") VALUES (:user_id, :event_date, :event_date_utc, :event_file, :event_file_line, :event_type, :event_number, :event_source, :event_severity, :event_ip_address, :event_description, :site_id";
		
		if ((isset($event_array['log_backtrace']) && ($event_array['log_backtrace'] == true)) || isset($event_array['event_trace'])) {
			$query .= ', :event_trace';
		}
		if (isset($event_array['server_id'])) {
			$query .= ', :server_id';
		}
		if (isset($event_array['event_id'])) {
			$query .= ', :remote_id';
		}
		
		$query .= ");";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		if (isset($event_array['user_id'])) {
			$stmt->bindParam(':user_id', $event_array['user_id']);
		}
		else {
			$auth = &singleton::get(__NAMESPACE__ . '\auth');
			$user_id = (int) $auth->get('id');
			$stmt->bindParam(':user_id', $user_id);
		}
		
		if (isset($event_array['event_date'])) {
			$stmt->bindParam(':event_date', $event_array['event_date']);		
		}
		else {
			$date 		= datetime();
			$stmt->bindParam(':event_date', $date);
		}
		
		if (isset($event_array['event_date_utc'])) {
			$stmt->bindParam(':event_date_utc', $event_array['event_date_utc']);
		}
		else {
			$date_utc	= datetime_utc();
			$stmt->bindParam(':event_date_utc', $date_utc);
		}
		$stmt->bindParam(':event_file', $event_array['event_file']);
		$stmt->bindParam(':event_file_line', $event_array['event_file_line']);
		$stmt->bindParam(':event_type', $event_array['event_type']);
		$stmt->bindParam(':event_number', $event_array['event_number']);
		$stmt->bindParam(':event_source', $event_array['event_source']);
		$stmt->bindParam(':event_severity', $event_array['event_severity']);
		$stmt->bindParam(':site_id', $site_id);

		if (isset($event_array['event_ip_address'])) {
			$stmt->bindParam(':event_ip_address', $event_array['event_ip_address']);
		}
		else {
			$ip_address	= ip_address();
			$stmt->bindParam(':event_ip_address', $ip_address);
		}
		
		$stmt->bindParam(':event_description', $event_array['event_description']);
		
		if (isset($event_array['event_trace'])) {
			$stmt->bindParam(':event_trace', $event_array['event_trace']);
		}
		else if (isset($event_array['log_backtrace']) && ($event_array['log_backtrace'] == true)) {
			$stmt->bindParam(':event_trace', $event_trace);
		}
		if (isset($event_array['server_id'])) {
			$stmt->bindParam(':server_id', $event_array['server_id']);
		}
		if (isset($event_array['event_id'])) {
			$stmt->bindParam(':remote_id', $event_array['event_id']);
		}
	
		try {
			$stmt->execute();
			$event_id = $db->lastInsertId();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		return $event_id;	
	
	}
	
	//clear events
	function clear($server_id = NULL) {
		global $db;
		
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$site_id		= SITE_ID;

		
		$query = "DELETE FROM `$table->events`";
		
		if (isset($server_id) && !empty($server_id)) {
			$query .= " WHERE server_id = :server_id AND site_id = :site_id";
		}
		else {
			$query .= " WHERE server_id IS NULL AND site_id = :site_id";
		}
		
		$stmt = $db->prepare($query);
	
		if (isset($server_id) && !empty($server_id)) {
			$stmt->bindParam(':server_id', $server_id);
		}
		
		$stmt->bindParam(':site_id', $site_id);

		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		return true;
		
	}
	
	//delete a single event
	function delete($event_id) {
		global $db;
		
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$site_id		= SITE_ID;
		
		$query = "DELETE FROM `events` WHERE id = :event_id AND site_id = :site_id";
		
		$stmt = $db->prepare($query);
	
		$stmt->bindParam(':event_id', $event_id);
		$stmt->bindParam(':site_id', $site_id);
		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		return true;
	}
	
	//mark events that were synced
	function set_synced($events) {
		global $db;
		
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$site_id		= SITE_ID;
			
		foreach ($events as $event) {
			$query = "UPDATE events SET event_synced = 1 WHERE id = :event_id AND site_id = :site_id";
			$stmt = $db->prepare($query);
			$stmt->bindParam(':event_id', $event['event_id']);
			$stmt->bindParam(':site_id', $site_id);
			try {
				$stmt->execute();
			}
			catch (\Exception $e) {
				$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
			}
		}
		return true;
	}
	
	function get($event_array) {
		global $db;
		
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$site_id		= SITE_ID;
	
		if (isset($event_array['not_synced']) && ($event_array['not_synced'] == true)) {
			$query = "
			SELECT 
			id, event_number, user_id, event_date, event_date_utc, event_type, event_severity, 
			event_source, event_file, event_file_line, event_ip_address, event_description,
			event_trace
			FROM `$tables->events` WHERE 1 = 1 AND event_synced = 0 AND site_id = :site_id";
		}
		else {
			$query = "SELECT l.*";
			
			if (isset($event_array['get_other_data']) && ($event_array['get_other_data'] == true)) {
				$query .= ", u.name";
			}
			
			$query .= " FROM $tables->events l";

			if (isset($event_array['get_other_data']) && ($event_array['get_other_data'] == true)) {
				$query .= " LEFT JOIN $tables->users u ON u.id = l.user_id";
			}
			
			$query .= " WHERE 1 = 1 AND l.site_id = :site_id";
		}
		
		
		if (isset($event_array['server_id']) && !empty($event_array['server_id'])) {
			$query .= " AND l.server_id = :server_id";
		}
		else {
			$query .= " AND l.server_id IS NULL";
		}
		if (isset($event_array['event_type'])) {
			$query .= " AND l.event_type = :event_type";
		}
		if (isset($event_array['event_source'])) {
			$query .= " AND l.event_source = :event_source";
		}
		if (isset($event_array['event_severity']) && !empty($event_array['event_severity'])) {
			$query .= " AND l.event_severity = :event_severity";
		}
		if (isset($event_array['id'])) {
			$query .= " AND l.id = :id";
		}		
		if (isset($event_array['user_id'])) {
			$query .= " AND l.user_id = :user_id";
		}		
		if (isset($event_array['like_search'])) {
			$query .= " AND (l.id LIKE :like_search OR l.event_type LIKE :like_search OR l.event_source LIKE :like_search OR l.event_severity LIKE :like_search OR l.event_ip_address LIKE :like_search OR l.event_summary LIKE :like_search OR l.event_description LIKE :like_search)";
		}		
		if (isset($event_array['order']) && !empty($event_array['order'])) {
			$query .= " ORDER BY :order DESC";
		}
		else {
			$query .= " ORDER BY l.event_date DESC, id DESC";
		}
		
		if (isset($event_array['limit'])) {
			$query .= " LIMIT :limit";
			if (isset($event_array['offset'])) {
				$query .= " OFFSET :offset";
			}
		}
		//echo $query;

		$stmt = $db->prepare($query);
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		if (isset($event_array['server_id']) && !empty($event_array['server_id'])) {
			$stmt->bindParam(':server_id', $event_array['server_id'], database::PARAM_INT);
		}
		if (isset($event_array['event_severity']) && !empty($event_array['event_severity'])) {
			$stmt->bindParam(':event_severity', $event_array['event_severity']);
		}
		if (isset($event_array['id'])) {
			$stmt->bindParam(':id', $event_array['id']);
		}	
		if (isset($event_array['user_id'])) {
			$stmt->bindParam(':user_id', $event_array['user_id'], database::PARAM_INT);
		}	
		if (isset($event_array['like_search'])) {
			$value = $event_array['like_search'];
			$value = "%{$value}%";
			$stmt->bindParam(':like_search', $value, database::PARAM_STR);
			unset($value);
		}
		if (isset($event_array['event_type'])) {
			$stmt->bindParam(':event_type', $event_array['event_type'], database::PARAM_STR);
		}			
		if (isset($event_array['event_source'])) {
			$stmt->bindParam(':event_source', $event_array['event_source'], database::PARAM_STR);
		}	
		if (isset($event_array['order']) && !empty($event_array['order'])) {
			$stmt->bindParam(':order', $event_array['order']);
		}
		
		if (isset($event_array['limit'])) {
			$limit = (int) $event_array['limit'];
			if ($limit < 0) $limit = 0;
			$stmt->bindParam(':limit', $limit, database::PARAM_INT);
			if (isset($event_array['offset'])) {
				$offset = (int) $event_array['offset'];
				$stmt->bindParam(':offset', $offset, database::PARAM_INT);					
			}
		}
	
		//$stmt->bindParam('', );
		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$events = $stmt->fetchAll(database::FETCH_ASSOC);
	
		if (!empty($events)) {
			return $events;
		}
		else {
			return array();
		}
	}
	
	//this function removes old entries from the event log
	public function prune() {
		global $db;
		
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		$log 			= &singleton::get(__NAMESPACE__ . '\log');

		$site_id		= SITE_ID;
		
		$max_logs = (int) $config->get('log_limit');
		
		if ($max_logs > 0) {
			/*
				Get Total Events
			*/
			
			$query = "SELECT count(*) as `count` FROM $tables->events WHERE site_id = :site_id";
							
			try {
				$stmt = $db->prepare($query);
			}
			catch (\Exception $e) {
				$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
			}
			
			$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

					
			try {
				$stmt->execute();
			}
			catch (\Exception $e) {
				$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
			}
			
			$count_return = $stmt->fetchAll(database::FETCH_ASSOC);
			
			$logs_count = (int) $count_return[0]['count'];
					
					
			if ($logs_count > $max_logs) {
				$events_to_delete = $logs_count - $max_logs;
				
				$events_to_delete = (int) $events_to_delete;
				
				$event_delete_query = "DELETE FROM $tables->events WHERE site_id = :site_id ORDER BY id LIMIT $events_to_delete";
				
				try {
					$stmt = $db->prepare($event_delete_query);
				}
				catch (\Exception $e) {
					$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
				}
				
				$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

				try {
					$stmt->execute();
				}
				catch (\Exception $e) {
					$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
				}
			}
			else {
				$events_to_delete = 0;
			}

			$log_array['event_severity'] = 'notice';
			$log_array['event_number'] = E_USER_NOTICE;
			$log_array['event_description'] = 'Logs auto prune has finished and deleted ' . $events_to_delete . ' events.';
			$log_array['event_file'] = __FILE__;
			$log_array['event_file_line'] = __LINE__;
			$log_array['event_type'] = 'prune';
			$log_array['event_source'] = 'log';	
			$log_array['event_version'] = '1';
			$log_array['log_backtrace'] = false;	
					
			$log->add($log_array);			
		}
	}
}
?>