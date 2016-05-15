<?php
/**
 * 	Files to Tickets
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *
 * @package     sts
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace mrpassword;

class password_files extends table_access {

	private $table_name 		= NULL;
	private $allowed_columns 	= NULL;

	function __construct() {
	
		$this->set_table('files_to_passwords');
		$this->allowed_columns(
				array(
					'password_id',
					'file_id',
					'private'
				)
			);
		$this->table_name = $this->get_table();
		$this->allowed_columns	= $this->get_allowed_columns();

	}

}


?>