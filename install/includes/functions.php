<?php

function ipm_htmlentities($string) {
	return htmlentities($string, ENT_QUOTES, 'utf-8');
}
function ip_address() {
	return $_SERVER['REMOTE_ADDR'];
}
function ipm_rand_str($length = 32, $chars = '~!@#$%^&*()-_+=[]<,>.ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890') {
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
function ipm_remove_end_slash($script_path) {
		
	if(substr($script_path, -1) == '/') {	
		$script_path = substr($script_path, 0, strlen($script_path) - 1);
		$script_path = ipm_remove_end_slash($script_path);
	}
	return $script_path;
}

/**
 * Returns the timezones supported in PHP
 *
 * @return  array		The timezones in an array
 */
function get_timezones() {
	
	$tzlist = DateTimeZone::listIdentifiers();
	
	return $tzlist;
}

function datetime_utc($add_seconds = 0) {
	$base_time = time() + (int) $add_seconds;
	return gmdate('Y-m-d H:i:s', $base_time);
}
?>