<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php echo safe_output($site->get_title()); ?></title>
	
    <meta name="viewport" content="width=device-width, maximum-scale=1.0" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
	
	
	 <!-- Core theme -->
    <link href="<?php echo $config->get('address'); ?>/user/themes/bootstrap3/core6/css/bootstrap.css" rel="stylesheet">
    <link href="<?php echo $config->get('address'); ?>/user/themes/bootstrap3/core6/css/responsive-tables.css" rel="stylesheet">

    <link href="<?php echo $config->get('address'); ?>/user/themes/bootstrap3/core6/css/font-awesome.min.css" rel="stylesheet">
    <link href="<?php echo $config->get('address'); ?>/user/themes/bootstrap3/core6/stylesheets/theme.css" rel="stylesheet">
    <link href="<?php echo $config->get('address'); ?>/user/themes/bootstrap3/core6/stylesheets/theme-custom.css" rel="stylesheet">

    <!-- Custom theme -->
    <?php if (file_exists(THEMES . '/' . CURRENT_THEME . '/sub/' . CURRENT_THEME_SUB . '/stylesheets/theme.css')) { ?>
        <link href="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/sub/<?php echo safe_output(CURRENT_THEME_SUB); ?>/stylesheets/theme.css" rel="stylesheet">
    <?php } ?>
    <?php if (file_exists(THEMES . '/' . CURRENT_THEME . '/sub/' . CURRENT_THEME_SUB . '/css/font-awesome.min.css')) { ?>
        <link href="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/sub/<?php echo safe_output(CURRENT_THEME_SUB); ?>/css/font-awesome.min.css" rel="stylesheet">
    <?php } ?>

	<link rel="shortcut icon" href="<?php echo $config->get('address'); ?>/favicon.ico" />

	<script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/libraries/js/jquery.js"></script>
	
    <!-- Core JS -->
    <script src="<?php echo $config->get('address'); ?>/user/themes/bootstrap3/core6/js/autocollapse.js" rel="stylesheet"></script>
    <script type="text/javascript" src="<?php echo $config->get('address'); ?>/user/themes/bootstrap3/core6/js/masonry.js"></script>
    <script type="text/javascript" src="<?php echo $config->get('address'); ?>/user/themes/bootstrap3/core6/js/bootstrap.min.js"></script>

    <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/libraries/js/respond.min.js"></script>

    <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/libraries/js/moment.min.js"></script>
    <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/libraries/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
    <link href="<?php echo $config->get('address'); ?>/system/libraries/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet">

	
	<script type="text/javascript">
		var mrp_base_url 			= "<?php echo safe_output($config->get('address')); ?>";
		var mrp_current_theme		= "<?php echo safe_output(CURRENT_THEME); ?>";
		var mrp_current_theme_sub	= "<?php echo safe_output(CURRENT_THEME_SUB); ?>";
	</script>
	
	<?php if ($auth->logged_in()) { ?>
		<script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/password.js"></script>
		<script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/custom_fields.js"></script>
		<script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/global_custom_fields.js"></script>
	<?php } ?>

	<link rel="stylesheet" href="<?php echo $config->get('address'); ?>/system/libraries/chosen/chosen.css" />			
	<script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/libraries/chosen/chosen.jquery.min.js"></script>
	
	<script type="text/javascript"> 
	$(document).ready(function () {
		//Custom Selectmenu
		$('select').chosen();
	});
	</script>
	
	<?php $plugins->run('html_header'); ?>

</head>

