<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('View Category'));
$site->set_config('container-type', 'container');

$id = (int) $url->get_item();

if ($id == 0) {
	header('Location: ' . $config->get('address') . '/categories/');
	exit;
}

$categories_array = $categories->get(array('user_id' => $auth->get('id'), 'get_other_data' => true, 'id' => $id, 'global' => 0));

if (count($categories_array) == 1) {
	$category = $categories_array[0];
}
else {
	header('Location: ' . $config->get('address') . '/categories/');
	exit;
}

$share_array = $shares->get(array('get_other_data' => true, 'user_id' => $auth->get('id'), 'category_id' => $id));

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<script type="text/javascript">
	$(document).ready(function () {
		$('#delete').click(function () {
			if (confirm("<?php echo $language->get('Are you sure you wish to delete this share?'); ?>")){
				return true;
			}
			else{
				return false;
			}
		});
	});
</script>
<div class="row">

	<div class="col-md-3">
		<div class="well well-sm">
			<div class="pull-left">
				<h4><?php echo $language->get('Category'); ?></h4>
			</div>
			
			<div class="pull-right">
				<p><a href="<?php echo $config->get('address'); ?>/categories/edit/<?php echo (int) $category['id']; ?>/" class="btn btn-default"><?php echo $language->get('Edit'); ?></a></p>		
			</div>
			
			<div class="clearfix"></div>
			<br />
			<div class="pull-right">
				<p><a href="<?php echo $config->get('address'); ?>/passwords/category/<?php echo (int) $category['id']; ?>/" class="btn btn-default"><?php echo $language->get('View Passwords'); ?></a></p>
			</div>
			<div class="clearfix"></div>

		</div>
		<div class="well well-sm">
	
			<div class="pull-left">
				<h4><?php echo $language->get('Shares'); ?></h4>
			</div>
			<div class="clearfix"></div>

			<p><?php echo $language->get('Sharing a category allows specified users of the system access to the passwords within that category.'); ?></p>
			<br />

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
				<h4><?php echo $language->get('Shares'); ?></h4>
			</div>
			
			<div class="pull-right">
				<a href="<?php echo $config->get('address'); ?>/categories/share/<?php echo (int) $category['id']; ?>/" class="btn btn-default"><?php echo $language->get('Add'); ?></a>			
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

							<td class="centre"><a id="delete" href="<?php echo $config->get('address'); ?>/categories/deleteshare/<?php echo (int) $share['id']; ?>/"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></a></td>
						</tr>
						<?php $i++; ?>
					<?php } ?>

				</table>
			</div>

			<div class="clearfix"></div>

		</div>
	</div>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>