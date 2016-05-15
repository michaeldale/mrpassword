<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Add Global Category'));
$site->set_config('container-type', 'container');

if ($auth->get('user_level') != 2) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if (isset($_POST['add'])) {
	if (!empty($_POST['name'])) {
		$id = $categories->add(
			array(
				'name' 				=> $_POST['name'],
				'global'			=> 1
			)
		);
		header('Location: ' . $config->get('address') . '/settings/passwords/#global_categories');

		exit;
	}
	else {
		$message = $language->get('Name Empty');
	}
}

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo $language->get('Add Global Category'); ?></h4>
				</div>
				<div class="pull-right">
					<p><button type="submit" name="add" class="btn btn-primary"><?php echo $language->get('Add'); ?></button></p>
				</div>
				
				<div class="clearfix"></div>

			</div>
		</div>

		<div class="col-md-9">
			<?php if (isset($message)) { ?>
				<div class="alert alert-danger">
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>
			
			<div class="well well-sm">
				<p><?php echo $language->get('Name'); ?><br /><input type="text" name="name" value="<?php if (isset($_POST['name'])) echo safe_output($_POST['name']); ?>" /></p>		

				<div class="clearfix"></div>

			</div>
		</div>
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>