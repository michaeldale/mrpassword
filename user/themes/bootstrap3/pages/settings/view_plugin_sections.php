<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title('Plugin Sections');

$site->set_config('container-type', 'container');

if ($auth->get('user_level') != 2) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}


$info = $plugins->get_sections();


include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<link href="<?php echo safe_output($config->get('address')); ?>/system/libraries/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
<script type="text/javascript" src="<?php echo safe_output($config->get('address')); ?>/system/libraries/datatables/js/jquery.dataTables.min.js"></script>

<script type="text/javascript">
$(document).ready(function () {
   var table = $('.sts_datatable').DataTable({
		paging: false
	});
		
	$('.dataTables_filter input').attr("placeholder", "Search");
});
</script>
	
<div class="row">
	
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Plugin Sections')); ?></h4>
				</div>	
				<div class="pull-right">
					<p><a class="btn btn-default" href="<?php echo safe_output($config->get('address')); ?>/settings/support/"><?php echo safe_output($language->get('Back')); ?></a></p>
				</div>
				<div class="clearfix"></div>
				
			</div>
		</div>

		<div class="col-md-9">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="pull-left">
						<h1 class="panel-title"><?php echo safe_output($language->get('Sections')); ?></h1>
					</div>
					<div class="clearfix"></div>
				</div>
				<div class="panel-body sts_datatable_contain">
					<?php if (!empty($info)) { ?>
						<section id="no-more-tables">	
							<table class="table table-striped sts_datatable">
								<thead>
									<tr>
										<th><?php echo safe_output($language->get('Section')); ?></th>
										<th><?php echo safe_output($language->get('Priorities')); ?></th>
										<th><?php echo safe_output($language->get('Tasks')); ?></th>
									</tr>
								</thead>
								<?php
									$i = 0;
									foreach ($info as $item) {
								?>
								<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
									<td data-title="<?php echo safe_output($language->get('Section')); ?>"><a href="<?php echo $config->get('address'); ?>/settings/view_plugins_section_info/?section=<?php echo safe_output($item['name']); ?>"><?php echo safe_output($item['name']); ?></a></td>
									<td data-title="<?php echo safe_output($language->get('Priorities')); ?>"><?php echo safe_output($item['priorities']); ?></td>
									<td data-title="<?php echo safe_output($language->get('Tasks')); ?>"><?php echo safe_output($item['tasks']); ?></td>
								</tr>
								<?php $i++; } ?>
							</table>
						</section>					
					<?php } else { ?>
						<div class="alert alert-success">
							<a href="#" class="close" data-dismiss="alert">&times;</a>
							<?php echo safe_output($language->get('Nothing Found')); ?>
						</div>
					<?php } ?>
					
					<div class="clearfix"></div>
				
				</div>
			</div>
			<div class="clearfix"></div>
		</div>
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>