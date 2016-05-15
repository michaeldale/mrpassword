<?php
namespace mrpassword;

class error {

	function create($array) {
	
		switch($array['type']) {
		
			case 'file_not_found':
				
			break;
			
			case 'sql_type_unsupported':
				
			break;
			
			case 'sql_connect_error':
				
			break;
			
			case 'sql_prepare_error':
				
			break;
			
			case 'sql_execute_error':
				
			break;
			
			case 'security_error':
			
			break;
		
		}
	
		echo '<p><strong>Mr Password Error</strong><br />Type: ' . safe_output($array['type']) . '<br />Message: ' . safe_output($array['message']) . '</p>';
		
		exit(1);
	}
}

?>