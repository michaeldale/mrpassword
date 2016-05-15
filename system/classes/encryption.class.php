<?php
/*
	Encryption Class
	Copyright Dalegroup Pty Ltd 2013
	support@dalegroup.net
*/
namespace mrpassword;

class encryption {

	private $config 	= array();

	function __construct() {
		$this->config['file_key']	= NULL;
		$this->config['file_level']	= 1;
		$this->config['db_key']		= NULL;
		$this->config['db_level']	= 1;
	}
	
	public function set($name, $value) {
		$this->config[$name] = $value;
		return true;
	}
	
	public function get($name) {
		if (isset($this->config[$name])) {
			return $this->config[$name];
		}
		else {
			return false;
		}
	}
	
	public function check() {
		$error 		= &singleton::get(__NAMESPACE__ . '\error');

		try {
			if ($this->config['file_level'] != $this->config['db_level']) {
				throw new \Exception('The encryption level of the database does not match the encryption level of the config file.');
			}
			else if ($this->config['db_level'] > 1 && (empty($this->config['file_key']) || empty($this->config['db_level']))) {
				throw new \Exception('At least one encryption key is empty.');			
			}
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'security_error', 'message' => $e->getMessage()));
		}
	}
	
	public function upgrade_check() {
		$error 		= &singleton::get(__NAMESPACE__ . '\error');

		if (!empty($this->config['file_key'])) {
			return true;
		}
		else {
			return false;
		}
	}
	
	public function encrypt($string) {
	
		switch ($this->config['db_level']) {
			case 2:		
				//new style
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
						md5($this->config['db_key'] . $this->config['file_key']), 
						$string, 
						MCRYPT_MODE_CBC, 
						$iv
					);
					
				$encrypted = base64_encode($iv . $encrypted_data);

			break;
			default:
				//old style
				$encrypted 	= 
					base64_encode(
						mcrypt_encrypt(
							MCRYPT_RIJNDAEL_256, 
							md5($this->config['db_key']), 
							$string, 
							MCRYPT_MODE_CBC, 
							md5(md5($this->config['db_key']))
						)
					);		
			break;
		}

		return $encrypted;
	}
	
	public function decrypt($string) {
		
		switch ($this->config['db_level']) {
			case 2:	
				$string = base64_decode($string);
			
				$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
				$iv = substr($string, 0, $iv_size);
    
				//retrieves the cipher text (everything except the $iv_size in the front)
				$string = substr($string, $iv_size);
			
				$decrypted = 
				rtrim(
					mcrypt_decrypt(
						MCRYPT_RIJNDAEL_256,
						md5($this->config['db_key'] . $this->config['file_key']), 
						$string,
						MCRYPT_MODE_CBC,
						$iv
					),
					"\0"
				);
		
			break;
			default:
				//old style
				$decrypted 	=
					rtrim(
						mcrypt_decrypt(
							MCRYPT_RIJNDAEL_256, 
							md5($this->config['db_key']), 
							base64_decode($string), 
							MCRYPT_MODE_CBC,
							md5(md5($this->config['db_key']))
						), 
						"\0"
					);
			break;
		}

		return $decrypted;
	}
	
}

?>