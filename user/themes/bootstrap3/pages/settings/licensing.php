<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Licensing'));
$site->set_config('container-type', 'container');

if ($auth->get('user_level') != 2) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}


if (isset($_POST['save'])) {

	$config->set('license_key', $_POST['license_key']);
	
	$message = $language->get('Settings Saved');
}


include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Licensing')); ?></h4>
				</div>
				
				<div class="pull-right">
					<p><button type="submit" name="save" class="btn btn-info"><?php echo safe_output($language->get('Save')); ?></button></p>
				</div>
				<div class="clearfix"></div>	
				<br />
				<p><?php echo safe_output($language->get('Your License Key can be found in your CodeCanyon account. Select Downloads and then select "License Certificate", inside this file is a key called "Item Purchase Code", this is your License Key.')); ?></p>
				<div class="clearfix"></div>	

			</div>
		</div>

		<div class="col-md-9">

			<?php if (isset($message)) { ?>
				<div class="alert alert-success">
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>
		
			<div class="well well-sm">
				<div class="input-group">
					<p><?php echo safe_output($language->get('License Key')); ?><br /><input type="text" name="license_key" class="form-control" size="30" value="<?php echo safe_output($config->get('license_key')); ?>" /></p>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>