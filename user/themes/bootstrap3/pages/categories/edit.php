<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Edit Category'));
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

if (isset($_POST['save'])) {
	if (!empty($_POST['name'])) {
		$categories->edit(
			array(
				'id'				=> $id,
				'name' 				=> $_POST['name']
			)
		);
		header('Location: ' . $config->get('address') . '/categories/view/' . $id . '/');
		exit;
	}
	else {
		$message = $language->get('Name Empty');
	}
}

if (isset($_POST['delete'])) {
	$categories->delete(array('id' => $id, 'user_id' => $auth->get('id')));
	header('Location: ' . $config->get('address') . '/categories/');
	exit;
}

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<script type="text/javascript">
			$(document).ready(function () {
				$('#delete').click(function () {
					if (confirm("<?php echo $language->get('Passwords within this category will become uncategorised. Are you sure you wish to delete this category?'); ?>")){
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
					<h4><?php echo $language->get('Category'); ?></h4>
				</div>
				
				<div class="pull-right">
					<p><button type="submit" name="save" class="btn btn-primary"><?php echo $language->get('Save'); ?></button> 
					<a href="<?php echo $config->get('address'); ?>/categories/view/<?php echo (int) $category['id']; ?>/" class="btn btn-default"><?php echo $language->get('Cancel'); ?></a></p>
				</div>
				
				<div class="clearfix"></div>

				<br />
				
				<div class="pull-right">
					<p><button type="submit" id="delete" class="btn btn-danger" name="delete"><?php echo $language->get('Delete'); ?></button></p>
				</div>
				<div class="clearfix"></div>

			</div>
		</div>

		<div class="col-md-9">
		
			<?php if (isset($message)) { ?>
				<div class="alert alert-danger">
					<?php echo html_output($message); ?>
					<div class="clearfix"></div>
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