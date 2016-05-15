<?php 
namespace mrpassword;
use mrpassword as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

?>
		<div class="clearfix"></div>
		<hr>
		<footer>
			<div class="pull-left">
				<p class="text-muted">
					<small>
					<?php echo safe_output($language->get('Copyright')); ?> <span class="glyphicon glyphicon-copyright-mark"></span> <a href="http://www.dalegroup.net/">Dalegroup Pty Ltd</a> <?php echo date('Y'); ?>
					</small>
				</p>
			</div>
			<div class="pull-right">
				<p class="text-muted">
					<small>
						<?php echo safe_output(stop_timer()); ?>
					</small>
			</div>
			<div class="clearfix"></div>
		</footer> 
		<div class="modal fade" id="modal_anchor" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>

	</div><!--/.container-->

	<script type="text/javascript"> 
		$('.dropdown-toggle').dropdown();
	</script>
	
</body>
</html>