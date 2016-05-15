<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Categories'));
$site->set_config('container-type', 'container');

$categories_array = $categories->get(array('user_id' => $auth->get('id'), 'get_other_data' => true, 'global' => 0));

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<div class="col-md-3">
		<div class="well well-sm">
			<div class="pull-left">
				<h4><?php echo $language->get('Categories'); ?> (<?php echo count($categories_array); ?>)</h4>
			</div>
			
			<div class="pull-right">
				<p><a href="<?php echo $config->get('address'); ?>/categories/add/" class="btn btn-default"><?php echo $language->get('Add'); ?></a></p>
			</div>
			
			<div class="clearfix"></div>
			
			<p><?php echo $language->get('This page displays all the categories in your account.'); ?></p>
			<br />
		</div>
	</div>

	<div class="col-md-9">
		<div class="table-responsive">	
			<table class="table table-striped">
				<tr>
					<th><?php echo $language->get('Name'); ?></th>
					<th><?php echo $language->get('Passwords'); ?></th>
					<th><?php echo $language->get('Shares'); ?></th>
				</tr>
				<?php
					$i = 0;
					foreach ($categories_array as $category) {
				?>
				<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
					<td class="centre"><a href="<?php echo $config->get('address'); ?>/categories/view/<?php echo (int) $category['id']; ?>/"><?php echo safe_output($category['name']); ?></a></td>
					<td class="centre"><a href="<?php echo $config->get('address'); ?>/passwords/category/<?php echo (int) $category['id']; ?>/"><?php echo safe_output($category['password_count']); ?></a></td>
					<td class="centre"><?php echo safe_output($category['share_count']); ?></td>
				</tr>
				<?php $i++; } ?>
			</table>
		</div>
		<div class="clearfix"></div>

	</div>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>