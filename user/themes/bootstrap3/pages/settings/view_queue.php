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

$id = (int) $url->get_item();

if ($id == 0) {
	header('Location: ' . $config->get('address') . '/settings/queue/');
	exit;
}

$items = $queue->get(array('id' => $id));

if (count($items) == 1) {
	$item = $items[0];
	
	if (isset($_POST['delete'])) {
		$queue->delete(array('id' => $item['id']));
		header('Location: ' . $config->get('address') . '/settings/queue/');
		exit;		
	}
}
else {
	header('Location: ' . $config->get('address') . '/settings/queue/');
	exit;
}

$queue_data = print_r(\unserialize(\base64_decode($item['data'])), true);

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">
	<script type="text/javascript">
		$(document).ready(function () {
			$('#delete').click(function () {
				if (confirm("<?php echo safe_output($language->get('Are you sure you wish to delete this queue item?')); ?>")){
					return true;
				}
				else{
					return false;
				}
			});
		});
	</script>
	
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Queue')); ?></h4>
				</div>
				<div class="pull-right">
					<p><a class="btn btn-default" href="<?php echo safe_output($config->get('address')); ?>/settings/queue/"><?php echo safe_output($language->get('View All')); ?></a></p>
				</div>
				<div class="clearfix"></div>

				<label class="left-result"><?php echo safe_output($language->get('Type')); ?></label>
				<p class="right-result"><?php echo safe_output(ucwords($item['type']));  ?></p>
				<div class="clearfix"></div>

				<label class="left-result"><?php echo safe_output($language->get('Added')); ?></label>
				<p class="right-result"><?php echo safe_output($item['date']);  ?></p>
				<div class="clearfix"></div>				

				<label class="left-result"><?php echo safe_output($language->get('Retry')); ?></label>
				<p class="right-result"><?php echo safe_output((int) $item['retry']);  ?></p>
				<div class="clearfix"></div>	
				
				<div class="pull-right">
					<p><button type="submit" id="delete" name="delete" class="btn btn-danger"><?php echo safe_output($language->get('Delete')); ?></button></p>		
				</div>
				
				<div class="clearfix"></div>
				
			</div>
		</div>

		<div class="col-md-9">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="pull-left">
						<h1 class="panel-title"><?php echo safe_output(ucwords($item['type'])); ?></h1>
					</div>
					<div class="clearfix"></div>
				</div>
				<div class="panel-body">
<pre>
<?php echo html_output($queue_data); ?>
</pre>
				
					<div class="clearfix"></div>
				
				</div>
			</div>
			<div class="clearfix"></div>
		</div>
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>