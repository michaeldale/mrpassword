<?php
/*
	Mr Password
	Copyright Dalegroup Pty Ltd
*/

if (!class_exists('PDO')) die('Unable to find the PHP PDO database class. This is required for IPManger to run.');

define('ROOT', '../');

include('includes/functions.php');
include('includes/install.class.php');
include('includes/apptrack.class.php');

//stop php from complaining
//date_default_timezone_set(date_default_timezone_get());
date_default_timezone_set('GMT');

$ipm_install 	= new install();
$apptrack 		= new apptrack();

session_name('mrp_install_sid');
ini_set('session.use_only_cookies', 1);
session_start();

?>