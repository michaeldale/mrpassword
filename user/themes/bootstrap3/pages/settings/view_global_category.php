<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('View Category'));
$site->set_config('container-type', 'container');

if ($auth->get('user_level') != 2) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$id = (int) $url->get_item();

if ($id == 0) {
	header('Location: ' . $config->get('address') . '/settings/passwords/');
	exit;
}

$categories_array = $categories->get(array('get_other_data' => true, 'id' => $id, 'global' => 1));

if (count($categories_array) == 1) {
	$category = $categories_array[0];
}
else {
	header('Location: ' . $config->get('address') . '/settings/passwords/');
	exit;
}

$share_array = $shares->get(array('get_other_data' => true, 'category_id' => $id));

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<script type="text/javascript">
		$(document).ready(function () {
			$('.delete_user').click(function () {
				if (confirm("<?php echo $language->get('Are you sure you wish to delete this user?'); ?>")){
					return true;
				}
				else{
					return false;
				}
			});
		});
	</script>

	
	<div class="col-md-3">
		<div class="well well-sm">
			<div class="pull-left">
				<h4><?php echo $language->get('View Global Category'); ?></h4>
			</div>
			
			<div class="pull-right">
				<p><a href="<?php echo $config->get('address'); ?>/settings/edit_global_category/<?php echo (int) $category['id']; ?>/" class="btn btn-default"><?php echo $language->get('Edit'); ?></a></p>		
			</div>
			
			<div class="clearfix"></div>

		</div>
	</div>

	<div class="col-md-9">
		<div id="content">			
			<div class="table-responsive">		
				<table class="table table-striped">
					<tr>
						<th><?php echo $language->get('Item'); ?></th>
						<th><?php echo $language->get('Value'); ?></th>
					</tr>
					<?php $i = 0; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre"><?php echo $language->get('Name'); ?></td>
						<td class="centre"><?php echo safe_output($category['name']); ?></td>
					</tr>
					<?php $i++; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre"><?php echo $language->get('Passwords'); ?></td>
						<td class="centre"><?php echo safe_output($category['password_count']); ?></td>
					</tr>

				</table>
			</div>
		</div>
		<div id="content">		
			<div class="pull-left">
				<h4><?php echo $language->get('Users'); ?></h4>
			</div>
			<div class="pull-right">
				<a href="<?php echo $config->get('address'); ?>/settings/add_user_global_category/<?php echo (int) $category['id']; ?>/" class="btn btn-default"><?php echo $language->get('Add'); ?></a>
			</div>
			<div class="clearfix"></div>

			<div class="table-responsive">		
				<table class="table table-striped">
					<tr>
						<th><?php echo $language->get('User'); ?></th>
						<th><?php echo $language->get('Access'); ?></th>
						<th><?php echo $language->get('Delete'); ?></th>
					</tr>
					<?php $i = 0; ?>
					<?php foreach ($share_array as $share) { ?>
						<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
							<td class="centre"><?php echo safe_output($share['client_name']); ?> (<?php echo safe_output($share['client_username']); ?>)</td>
							<td class="centre">
								<?php if ($share['access_level'] == 2) { ?>
									<?php echo $language->get('View, Edit and Add'); ?>
								<?php } else { ?>
									<?php echo $language->get('View Only'); ?>
								<?php } ?>
							</td>

							<td class="centre"><a class="delete_user" href="<?php echo $config->get('address'); ?>/settings/delete_user_global_category/<?php echo (int) $share['id']; ?>/"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></a></td>
						</tr>
						<?php $i++; ?>
					<?php } ?>

				</table>
			</div>

			<div class="clear"></div>

		</div>
	</div>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>