<?php
/**
 * 	Auto Update Class V1.1
 *	Copyright Dalegroup Pty Ltd 2016
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace mrpassword;

class update {
	
	function __construct() {
	
	}
	
	public function install() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
	
		$result = $this->get();
		
		$write_results = array();
		
		//if (is_array($results)) {

		//	foreach($results as $result) {
		
				if (isset($result['folders']) && !empty($result['folders'])) {
					foreach($result['folders'] as $folder_name) {
						 if (is_dir(ROOT . $folder_name)) {
							//no need to create
						 }
						 else {
							if (@mkdir(ROOT . $folder_name, 0777, true)) {
								$write_results['folders'][] = array('name' => ROOT . $folder_name, 'success' => true);
							} else {
								$write_results['folders'][] = array('name' => ROOT . $folder_name, 'success' => false);
							} 
						}
					}
				}
				
				
				if (isset($result['files']) && !empty($result['files'])) {
					foreach($result['files'] as $file_name => $file_contents) {
						if (@file_put_contents(ROOT . $file_name, base64_decode($file_contents)) === FALSE) {
							$write_results['files'][] = array('name' => ROOT . $file_name, 'success' => false);
						}
						else {
							$write_results['files'][] = array('name' => ROOT . $file_name, 'success' => true);				
						}
					}
				}
			//}
		
		//}
		
		$config->set('auto_update_cache', array());

		return array('message' => 'Update Installed. Please ensure all files were successfully copied.', 'write_results' => $write_results);
	}
	
	public function get() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	

		//built in auto detect serialized data fails with such a large string, do it manually
		$return = unserialize($config->get('auto_update_cache', false));
		
		return $return;
	}
	
	public function download($get_array = NULL) {
	
		if (!ini_get('safe_mode')) {
			//ooh we can process for sooo long
			set_time_limit(280); 
		}
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$upgrade 	= new upgrade();
		$apptrack 	= new apptrack();

		$config->set('auto_update_cache', array());
		
		$version_info	= $upgrade->version_info();
		
		$application_id = $config->get('application_id');
		
		$array =
			array(
				'application_id'				=> $application_id,
				'api_action'					=> 'auto_updater',
				'installed_database_version'	=> $version_info['installed_database_version'],
				'installed_program_version'		=> $version_info['installed_program_version'],
				'license_key'					=> $config->get('license_key'),
				'files'							=> $this->hash_files(),
				'directory_separator'			=> DIRECTORY_SEPARATOR,
				'php_version'					=> PHP_VERSION
			);
		
		if (isset($get_array) && $get_array['force_download'] == true) {
			$array['code_database_version']			= 1;
			$array['code_program_version']			= '1.0';		
		}
		else {
			$array['code_database_version']			= $version_info['code_database_version'];
			$array['code_program_version']			= $version_info['code_program_version'];
		}
		
		$result = $apptrack->send($array);

		if (!empty($result)) {
			$config->set('auto_update_cache', $result);
		}
	}
	
	function hash_files() {
		
		$temp_array = $this->glob_recursive(ROOT . DIRECTORY_SEPARATOR);
		
		$file_array = array();
		
		foreach($temp_array as $index => $value) {
			if (is_readable($value)) {
				$file_explode = explode(ROOT, $value);
				$file_array[$file_explode[1]] = hash_file('sha1', $value);
			}
		}
		
		return $file_array;
	}
	
	function glob_recursive($dir) {
			
		$files 		= array();
		$file_tmp	= glob($dir . '*', GLOB_MARK | GLOB_NOSORT);

		if (is_array($file_tmp)) {
			foreach($file_tmp as $item){
				if(substr($item, -1) != DIRECTORY_SEPARATOR) {
					$files[] = $item;
				}
				else {
					$files = array_merge($files, $this->glob_recursive($item));
				}
			}

			return $files;
		}
		else {
			return array();
		}
	}




}

?>