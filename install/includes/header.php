<?php
include('common.php');

//$ipm_install = new ipm_install();

if (isset($_SESSION['install_data'])) {

}
else {
	$_SESSION['install_data']['stage'] = 1;
}

?>