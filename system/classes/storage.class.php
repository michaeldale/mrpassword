<?php
/**
 * 	Storage Class
 *	Copyright Dalegroup Pty Ltd 2013
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace mrpassword;

class storage {
	
	var $upload_path = NULL;

	function __construct() {
		$config =	&singleton::get(__NAMESPACE__ . '\config');

		$this->upload_path = '';
		if ($config->get('storage_enabled')) {
			$this->upload_path 	= $config->get('storage_path');
		}
		
		return true;
	}
	
	private function uuid() {
		// version 4 UUID
		return sprintf(
			'%08x-%04x-%04x-%02x%02x-%012x',
			mt_rand(),
			mt_rand(0, 65535),
			bindec(substr_replace(
			   sprintf('%016b', mt_rand(0, 65535)), '0100', 11, 4)
			),
			bindec(substr_replace(sprintf('%08b', mt_rand(0, 255)), '01', 5, 2)),
			mt_rand(0, 255),
			mt_rand()
		);
	}
	
	//from email
	public function save_data($array) {
	
		if (empty($this->upload_path)) {
			$log =	&singleton::get(__NAMESPACE__ . '\log');

			$log_array['event_severity'] = 'warning';
			$log_array['event_number'] = E_USER_WARNING;
			$log_array['event_description'] = 'Storage Path empty, file not uploaded.';
			$log_array['event_file'] = __FILE__;
			$log_array['event_file_line'] = __LINE__;
			$log_array['event_type'] = 'save_data';
			$log_array['event_source'] = 'storage';
			$log_array['event_version'] = '1';
			$log_array['log_backtrace'] = false;	
					
			$log->add($log_array);
			
			return false;
		}
		
		$image_uuid 			= $this->uuid();
		
		$file 					= $array['file'];
		
		$current_file_name		= strtolower($file['name']);
		
		$extension_array 		= explode('.', $current_file_name);
		$extension				= end($extension_array);
				
		$new_file_name	= $this->upload_path . $image_uuid . '.' . $extension;
	
		if(!file_exists($new_file_name)) {
			
			$fh = fopen($new_file_name, 'w');
			
			if ($fh) {
				$stringData = $file['data'];
				fwrite($fh, $stringData);
			}
			
			fclose($fh);
			
			chmod($new_file_name, 0644);

		
			$add_array['uuid'] 				= $image_uuid;
			$add_array['extension'] 		= $extension;
			if (isset($array['name'])) {
				$add_array['name'] 			= $array['name'];
			}	
			if (isset($array['description'])) {
				$add_array['description'] 	= $array['description'];
			}
			if (isset($array['type'])) {
				$add_array['type'] 			= $array['type'];
			}
			if (isset($array['category_id'])) {
				$add_array['category_id']	= $array['category_id'];
			}
			if (isset($array['user_id'])) {
				$add_array['user_id']		= (int) $array['user_id'];
			}
			$add_array['date_added']		= datetime();
			$file_id 						= $this->add($add_array);
			
			return $file_id;
		}
		else {
			return false;
		}
	}
	
	public function get_file_size($array) {
		if (isset($array['uuid']) && isset($array['extension'])) {
			//we already have the file, just need to look it up
			if (file_exists($this->upload_path . '/' . $array['uuid'] . '.' . $array['extension'])) {
				$size = filesize($this->upload_path . '/' . $array['uuid'] . '.' . $array['extension']);
				if ($size > 0) {
					//return in mb
					$size = (($size / 1024) / 1024);
					$size = round($size, 3);
					return $size;
				}
			}
		}
		return false;
	}
	
	public function add_file_to_password($array) {
		global $db;
		
		$error =	&singleton::get(__NAMESPACE__ . '\error');
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$site_id	= SITE_ID;

		
		$password_id		= (int) $array['password_id'];
		$file_id		= (int)	$array['file_id'];
		
		$query = "INSERT INTO $tables->files_to_passwords (file_id, password_id, site_id";
		
		if (isset($array['private'])) {
			$query .= ", private";
		}
		
		$query .= ") VALUES (:file_id, :password_id, :site_id";

		if (isset($array['private'])) {
			$query .= ", :private";
		}
		
		$query .= ")";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':file_id', $file_id, database::PARAM_INT);
		$stmt->bindParam(':password_id', $password_id, database::PARAM_INT);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);


		if (isset($array['private'])) {
			$private = (int) $array['private'];
			$stmt->bindParam(':private', $private, database::PARAM_INT);
		}
		
		try {
			$stmt->execute();
			$log =	&singleton::get(__NAMESPACE__ . '\log');

			$log_array['event_severity'] = 'notice';
			$log_array['event_number'] = E_USER_NOTICE;
			$log_array['event_description'] = 'File attached';
			$log_array['event_file'] = __FILE__;
			$log_array['event_file_line'] = __LINE__;
			$log_array['event_type'] = 'add_file_to_password';
			$log_array['event_source'] = 'storage';
			$log_array['event_version'] = '1';
			$log_array['log_backtrace'] = false;	
					
			//$log->add($log_array);
			
			return $db->lastInsertId();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
	}
	
	//from post
	public function upload($array) {
	
		if (empty($this->upload_path)) {
			$log =	&singleton::get(__NAMESPACE__ . '\log');

			$log_array['event_severity'] = 'warning';
			$log_array['event_number'] = E_USER_WARNING;
			$log_array['event_description'] = 'Storage Path empty, file not uploaded.';
			$log_array['event_file'] = __FILE__;
			$log_array['event_file_line'] = __LINE__;
			$log_array['event_type'] = 'upload';
			$log_array['event_source'] = 'storage';
			$log_array['event_version'] = '1';
			$log_array['log_backtrace'] = false;	
					
			$log->add($log_array);
			return false;
		}		
	
		$image_uuid 		= $this->uuid();
		
		$file 				= $array['file'];
		
		$current_file_name		= strtolower($file['name']);
		$extension_array 		= explode('.', $current_file_name);
		$extension				= end($extension_array);
		
		$new_file_name	= $this->upload_path . $image_uuid . '.' . $extension;
	
		if(!file_exists($new_file_name)) {
			if (move_uploaded_file($file['tmp_name'], $new_file_name)) {
				chmod($new_file_name, 0644);
				$add_array['uuid'] 				= $image_uuid;
				$add_array['extension'] 		= $extension;
				if (isset($array['name'])) {
					$add_array['name'] 			= $array['name'];
				}	
				if (isset($array['description'])) {
					$add_array['description'] 	= $array['description'];
				}
				if (isset($array['type'])) {
					$add_array['type'] 			= $array['type'];
				}
				if (isset($array['category_id'])) {
					$add_array['category_id']	= $array['category_id'];
				}
				if (isset($array['user_id'])) {
					$add_array['user_id']		= (int) $array['user_id'];
				}
				$add_array['date_added']		= datetime();
				$file_id 						= $this->add($add_array);
				
				return $file_id;
			}
			else {
					
				if (!SAAS_MODE) {
					$log =	&singleton::get(__NAMESPACE__ . '\log');

					$log_array['event_severity'] = 'warning';
					$log_array['event_number'] = E_USER_WARNING;
					$log_array['event_description'] = 'Unable to move file "' . safe_output($file['tmp_name']) . '" to "' . safe_output($new_file_name) . '"';
					$log_array['event_file'] = __FILE__;
					$log_array['event_file_line'] = __LINE__;
					$log_array['event_type'] = 'upload';
					$log_array['event_source'] = 'storage';
					$log_array['event_version'] = '1';
					$log_array['log_backtrace'] = false;	
							
					$log->add($log_array);
				}
			
				return false;
			}
		}
		else {
			return false;
			//throw new Exception('File "' . $file['tmp_name'] . '" already exists');
		}
	
	}
	
	private function add($array) {
		global $db;
		
		$error 		=	&singleton::get(__NAMESPACE__ . '\error');
		$tables 	=	&singleton::get(__NAMESPACE__ . '\tables');
		$site_id	= SITE_ID;

		
		$query = "INSERT INTO $tables->storage (uuid, site_id";
		
		if (isset($array['name'])) {
			$query .= ", name";
		}
		if (isset($array['extension'])) {
			$query .= ", extension";
		}
		if (isset($array['description'])) {
			$query .= ", description";
		}
		if (isset($array['type'])) {
			$query .= ", type";
		}
		if (isset($array['category_id'])) {
			$query .= ", category_id";
		}
		if (isset($array['user_id'])) {
			$query .= ", user_id";
		}
		if (isset($array['date_added'])) {
			$query .= ", date_added";
		}
		
		$query .= ") VALUES (:uuid, :site_id";
		
		if (isset($array['name'])) {
			$query .= ", :name";
		}
		if (isset($array['extension'])) {
			$query .= ", :extension";
		}
		if (isset($array['description'])) {
			$query .= ", :description";
		}
		if (isset($array['type'])) {
			$query .= ", :type";
		}
		if (isset($array['category_id'])) {
			$query .= ", :category_id";
		}
		if (isset($array['user_id'])) {
			$query .= ", :user_id";
		}
		if (isset($array['date_added'])) {
			$query .= ", :date_added";
		}
		
		$query .= ")";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':uuid', $array['uuid'], database::PARAM_STR);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_STR);
		
		if (isset($array['name'])) {
			$stmt->bindParam(':name', $array['name'], database::PARAM_STR);
		}
		if (isset($array['extension'])) {
			$stmt->bindParam(':extension', $array['extension'], database::PARAM_STR);
		}
		if (isset($array['description'])) {
			$stmt->bindParam(':description', $array['description'], database::PARAM_STR);
		}
		if (isset($array['type'])) {
			$stmt->bindParam(':type', $array['type'], database::PARAM_STR);
		}
		if (isset($array['category_id'])) {
			$stmt->bindParam(':category_id', $array['category_id'], database::PARAM_STR);
		}
		if (isset($array['user_id'])) {
			$stmt->bindParam(':user_id', $array['user_id'], database::PARAM_INT);
		}
		if (isset($array['date_added'])) {
			$stmt->bindParam(':date_added', $array['date_added'], database::PARAM_STR);
		}		
		try {
			$stmt->execute();
			$id = $db->lastInsertId();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$log =	&singleton::get(__NAMESPACE__ . '\log');
		
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'File Added "' . safe_output($array['name']) . '"';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'add';
		$log_array['event_source'] = 'storage';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
		
		return $id;
	
	}
	
	public function get($array) {
		global $db;
		
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$site_id	= SITE_ID;
		
		$query = "SELECT * FROM $tables->storage WHERE 1 = 1 AND site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id = :id";
		}
		if (isset($array['ids'])) {
							
			$return = " AND $tables->storage.id IN (";
			
			foreach ($array['ids'] as $index => $value) {
				$return .= ':ids' . (int) $index . ',';
			}
			
			if(substr($return, -1) == ',') {	
				$return = substr($return, 0, strlen($return) - 1);
			}
			
			$return .= ')';
			
			$query .= $return;

		}		
		
		if (isset($array['type'])) {
			$query .= " AND type = :type";
		}
		if (isset($array['user_id'])) {
			$query .= " AND user_id = :user_id";
		}
		if (isset($array['uuid'])) {
			$query .= " AND uuid = :uuid";
		}
		if (isset($array['search'])) {
			$query .= " AND ((`name` LIKE :search) OR (`description` LIKE :search))";
		}
		
		$query .= " ORDER BY id DESC";
		
		if (isset($array['limit'])) {
			$query .= " LIMIT :limit";
			if (isset($array['offset'])) {
				$query .= " OFFSET :offset";
			}
		}
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		if (isset($array['id'])) {
			$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		}
		if (isset($array['ids'])) {	
			foreach ($array['ids'] as $index => $value) {
				$t_id = (int) $value;
				$stmt->bindParam(':ids' . (int) $index, $t_id, database::PARAM_INT);
				unset($t_id);
			}
		}			
		if (isset($array['type'])) {
			$stmt->bindParam(':type', $array['type'], database::PARAM_STR);
		}	
		if (isset($array['user_id'])) {
			$stmt->bindParam(':user_id', $array['user_id'], database::PARAM_INT);
		}		
		if (isset($array['uuid'])) {
			$stmt->bindParam(':uuid', $array['uuid'], database::PARAM_INT);
		}	
		if (isset($array['search'])) {
			$value = $array['search'];
			$value = "%{$value}%";
			$stmt->bindParam(':search', $value, database::PARAM_STR);
			unset($value);
		}	
		if (isset($array['limit'])) {
			$limit = (int) $array['limit'];
			if ($limit < 0) $limit = 0;
			$stmt->bindParam(':limit', $limit, database::PARAM_INT);
			if (isset($array['offset'])) {
				$offset = (int) $array['offset'];
				$stmt->bindParam(':offset', $offset, database::PARAM_INT);					
			}
		}
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$files = $stmt->fetchAll(database::FETCH_ASSOC);
		
		return $files;
	
	}
	
	public function edit($array) {
		global $db;

		$tables =	&singleton::get(__NAMESPACE__ . '\tables');		
		$error =	&singleton::get(__NAMESPACE__ . '\error');
		$site_id	= SITE_ID;

		
		$query = "UPDATE $tables->storage 
					SET 
						name 		= :name, 
						description = :description
					WHERE
						id = :id AND site_id = :site_id	
				";
				
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		$stmt->bindParam(':name', $array['name'], database::PARAM_STR);
		$stmt->bindParam(':description', $array['description'], database::PARAM_STR);

		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		return true;
	}
	
	public function delete($array) {
		global $db;
		
		$error =	&singleton::get(__NAMESPACE__ . '\error');
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		
		$site_id	= SITE_ID;

		
		if (!isset($array['id'])) return false;
		
		//remove attached files from objects
		
		//tickets
		$query = "DELETE FROM $tables->files_to_tickets WHERE file_id = :file_id AND site_id = :site_id";
	
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		$stmt->bindParam(':file_id', $array['id'], database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
				
		//delete file
		$files = $this->get(array('id' => $array['id'], 'limit' => 1));

		if (!empty($files)) {
			$file_name	= $this->upload_path . $files[0]['uuid'] . '.' . $files[0]['extension'];
			unlink($file_name);
		}
			
		$query = "DELETE FROM $tables->storage WHERE id = :id AND site_id = :site_id";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		return true;
	
	}
	
	private function load_image_data($image_file) {

		// Firstly, to disambiguate a loading error with a nonexistant file error,
		// check to see if the file actually exists.
		if (!file_exists($image_file)) {
			return false;
		}

		// We're going to check the return value of getimagesize, so we don't
		// need any pesky warnings or notices popping up, since we're going to
		// stop execution of this function if something goes wrong.
		$image_data = @getimagesize($image_file);

		if ($image_data === false) {
			return false;
		}

		$array['mime'] = $image_data['mime'];

		return $array;
	}

	public function load_image($image_file) {
	
		$array = $this->load_image_data($image_file);
		
		if (is_array($array)) { 
			// Suppress warning messages because we're going to throw an
			// exception if it didn't work instead.
			switch($array['mime']) {
				case 'image/jpeg':
				case 'image/pjpeg':
					$image = @imagecreatefromjpeg($image_file);
				break;
				case 'image/gif':
					$image = @imagecreatefromgif($image_file);
				break;
				case 'image/png':
					$image = @imagecreatefrompng($image_file);
				break;
				default:
					$image = false;
				break;
			}

			if ($image === false) {
				return false;
			}
			else {
				return true;
			}	
		}
		else {
			return false;
		}
	}		
}

?>