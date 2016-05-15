<?php
namespace mrpassword;

class singleton {

	public static function &get() {

		static $loaded_classes = array();
	
		$args = func_get_args();
		
		$class_name = array_shift($args);
		
		if (array_key_exists($class_name, $loaded_classes)) {
			$instance = $loaded_classes[$class_name];
			return $instance;
		}
		else {
			if (isset($args[0]) && isset($args[1]) && isset($args[2])) {
				$loaded_classes[$class_name] = new $class_name($args[0], $args[1], $args[2]);
			}	
			else if (isset($args[0]) && isset($args[1])) {
				$loaded_classes[$class_name] = new $class_name($args[0], $args[1]);
			}			
			else if (isset($args[0])) {
				$loaded_classes[$class_name] = new $class_name($args[0]);
			}
			else {
				$loaded_classes[$class_name] = new $class_name();
			}
			return $loaded_classes[$class_name];
		}
	}
}

?>