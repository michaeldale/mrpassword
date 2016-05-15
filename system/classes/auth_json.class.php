<?php
/**
 * 	JSON Authentication Class
 *	Copyright Dalegroup Pty Ltd 2013
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace mrpassword;

class auth_json {
	
	private $config = array();
	private $user	= array();

	function __construct() {	

		$config 						= &singleton::get(__NAMESPACE__ . '\config');
	
		$this->config['api_url']				= $config->get('auth_json_url');
		$this->config['api_site_id']			= (int) $config->get('auth_json_site_id');
		$this->config['api_key']				= $config->get('auth_json_key');
		$this->config['api_version']			= 1;
		
	}

	public function authenticate($username, $password) {
		$return = false;

		$array['username'] 			= $username;
		$array['password']			= $password;
		$array['task']				= 'authenticate';
			
		$result = $this->send($array);
		
		if (!empty($result)) {
			if ($result['success'] == 1) {
				$this->user = $result;
				$return 	= true;
			}
		}
			
		return $return;
	}
	
	public function get_user() {
		return $this->user;
	}
	
	/**
	 * Converts the submitted array into a json array and submits to the apptrack server
	 *
	 * @param   array   	$data 		The array of data to send
	 * @return  array					The response from the apptrack server
	 */	
	public function send($data) {
		$config 						= &singleton::get(__NAMESPACE__ . '\config');

		$data['api_version']			= $this->config['api_version'];
		$data['program_version']		= $config->get('program_version');
		$data['program_name']			= 'dalegroup.mrpassword';
		
		$json_array 			= json_encode($data);
		$encrypt_array			= $this->encrypt($json_array);
		$post_data 				= http_build_query(array('data' => $encrypt_array, 'site_id' => $this->config['api_site_id']));
		
		$options = array(
			'http' => array(
				'user_agent'    => 'Dalegroup MrPassword/' . $config->get('program_version'),
				'timeout'       => 5,
				'method'		=> 'POST',
				'header'		=> 'Content-type: application/x-www-form-urlencoded',
				'content'		=> $post_data
			)
		);

		$context 				= stream_context_create($options);
		$result 				= @file_get_contents($this->config['api_url'], false, $context);
		
		//print_r($result);
		//exit;
		
		if ($result) {
			$decrypt_array = $this->decrypt($result);
			$return_data = json_decode($decrypt_array, true);
		}
		else {
			$return_data = array();
		}
		
		return $return_data;
	
	}
	
	public function encrypt($string) {
		$iv = mcrypt_create_iv(
				mcrypt_get_iv_size(
					MCRYPT_RIJNDAEL_256, 
					MCRYPT_MODE_CBC
				), 
				MCRYPT_RAND
			);
		
		$encrypted_data = 
			mcrypt_encrypt(
				MCRYPT_RIJNDAEL_256, 
				md5($this->config['api_key']), 
				$string, 
				MCRYPT_MODE_CBC, 
				$iv
			);
			
		$encrypted = base64_encode($iv . $encrypted_data);

		return $encrypted;
	}
	
	public function decrypt($string) {
		$string = base64_decode($string);
	
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
		$iv = substr($string, 0, $iv_size);

		//retrieves the cipher text (everything except the $iv_size in the front)
		$string = substr($string, $iv_size);
	
		$decrypted = 
		rtrim(
			@mcrypt_decrypt(
				MCRYPT_RIJNDAEL_256,
				md5($this->config['api_key']), 
				$string,
				MCRYPT_MODE_CBC,
				$iv
			),
			"\0"
		);	
		
		return $decrypted;
	}


}

?>