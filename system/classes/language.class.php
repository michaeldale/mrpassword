<?php
//declare(encoding='UTF-8');
namespace mrpassword;

/**
 * 	Language Class
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */

class language {

	private $language_array = array();
	private $system_folder 	= NULL;
	private $user_folder 	= NULL;

	function __construct() {
		$this->system_folder 	= SYSTEM . '/languages/';
		$this->user_folder 		= USER . '/languages/';

		$this->load();
	}
	
	private function load() {
		$config 	=	&singleton::get(__NAMESPACE__ . '\config');
		$error 		=	&singleton::get(__NAMESPACE__ . '\error');


		$language_temp = $config->get('default_language');
		
		$language = $this->sanitize_language_name($language_temp);
		
		//try user defined
		if (file_exists($this->user_folder . $language . '.lang.php')) {
			include($this->user_folder . $language . '.lang.php');
		}
		//try system defined
		else if (file_exists($this->system_folder . $language . '.lang.php')) {
			include($this->system_folder . $language . '.lang.php');
		}
		//fall back to default
		else {
			include($this->system_folder . 'english_aus.lang.php');
		}
		
		$lang = new lang();
		
		$this->language_array = $lang->get();
		
	}
	
	function sanitize_language_name($language) {
		$language = strtolower($language);
		$language = preg_replace('([^0-9a-z_\/])', '', $language);
		return $language;
	}	
	
	public function get($name) {
		$error 	=	&singleton::get(__NAMESPACE__ . '\error');
		
		/*
			//used to generate word list file
			$language_words 	=	&singleton::get(__NAMESPACE__ . '\language_words');
			$config 			=	&singleton::get(__NAMESPACE__ . '\config');
			
			if ($language_words->count(array('where' => array('name' => $name))) < 1) {
				$language_words->add(array('columns' => array('name' => $name, 'date_added' => datetime(), 'version' => $config->get('program_version'))));
			}
		*/

		if (isset($this->language_array[$name])) {
			//return '試験';
			//return '試験' . $this->language_array[$name];
			return $this->language_array[$name];
		}
		else {
			//return '試験'; 
			//$error->create(array('type' => 'language_item_missing', 'message' => 'Unable to find language item "'.$name.'".'));
			return $name;
		}
	}
	
	public function get_system_languages() {
		$lang_info = array();
		
		if (is_dir($this->system_folder)) {
			$folder = opendir($this->system_folder);
			while (false !== ($file_array = readdir($folder))) {
				if ($file_array != '.' && $file_array != '..') {
					if(filetype($this->system_folder . $file_array) == 'file') {
						if(preg_match("/^([a-z0-9_\/]+).lang.php$/", $file_array, $matches) > 0){
						
							$nice_array = explode('_', $matches[1]);
							
							$nice_name = ucwords($nice_array[0]);
							if (isset($nice_array[1]) && !empty($nice_array[1])) {
								$nice_name .= ' (' . strtoupper($nice_array[1]) . ')';
							}

							$lang_info[] =
								array (
									'file_name' => $this->system_folder . $matches[1],
									'name' => $matches[1],
									'nice_name' => $nice_name
								);
						}
					}
				}
			}
			closedir($folder);
		}
		
		return $lang_info;
		
	}
	
	public function get_user_languages() {
		$lang_info = array();
		
		if (is_dir($this->user_folder)) {
			$folder = opendir($this->user_folder);
			while (false !== ($file_array = readdir($folder))) {
				if ($file_array != '.' && $file_array != '..') {
					if(filetype($this->user_folder . $file_array) == 'file') {
						if(preg_match("/^([a-z0-9_\/]+).lang.php$/", $file_array, $matches) > 0){
						
							$nice_array = explode('_', $matches[1]);
							
							$nice_name = ucwords($nice_array[0]);
							if (isset($nice_array[1]) && !empty($nice_array[1])) {
								$nice_name .= ' (' . strtoupper($nice_array[1]) . ')';
							}

							$lang_info[] =
								array (
									'file_name' => $this->user_folder . $matches[1],
									'name' => $matches[1],
									'nice_name' => $nice_name
								);
						}
					}
				}
			}
			closedir($folder);
		}
		
		return $lang_info;	
	}

}

?>