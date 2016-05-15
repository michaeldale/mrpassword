<?php
/*
	Required.
	You must include these two lines at the start of your plugin.
*/
namespace mrpassword\themes;
use mrpassword;

class bootstrap3 {

	function __construct() {

	}
	
	/*
		This method is used to get the theme details.
		It is required.
	*/
	public function meta_data() {
		$info = array(
			'name' 						=> 'Bootstrap3',
			'version' 					=> '1.0',
			'description'				=> '',
			'website'					=> 'http://codecanyon.net/item/tickets/2478843?ref=michaeldale',
			'author'					=> 'Michael Dale',
			'author_website'			=> 'http://michaeldale.com.au/',
			'min_supported_version' 	=> '',
			'max_supported_version' 	=> '',
			'type'						=> 'bootstrap3',
			'sub_themes'				=> array('default')

		);

		return $info;
	}	
	
}

?>