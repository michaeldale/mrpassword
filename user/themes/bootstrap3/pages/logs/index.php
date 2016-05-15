<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Logs'));
$site->set_config('container-type', 'container');

if ($auth->get('user_level') != 2) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if (isset($_GET['page']) && (int) $_GET['page'] != 0) {
	$page = (int) $_GET['page'];
}
else {
	$page = 1;
}

$get_array = array();

$get_array['limit']				= 50;
$get_array['get_other_data']	= true;

$page_array_temp 			= paging_start(array('page' => $page, 'limit' => $get_array['limit']));
$get_array['offset'] 		= $page_array_temp['offset'];

if (isset($_GET['filter'])) {
	if (isset($_GET['event_severity']) && !empty($_GET['event_severity'])) {
		$get_array['event_severity'] = $_GET['event_severity'];
	}
	if (isset($_GET['event_source']) && !empty($_GET['event_source'])) {
		$get_array['event_source'] = $_GET['event_source'];
	}
	if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
		$get_array['user_id'] = $_GET['user_id'];
	}
	if (isset($_GET['like_search']) && !empty($_GET['like_search'])) {
		$get_array['like_search'] = $_GET['like_search'];
	}
}

$logs_array = $log->get($get_array);

$page_array 				= paging_finish(array('events' => count($logs_array), 'limit' => $get_array['limit'], 'next_page' => $page_array_temp['next_page']));
$next_page 					= $page_array['next_page'];
$previous_page 				= $page_array_temp['previous_page'];

$page_url 		= $config->get('address') 	. '/logs/' . '?filter=&amp;limit=' . (int)$get_array['limit'];
			
if (isset($_GET['event_severity']) && !empty($_GET['event_severity'])) {
	$page_url .= '&amp;event_severity=' . safe_output($_GET['event_severity']);
}		
if (isset($_GET['event_source']) && !empty($_GET['event_source'])) {
	$page_url .= '&amp;event_source=' . safe_output($_GET['event_source']);
}
				
$page_previous 	= $page_url . '&amp;page=' . (int)$previous_page;
$page_next 		= $page_url . '&amp;page=' . (int)$next_page;

