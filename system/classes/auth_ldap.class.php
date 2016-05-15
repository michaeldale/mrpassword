<?php
/**
 * 	LDAP Authentication Class
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace mrpassword;

class auth_ldap {
	
	private $config = array();
	private $user	= array();

	function __construct() {	

		$config 							= &singleton::get(__NAMESPACE__ . '\config');
	
		//ldap stuff
		$this->config['hostname']			= $config->get('ldap_server');
		//$this->config['account_suffix']		= $config->get('ldap_account_suffix');
		$this->config['base_dn']			= $config->get('ldap_base_dn');
		$this->config['port']				= 389;		
		
	
	}

	public function authenticate($username, $password) {

		$return = false;
	
		// connect to ldap server
		$ldap = @ldap_connect($this->config['hostname'], $this->config['port']);

		@ldap_set_option($ldap, LDAP_OPT_NETWORK_TIMEOUT, 5);
		@ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
						
		if($bind = @ldap_bind($ldap, 'uid=' . $username . ',' . $this->config['base_dn'], $password)) {
							
			$filter 	= 'uid=' . $username; 
			$results 	= ldap_search($ldap, $this->config['base_dn'], $filter, array('dn', 'cn', 'username', 'mail'));
			$info 		= ldap_get_entries($ldap, $results);
			$count 		= $info['count'];

			ldap_free_result($results);
			
			if ($count == 1) {
				$this->user['name'] 	= '';
				$this->user['email'] 	= '';
				if (isset($info[0]['cn'][0])) {
					$this->user['name'] = $info[0]['cn'][0];
				}
				if (isset($info[0]['mail'][0])) {
					$this->user['email'] = $info[0]['mail'][0];
				}
				$return = true;
			}

			ldap_unbind($ldap);	

		} else {
			// invalid name or password
			$return = false;
		}
				
		return $return;
	}
	
	public function get_user() {
		return $this->user;
	}

}

?>