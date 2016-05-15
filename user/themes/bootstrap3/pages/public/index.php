<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

if ($url->get_module() == '') {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$plugins->run('public_page_header_' . $url->get_module());

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>

<?php $plugins->run('public_page_body_' . $url->get_module()); ?>

<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>