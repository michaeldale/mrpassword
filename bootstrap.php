<?php
/**
 * 	MrP		- Bootstrap for application use
 *	Copyright Dalegroup Pty Ltd 2013
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace mrpassword;

//it would be nice to have this above the namespace as currently it isn't too useful.
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
	die('This program requires PHP 5.3.0 or higher to run.');
}

//get the directory root info
define(__NAMESPACE__ . '\ROOT', __DIR__);
define(__NAMESPACE__ . '\SYSTEM', ROOT . '/system');

/**
 * Loader does all the important startup stuff.
 */	
include(SYSTEM . '/loader.php');


?>