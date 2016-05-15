<?php
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

if (SAAS_MODE) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$site->set_title($language->get('Update Info'));
$site->set_config('container-type', 'container');

if ($auth->get('user_level') != 2) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$upgrade 				= new upgrade();

$update_available 		= false;
if ($upgrade->update_available()) {
	$update_available 	= true;
	$update_info 		= $upgrade->get_update_info();
}

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">
	<div class="col-md-3">
		<div class="well well-sm">
			<div class="pull-left">
				<h4><?php echo safe_output($language->get('Update Information')); ?></h4>
			</div>
			<div class="clearfix"></div>

			<?php if ($update_available) { ?>
									
				<label class="left-result"><?php echo safe_output($language->get('Installed Version')); ?></label>
				<p class="right-result">
					<?php echo safe_output($config->get('program_version')); ?>
				</p>
				<div class="clearfix"></div>			

				<label class="left-result"><?php echo safe_output($language->get('Available Version')); ?></label>
				<p class="right-result">
					<?php echo safe_output($update_info['version']); ?>
				</p>
				<div class="clearfix"></div>	

								
				<div class="pull-right">
					<p><a href="<?php echo $config->get('address'); ?>/update/" class="btn btn-default"><?php echo safe_output($language->get('Auto Updater')); ?></a></p>
				</div>	
				<div class="clearfix"></div>	
				
	
				<?php if (isset($update_info['download_url']) && !empty($update_info['download_url'])) { ?>
					<div class="pull-right">
						<p><a href="<?php echo safe_output($update_info['download_url']); ?>" class="btn btn-default"><?php echo safe_output($language->get('Manual Download')); ?></a></p>
					</div>
					<div class="clearfix"></div>
				<?php } ?>	

			<?php } ?>	
			
			<div class="clearfix"></div>

		</div>
	</div>

	<div class="col-md-9">
	
		<?php if ($update_available) { ?>
			<?php if (isset($update_info['message']) && !empty($update_info['message'])) { ?>
			<div class="alert alert-success">
				<a href="#" class="close" data-dismiss="alert">&times;</a>
				<?php echo html_output($update_info['message']); ?>
			</div>
			<?php } ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="pull-left">
						<h1 class="panel-title"><?php echo $language->get('Release Notes'); ?></h1>
					</div>
					<div class="clearfix"></div>
				</div>
				<div class="panel-body">
					<?php				
						if (isset($update_info['release_notes']) && !empty($update_info['release_notes'])) {
							echo html_output($update_info['release_notes']);
						}
					?>					
					<div class="clearfix"></div>
				</div>
			</div>
		<?php } else { ?>
			<div class="alert alert-success">
				<a href="#" class="close" data-dismiss="alert">&times;</a>
				<?php echo $language->get('No updates found.'); ?>
			</div>		
		<?php } ?>
	</div>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>