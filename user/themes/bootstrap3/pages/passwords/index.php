<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Passwords'));
$site->set_config('container-type', 'container');

$id = (int) $url->get_item();

if (isset($_GET['action']) && ($_GET['action'] == 'show_all')) {
	$show_all = true;
}
else {
	$show_all = false;
}

if ($id != 0) {
	$paswd_array['category_id'] = $id;
	$category_names 		= $categories->get(array('user_id' => $auth->get('id'), 'id' => $id, 'limit' => 1, 'global' => 0));
	
	if (!empty($category_names)) {
		$category_name = $category_names[0]['name'];
	}
}

//search
$like_search_temp = '';
if (isset($_GET['like_search']) && ($_GET['like_search'] != '')) {
	$paswd_array['like_search']		= $_GET['like_search'];
	$like_search_temp				= $_GET['like_search'];
}

//order by
$order_by_temp = '';
if (isset($_GET['order_by']) && ($_GET['order_by'] != '')) {
	$paswd_array['order_by'] 		= $_GET['order_by'];
	$order_by_temp 					= $_GET['order_by'];
}

//order
$order_temp = '';
if (isset($_GET['order']) && ($_GET['order'] != '')) {
	$paswd_array['order'] 			= $_GET['order'];
	$order_temp 					= $_GET['order'];
}

$paswd_array['get_other_data']		= true;
$paswd_array['global_or_null']		= 0;
$paswd_array['old']					= 0;
$paswd_array['user_id'] 			= $auth->get('id');

$passwords_array 			= $passwords->get($paswd_array);
$categories_array 			= $categories->get(array('user_id' => $auth->get('id'), 'get_other_data' => true, 'global' => 0));
$shares_array 				= $categories->get(array('shared_user_id' => $auth->get('id'), 'get_other_data' => true, 'global' => 0));
$global_categories_array 	= $categories->get(array('shared_user_id' => $auth->get('id'), 'get_other_data' => true, 'global' => 1));

//print_r($passwords_array);

$export_url 	= $config->get('address') 	. '/passwords/export/';

if ($id != 0) {
	$export_url .= (int) $id . '/';
}

$export_url     .= '?like_search=' 			. _s($like_search_temp)
                . '&amp;order_by=' 			. _s($order_by_temp)
                . '&amp;order=' 			. _s($order_temp);

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<script type="text/javascript">
	$(document).ready(function () {
		$('.nav-tabs').button();
		$('.default-toggle').button('toggle');
	});
