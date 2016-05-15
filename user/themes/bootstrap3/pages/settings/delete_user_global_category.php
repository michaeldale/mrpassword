<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title('Delete Share');

if ($auth->get('user_level') != 2) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$id = (int) $url->get_item();

if ($id == 0) {
	header('Location: ' . $config->get('address') . '/settings/passwords/');
	exit;
}

$share_array = $shares->get(array('user_id' => $auth->get('id'), 'id' => $id));

if (count($share_array) == 1) {
	$share = $share_array[0];
}
else {
	header('Location: ' . $config->get('address') . '/settings/passwords/');
	exit;
}

$category_id = (int) $share['category_id'];

$shares->delete(array('id' => $id, 'user_id' => $auth->get('id')));
header('Location: ' . $config->get('address') . '/settings/view_global_category/' . $category_id . '/');
exit;
?>