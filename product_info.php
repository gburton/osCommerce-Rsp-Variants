<?php
	/*
		$Id$
		
		osCommerce, Open Source E-Commerce Solutions
		http://www.oscommerce.com
		
		Copyright (c) 2015 osCommerce
		
		Released under the GNU General Public License
	*/
	
	require('includes/application_top.php');
	
	
	//if (!isset($_GET['products_id'])) {
	//	tep_redirect(tep_href_link(FILENAME_DEFAULT));
	//}
	
	require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_PRODUCT_INFO);
	require(DIR_WS_INCLUDES . 'template_top.php');
	
	// PHP < 5.0.2; array_slice() does not preserve keys and will not work with numerical key values, so foreach() is used
	foreach ($_GET as $key => $value) {
		if ( (preg_match('/^[0-9]+(#?([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)*$/', $key) || preg_match('/^[a-zA-Z0-9 -_]*$/', $key)) ) {
			$id = $key;
		}
		
		break;
	}
	if (($id !== false) && osC_Product::checkEntry($id)) {
		
		$osC_Product = new osC_Product($id);	
		
		$osC_Product->incrementCounter();
		
		$products_price = $osC_Product->getPriceFormated(true);
		
		if ( $osC_Product->getDateAvailable() > date('Y-m-d H:i:s')) {
			$products_price .= '<link itemprop="availability" href="http://schema.org/PreOrder" />';
			} elseif ((STOCK_CHECK == 'true') && ($osC_Product->getQuantity() < 1)) {
			$products_price .= '<link itemprop="availability" href="http://schema.org/OutOfStock" />';
			} else {
			$products_price .= '<link itemprop="availability" href="http://schema.org/InStock" />';
		}
		
		$products_price .= '<meta itemprop="priceCurrency" content="' . tep_output_string($currency) . '" />';
		
		$products_name = '<a href="' . tep_href_link('product_info.php', $osC_Product->getKeyword()) . '" itemprop="url"><span itemprop="name">' . $osC_Product->getTitle() . '</span></a>';
		
		if ( $osC_Product->hasModel() ) {
			$products_name .= '<br><small>[<span itemprop="model">' . $osC_Product->getModel() . '</span>]</small>';
		}
	?>
	
	<?php echo tep_draw_form('cart_quantity', tep_href_link($PHP_SELF, $osC_Product->getKeyword() . '&action=cart_add', 'NONSSL'), 'post', 'class="form-horizontal" role="form"'); ?>
	
	<div itemscope itemtype="http://schema.org/Product">
		
		<div class="page-header">
			<h1 class="pull-right" itemprop="offers" itemscope itemtype="http://schema.org/Offer"><?php echo $products_price; ?></h1>
			<h1><?php echo $products_name; ?></h1>
		</div>
		
		<?php
			if ($messageStack->size('product_action') > 0) {
				echo $messageStack->output('product_action');
			}
		?>
		
		<div class="contentContainer">
			<div class="contentText">
				
				<?php
					if ( $osC_Product->hasImage() ) {
						
						echo tep_image(DIR_WS_IMAGES . $osC_Product->getImage(), NULL, NULL, NULL, 'itemprop="image" style="display:none;"');
						
						$photoset_layout = (int)MODULE_HEADER_TAGS_PRODUCT_COLORBOX_LAYOUT;
						
						$pi_query = $osC_Product->getImages();
						$pi_total = $osC_Product->numberOfImages();
						
						if ($pi_total > 0) {
							
						?>
						
						<div class="piGal pull-right" data-imgcount="<?php echo $photoset_layout; ?>">
							
							<?php
								$pi_counter = 0;
								$pi_html = array();
								
								foreach($pi_query as $pi){
									
									$pi_counter++;
									
									if (tep_not_null($pi['htmlcontent'])) {
										$pi_html[] = '<div id="piGalDiv_' . $pi_counter . '">' . $pi['htmlcontent'] . '</div>';
									}
									
									echo tep_image(DIR_WS_IMAGES . $pi['image'], '', '', '', 'id="piGalImg_' . $pi_counter . '"');
								}
								
							?>
							
						</div>
						
						<?php
							if ( !empty($pi_html) ) {
								echo '    <div style="display: none;">' . implode('', $pi_html) . '</div>';
							}
							} else {
						?>
						
						<div class="piGal pull-right">
							<?php echo tep_image(DIR_WS_IMAGES . $osC_Product->getImage(), addslashes($osC_Product->getTitle())); ?>
						</div>
						
						<?php
						}
					}
				?>
				
				<div itemprop="description">
					<?php echo stripslashes($osC_Product->getDescription()); ?>
				</div>
				
				<?php
					if ( $osC_Product->hasVariants() ) {
					?>
					
					<div class="col-md-4" id="variantsBlock">
						
						<?php
							foreach ( $osC_Product->getVariants() as $group_id => $value ) {
								echo osC_Variants::parse($value['module'], $value);
							}
							
							echo osC_Variants::defineJavascript($osC_Product->getVariants(false));
						?>
					</div>
					
					<?php
					}
				?>							
				<div class="col-md-8">
					<table class="table table-condensed table-bordered table-striped">
						<tbody>
							<tr>
								<th class="col-md-3 productInfoKey">Price:</th>
								<td class="productInfoValue"><span id="productInfoPrice"><?php echo $osC_Product->getPriceFormated(true); ?></span> (plus <a href="info.php?shipping">shipping</a>)</td>
							</tr>	  
							<?php
								if ( $osC_Product->hasAttribute('manufacturers') ) {
								?>
								
								<tr>
									<th class="col-md-3 productInfoKey">Manufacturer:</th>
									<td class="productInfoValue"><?php echo $osC_Product->getAttribute('manufacturers'); ?></td>
								</tr>
								
								<?php
								}
							?>
							
							
							<tr>
								<th class="col-md-3 productInfoKey">Model:</th>
								<td class="productInfoValue"><span id="productInfoModel"><?php echo $osC_Product->getModel(); ?></span></td>
							</tr>
							<?php
								if ( $osC_Product->hasAttribute('shipping_availability') ) {
								?>
								
								<tr>
									<th class="col-md-3 productInfoKey">Availability:</th>
									<td class="productInfoValue" id="productInfoAvailability"><?php echo $osC_Product->getAttribute('shipping_availability'); ?></td>
								</tr>
								
								<?php
								}
							?>
							<?php
								if ( $osC_Product->getDateAvailable() > date('Y-m-d H:i:s') ) {
								?>
								
								<tr>
									<th class="productInfoKey">Date Available:</th>
									<td class="productInfoValue alert alert-info"><?php echo sprintf(TEXT_DATE_AVAILABLE, tep_date_long($osC_Product->getDateAvailable())); ?></td>
								</tr>
								
								<?php
								}
							?>
							<?php
								if ( $osC_Product->hasURL() ) {
								?>
								
								<tr>
									<th class="col-md-3 productInfoKey">Products Url:</th>
									<td class="productInfoValue"><?php echo $osC_Product->getUrl(); ?></td>
								</tr>
								
								<?php
								}
							?>							
						</tbody>
					</table>
				</div>
				
				
				<div class="clearfix"></div>
				
			</div>
			
			<?php				
				if ($osC_Product->getReviewsCount() > 0) {
					echo '<span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"><meta itemprop="ratingValue" content="' . $osC_Product->getReviewsAvg() . '" /><meta itemprop="ratingCount" content="' . $osC_Product->getReviewsCount() . '" /></span>';
				}
			?>
			
			<div class="buttonSet row">
				<div class="col-xs-6"><?php echo tep_draw_button(IMAGE_BUTTON_REVIEWS . (($osC_Product->getReviewsCount() > 0) ? ' (' . $osC_Product->getReviewsCount() . ')' : ''), 'glyphicon glyphicon-comment', tep_href_link(FILENAME_PRODUCT_REVIEWS, tep_get_all_get_params())); ?></div>
				<div class="col-xs-6 text-right"><?php echo tep_draw_hidden_field('products_id', $osC_Product->getMasterID()) . tep_draw_button(IMAGE_BUTTON_IN_CART, 'glyphicon glyphicon-shopping-cart', null, 'primary', null, 'btn-success add-cart'); ?></div>
			</div>
			
			<div class="row">
				<?php echo $oscTemplate->getContent('product_info'); ?>
			</div>
			
			<?php
				if ((USE_CACHE == 'true') && empty($SID)) {
					echo tep_cache_also_purchased(3600);
					} else {
					include(DIR_WS_MODULES . FILENAME_ALSO_PURCHASED_PRODUCTS);
				}
			?>
			
		</div>
		
	</div>
</form>
<?php
	if ( $osC_Product->hasVariants() ) {
	?>
	<script>
		var originalPrice = '<?php echo $osC_Product->getPriceFormated(true); ?>';
		var productInfoNotAvailable = '<span id="productVariantCombinationNotAvailable">Not available in this combination. Please select another combination for your order.</span>';
		var productInfoAvailability = '<?php if ( $osC_Product->hasAttribute('shipping_availability') ) { echo addslashes($osC_Product->getAttribute('shipping_availability')); } ?>';
		
		refreshVariants();
	</script>
	
	<?php
	}
	}else {
?>

<div class="contentContainer">
	<div class="contentText">
		<div class="alert alert-warning"><?php echo TEXT_PRODUCT_NOT_FOUND; ?></div>
	</div>
	
	<div class="pull-right">
		<?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'glyphicon glyphicon-chevron-right', tep_href_link(FILENAME_DEFAULT)); ?>
	</div>
</div>

<?php
	
}
require(DIR_WS_INCLUDES . 'template_bottom.php');
require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
