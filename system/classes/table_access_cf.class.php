<?php
/**
 * 	Permission Groups to Department Notifications
 *	Copyright Dalegroup Pty Ltd 2014
 *	support@dalegroup.net
 *
 *
 * @package     sts
 * @author      Michael Dale <support@dalegroup.net>
 */

namespace mrpassword;

class table_access_cf extends table_access {

	private $table_name 		= NULL;
	private $allowed_columns 	= NULL;

	function __construct() {
	
		$this->set_table('table_access_cf');
		$this->allowed_columns(
				array(
					'table_name',
					'name',
					'type',
					'client_modify',
					'enabled',
					'allowed_values',
					'index_display'
				)
			);
		$this->table_name 		= $this->get_table();
		$this->allowed_columns	= $this->get_allowed_columns();

	}

}


?>