<?php
/**
 * 	Mr. Password
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *
 * @package     mrpassword
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

$dont_redirect_to = array(
	$config->get('script_path') . '/login/', 
	$config->get('script_path') . '/captcha/'
);

//store requested page in session, used to redirect user to correct page after login
if (!in_array($_SERVER['REQUEST_URI'], $dont_redirect_to)) {
	$_SESSION['page'] = $_SERVER['REQUEST_URI'];
}

//check if user is browsing to the login or registration page
if ($url->get_action() == 'api' || $url->get_action() == 'login' || $url->get_action() == 'register' || $url->get_action() == 'forgot' || $url->get_action() == 'cron' || $url->get_action() == 'reset') {
	//if already logged in redirect to the dashboard
	if ($auth->logged_in()) {
		header('Location: ' . $config->get('address') . '/');
	}
	else {
		include(THEMES . '/'.CURRENT_THEME.'/pages/'.$url->get_action().'/index.php');
	}
}
//public plugin
else if  ($url->get_action() == 'public' || $url->get_action() == 'simple' || $url->get_action() == 'captcha') {
	try {
		if (!file_exists(THEMES . '/' . CURRENT_THEME . '/pages/' . $url->get_action() . '/index.php')) {
			throw new \Exception('The theme action file "' . THEMES . '/' . CURRENT_THEME . '/pages/' . $url->get_action() . '/index.php' . '" could not be found.');
		}
		else {
			include(THEMES . '/' . CURRENT_THEME . '/pages/' . $url->get_action() . '/index.php');
		}
	}
	catch (\Exception $e) {
		//send error if unable to find a theme file for the URL
		$error->create(array('type' => '404', 'message' => $e->getMessage()));
	}
}
//all other pages require authentication
else if ($auth->logged_in()) {
	try {
		//plugins
		if ($url->get_action() == 'p') {
			if (!file_exists(THEMES . '/' . CURRENT_THEME . '/pages/' . $url->get_action() . '/index.php')) {
				throw new \Exception('The theme action file "' . THEMES . '/' . CURRENT_THEME . '/pages/' . $url->get_action() . '/index.php' . '" could not be found.');
			}
			else {
				include(THEMES . '/' . CURRENT_THEME . '/pages/' . $url->get_action() . '/index.php');
			}		
		}
		//this is a first level url i.e /users/
		else if ($url->get_module() == '' || $url->get_module() == 'category') {
			if (!file_exists(THEMES . '/' . CURRENT_THEME . '/pages/' . $url->get_action() . '/index.php')) {
				throw new \Exception('The theme action file "' . THEMES . '/' . CURRENT_THEME . '/pages/' . $url->get_action() . '/index.php' . '" could not be found.');
			}
			else {
				include(THEMES . '/' . CURRENT_THEME . '/pages/' . $url->get_action() . '/index.php');
			}
		}
		//this is a second level url i.e /users/view/
		else {
			if (!file_exists(THEMES . '/' . CURRENT_THEME . '/pages/' . $url->get_action() . '/' . $url->get_module(). '.php')) {
				throw new \Exception('The theme action file "' . THEMES . '/' . CURRENT_THEME . '/pages/' . $url->get_action() . '/' . $url->get_module() . '.php' . '" could not be found.');
			}
			else {
				include(THEMES . '/' . CURRENT_THEME . '/pages/' . $url->get_action() . '/' . $url->get_module() . '.php');
			}
		}
	}
	catch (\Exception $e) {
		//send error if unable to find a theme file for the URL
		$error->create(array('type' => '404', 'message' => $e->getMessage()));
	}
}
else {
	//if all else fails go to the login page
	header('Location: ' . $config->get('address') . '/login/');
}
exit;
?>