<body>
	<?php $plugins->run('body_header'); ?>	
	<nav id="autocollapse" class="navbar navbar-default navbar-fixed-top" role="navigation">
		<div class="navbar-header">
			<a class="navbar-brand" href="<?php echo $config->get('address'); ?>/"><?php echo safe_output($config->get('name')); ?></a>			
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
		</div>
		
		<div class="collapse navbar-collapse">			  		
			<ul class="nav navbar-nav">
			
				<?php $plugins->run('html_header_nav_start'); ?>

				<?php if ($auth->logged_in()) { ?>
					<li<?php if ($url->get_action() == 'dashboard') echo ' class="active"'; ?>><a href="<?php echo $config->get('address'); ?>/dashboard/"><span class="glyphicon glyphicon-th-large"></span> <?php echo safe_output($language->get('Dashboard')); ?></a></li>
					<li<?php if ($url->get_action() == 'passwords') echo ' class="active"'; ?>><a href="<?php echo $config->get('address'); ?>/passwords/"><span class="glyphicon glyphicon-lock"></span> <?php echo safe_output($language->get('Passwords')); ?></a></li>
					<li<?php if ($url->get_action() == 'categories') echo ' class="active"'; ?>><a href="<?php echo $config->get('address'); ?>/categories/"><span class="glyphicon glyphicon-list"></span> <?php echo safe_output($language->get('Categories')); ?></a></li>

					<?php if ($auth->get('user_level') == 2) { ?>
						<li<?php if ($url->get_action() == 'users') echo ' class="active"'; ?>><a href="<?php echo $config->get('address'); ?>/users/"><span class="glyphicon glyphicon-user"></span> <?php echo safe_output($language->get('Users')); ?></a></li>
						<li class="dropdown<?php if ($url->get_action() == 'settings' || $url->get_action() == 'logs') echo ' active'; ?>">
							<a class="dropdown-toggle" data-toggle="dropdown" data-target="#settings" href="<?php echo $config->get('address'); ?>/settings/"><span class="glyphicon glyphicon-cog"></span> <?php echo safe_output($language->get('Settings')); ?> <strong class="caret"></strong></a>							
							<ul class="dropdown-menu">
								<li><a href="<?php echo $config->get('address'); ?>/settings/"><?php echo safe_output($language->get('General')); ?></a></li>
								<li><a href="<?php echo $config->get('address'); ?>/settings/authentication/"><?php echo safe_output($language->get('Authentication')); ?></a></li>
								<li><a href="<?php echo $config->get('address'); ?>/settings/email/"><?php echo safe_output($language->get('Email')); ?></a></li>
								<li><a href="<?php echo $config->get('address'); ?>/settings/passwords/"><?php echo safe_output($language->get('Passwords')); ?></a></li>
								<li><a href="<?php echo $config->get('address'); ?>/settings/plugins/"><?php echo safe_output($language->get('Plugins')); ?></a></li>
								<li><a href="<?php echo $config->get('address'); ?>/logs/"><?php echo safe_output($language->get('Logs')); ?></a></li>
								<?php $plugins->run('html_header_nav_settings'); ?>
							</ul>
						</li>
					<?php } ?>
				<?php } else { ?>
					<li<?php if ($url->get_action() == 'login') echo ' class="active"'; ?>><a href="<?php echo $config->get('address'); ?>/"><span class="glyphicon glyphicon-home"></span> <?php echo safe_output($language->get('Home')); ?></a></li>
				<?php } ?>
				
				<?php $plugins->run('html_header_nav_finish'); ?>
			</ul>
			<?php if ($auth->logged_in()) { ?>
				<ul class="nav navbar-nav navbar-right">						
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-star"></span> <?php echo safe_output(ucwords($auth->get('name'))); ?> <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li><a href="<?php echo $config->get('address'); ?>/profile/"><span class="glyphicon glyphicon-user"></span> <?php echo safe_output($language->get('Profile')); ?></a></li>
							<li class="divider"></li>
							<li><a href="<?php echo $config->get('address'); ?>/logout/"><span class="glyphicon glyphicon-eject"></span> <?php echo safe_output($language->get('Logout')); ?></a></li>

						</ul>
					</li>
				</ul>
			<?php } ?>
		</div><!--/.nav-collapse -->
	</nav>
	
    <div class="<?php echo safe_output($site->get_config('container-type')); ?>">
	