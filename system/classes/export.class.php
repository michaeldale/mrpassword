<?php
/**
 * 	Export Class
 *	Copyright Dalegroup Pty Ltd 2014
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <support@dalegroup.net>
 */
 
namespace mrpassword;

if (!ini_get('safe_mode')) {
	//ooh we can process for sooo long
	set_time_limit(280); 
}

class export {     

	function direct_download($array) {
		
		// send response headers to the browser
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment;filename="' . $array['filename_prefix'] .  '-' . datetime() . '.csv"');
		$fp = fopen('php://output', 'w');

		fputcsv($fp, $array['headers']);
		
		foreach($array['rows'] as $row) {
			fputcsv($fp, $row);
		}
       
        fclose($fp);
	}
}
?>