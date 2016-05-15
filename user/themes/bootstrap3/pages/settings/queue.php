<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title('Queue');
$site->set_config('container-type', 'container');

if ($auth->get('user_level') != 2) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if (isset($_POST['clear_queue'])) {
	$queue->delete();
}

$items = $queue->get();

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

<div class="row-fluid">
	
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Queue')); ?></h4>
				</div>
				
				<div class="clearfix"></div>


				<div class="pull-right">
					<p><button type="submit" id="clear_queue" name="clear_queue" class="btn btn-danger"><?php echo safe_output($language->get('Clear Queue')); ?></button></p>
				</div>
				
				<div class="clearfix"></div>

			</div>
		</div>

		<div class="col-md-9">
			<table class="table table-striped table-bordered">
				<thead>
					<tr>
						<th><?php echo safe_output($language->get('Added')); ?></th>
						<th><?php echo safe_output($language->get('Type')); ?></th>
						<th><?php echo safe_output($language->get('Retry')); ?></th>
					</tr>
				</thead>
				<?php foreach($items as $item) { ?>
					<tr>
						<td><a href="<?php echo safe_output($config->get('address')); ?>/settings/view_queue/<?php echo (int) $item['id']; ?>/"><?php echo safe_output(nice_datetime($item['date'])); ?></a></td>
						<td><?php echo safe_output(ucwords($item['type'])); ?></td>
						<td><?php echo safe_output((int)($item['retry'])); ?></td>
					</tr>
				<?php } ?>			
			</table>
			<div class="clearfix"></div>
		</div>
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>