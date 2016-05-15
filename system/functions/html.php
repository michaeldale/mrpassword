<?php
/**
 * 	Dalegroup Framework
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *  HTML Functions
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace mrpassword;

/**
 * Returns a string in a safe way to output to HTML
 *
 * @param   string   	$string 	The string to make safe
 * @return  string					The safe string
 */
function safe_output($string) {
	return \htmlentities($string, ENT_QUOTES, 'utf-8');
}

function _s($string) {
	return safe_output($string);
}

/**
 * Returns an HTML string in a safe and styled way to output to HTML
 *
 * @param   string   	$string 	The string to make safe
 * @return  string					The safe string
 */
function message($string) {
	return '<div class="message">' . html_output($string) . '</div>';
}

/**
 * Returns a date based on how long ago the date occurred (i.e 5 minutes ago)
 *
 * @param   string   	$timestring 	The date to convert
 * @return  string						The date in time ago words
 */
function time_ago_in_words($timestring) {

	$datetime = new \DateTime($timestring);
	
	return ago($datetime);

}

/**
 * Returns an HTML string while stripping out bad HTML
 *
 * @param   string   	$string 		The HTML to make safe
 * @return  string						The safe HTML
 */
function html_output($string) {
	
	$purifier	= &singleton::get('HTMLPurifier');

	return $purifier->purify($string);

}

function _h($string) {
	return html_output($string);
}

function _l($string) {
	$language 	= &singleton::get(__NAMESPACE__ . '\language');

	return $language->get($string);
}

?>