</script>
<div class="row">

	<div class="col-md-3">
		<div class="well well-sm">
			<h4>
				<?php if (isset($category_name)) { echo safe_output($category_name); } else { ?><?php echo $language->get('Personal Passwords'); ?><?php } ?> (<?php echo count($passwords_array); ?>)
			</h4>
			<?php if (isset($category_name)) { ?>
				<p><?php echo $language->get('This page displays all the passwords stored in the'); ?> <?php echo safe_output($category_name); ?> <?php echo $language->get('category'); ?>.</p>
			<?php } else { ?>
				<p><?php echo $language->get('This page displays all the passwords stored in your account.'); ?></p>			
			<?php }?>
			<br />
		</div>
		<div class="well well-sm">
			<form method="get" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">
				<input type="text" class="form-control" placeholder="<?php echo $language->get('Search'); ?>" name="like_search" value="<?php if (isset($like_search_temp)) echo safe_output($like_search_temp); ?>" size="15" />
				
				<div class="clearfix"></div>
				<br />
				
				<label class="left-result"><?php echo $language->get('Sort By'); ?></label>
				<p class="right-result">
					<select name="order_by">
						<option value=""></option>
						<option value="category"<?php if ($order_by_temp == 'category') echo ' selected="selected"'; ?>><?php echo $language->get('Category'); ?></option>
						<option value="date_added"<?php if ($order_by_temp == 'date_added') echo ' selected="selected"'; ?>><?php echo $language->get('Added'); ?></option>
						<option value="name"<?php if ($order_by_temp == 'name') echo ' selected="selected"'; ?>><?php echo $language->get('Name'); ?></option>
						<option value="username"<?php if ($order_by_temp == 'username') echo ' selected="selected"'; ?>><?php echo $language->get('Username'); ?></option>
					</select>
				</p>
				<div class="clearfix"></div>
				
				<label class="left-result"><?php echo $language->get('Sort Order'); ?></label>
				<p class="right-result">
					<select name="order">
						<option value=""></option>
						<option value="asc"<?php if ($order_temp == 'asc') echo ' selected="selected"'; ?>><?php echo $language->get('Ascending'); ?></option>
						<option value="desc"<?php if ($order_temp == 'desc') echo ' selected="selected"'; ?>><?php echo $language->get('Descending'); ?></option>							
					</select>
				</p>
				<div class="clearfix"></div>				
				<br />
				<div class="pull-right">
					<button type="submit" name="filter" class="btn btn-info"><?php echo $language->get('Filter'); ?></button> 
					<a href="<?php echo safe_output($config->get('address')); ?>/passwords/" class="btn btn-default"><?php echo $language->get('Clear'); ?></a>
				</div>
				<div class="clearfix"></div>
			</form>
		</div>
		<div class="well well-sm">
			<?php if (!empty($global_categories_array)) { ?>
				<h4><span class="glyphicon glyphicon-globe"></span> <?php echo $language->get('Global Categories'); ?> - <?php echo count($global_categories_array); ?></h4>
				<ul>
				<?php foreach ($global_categories_array as $category) { ?>
					<li><a href="<?php echo $config->get('address'); ?>/passwords/global/<?php echo (int) $category['id']; ?>/"><?php echo safe_output($category['name']); ?> (<?php echo $category['password_count']; ?>)</a></li>
				<?php } ?>
				<li><a href="<?php echo $config->get('address'); ?>/passwords/global/"><?php echo $language->get('All Passwords'); ?></a></li>
				</ul>	
			<?php } ?>
			
			<h4><span class="glyphicon glyphicon-user"></span> <?php echo $language->get('Personal Categories'); ?> - <?php echo count($categories_array); ?></h4>
			<?php if (!empty($categories_array)) { ?>
				<ul>
				<?php foreach ($categories_array as $category) { ?>
					<li><a href="<?php echo $config->get('address'); ?>/passwords/category/<?php echo (int) $category['id']; ?>/"><?php echo safe_output($category['name']); ?> (<?php echo $category['password_count']; ?>)</a></li>
				<?php } ?>
				<li><a href="<?php echo $config->get('address'); ?>/passwords/"><?php echo $language->get('All Passwords'); ?></a></li>
				</ul>
			<?php } ?>

			<?php if (!empty($shares_array)) { ?>
			<h4><span class="glyphicon glyphicon-transfer"></span> <?php echo $language->get('Shares'); ?> - <?php echo count($shares_array); ?></h4>
				<ul>
				<?php foreach ($shares_array as $share) { ?>
					<li><a href="<?php echo $config->get('address'); ?>/passwords/share/<?php echo (int) $share['id']; ?>/"><?php echo safe_output($share['name'] . ' - ' . $share['owner_name']); ?> (<?php echo $share['password_count']; ?>)</a></li>
				<?php } ?>
				<li><a href="<?php echo $config->get('address'); ?>/passwords/share/"><?php echo $language->get('All Passwords'); ?></a></li>
				</ul>
			<?php } ?>
		</div>
		<div class="well well-sm">
			<form method="post" action="<?php echo safe_output($config->get('address')); ?>/passwords/add/<?php if ($id != 0) echo (int) $id . '/'; ?>" class="form-inline" role="form">

				<div class="pull-left">
					<h4><?php echo $language->get('Generate Password'); ?></h4>
				</div>
				<div class="pull-right">
				<button class="btn btn-default" type="submit" name="save_password"><?php echo $language->get('Save'); ?></button>

				</div>
				<div class="clearfix"></div>
				<br />					

				<p><input class="form-control generate_field" name="password" placeholder="<?php echo $language->get('Password'); ?>" type="text" value="" autocomplete="off" /></p>		
				
				<div class="btn-group" data-toggle="buttons">
					<label class="btn btn-primary btn-sm">
						<input type="radio" name="password_type" id="option1" value="1"> <?php echo $language->get('Easy'); ?>
					</label>
					<label class="btn btn-primary btn-sm">
						<input type="radio" name="password_type" id="option2" value="2"> <?php echo $language->get('Simple'); ?>
					</label>
					<label class="btn btn-primary btn-sm default-toggle">
						<input type="radio" name="password_type" id="option3" value="3" checked="checked"> <?php echo $language->get('Complex'); ?> 
					</label>
					
				</div>
				<div class="clearfix"></div>
				<br />
				<p><a class="btn btn-info generate_password" href="#"><?php echo $language->get('Generate'); ?></a></p>
	
			
			</form>
		</div>		
	</div>

	<div class="col-md-9">
		<!--<div class="well well-sm">-->

			<div class="pull-left">
				<a href="<?php echo $config->get('address'); ?>/passwords/add/<?php if ($id != 0) echo (int) $id . '/'; ?>" class="btn btn-default btn-sm"><?php echo $language->get('Add'); ?></a>

				<?php if ($id != 0) { ?>
					<?php if ($show_all) { ?>
						<a href="<?php echo $config->get('address'); ?>/passwords/category/<?php echo (int) $id; ?>/" class="btn btn-default btn-sm"><?php echo $language->get('Hide Passwords'); ?></a>
					<?php } else { ?>
						<a href="<?php echo $config->get('address'); ?>/passwords/category/<?php echo (int) $id; ?>/?action=show_all" class="btn btn-default btn-sm"><?php echo $language->get('Show Passwords'); ?></a>
					<?php } ?>
				<?php } else { ?>
					<?php if ($show_all) { ?>
						<a href="<?php echo $config->get('address'); ?>/passwords/" class="btn btn-default btn-sm"><?php echo $language->get('Hide Passwords'); ?></a>
					<?php } else { ?>
						<a href="<?php echo $config->get('address'); ?>/passwords/?action=show_all" class="btn btn-default btn-sm"><?php echo $language->get('Show Passwords'); ?></a>
					<?php } ?>
				<?php } ?>
			</div>
		
			<div class="pull-right">
				<a href="<?php echo _s($export_url); ?>" class="btn btn-success btn-sm">
					<?php echo $language->get('Export'); ?>
				</a>
			</div>

			<div class="clearfix"></div>
			<br />
			<div class="table-responsive">		
				<table class="table table-striped">
					<tr>
						<th><?php echo $language->get('Name'); ?></th>
						<th><?php echo $language->get('Category'); ?></th>
						<th><?php echo $language->get('Username'); ?></th>
						<th><?php echo $language->get('Password'); ?></th>
					</tr>
					<?php
						$i = 0;
						foreach ($passwords_array as $password) {
					?>
					<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
						<td class="centre"><a href="<?php echo $config->get('address'); ?>/passwords/view/<?php echo (int) $password['id']; ?>/"><?php echo safe_output($password['name']); ?></a></td>
						<td class="centre"><?php echo safe_output($password['category_name']); ?></td>
						<td class="centre"><?php echo safe_output($password['username']); ?></td>
						<td class="centre" name="password<?php echo (int) $password['id']; ?>"><?php if ($show_all) { ?><?php echo safe_output($encryption->decrypt($password['password'])); ?><?php } else { ?><a href="#password<?php echo (int) $password['id']; ?>" class="show_password" id="id-<?php echo (int) $password['id']; ?>"><?php echo $language->get('Show'); ?></a><?php } ?></td>
					</tr>
					<?php $i++; } ?>
				</table>
			</div>

			<div class="clearfix"></div>

		<!--</div>-->
	</div>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>