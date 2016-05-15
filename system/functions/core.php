<?php
/**
 * 	Dalegroup Framework
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *  Core Functions
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace mrpassword;

/**
 * Class Auto Loader. Allows php class files to be included automatically when constructing a class.
 *
 * @param   string   $class_name The name of the class to be included (excluding .class.php)
 */
function class_auto_load($class_name) {
	//echo $class_name . '<br />';
	
	$class_name = \str_replace(__NAMESPACE__, '', $class_name);
	$class_name = \str_replace('\\', '', $class_name);
	if (file_exists((CLASSES . '/' . $class_name . '.class.php'))) {
		require(CLASSES . '/' . $class_name . '.class.php');
	}
}

/**
 * This function is called at the shutdown of the PHP file.
 * This is currently only used to close the session (and only really needed on buggy versions of PHP)
 *
 */
function shutdown() {
	
	//fixes a bug with certain PHP installs
	session_write_close();
}

/**
 * Returns the UTC date in MySQL datetime format.
 *
 * @param   int   	$add_seconds The number of seconds you wish to add to the returned datetime.
 * @return  string	The UTC datetime value.
 */
function datetime_utc($add_seconds = 0) {
	$base_time = time() + (int) $add_seconds;
	return gmdate('Y-m-d H:i:s', $base_time);
}

/**
 * Returns the date in MySQL datetime format based on the currently set timezone.
 *
 * @param   int   	$add_seconds The number of seconds you wish to add to the returned datetime.
 * @return  string	The datetime value.
 */
function datetime($add_seconds = 0) {
	$base_time = time() + (int) $add_seconds;
	return date('Y-m-d H:i:s', $base_time);
}

/**
 * Returns the current IP address of connection that requested the PHP file.
 *
 * @return  string	The ip address.
 */
function ip_address() {
	if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
		return $_SERVER['REMOTE_ADDR'];
	}
	else {
		return '';
	}
}

/**
 * Returns a random string.
 *
 * @param   int   	$length 		The length of the random string to return.
 * @param   string  $chars 			The characters included in the random string.
 * @return  string					The random string.
 */
function rand_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890') {
    // Length of character list
    $chars_length = (strlen($chars) - 1);

    // Start our string
    $string = $chars{rand(0, $chars_length)};
   
    // Generate random string
    for ($i = 1; $i < $length; $i = strlen($string))
    {
        // Grab a random character from our list
        $r = $chars{rand(0, $chars_length)};
       
        // Make sure the same two characters don't appear next to each other
        if ($r != $string{$i - 1}) $string .=  $r;
    }
   
    // Return the string
    return $string;
}

function paging_start($array) {

	$return_array	= array();

	$page 	= (int) $array['page'];
	$limit	= (int) $array['limit'];
	
	$offset = $page * $limit - $limit;
	
	if ($offset < 0) {
		$offset = 0;
	}
	
	$return_array['offset'] = (int) $offset;
	
	$return_array['next_page'] = $page + 1;
	
	$return_array['previous_page'] = $page - 1;
	
	if ($return_array['previous_page'] < 1) {
		$return_array['previous_page'] = 1;
	}
	if ($return_array['next_page'] < 1) {
		$return_array['next_page'] = 1;
	}
	return $return_array;
}

function paging_finish($array) {
	if ($array['events'] < (int) $array['limit']) {
		$array['next_page'] = $array['next_page'] - 1;
	}
	return $array;
}
function decode($string) {
	$config 	= &singleton::get(__NAMESPACE__ . '\config');
	$error 		= &singleton::get(__NAMESPACE__ . '\error');

	$level		= $config->get('encryption_level');
	$key 		= $config->get('encryption_key');
	
	switch ($level) {
		case 2:
			$error->create(array('type' => 'security_error', 'message' => 'Decode function cannot be used for this database security level.'));
		break;
		default:
			$decrypted 	= rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
		break;
	}

	return $decrypted;
}
function encode($string) {
	$config 	= &singleton::get(__NAMESPACE__ . '\config');
	$error 		= &singleton::get(__NAMESPACE__ . '\error');

	$level		= $config->get('encryption_level');
	$key 		= $config->get('encryption_key');

	switch ($level) {
		case 2:
			$error->create(array('type' => 'security_error', 'message' => 'Encode function cannot be used for this database security level.'));
		break;
		default:
			$encrypted 	= base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, md5(md5($key))));		
		break;
	}

	return $encrypted;
}
/**
 * Checks to see if an email address is valid
 *
 * @param   string  $email 			The email address
 * @return  bool					TRUE if the email is value or FALSE if it is not valid
 */
function check_email_address($email) {
	
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return true;
	}
	else {
		return false;
	}
}

