<?php
/**
 * 	Language Words
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace mrpassword;

class language_words extends table_access {

	private $table_name 		= NULL;
	private $allowed_columns 	= NULL;


	function __construct() {
		$this->set_table('language_words');
		$this->allowed_columns(
				array(
					'name', 
					'date_added',
					'version',
					'translation'
				)
			);
		$this->table_name = $this->get_table();
		$this->allowed_columns	= $this->get_allowed_columns();
	}
	
	function create_file() {
	
		$words = $this->get();
	
$file	= '<?php
namespace mrpassword;

class lang {

	private $lang_array = array();

	function __construct() {

';

	foreach($words as $word) {
		$file .= '$language_array[\'' . $word['name'] . '\'] = \'' . $word['name'] . '\';' . "\n";
	}

$file .= '$this->lang_array 			= $language_array;
		
	}
	
	public function get() {
		return $this->lang_array;
	}
}	
?>';

		$filename = ROOT . '/new_lang.php';

		if ($handle = fopen($filename, 'x')) { 

			if (fwrite($handle, $file)) {   
				fclose($handle);
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
		
	}

}


?>