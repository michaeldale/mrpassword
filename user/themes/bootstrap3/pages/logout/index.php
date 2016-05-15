<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$auth->logout();

header('Location: ' . $config->get('address') . '/');
?>