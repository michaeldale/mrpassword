<?php
include('common.php');

$send_data['application_id'] 	= 2;
$send_data['version'] 			= $ipm_install->get_config('version');
$send_data['custom_data']		= array('type' => 'installer');

$data = $apptrack->send($send_data);

if (!empty($data)) {
	$version = explode('-', $ipm_install->get_config('version'));
	if (version_compare($version[0], $data['version'], '<')) {
		?>
		Fail
		<?php
	}
	else {
		?>
		Pass
		<?php
	}
}
else {
	?>
	Unknown
	<?php
}
?>