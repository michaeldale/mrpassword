<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Edit Global Category'));
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

if (isset($_POST['delete'])) {
	$categories->delete(
			array(
				'id'		=> $category['id']
			)
		);
	header('Location: ' . $config->get('address') . '/settings/passwords/#global_categories');
	exit;
}

if (isset($_POST['save'])) {
	if (!empty($_POST['name'])) {
		$categories->edit(
			array(
				'name' 		=> $_POST['name'],
				'id'		=> $category['id']
			)
		);
		header('Location: ' . $config->get('address') . '/settings/view_global_category/'.$id.'/');

		exit;
	}
	else {
		$message = $language->get('Name Empty');
	}
}

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<script type="text/javascript">
	$(document).ready(function () {
		$('#delete').click(function () {
			if (confirm("<?php echo safe_output($language->get('All passwords attached to this category will be left without a category. Are you sure you wish to delete this Global Category?')); ?>")){
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
					<h4><?php echo $language->get('Edit Global Category'); ?></h4>
				</div>
				<div class="pull-right">
					<p><button type="submit" name="save" class="btn btn-info"><?php echo $language->get('Save'); ?></button></p>
				</div>
				
				<div class="clearfix"></div>
				
				<br />
				<div class="pull-right"><button type="submit" id="delete" name="delete" class="btn btn-danger"><?php echo safe_output($language->get('Delete')); ?></button></div>
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

				<p><?php echo $language->get('Name'); ?><br /><input type="text" name="name" value="<?php echo safe_output($category['name']); ?>" /></p>		

				<div class="clearfix"></div>

			</div>
		</div>
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>