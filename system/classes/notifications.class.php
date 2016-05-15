<?php
/**
 * 	Notifications Class
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 * 	This class is used for sending email notifications
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace mrpassword;

class notifications {
	
	function __construct() {

	}
	
	public function reset_new_user_notification() {
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		
		$config->set('notification_new_user_subject', '#SITE_NAME# - New Account');
$config->set('notification_new_user_body', '
Hi #USER_FULLNAME#,
<br /><br />
A user account has been created for you at #SITE_NAME#.
<br /><br />
URL: 		#SITE_ADDRESS#<br />
Name:		#USER_FULLNAME#<br />
Username:	#USER_NAME#<br />
Password:	#USER_PASSWORD#');	

	}

	public function password_reset($array) {
		global $db;
		
		$mailer 		= &singleton::get(__NAMESPACE__ . '\mailer');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		
				
		if (is_array($array['user'])) {
			$user = $array['user'];
						
			if (!empty($user['email'])) {
				
				$email_array['subject']				= $config->get('name') . ' - Password Reset';
				$email_array['body']				= 'A password reset request has been created for your account at ' . $config->get('name');
				$email_array['body']				.= "<br /><br />To approve this reset please click on this link:";
				$email_array['body']				.= '<br /><a href="'. $config->get('address') . '/reset/?key=' . urlencode($array['reset_key']) . '&amp;username=' . urlencode($user['username']) . '">' . $config->get('address') . '/reset/?key=' . urlencode($array['reset_key']) . '&amp;username=' . urlencode($user['username']) . '</a>';

				$email_array['to']['to']			= $user['email'];
				$email_array['to']['to_name']		= $user['name'];
				$email_array['html']				= true;
				
				$mailer->send_email($email_array);
			}
		}
		else {
			return false;
		}
	
	}
	
	public function new_user($array) {
		$mailer 		= &singleton::get(__NAMESPACE__ . '\mailer');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');

		if (is_array($array)) {
		
			if (!isset($array['password'])) {
				$array['password'] = '';
			}
		
			$tokens = array(
				array('token' => '#SITE_NAME#', 'value' => $config->get('name')),
				array('token' => '#USER_FULLNAME#', 'value' => $array['name']),
				array('token' => '#USER_NAME#', 'value' => $array['username']),
				array('token' => '#USER_PASSWORD#', 'value' => $array['password']),
				array('token' => '#USER_EMAIL#', 'value' => $array['email']),
				array('token' => '#SITE_ADDRESS#', 'value' => $config->get('address')),
				
			);
						
			$email_array['subject'] 	= $this->token_replace($config->get('notification_new_user_subject'), $tokens);	
			$email_array['body'] 		= $this->token_replace($config->get('notification_new_user_body'), $tokens);	
				
			$email_array['html']					= true;
			$email_array['to']['to']				= $array['email'];
			$email_array['to']['to_name']			= $array['name'];			
			
			$mailer->queue_email($email_array);
			
			return true;

		}
		else {
			return false;
		}	
	}
	
	public function token_replace($string, $array) {
	
		foreach($array as $item) {
			$string = str_replace($item['token'], $item['value'], $string);	
		}
		
		return $string;
	
	}
}

?>