<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Support'));
$site->set_config('container-type', 'container');

if ($auth->get('user_level') != 2) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if (isset($_POST['reset_cron'])) {
	$cron_intervals = array(
		array('name' => 'every_two_minutes', 'description' => 'Every Two Minutes', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '120'),
		array('name' => 'every_five_minutes', 'description' => 'Every Five Minutes', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '300'),
		array('name' => 'every_fifteen_minutes', 'description' => 'Every Fifteen Minutes', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '900'),
		array('name' => 'every_hour', 'description' => 'Every Hour', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '3600'),
		array('name' => 'every_day', 'description' => 'Every Day', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '86400'),
		array('name' => 'every_week', 'description' => 'Every Week', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '604800'),
		array('name' => 'every_month', 'description' => 'Every Month', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '2592000'),
	);
			
	$config->set('cron_intervals', $cron_intervals);
}
if (isset($_POST['clear_queue'])) {
	$queue->delete();
}

$upgrade 						= new upgrade();

$version_info					= $upgrade->version_info();
$cron_intervals					= $config->get('cron_intervals');

$users_count					= $users->count();
$queue_count					= $queue->count();

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>

<script type="text/javascript">
	$(document).ready(function () {
		$('#clear_queue').click(function () {
			if (confirm("<?php echo safe_output($language->get('Are you sure you wish to clear the queue?')); ?>")){
				return true;
			}
			else{
				return false;
			}
		});
	});
</script>
<div class="row">
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4>Support</h4>
				</div>
				
				<div class="clearfix"></div>

				<p>This page contains useful debug information.</p>
				<br />
				<div class="pull-right">
					<p><button type="submit" name="reset_cron" class="btn btn-info"><?php echo safe_output($language->get('Reset Cron')); ?></button></p>
					<p><button type="submit" id="clear_queue" name="clear_queue" class="btn btn-danger"><?php echo safe_output($language->get('Clear Queue')); ?></button></p>
				</div>
				
				<div class="clearfix"></div>

			</div>
		</div>

		<div class="col-md-9">
			<table class="table table-striped">
				<tr>
					<th>Name</th>
					<th>Value</th>
				</tr>
				<?php $i = 0; ?>				
				<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
					<td class="centre">Date Installed</td>
					<td class="centre"><?php echo safe_output(nice_datetime($config->get('utc_date_installed'), true)); ?></td>
				</tr>
				<?php $i++; ?>
				<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
					<td class="centre">Server Time</td>
					<td class="centre"><?php echo safe_output(nice_datetime(datetime())); ?></td>
				</tr>
				<?php $i++; ?>
				<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
					<td class="centre">Encryption Level</td>
					<td class="centre"><?php echo safe_output($config->get('encryption_level')); ?></td>
				</tr>
				<?php $i++; ?>
				<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
					<td class="centre">Installed Program Version</td>
					<td class="centre"><?php echo safe_output($version_info['installed_program_version']); ?></td>
				</tr>
				<?php $i++; ?>
				<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
					<td class="centre">Installed Database Version</td>
					<td class="centre"><?php echo safe_output($version_info['installed_database_version']); ?></td>
				</tr>
				<?php $i++; ?>
				<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
					<td class="centre">Code Program Version</td>
					<td class="centre"><?php echo safe_output($version_info['code_program_version']); ?></td>
				</tr>
				<?php $i++; ?>
				<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
					<td class="centre">Code Database Version</td>
					<td class="centre"><?php echo safe_output($version_info['code_database_version']); ?></td>
				</tr>
				<?php $i++; ?>
				<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
					<td class="centre">Latest Program Version</td>
					<td class="centre"><?php echo safe_output($version_info['latest_program_version']); ?></td>
				</tr>
				<?php $i++; ?>
				<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
					<td class="centre">PHP Version</td>
					<td class="centre"><?php echo safe_output(PHP_VERSION); ?></td>
				</tr>				
				<?php $i++; ?>
				<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
					<td class="centre">Users</td>
					<td class="centre"><?php echo safe_output($users_count); ?></td>
				</tr>
				<?php $i++; ?>
				<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
					<td class="centre">Queue</td>
					<td class="centre"><a href="<?php echo safe_output($config->get('address')); ?>/settings/queue/"><?php echo safe_output($queue_count); ?></a></td>
				</tr>
				<?php foreach ($cron_intervals as $cron) { ?>
					<?php $i++; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre">Cron <?php echo safe_output($cron['name']); ?></td>
						<td class="centre"><a href="<?php echo safe_output($config->get('address')); ?>/settings/view_plugins_section_info/?section=cron_<?php echo safe_output($cron['name']); ?>">Next Run <?php echo safe_output($cron['next_run']); ?></a></td>
					</tr>
				<?php } ?>
			</table>
			<div class="clear"></div>
		</div>
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>