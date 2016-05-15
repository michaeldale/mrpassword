<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

if ($url->get_module() == '') {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$plugins->run('simple_page_header_' . $url->get_module());

?>

<?php $plugins->run('simple_page_body_' . $url->get_module()); ?>

