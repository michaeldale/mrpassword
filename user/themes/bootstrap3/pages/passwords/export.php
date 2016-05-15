<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

set_time_limit(280); 


$id = (int) $url->get_item();

if ($id != 0) {
	$paswd_array['category_id'] = $id;
}

//search
if (isset($_GET['like_search']) && ($_GET['like_search'] != '')) {
	$paswd_array['like_search']		= $_GET['like_search'];
	$like_search_temp				= $_GET['like_search'];
}

//order by
$order_by_temp = '';
if (isset($_GET['order_by']) && ($_GET['order_by'] != '')) {
	$paswd_array['order_by'] 		= $_GET['order_by'];
	$order_by_temp 					= $_GET['order_by'];
}

//order
$order_temp = '';
if (isset($_GET['order']) && ($_GET['order'] != '')) {
	$paswd_array['order'] 			= $_GET['order'];
	$order_temp 					= $_GET['order'];
}

$paswd_array['get_other_data']		= true;
$paswd_array['global_or_null']		= 0;
$paswd_array['old']					= 0;
$paswd_array['user_id'] 			= $auth->get('id');

$items 			= $passwords->get($paswd_array);

if (!empty($items)) {
	$array['filename_prefix']	= 'passwords';
	
	$array['headers']	= array(
		'id',
		'category',
		'name',
		'username',
		'password',
		'url',	
		'description',
		'added',
		'updated'
	);
	foreach($items as $item) {
		$array['rows'][]	= array(
			$item['id'],
			$item['category_name'],
			$item['name'],
			$item['username'],
			$encryption->decrypt($item['password']),
			$item['url'],
			$item['description'],
			$item['date_added'],
			$item['last_modified']
		);
	}
	
	$export = new export();
	$export->direct_download($array);
}
else {
	$error->create(array('type' => 'file_not_found', 'message' => 'Nothing to export.'));
}
?>