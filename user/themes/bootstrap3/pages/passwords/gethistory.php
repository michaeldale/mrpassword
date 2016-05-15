<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$id = (int) $url->get_item();

if ($id != 0) {
	$passwords_array = $passwords->get(array('id' => $id, 'get_other_data' => true, 'user_id' => $auth->get('id'), 'old' => 1, 'global_or_null' => 0));

	if (count($passwords_array) == 1) {
		$password = $passwords_array[0];
		echo safe_output($encryption->decrypt($password['password']));
	}
	else {
		echo $language->get('Password Not Found.');
	}
}
else {
	echo $language->get('Password Not Found.');
}
?>