$users_array = $users->get();

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<div class="col-md-3">
		<div class="well well-sm">
			<h4><?php echo safe_output($language->get('Logs')); ?></h4>
		</div>
		
		<div class="well well-sm">
			<form method="get" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">
		
				<div class="form-group">		
					<input type="text" class="form-control" placeholder="<?php echo safe_output($language->get('Search')); ?>" name="like_search" value="<?php if (isset($get_array['like_search'])) echo safe_output($get_array['like_search']); ?>" size="10" />
				</div>
				
				<div class="clearfix"></div>
			
				<label class="left-result"><?php echo safe_output($language->get('Severity')); ?></label>
				
				<p class="right-result">
				<select name="event_severity">
					<option value=""></option>
					<option value="notice"<?php if (isset($get_array['event_severity']) && ($get_array['event_severity'] == 'notice')) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('Notice')); ?></option>
					<option value="warning"<?php if (isset($get_array['event_severity']) && ($get_array['event_severity'] == 'warning')) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('Warning')); ?></option>
					<option value="error"<?php if (isset($get_array['event_severity']) && ($get_array['event_severity'] == 'error')) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('Error')); ?></option>
				</select>
				</p>
				<div class="clearfix"></div>

				<label class="left-result"><?php echo safe_output($language->get('Source')); ?></label>
				<p class="right-result">
				<select name="event_source">
					<option value=""></option>
					<option value="auth"<?php if (isset($get_array['event_source']) && ($get_array['event_source'] == 'auth')) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('Authentication')); ?></option>
					<option value="cron"<?php if (isset($get_array['event_source']) && ($get_array['event_source'] == 'cron')) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('Cron')); ?></option>
					<option value="mailer"<?php if (isset($get_array['event_source']) && ($get_array['event_source'] == 'mailer')) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('Email')); ?></option>
					<option value="settings"<?php if (isset($get_array['event_source']) && ($get_array['event_source'] == 'settings')) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('Settings')); ?></option>					
					<option value="storage"<?php if (isset($get_array['event_source']) && ($get_array['event_source'] == 'storage')) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('Storage')); ?></option>
					<option value="passwords"<?php if (isset($get_array['event_source']) && ($get_array['event_source'] == 'passwords')) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('Passwords')); ?></option>					
					<option value="users"<?php if (isset($get_array['event_source']) && ($get_array['event_source'] == 'user')) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('Users')); ?></option>
				</select>
				</p>
				
				<div class="clearfix"></div>
				
				<label class="left-result"><?php echo safe_output($language->get('User')); ?></label>
				<p class="right-result">
					<select name="user_id" id="user_id">
						<option value="">&nbsp;</option>
							<?php foreach ($users_array as $user) { ?>
								<option value="<?php echo (int) $user['id']; ?>"<?php if (isset($get_array['user_id']) && ($get_array['user_id'] == $user['id'])) { echo ' selected="selected"'; } ?>><?php echo safe_output(ucwords($user['name'])); ?></option>
							<?php } ?>
					</select>
				</p>
				<div class="clearfix"></div>		
				
				<br />
				<div class="pull-right"><p><button type="submit" name="filter" class="btn btn-info"><?php echo safe_output($language->get('Filter')); ?></button> <a href="<?php echo safe_output($config->get('address')); ?>/logs/" class="btn btn-default"><?php echo safe_output($language->get('Clear')); ?></a></p></div>
				<div class="clearfix"></div>
			</form>
		</div>
	</div>

	<div class="col-md-9">				
		<div class="pull-right">
			<ul class="pagination pagination-sm">
				<li><a href="<?php echo $page_previous; ?>">&laquo; <?php echo safe_output($language->get('Previous')); ?></a></li>
				<li><a href=""><?php echo (int)$page; ?></a></li>
				<li><a href="<?php echo $page_next; ?>"><?php echo safe_output($language->get('Next')); ?> &raquo;</a></li>
			</ul>
		</div>	
		<div class="clearfix"></div>
				
		<div class="table-responsive">
			<table class="table table-striped">
				<thead>
					<tr>
						<th><?php echo safe_output($language->get('Added')); ?></th>
						<th><?php echo safe_output($language->get('User')); ?></th>
						<th><?php echo safe_output($language->get('IP Address')); ?></th>
						<th><?php echo safe_output($language->get('Description')); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
						$i = 0;
						foreach ($logs_array as $log_item) {
					?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td data-title="Added" class="centre"><a href="<?php echo $config->get('address'); ?>/logs/view/<?php echo (int) $log_item['id']; ?>/"><?php echo safe_output(time_ago_in_words($log_item['event_date'])); ?> <?php echo safe_output($language->get('ago')); ?></a></td>
						<td data-title="User" class="centre"><a href="<?php echo $config->get('address'); ?>/users/view/<?php echo (int) $log_item['user_id']; ?>/"><?php echo safe_output(ucwords($log_item['name'])); ?></a></td>
						<td data-title="IP Address" class="centre"><?php echo safe_output($log_item['event_ip_address']); ?></td>
						<td data-title="Description" class="centre"><?php echo html_output($log_item['event_description']); ?></td>
					</tr>
					<?php $i++; } ?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="pull-right">
		<ul class="pagination pagination-sm">
			<li><a href="<?php echo $page_previous; ?>">&laquo; <?php echo safe_output($language->get('Previous')); ?></a></li>
			<li><a href=""><?php echo (int)$page; ?></a></li>
			<li><a href="<?php echo $page_next; ?>"><?php echo safe_output($language->get('Next')); ?> &raquo;</a></li>
		</ul>
	</div>	
	<div class="clearfix"></div>

</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>