//start the timer, works out page generation time
function start_timer() {
	global $sts_tstart;
	
	$starttime = explode(' ', microtime());
	$sts_tstart = $starttime[1] + $starttime[0];
	
	return true;	
}

//stops the timer and returns the time it took for generation. Level of accuracy can be changed
function stop_timer($accuracy = 4) {
	global $sts_tstart;
	
	$starttime = explode(' ', microtime());
	$tend = $starttime[1] + $starttime[0];
	$totaltime = number_format($tend - $sts_tstart, $accuracy);
	
	return $totaltime;
}
//removes slashes from a value. This will remove slashes from an array too.
function remove_magic_quotes($array) {

	foreach ($array as $key => $value) {
		if (is_array($value)) {
			$array[$key] = remove_magic_quotes($value);
		}
		else {
			$array[$key] = stripslashes($value);
		}
	}

	return $array;
}

// register_globals off
function unregister_globals() {
	if (!ini_get('register_globals')) {
		return true;
	}

	// Might want to change this perhaps to a nicer error
	if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) {
		die('GLOBALS overwrite attempt detected.');
	}

	// Variables that shouldn't be unset
	$noUnset = array('GLOBALS',  '_GET',
		'_POST',    '_COOKIE',
		'_REQUEST', '_SERVER',
		'_ENV',    '_FILES');

	$input = array_merge($_GET,    $_POST,
	$_COOKIE, $_SERVER,
	$_ENV,    $_FILES,
	
	isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());

	foreach ($input as $k => $v) {
		if (!in_array($k, $noUnset) && isset($GLOBALS[$k])) {
			unset($GLOBALS[$k]);
		}
	}

	return true;
}

/**
 * Checks a submitted date matches Y-m-d H:i
 *
 * @param   string $data 		The date to test
 * @return  bool				TRUE or FALSE
 */
function check_datetime($data) {
    if (date('Y-m-d H:i', strtotime($data)) == $data) {
        return true;
    } else {
        return false;
    }
}

/**
 * Formats a date into a human readable style
 *
 * @param   string $data 		The date to format
 * @return  string				The date in a nice format :)
 */
function nice_date($date, $utc = false) {
	if ($utc) {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');
		
		$date 		= new \DateTime($date, new \DateTimeZone('utc'));
		$tz 		= new \DateTimeZone($config->get('default_timezone'));
		
		$date->setTimezone($tz);
		
		return date('D M d, Y', strtotime($date->format('D M d, Y')));		
	}
	else {
		return date('D M d, Y', strtotime($date));
	}
}

/**
 * Formats a date and time into a human readable style
 *
 * @param   string $date 		The datetime to format
 * @param   string $utc 		True or False (true is input date is in UTC)
 * @return  string				The date in a nice format :)
 */
function nice_datetime($date, $utc = false) {
	if ($utc) {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');
		
		$date 		= new \DateTime($date, new \DateTimeZone('utc'));
		$tz 		= new \DateTimeZone($config->get('default_timezone'));
		
		$date->setTimezone($tz);
		
		return date('D M d, Y, h:i A', strtotime($date->format('D M d, Y, h:i A')));		
	}
	else {
		return date('D M d, Y, h:i A', strtotime($date));
	}
}

/**
 * Returns the timezones supported in PHP
 *
 * @return  array		The timezones in an array
 */
function get_timezones() {
	
	$tzlist = \DateTimeZone::listIdentifiers();
	
	return $tzlist;
}

/**
 * Adds an s to the end of a string if count is greater than 1
 *
 * @param   int 	$count 		The count
 * @param   string 	$text 		The text to add s to if needed
 * @return  string				The returned text
 */
function pluralize($count, $text) { 
    return $count . ( ( $count == 1 ) ? ( " $text" ) : ( " ${text}s" ) );
}

/**
 * Time ago that the datetime occurred
 *
 */
function ago($datetime) {

	$language 		= &singleton::get(__NAMESPACE__ . '\language');

    $interval = date_create(datetime())->diff($datetime);		

    if ( $v = $interval->y >= 1 ) return pluralize( $interval->y, $language->get('year') );
    if ( $v = $interval->m >= 1 ) return pluralize( $interval->m, $language->get('month') );
    if ( $v = $interval->d >= 1 ) return pluralize( $interval->d, $language->get('day') );
    if ( $v = $interval->h >= 1 ) return pluralize( $interval->h, $language->get('hour') );
    if ( $v = $interval->i >= 1 ) return pluralize( $interval->i, $language->get('minute') );
	
    return pluralize( $interval->s, $language->get('second') );
}


function rearrange( $arr ){
    foreach( $arr as $key => $all ){
        foreach( $all as $i => $val ){
            $new[$i][$key] = $val;   
        }   
    }
    return $new;
}

function str_replace_array(array $replace, $subject) { 
   return str_replace(array_keys($replace), array_values($replace), $subject);    
} 
?>