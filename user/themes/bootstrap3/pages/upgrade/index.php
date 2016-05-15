<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title('Upgrade');
$site->set_config('container-type', 'container');

if ($auth->get('user_level') != 2) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$upgrade 		= new upgrade();

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<div class="col-md-3">
		<div class="well well-sm">
			<h4>Upgrade</h4>
			<p>This page upgrades the database to the latest version.</p>
		</div>
	</div>

	<div class="col-md-9">
		<!--<div class="well well-sm">-->
					
			<?php
			if ($config->get('database_version') == $upgrade->get_db_version() && $config->get('program_version') == $upgrade->get_program_version() ) {
				?>
				<div class="alert alert-success">
					<?php echo safe_output($language->get('Your database is currently up to date and does not need upgrading.')); ?>
				</div>
				<?php
			}
			elseif (isset($_GET['run']) && $_GET['run'] == 'upgrade') {
				$upgrade->do_upgrade();
				?>
				<div class="alert alert-success">
					<?php echo safe_output($language->get('Upgrade Complete.')); ?>
				</div>
				<?php
			}
			else {
				?>
				<div class="alert alert-warning">
					<?php echo safe_output($language->get('Please ensure you have a full database backup before continuing.')); ?>
				</div>		
				<div class="alert alert-success">
					<?php echo html_output($language->get('Your database needs upgrading, please click <a href="'.safe_output($config->get('address')).'/upgrade/?run=upgrade" class="button">here</a> to continue.')); ?>
				</div>	
				<?php
			}
			?>

			<div class="clearfix"></div>

		<!--</div>-->
	</div>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>