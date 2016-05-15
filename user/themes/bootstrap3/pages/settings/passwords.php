<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Password Settings'));
$site->set_config('container-type', 'container');

if ($auth->get('user_level') != 2) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$custom_field_groups		= $password_custom_fields->get_groups();
$global_categories_array	= $categories->get(array('get_other_data' => true, 'global' => 1));


include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">	
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Password Settings')); ?></h4>
				</div>
				
				<div class="clearfix"></div>
								
					
			</div>
		</div>
		<div class="col-md-9">	
			
			<a name="custom_fields"></a>

			<div class="pull-left">
				<h4><?php echo safe_output($language->get('Global Custom Fields')); ?></h4>
			</div>
			
			<div class="pull-right">
				<p><a href="<?php echo safe_output($config->get('address')); ?>/settings/add_custom_field/" class="btn btn-default"><?php echo $language->get('Add'); ?></a></p>
			</div>
				
			<div class="clearfix"></div>
		
			<div class="table-responsive">
				<table class="table table-striped">
					<thead>
						<tr>
							<th><?php echo $language->get('Name'); ?></th>
							<th><?php echo $language->get('Type'); ?></th>
							<th><?php echo $language->get('Enabled'); ?></th>
						</tr>
					</thead>
					
					<tbody>

						<?php $i = 0; 
							foreach($custom_field_groups as $custom_field_group) { ?>
							<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
								<td class="centre"><a href="<?php echo safe_output($config->get('address')); ?>/settings/edit_custom_field/<?php echo (int) $custom_field_group['id']; ?>/"><?php echo safe_output($custom_field_group['name']); ?></a></td>
								<td class="centre">
								<?php
									switch($custom_field_group['type']) {
										case 'textinput':
											echo $language->get('Text Input');
										break;

										case 'textarea':
											echo $language->get('Text Area');
										break;

										case 'dropdown':
											echo $language->get('Drop Down');
										break;

										case 'date':
											echo $language->get('Date');
										break;

										case 'datetime':
											echo $language->get('Date & Time');
										break;
										
										case 'checkbox':
											echo $language->get('Checkbox');
										break;
									}
								?>						
								</td>
								<td class="centre"><?php if ($custom_field_group['enabled'] == '0') { echo $language->get('No'); } else { echo $language->get('Yes'); } ?></td>
							</tr>
						<?php $i++; } ?>
					</tbody>
				</table>
			</div>


			<a name="global_categories"></a>

			<div class="pull-left">
				<h4><?php echo safe_output($language->get('Global Password Categories')); ?></h4>
			</div>
			
			<div class="pull-right">
				<p><a href="<?php echo safe_output($config->get('address')); ?>/settings/add_global_category/" class="btn btn-default"><?php echo $language->get('Add'); ?></a></p>
			</div>
				
			<div class="clearfix"></div>
		
			<div class="table-responsive">
				<table class="table table-striped">
					<thead>
						<tr>
							<th><?php echo $language->get('Name'); ?></th>
							<th><?php echo $language->get('Users'); ?></th>
						</tr>
					</thead>
					
					<tbody>
						<?php $i = 0; 
							foreach($global_categories_array as $item) { ?>
							<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
								<td class="centre"><a href="<?php echo safe_output($config->get('address')); ?>/settings/view_global_category/<?php echo (int) $item['id']; ?>/"><?php echo safe_output($item['name']); ?></a></td>								
								<td class="centre"><?php echo safe_output($item['share_count']); ?></td>
							</tr>
						<?php $i++; } ?>
					</tbody>
				</table>
			</div>
			<div class="clearfix"></div>


		</div>
	
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>