<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

if ($config->get('storage_enabled')) {

	$files = array();
	
	if (isset($_GET['password_id'])) {
	
		$id 			= (int) $url->get_item();
		$password_id	= (int) $_GET['password_id'];
		
		$passwords_array = $passwords->get(array('id' => $password_id, 'user_id' => $auth->get('id'), 'old' => 0, 'get_other_data' => true, 'global_or_null' => 0));

		if (count($passwords_array) == 1) {
			$files = $passwords->get_files(array('id' => $password_id, 'file_id' => $id));
		}
		else {
			$error->create(array('type' => 'storage_file_not_found', 'message' => 'File Not Found'));
		}
	}
	else {
		$plugins->run('download_other_files', $files);
	}
	
	if (count($files) == 1) {
		$file = file_get_contents($config->get('storage_path') . $files[0]['uuid'] . '.' . $files[0]['extension']);
		
		switch ($files[0]['extension']) { 
			case 'pdf': 	$ctype="application/pdf"; break; 
			case 'exe': 	$ctype="application/octet-stream"; break; 
			case 'zip': 	$ctype="application/zip"; break; 
			case 'doc': 	$ctype="application/msword"; break; 
			case 'xls': 	$ctype="application/vnd.ms-excel"; break; 
			case 'ppt': 	$ctype="application/vnd.ms-powerpoint"; break; 
			case 'gif': 	$ctype="image/gif"; break; 
			case 'png': 	$ctype="image/png"; break; 
			case 'htm':		
			case 'html':	$ctype="text/html"; break;
			case 'jpeg': 
			case 'jpg': 	$ctype="image/jpg"; break; 
			default: 		$ctype="application/force-download"; 
		} 
		
		header("Pragma: public"); // required 
		header("Expires: 0"); 
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
		header("Cache-Control: private",false); // required for certain browsers 
		header("Content-Type: $ctype"); 
		header("Content-Disposition: attachment; filename=\"".html_output($files[0]['name'])."\";" ); 
		header("Content-Transfer-Encoding: binary"); 
		
		echo $file;
	}
	else {
		$error->create(array('type' => 'storage_file_not_found', 'message' => 'File Not Found'));
	}
}
else {
	$error->create(array('type' => 'storage_disabled', 'message' => 'File Storage Is Disabled'));
}

?>