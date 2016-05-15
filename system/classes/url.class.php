<?php
/**
 * 	URL Class
 *	Copyright Dalegroup Pty Ltd 2015
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <support@dalegroup.net>
 */

 
namespace mrpassword;

class url {
	
	private $config = NULL;

	function __construct($array) {
		$this->config['url'] 	= strtolower($array['url']);
		
		$this->config['parts'] 	= explode('/', $this->config['url']);
		
		if (!isset($this->config['parts'][1])) {
			$this->config['parts'][1] = '';
		}
		
		foreach ($this->config['parts'] as &$part) {
			$part = preg_replace('([^0-9a-z_\/])', '', $part);
		}
	}
	
	public function get_action() {
		
		$parts = $this->config['parts'];
		
		if (isset($parts[0]) && ($parts[0] != '')) {
			$this->config['action'] = $parts[0];
		}
		else {
			$this->config['action'] = 'dashboard';
		}
		
		return $this->config['action'];
	}
	
	public function get_module() {
		
		$parts = $this->config['parts'];
		
		if (isset($parts[1]) && ($parts[1] != '')) {
			$this->config['module'] = $parts[1];
		}
		else {
			$this->config['module'] = '';
		}
		
		return $this->config['module'];	
	}
	
	public function set_item($value) {
		$this->config['item'] = $value;
	}
	
	public function get_item() {
		
		if (!isset($this->config['item'])) {
			$parts = $this->config['parts'];
			
			if (isset($parts[2]) && ($parts[2] != '')) {
				$this->config['item'] = $parts[2];
			}
			else {
				$this->config['item'] = '';
			}
		}
		
		return $this->config['item'];	
	}
	
	public function get_parts() {
		return $this->config['parts'];
	}
	
}
?>