<?php
	/*
		$Id$
		
		osCommerce, Open Source E-Commerce Solutions
		http://www.oscommerce.com
		
		Copyright (c) 2014 osCommerce
		
		Released under the GNU General Public License
	*/
	$data = array();
	
	if ( (!isset($new_products_category_id)) || ($new_products_category_id == '0') ) {
		$new_products_query = tep_db_query("select p.products_id, p.products_tax_class_id, pd.products_name, if(s.status, s.specials_new_products_price, p.products_price) as products_price, i.image from " . TABLE_PRODUCTS . " p left join products_images i on (p.products_id = i.products_id and i.default_flag = '1') left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.parent_id = '0' and p.products_status = '1' and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' order by p.products_date_added desc limit " . MAX_DISPLAY_NEW_PRODUCTS);
		} else {
		$new_products_query = tep_db_query("select distinct p.products_id, p.products_tax_class_id, pd.products_name, if(s.status, s.specials_new_products_price, p.products_price) as products_price, i.image from " . TABLE_PRODUCTS . " p left join products_images i on (p.products_id = i.products_id and i.default_flag = '1') left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c where p.parent_id = '0' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and c.parent_id = '" . (int)$new_products_category_id . "' and p.products_status = '1' and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' order by p.products_date_added desc limit " . MAX_DISPLAY_NEW_PRODUCTS);
	}
	
	$num_new_products = tep_db_num_rows($new_products_query);
	
	if ($num_new_products > 0) {
		
		$new_prods_content = NULL;
		
		while ($new_products = tep_db_fetch_array($new_products_query)) {
			
			$osC_Product = new osC_Product($new_products['products_id']);
			$data[$osC_Product->getID()] = $osC_Product->getData();
			$data[$osC_Product->getID()]['display_price'] = $osC_Product->getPriceFormated(true);
			$data[$osC_Product->getID()]['display_image'] = $osC_Product->getImage();
		}
		
		foreach ( $data as $product ) {
		
			$new_prods_content .= '<div class="col-sm-6 col-md-4">';
			$new_prods_content .= '  <div class="thumbnail equal-height">';
			$new_prods_content .= '    <a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $product['id']) . '">' . tep_image(DIR_WS_IMAGES . $product['display_image'], $product['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a>';
			$new_prods_content .= '    <div class="caption">';
			$new_prods_content .= '      <p class="text-center"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $product['id']) . '">' . $product['name'] . '</a></p>';
			$new_prods_content .= '      <hr>';
			$new_prods_content .= '      <p class="text-center">' . $product['display_price'] . '</p>';
			$new_prods_content .= '      <div class="text-center">';
			$new_prods_content .= '        <div class="btn-group">';
			$new_prods_content .= '          <a href="' . tep_href_link(FILENAME_PRODUCT_INFO, tep_get_all_get_params(array('action')) . 'products_id=' . $product['id']) . '" class="btn btn-default" role="button">' . SMALL_IMAGE_BUTTON_VIEW . '</a>';
			$new_prods_content .= '          <a href="' . tep_href_link($PHP_SELF, tep_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $product['id']) . '" class="btn btn-success" role="button">' . SMALL_IMAGE_BUTTON_BUY . '</a>';
			$new_prods_content .= '        </div>';
			$new_prods_content .= '      </div>';
			$new_prods_content .= '    </div>';
			$new_prods_content .= '  </div>';
			$new_prods_content .= '</div>';
		}
	?>
	
	<h3><?php echo sprintf(TABLE_HEADING_NEW_PRODUCTS, strftime('%B')); ?></h3>
	
	<div class="row">
		<?php echo $new_prods_content; ?>
	</div>
	
	<?php
	}
?>
