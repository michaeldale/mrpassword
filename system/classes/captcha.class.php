<?php
/**
 * 	Captcha Class
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */
namespace mrpassword;

class captcha {

	private $text = NULL;

	function __construct() {

	}
	
	function set_text($text) {
		$this->text = $text;
	
	}
	function get_text() {
		return $this->text;
	}

	function get_random_text() {
		return rand_str(6, 'QWERTYUPASDFGHJKZXCVBNM123456789');
	}
	
	function display() {
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		$language 		= &singleton::get(__NAMESPACE__ . '\language');

		header("Content-type: image/png");
		
		if (isset($this->text) && !empty($this->text)) {
			$capture_text = $this->text;
		}
		else {
			$capture_text = $language->get('Error');
		}
		
		$string = strtoupper($capture_text);
		
		$r = rand (0, 150); 
		$g = rand (0, 150); 
		$b = rand (0, 150);
		
		$im = imagecreatefrompng(THEMES . '/' . CURRENT_THEME . '/images/captcha_background.png');
		$colour = imagecolorallocate($im, $r, $g, $b);
		
		$size = rand (20, 25);
		$angle = rand (0, 3);
		$left = rand (5, 17);
		$bottomleft = 38;
		
		imagettftext ($im, $size, $angle, $left, $bottomleft, $colour, SYSTEM . "/fonts/delicious.otf", $string);
		imagepng($im);
		imagedestroy($im);	
	}

}


?>
