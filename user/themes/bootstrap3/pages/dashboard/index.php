<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Dashboard'));
$site->set_config('container-type', 'container');

//Level 2 is Admin
if ($auth->get('user_level') == 2) {
	$users_count 		= $users->count();
}
$passwords_count 			= $passwords->count(array('user_id' => $auth->get('id'), 'old' => 0));
$categories_array 			= $categories->get(array('user_id' => $auth->get('id'), 'get_other_data' => true, 'global' => 0));
$shares_array 				= $categories->get(array('shared_user_id' => $auth->get('id'), 'get_other_data' => true, 'global' => 0));
$global_categories_array 	= $categories->get(array('shared_user_id' => $auth->get('id'), 'get_other_data' => true, 'global' => 1));

$upgrade 			= new upgrade();

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
			<h4><?php echo $language->get('Dashboard'); ?></h4>
			<p><?php echo $language->get('Welcome to'); ?> <?php echo safe_output($config->get('name')); ?>.</p>
		</div>
		
		<div class="well well-sm">
			<form method="get" action="<?php echo safe_output($config->get('address')); ?>/passwords/">
				<input type="text" class="form-control" placeholder="<?php echo $language->get('Search Personal Passwords'); ?>" name="like_search" value="" size="15" />
								
				<div class="clearfix"></div>				
				<br />
				<div class="pull-right">
					<button type="submit" name="filter" class="btn btn-info"><?php echo $language->get('Search'); ?></button> 
				</div>
				<div class="clearfix"></div>
			</form>
		</div>
		
		<div class="well well-sm">
			<form method="post" action="<?php echo safe_output($config->get('address')); ?>/passwords/add/" role="form">

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
		<?php
			if (($auth->get('user_level') == 2) && (($config->get('database_version') !== $upgrade->get_db_version()) || ($config->get('program_version') !== $upgrade->get_program_version()))) {
		?>
			<div class="alert alert-warning">
				<?php echo html_output($language->get('The database needs upgrading before you continue.')); ?>
				<br />
				<div class="pull-right">
					<p><a href="<?php echo safe_output($config->get('address')); ?>/upgrade/" class="btn btn-default"><?php echo safe_output($language->get('Upgrade')); ?></a></p>
				</div>
				<div class="clearfix"></div>
			</div>
		<?php } ?>
		<div class="well well-sm">
			<h3><span class="glyphicon glyphicon-stats"></span> <?php echo $language->get('Statistics'); ?></h3>
			<ul>
				<li><span class="glyphicon glyphicon-lock"></span> <?php echo $language->get('Personal Passwords'); ?> - <?php echo $passwords_count; ?></li>
				<?php if ($auth->get('user_level') == 2) { ?>
					<li><span class="glyphicon glyphicon-user"></span> <?php echo $language->get('Users'); ?> -  <?php echo $users_count; ?></li>
				<?php } ?>
			</ul>
			<hr />
			<?php if (!empty($global_categories_array)) { ?>
				<h3><span class="glyphicon glyphicon-globe"></span> <?php echo $language->get('Global Categories'); ?> - <?php echo count($global_categories_array); ?></h3>
				<p><?php echo $language->get('Global Categories (and the passwords within) are managed by the System Administrator.'); ?></p>
				<ul>
					<?php foreach ($global_categories_array as $category) { ?>
						<li><a href="<?php echo $config->get('address'); ?>/passwords/global/<?php echo (int) $category['id']; ?>/"><?php echo safe_output($category['name']); ?> (<?php echo $category['password_count']; ?>)</a></li>
					<?php } ?>
					<li><a href="<?php echo $config->get('address'); ?>/passwords/global/"><?php echo $language->get('All Passwords'); ?></a></li>
				</ul>
				<hr />				
			<?php } ?>
			<h3><span class="glyphicon glyphicon-user"></span> <?php echo $language->get('Personal Categories'); ?> - <?php echo count($categories_array); ?></h3>
			<p><?php echo $language->get('Personal Categories allow you to store your own private passwords.'); ?></p>
			<?php if (!empty($categories_array)) { ?>
				<ul>
					<?php foreach ($categories_array as $category) { ?>
						<li><a href="<?php echo $config->get('address'); ?>/passwords/category/<?php echo (int) $category['id']; ?>/"><?php echo safe_output($category['name']); ?> (<?php echo $category['password_count']; ?>)</a></li>
					<?php } ?>
					<li><a href="<?php echo $config->get('address'); ?>/passwords/"><?php echo $language->get('All Passwords'); ?></a></li>
				</ul>
				<hr />
			<?php } ?>
			<?php if (!empty($shares_array)) { ?>
				<h3><span class="glyphicon glyphicon-transfer"></span> <?php echo $language->get('Shares'); ?> - <?php echo count($shares_array); ?></h3>
					<p><?php echo $language->get('Shared Categories are private categories other users have shared specifically with you.'); ?></p>
					<ul>
					<?php foreach ($shares_array as $share) { ?>
						<li><a href="<?php echo $config->get('address'); ?>/passwords/share/<?php echo (int) $share['id']; ?>/"><?php echo safe_output($share['name'] . ' - ' . $share['owner_name']); ?> (<?php echo $share['password_count']; ?>)</a></li>
					<?php } ?>
					<li><a href="<?php echo $config->get('address'); ?>/passwords/share/"><?php echo $language->get('All Passwords'); ?></a></li>
				</ul>
				<hr />
			<?php } ?>
			
		</div>
	</div>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>