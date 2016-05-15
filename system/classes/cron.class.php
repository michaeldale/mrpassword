<?php
/**
 * 	Cron Class
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */
 
namespace mrpassword;

class cron {
	
	function __construct() {
	}
	
	public function run() {

		if (!ini_get('safe_mode')) {
			//ooh we can process for sooo long
			set_time_limit(280); 
		}

		//get the required classes		
		$config 			= &singleton::get(__NAMESPACE__ . '\config');
		$plugins 			= &singleton::get(__NAMESPACE__ . '\plugins');
		
		//don't want to stop processing when the http client disconnects
		ignore_user_abort(TRUE);

		//stop from running over and over again
		define('RUNNING_CRON', TRUE);

		//get the cron intervals
		$cron_intervals = $config->get('cron_intervals');

		if (!is_array($cron_intervals)) exit;
		
		//update intervals first so that slow processing tasks don't hold up the update of the intervals.
		$update_intervals = $cron_intervals;
		
		$datetime = datetime();
		
		foreach ($update_intervals as &$update_interval) {
			if ($update_interval['next_run'] < $datetime) {
				$update_interval['next_run'] = datetime($update_interval['frequency']);
			}
		}

		//update cron intervals
		$config->set('cron_intervals', $update_intervals);
		
		//now to the processing
		foreach ($cron_intervals as $cron_interval) {
			if ($cron_interval['next_run'] < $datetime) {
				$plugins->run('cron_' . $cron_interval['name']);
			}
		}
		
	}
	
	public function update_check() {
		
		$config 			= &singleton::get(__NAMESPACE__ . '\config');
		$apptrack 			= &singleton::get(__NAMESPACE__ . '\apptrack');
	
		$send_data['application_id'] 	= 2;
		$send_data['version'] 			= $config->get('program_version');

		$data = $apptrack->send($send_data);
		
		if (!empty($data)) {
			$config->set('last_update_response', $data);
			return true;
		}
		else {
			$log = &singleton::get(__NAMESPACE__ . '\log');
								
			$log_array['event_severity'] = 'warning';
			$log_array['event_number'] = E_USER_WARNING;
			$log_array['event_description'] = 'Unable to contact update server.';
			$log_array['event_file'] = __FILE__;
			$log_array['event_file_line'] = __LINE__;
			$log_array['event_type'] = 'update_check';
			$log_array['event_source'] = 'cron';
			$log_array['event_version'] = '1';
			$log_array['log_backtrace'] = false;	
					
			$log->add($log_array);
			
			return false;
		}
	
	}
	
}

?>