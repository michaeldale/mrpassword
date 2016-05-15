<?php
namespace mrpassword;

class mailer {
	private $phpmailer;
	
	function __construct() {		
		//$this->phpmailer 	= &singleton::get(__NAMESPACE__ . '\phpmailer', true);
		
		$this->phpmailer 	= new \PHPMailer(true);

	}

	//this kicks off the email processing
	public function run_queue() {

		$queue =	&singleton::get(__NAMESPACE__ . '\queue');
		
		$queue->run('email');

	}
	
	//this function processes the email stored in the queue
	function process_email_queue(&$queue) {

		$log =	&singleton::get(__NAMESPACE__ . '\log');
		
		//get the queue data
		$qarray = $queue['data'];
		
		if (isset($qarray['from_name'])) {
			$array['from_name']		= $qarray['from_name'];
		}
		if (isset($qarray['from'])) {
			$array['from']			= $qarray['from'];
		}
		if (isset($qarray['html'])) {
			$array['html']			= $qarray['html'];
		}
		if (isset($qarray['file'])) {
			$array['file']			= $qarray['file'];
		}
		if (isset($qarray['string_file'])) {
			$array['string_file']	= $qarray['string_file'];
		}
		
		$array['subject']		= $qarray['subject'];
		$array['body']			= $qarray['body'];
		$array['to']['to']		= $qarray['to']['to'];
		$array['to']['to_name']	= $qarray['to']['to_name'];
		
		//print_r($array);
		
		//let's try and send the email now
		if($this->send_email($array)) {
			//this means the email will be deleted from the queue (success)
			$queue['processed'] = true;
		}
		else {
			if ($queue['retry'] >= 5) {
				//this means the email will be deleted from the queue (full fail)
				$queue['processed'] = true;
				
				$larray['event_severity'] = 'error';
				$larray['event_number'] = E_USER_ERROR;
				$larray['event_description'] = 'Unable to send email, removing from queue after 5 retries';
				$larray['event_file'] = __FILE__;
				$larray['event_file_line'] = __LINE__;
				$larray['event_type'] = 'email_not_sent';
				$larray['event_source'] = 'mailer';
				$larray['event_version'] = '1';
				$larray['log_backtrace'] = true;	
				
				$log->add($larray);
				
			} else {
				$queue['retry']++;
			}
		}
	}
	
	//add an email to the email queue
	public function queue_email($email_array) {
		$queue =	&singleton::get(__NAMESPACE__ . '\queue');
		
		$queue->add(array('data' => $email_array, 'type' => 'email'));

		return true;
	}
	
	public function send_email($array) {
	
		$config 	=	&singleton::get(__NAMESPACE__ . '\config');
		$log 		=	&singleton::get(__NAMESPACE__ . '\log');
		
		try {

			//clear any current info
			$this->phpmailer->ClearAllRecipients();
			$this->phpmailer->ClearAttachments();
			
			$smtp_server = $config->get('smtp_server');
			
			if (!empty($smtp_server) && $config->get('smtp_enabled')) {
				//what server to send the email to
				$this->phpmailer->Host = $smtp_server;
				$this->phpmailer->Mailer = 'smtp';
			}
			
			//increase the default timeout to 15 seconds
			$this->phpmailer->Timeout = 15;
			
			$this->phpmailer->CharSet = 'utf-8';
			
			//setup authentication if required
			if (!empty($smtp_server) && $config->get('smtp_enabled') && $config->get('smtp_auth')) {
				$this->phpmailer->SMTPAuth = true;     // turn on SMTP authentication
				$this->phpmailer->Username = $config->get('smtp_username');
				$this->phpmailer->Password = $config->get('smtp_password');
			}
			
			if ($config->get('smtp_tls')) {
				$this->phpmailer->SMTPSecure = 'tls';
			}
			
			$this->phpmailer->Port = (int) $config->get('smtp_port');
			
			if (isset($array['html']) && ($array['html'] == true)) {
				$this->phpmailer->IsHTML(true);
			}
			
			//setup the basic email stuff
			
			if (isset($array['from'])) {
				$this->phpmailer->From = $array['from'];
			}
			else {
				$smtp_email = $config->get('smtp_email_address');
				if (!empty($smtp_email)) {
					$this->phpmailer->From = $config->get('smtp_email_address');
				}
				else {
					$this->phpmailer->From = 'do_not_reply@' . $config->get('domain');
				}
			}
			
			if (isset($array['from_name'])) {
				$this->phpmailer->FromName = $array['from_name'];
			}
			else {
				$this->phpmailer->FromName = $config->get('name');
			}
			
			$this->phpmailer->Subject = $array['subject'];
			
			$this->phpmailer->Body = $array['body'];
			
			if (isset($array['to']) && is_array($array['to'])) {
				if (!empty($array['to']['to'])) {
					$this->phpmailer->AddAddress($array['to']['to'], $array['to']['to_name']);
				}
			}
			
			//add multiple files
			if (isset($array['file']) && is_array($array['file'])) {
				foreach ($array['file'] as $file) {
					if (file_exists($file['file'])) {
						$this->phpmailer->AddAttachment($file['file'], $file['file_name']);
					}
				}
			}
			
			//add multiple files via a string (I haven't really tested this yet)
			if (isset($array['string_file']) && is_array($array['string_file'])) {
				foreach ($array['string_file'] as $string) {
					$this->phpmailer->AddStringAttachment($string['string'], $string['string_name']);
				}
			}

			//let's try and send the email now
			$this->phpmailer->Send();
			
			$array['event_severity'] = 'notice';
			$array['event_number'] = E_USER_NOTICE;
			if (isset($array['to']) && is_array($array['to'])) {
				$array['event_description'] = 'Email sent to "' . safe_output($array['to']['to']) . '"';
			}
			else {
				$array['event_description'] = 'Email sent';
			}
			$array['event_file'] = __FILE__;
			$array['event_file_line'] = __LINE__;
			$array['event_type'] = 'email_sent';
			$array['event_source'] = 'mailer';
			$array['event_version'] = '1';
			$array['log_backtrace'] = false;	
			
			$log->add($array);
			
			return true;
		}
		catch (\phpmailerException $e) {
			$array['event_severity'] = 'warning';
			$array['event_number'] = E_USER_WARNING;
			$array['event_description'] = $e->errorMessage();
			$array['event_file'] = __FILE__;
			$array['event_file_line'] = __LINE__;
			$array['event_type'] = 'email_not_sent';
			$array['event_source'] = 'mailer';
			$array['event_version'] = '1';
			$array['log_backtrace'] = true;	
			
			$log->add($array);
			
			return false;
		} catch (\Exception $e) {
			$array['event_severity'] = 'warning';
			$array['event_number'] = E_USER_WARNING;
			$array['event_description'] = $e->getMessage();
			$array['event_file'] = __FILE__;
			$array['event_file_line'] = __LINE__;
			$array['event_type'] = 'email_not_sent';
			$array['event_source'] = 'mailer';
			$array['event_version'] = '1';
			$array['log_backtrace'] = true;	
			
			$log->add($array);
			
			return false;
		}
	}
} 

?>