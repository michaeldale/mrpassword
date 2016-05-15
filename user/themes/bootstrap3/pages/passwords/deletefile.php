<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$id = (int) $url->get_item();

if (isset($_POST['password_id']) && !empty($_POST['password_id']) && $id != 0) {

	$passwords_array = $passwords->get(array('id' => (int) $_POST['password_id'], 'user_id' => $auth->get('id'), 'old' => 0, 'get_other_data' => true, 'global_or_null' => 0));

	if (count($passwords_array) == 1) {
	
		$password_files->delete(array('columns' => array('file_id' => $id, 'password_id' => (int) $_POST['password_id'])));
	}

}

?>