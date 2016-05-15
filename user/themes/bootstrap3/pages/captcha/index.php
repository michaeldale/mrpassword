<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

if (isset($_SESSION['captcha_text']) && !empty($_SESSION['captcha_text'])) {
	$captcha->set_text($_SESSION['captcha_text']);
}

$captcha->display();
?>
