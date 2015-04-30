<div class="page-header">
	<h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php
	if ($messageStack->size('product_action') > 0) {
		echo $messageStack->output('product_action');
	}
?>

<div class="contentContainer">
	<div class="alert alert-info">
		<?php echo tep_customer_greeting(); ?>
	</div>
	
	<?php
		if (tep_not_null(TEXT_MAIN)) {
		?>
		
		<div class="contentText">
			<?php echo TEXT_MAIN; ?>
		</div>
		
		<?php
		}
		
		include(DIR_WS_MODULES . FILENAME_NEW_PRODUCTS);
		include(DIR_WS_MODULES . FILENAME_UPCOMING_PRODUCTS);
	?>
	
</div>