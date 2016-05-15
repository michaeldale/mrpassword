<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('View History'));
$site->set_config('container-type', 'container');

$id = (int) $url->get_item();

if ($id == 0) {
	header('Location: ' . $config->get('address') . '/passwords/');
	exit;
}

$passwords_array = $passwords->get(array('id' => $id, 'user_id' => $auth->get('id'), 'get_other_data' => true, 'old' => 1, 'global_or_null' => 0));

if (count($passwords_array) == 1) {
	$password = $passwords_array[0];
}
else {
	header('Location: ' . $config->get('address') . '/passwords/');
	exit;
}

if ((int)$password['category_id'] != 0) {
	$category_names 		= $categories->get(array('user_id' => $auth->get('id'), 'id' => $password['category_id'], 'limit' => 1, 'global' => 0));
}

if (!empty($category_names)) {
	$category_name = $category_names[0]['name'];
}

$fields 		= $custom_fields->get(array('password_id' => $id));
$history_count 	= $passwords->count(array('parent_id' => $id, 'user_id' => $auth->get('id'), 'old' => 1));

$custom_field_groups = $password_custom_fields->get_groups(array('enabled' => 1));

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<div class="col-md-3">
		<div class="well well-sm">
			<div class="pull-left">
				<h4><?php echo $language->get('History'); ?></h4>
			</div>
			<div class="pull-right">
				<p><a href="<?php echo $config->get('address'); ?>/passwords/view/<?php echo (int) $password['parent_id']; ?>/" class="btn btn-default"><?php echo $language->get('View'); ?></a></p>
			</div>
			<div class="clearfix"></div>
			
			<label class="left-result"><?php echo $language->get('Last Used'); ?></label>
			<p class="right-result"><?php echo safe_output(time_ago_in_words($password['date_added'])); ?> <?php echo $language->get('ago'); ?></p>
			<div class="clearfix"></div>
			<label class="left-result"></label><p class="right-result"><?php echo safe_output(($password['date_added'])); ?></p>
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
						<td class="centre"><?php echo safe_output($password['name']); ?></td>
					</tr>
					<?php $i++; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre"><?php echo $language->get('Category'); ?></td>
						<td class="centre"><?php if (isset($category_name)) echo safe_output($category_name); ?></td>
					</tr>
					<?php $i++; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre"><?php echo $language->get('Username'); ?></td>
						<td class="centre"><?php echo safe_output($password['username']); ?></td>
					</tr>
					<?php $i++; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre"><?php echo $language->get('Password'); ?></td>
						<td class="centre"><?php echo safe_output($encryption->decrypt($password['password'])); ?></td>
					</tr>
					<?php $i++; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre"><?php echo $language->get('URL'); ?></td>
						<td class="centre"><a href="<?php echo safe_output($password['url']); ?>"><?php echo safe_output($password['url']); ?></a></td>
					</tr>
					<?php $i++; ?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre"><?php echo $language->get('Description'); ?></td>
						<td class="centre"><?php echo nl2br(safe_output($password['description'])); ?></td>
					</tr>
					<?php $i++; ?>
					
					<?php if (!empty($custom_field_groups)) { ?>
						<?php foreach($custom_field_groups as $custom_field_group) { ?>
							<?php $gfields = $password_custom_fields->get_values(array('password_field_group_id' => $custom_field_group['id'], 'password_id' => (int) $password['id'])); 
							?>
							<?php if (!empty($gfields) && !empty($gfields[0]['value'])) { ?>
								<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
									<td class="centre"><?php echo safe_output($custom_field_group['name']); ?></td>
									<?php if ($custom_field_group['type'] == 'textinput') { ?>
										<td class="centre"><?php echo safe_output($gfields[0]['value']); ?></td>
									<?php } else if ($custom_field_group['type'] == 'textarea') { ?>							
										<td class="centre"><?php echo nl2br(safe_output($gfields[0]['value'])); ?></td>
									<?php } else if ($custom_field_group['type'] == 'dropdown') { 
											$set_fields = $password_custom_fields->get_fields(array('password_field_group_id' => $custom_field_group['id']));
										?>
										<?php foreach ($set_fields as $gfield) { ?>
											<?php if (isset($gfields[0]['value']) && ($gfield['id'] == $gfields[0]['value'])) { ?>
											<td class="centre"><?php echo safe_output($gfield['value']); ?></td>
											<?php }?>
										<?php } ?>
									<?php } ?>
								</tr>
							<?php $i++; ?>
							<?php }?>
						<?php } ?>						
					<?php } ?>	
		
					<?php
					foreach($fields as $field) { ?>
						<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
							<td class="centre"><?php echo safe_output($field['name']); ?></td>
							<td class="centre"><?php echo safe_output($encryption->decrypt($field['value'])); ?></td>
						</tr>
					<?php $i++; } ?>
				</table>
			</div>
			
			<div class="clear"></div>

		</div>
	</div>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>