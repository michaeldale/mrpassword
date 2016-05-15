<?php
namespace mrpassword;
use mrpassword as core;

$site->set_title($language->get('View Log'));
$site->set_config('container-type', 'container');

if ($auth->get('user_level') != 2) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$id = (int) $url->get_item();

if ($id == 0) {
	header('Location: ' . $config->get('address') . '/logs/');
	exit;
}

$logs_array = $log->get(array('id' => $id));

if (count($logs_array) == 1) {
	$log_item = $logs_array[0];
}
else {
	header('Location: ' . $config->get('address') . '/logs/');
	exit;
}

if ($log_item['user_id'] !== 0) {
	$user_temp = $users->get(array('id' => $log_item['user_id']));

	if (count($user_temp) == 1) {
		$user_account = $user_temp[0];
	}
}

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<div class="col-md-3">
		<div class="well well-sm">
			<div class="pull-right">
				<p><a href="<?php echo safe_output($config->get('address')); ?>/logs/" class="btn btn-default"><?php echo safe_output($language->get('Show All')); ?></a></p>
			</div>
			
			<div class="pull-left">
				<h4><?php echo safe_output($language->get('Log')); ?></h4>
			</div>
			
			<div class="clearfix"></div>
		
		</div>
	</div>

	<div class="col-md-9">
		<table class="table table-striped table-bordered">
			<tr>
				<th><?php echo safe_output($language->get('Item')); ?></th>
				<th><?php echo safe_output($language->get('Value')); ?></th>
			</tr>
			<?php $i = 0; ?>
			<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
				<td class="centre"><?php echo safe_output($language->get('Added')); ?></td>
				<td class="centre"><?php echo safe_output($log_item['event_date']); ?></td>
			</tr>
			<?php $i++; ?>
			<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
				<td class="centre"><?php echo safe_output($language->get('Description')); ?></td>
				<td class="centre"><?php echo html_output($log_item['event_description']); ?></td>
			</tr>
			<?php $i++; ?>
			<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
				<td class="centre"><?php echo safe_output($language->get('Severity')); ?></td>
				<td class="centre"><?php echo safe_output($log_item['event_severity']); ?></td>
			</tr>
			<?php $i++; ?>
			<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
				<td class="centre"><?php echo safe_output($language->get('Type')); ?></td>
				<td class="centre"><?php echo safe_output($log_item['event_type']); ?></td>
			</tr>
			<?php $i++; ?>
			<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
				<td class="centre"><?php echo safe_output($language->get('Source')); ?></td>
				<td class="centre"><?php echo safe_output($log_item['event_source']); ?></td>
			</tr>
			<?php $i++; ?>
			<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
				<td class="centre"><?php echo safe_output($language->get('User ID')); ?></td>
				<td class="centre"><?php echo safe_output($log_item['user_id']); ?></td>
			</tr>
			<?php if (isset($user_account)) { ?>
				<?php $i++; ?>
				<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
					<td class="centre"><?php echo safe_output($language->get('User')); ?></td>
					<td class="centre"><a href="<?php echo $config->get('address'); ?>/users/view/<?php echo safe_output($log_item['user_id']); ?>/"><?php echo safe_output(ucwords($user_account['name'])); ?></a></td>
				</tr>
			<?php } ?>
			<?php $i++; ?>
			<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
				<td class="centre"><?php echo safe_output($language->get('IP Address')); ?></td>
				<td class="centre"><?php echo safe_output($log_item['event_ip_address']); ?></td>
			</tr>
			<?php $i++; ?>
			<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
				<td class="centre"><?php echo safe_output($language->get('Reverse DNS Entry')); ?></td>
				<td class="centre"><?php echo safe_output(@gethostbyaddr($log_item['event_ip_address'])); ?></td>
			</tr>
			<?php if (!SAAS_MODE) { ?>
				<?php $i++; ?>
				<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
					<td class="centre"><?php echo safe_output($language->get('File')); ?></td>
					<td class="centre"><?php echo wordwrap(safe_output($log_item['event_file']), 40, "<br />\n", true); ?></td>
				</tr>
				<?php $i++; ?>
				<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
					<td class="centre"><?php echo safe_output($language->get('File Line')); ?></td>
					<td class="centre"><?php echo safe_output($log_item['event_file_line']); ?></td>
				</tr>
			<?php } ?>
		</table>


		<div class="clearfix"></div>

	</div>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>