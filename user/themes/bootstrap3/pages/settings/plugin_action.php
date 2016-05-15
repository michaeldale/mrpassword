<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title('Plugins');

if ($auth->get('user_level') != 2) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if (isset($_GET['action']) && isset($_GET['name']) && !empty($_GET['name'])) {
	if ($_GET['action'] == 'enable') {
		$plugins->enable($_GET['name']);
	}
	if ($_GET['action'] == 'disable') {
		$plugins->disable($_GET['name']);
	}	
}

$enabled = '';
if (isset($_GET['enabled'])) {
	$enabled = $_GET['enabled'];
}

header('Location: ' . $config->get('address') . '/settings/plugins/?enabled=' . safe_output($enabled));
exit;
?>