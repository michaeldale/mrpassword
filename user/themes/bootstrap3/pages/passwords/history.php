<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Password History'));
$site->set_config('container-type', 'container');

$id = (int) $url->get_item();

if ($id == 0) {
	header('Location: ' . $config->get('address') . '/passwords/');
	exit;
}

if (isset($_GET['action']) && ($_GET['action'] == 'show_all')) {
	$show_all = true;
}
else {
	$show_all = false;
}


$paswd_array['parent_id']		= $id;
$paswd_array['old']				= 1;
$paswd_array['get_other_data']	= true;
$paswd_array['global_or_null']	= 0;
$paswd_array['user_id'] 		= $auth->get('id');
$paswd_array['order_by'] 		= 'id';
$paswd_array['order'] 			= 'desc';

$passwords_array 				= $passwords->get($paswd_array);


include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<div class="col-md-3">
		<div class="well well-sm">
			<div class="pull-left">
				<h4><?php echo $language->get('History'); ?></h4>
			</div>

			<div class="pull-right">
				<a href="<?php echo $config->get('address'); ?>/passwords/view/<?php echo (int) $id; ?>/" class="btn btn-default"><?php echo $language->get('View'); ?></a>
			</div>

			<div class="clearfix"></div>

		</div>
	</div>

	<div class="col-md-9">

			<p>
				<?php if ($show_all) { ?>
					<a href="<?php echo $config->get('address'); ?>/passwords/history/<?php echo (int) $id; ?>/" class="btn btn-default btn-sm"><?php echo $language->get('Hide Passwords'); ?></a>
				<?php } else { ?>
					<a href="<?php echo $config->get('address'); ?>/passwords/history/<?php echo (int) $id; ?>/?action=show_all" class="btn btn-default btn-sm"><?php echo $language->get('Show Passwords'); ?></a>
				<?php } ?>
			</p>

			<div class="table-responsive">
				<table class="table table-striped">
					<tr>
						<th><?php echo $language->get('Name'); ?></th>
						<th><?php echo $language->get('Username'); ?></th>
						<th><?php echo $language->get('Password'); ?></th>
						<th><?php echo $language->get('Last Used'); ?></th>
					</tr>
					<?php
						$i = 0;
						foreach ($passwords_array as $password) {
					?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre"><a href="<?php echo $config->get('address'); ?>/passwords/viewhistory/<?php echo (int) $password['id']; ?>/"><?php echo safe_output($password['name']); ?></a></td>
                        <td class="centre"><span class="content-as-copy-box"><?php echo safe_output($password['username']); ?></span></td>
                        <td class="centre" name="password<?php echo (int) $password['id']; ?>"><?php if ($show_all) { ?><span class="content-as-copy-box"><?php echo safe_output($encryption->decrypt($password['password'])); ?></span><?php } else { ?><a href="#password<?php echo (int) $password['id']; ?>" class="show_password_history" id="id-<?php echo (int) $password['id']; ?>"><?php echo $language->get('Show'); ?></a><?php } ?></td>
						<td class="centre"><a href="<?php echo $config->get('address'); ?>/passwords/viewhistory/<?php echo (int) $password['id']; ?>/"><?php echo safe_output(time_ago_in_words($password['date_added'])); ?> <?php echo $language->get('ago'); ?></a></td>
					</tr>
					<?php $i++; } ?>
				</table>
			</div>

			<div class="clearfix"></div>

	</div>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>
