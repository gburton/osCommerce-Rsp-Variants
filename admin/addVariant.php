<?php 
	require('includes/application_top.php');
	
	// Were is admin language loaded lol... i must find it and fix this line.
	include('includes/languages/' . $language . '/products_product_add_variants.php');

	// We require the taxclass array, else js calculations not work (gross/netprice/pulldown tax)
	$tax_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
	$tax_class_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS . " order by tax_class_title");
	while ($tax_class = tep_db_fetch_array($tax_class_query)) {
		$tax_class_array[] = array('id' => $tax_class['tax_class_id'],
		'text' => $tax_class['tax_class_title']);
	}
	// Somehow we must get an unique id
	$new_variant_id = date("Ymdhis");	
	
		$new_variant = '<table border="0" width="100%" cellspacing="0" cellpadding="2" class="variantData countVariants" onclick="highlight(this);">' . PHP_EOL;
		
		$new_variant .= '	<tr>' . PHP_EOL;
		$new_variant .= '		<td><ul id="variantValuesList_'.$new_variant_id.'" class="variantsOptionValues"></ul></td>' . PHP_EOL;									
		
		$new_variant .= '		<td colspan="2" class="smallText" align="right">' . tep_get_default_combo($new_variant_id) . '</td>' . PHP_EOL;
		
		$new_variant .= '		<td colspan="2" class="smallText" align="right">' . tep_draw_button(IMAGE_DELETE, 'trash', null, null, array('type' => 'button'), 'onclick="removeVariant(this);"') . '</td>' . PHP_EOL;
		$new_variant .= '	</tr>' . PHP_EOL;
		
		$new_variant .= '	<tr>' . PHP_EOL;
		$new_variant .= '		<td>' . tep_draw_separator('pixel_trans.gif', '1', '10') . '</td>' . PHP_EOL;
		$new_variant .= '	</tr>' . PHP_EOL;

		
		
		$new_variant .= '	<tr>' . PHP_EOL;
		$new_variant .= '		<td class="main">' . TEXT_PRODUCTS_STATUS . '</td>' . PHP_EOL;
		$new_variant .= '		<td>'. tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_radio_field('new_products_status['.$new_variant_id.']', '1', 'checked') . '&nbsp;' . TEXT_PRODUCT_AVAILABLE . '&nbsp;' . tep_draw_radio_field('new_products_status['.$new_variant_id.']', '0', NULL) . '&nbsp;' . TEXT_PRODUCT_NOT_AVAILABLE . '</td>' . PHP_EOL;
		$new_variant .= '	</tr>' . PHP_EOL;
		
		$new_variant .= '	<tr>' . PHP_EOL;
		$new_variant .= '		<td colspan="2">' . tep_draw_separator('pixel_trans.gif', '1', '10') . '</td>' . PHP_EOL;
		$new_variant .= '	</tr>' . PHP_EOL;
		
		$new_variant .= '	<tr>' . PHP_EOL;
		$new_variant .= '		<td class="main">' . TEXT_PRODUCTS_DATE_AVAILABLE . '</td>' . PHP_EOL;
		$new_variant .= '		<td class="main">' . tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('new_products_date_available['.$new_variant_id.']', '', 'class="products_date_available"') . ' <small>(YYYY-MM-DD)</small>' . '</td>' . PHP_EOL;
		$new_variant .= '	</tr>' . PHP_EOL;

		$new_variant .= '	<tr>' . PHP_EOL;
		$new_variant .= '		<td colspan="2">' . tep_draw_separator('pixel_trans.gif', '1', '10') . '</td>' . PHP_EOL;
		$new_variant .= '	</tr>' . PHP_EOL;
		
		$new_variant .= '	<tr bgcolor="#ebebff">' . PHP_EOL;
		$new_variant .= '		<td class="main">' . TEXT_PRODUCTS_TAX_CLASS . '</td>' . PHP_EOL;
		$new_variant .= '		<td class="main">' . tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_pull_down_menu('new_products_tax_class_id['.$new_variant_id.']', $tax_class_array, '', 'id="products_tax_class_'.$new_variant_id.'" onchange="updateGross('.$new_variant_id.')"') . '</td>' . PHP_EOL;
		$new_variant .= '	</tr>' . PHP_EOL;
		
		$new_variant .= '	<tr bgcolor="#ebebff">' . PHP_EOL;
		$new_variant .= '		<td class="main">' . TEXT_PRODUCTS_PRICE_NET . '</td>' . PHP_EOL;
		$new_variant .= '		<td class="main">' . tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('new_products_price['.$new_variant_id.']', '', 'id="products_price_'.$new_variant_id.'" onkeyup="updateGross('.$new_variant_id.')" required="required"') . '</td>' . PHP_EOL;
		$new_variant .= '	</tr>' . PHP_EOL;
		
		$new_variant .= '	<tr bgcolor="#ebebff">' . PHP_EOL;
		$new_variant .= '		<td class="main">' . TEXT_PRODUCTS_PRICE_GROSS . '</td>' . PHP_EOL;
		$new_variant .= '		<td class="main">' . tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('new_products_price_gross['.$new_variant_id.']', '', 'id="products_price_gross_'.$new_variant_id.'" onkeyup="updateNet('.$new_variant_id.')" required="required"') . '</td>' . PHP_EOL;
		$new_variant .= '	</tr>' . PHP_EOL;
		
		$new_variant .= '	<tr>' . PHP_EOL;
		$new_variant .= '		<td colspan="2">' . tep_draw_separator('pixel_trans.gif', '1', '10') . '</td>' . PHP_EOL;
		$new_variant .= '	</tr>' . PHP_EOL;
		
		$new_variant .= '	<script type="text/javascript"> updateGross('.$new_variant_id.'); </script>' . PHP_EOL;
		
		$new_variant .= '	<tr>' . PHP_EOL;
		$new_variant .= '		<td colspan="2">' . tep_draw_separator('pixel_trans.gif', '1', '10') . '</td>' . PHP_EOL;
		$new_variant .= '	</tr>' . PHP_EOL;
		
		$new_variant .= '	<tr>' . PHP_EOL;
		$new_variant .= '		<td class="main">' . TEXT_PRODUCTS_QUANTITY . '</td>' . PHP_EOL;
		$new_variant .= '		<td class="main">' . tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('new_products_quantity['.$new_variant_id.']', '', 'required="required"') . '</td>' . PHP_EOL;
		$new_variant .= '	</tr>' . PHP_EOL;
		
		$new_variant .= '	<tr>' . PHP_EOL;
		$new_variant .= '		<td colspan="2">' . tep_draw_separator('pixel_trans.gif', '1', '10') . '</td>' . PHP_EOL;
		$new_variant .= '	</tr>' . PHP_EOL;
		
		$new_variant .= '	<tr>' . PHP_EOL;
		$new_variant .= '		<td class="main">' . TEXT_PRODUCTS_MODEL . '</td>' . PHP_EOL;
		$new_variant .= '		<td class="main">' . tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('new_products_model['.$new_variant_id.']', '', 'required="required"') . '</td>' . PHP_EOL;
		$new_variant .= '	</tr>' . PHP_EOL;
		
		$new_variant .= '	<tr>' . PHP_EOL;
		$new_variant .= '		<td colspan="2">' . tep_draw_separator('pixel_trans.gif', '1', '10') . '</td>' . PHP_EOL;
		$new_variant .= '	</tr>' . PHP_EOL;
		
		$new_variant .= '	<tr>' . PHP_EOL;
		$new_variant .= '		<td class="main">' . TEXT_PRODUCTS_WEIGHT . '</td>' . PHP_EOL;
		$new_variant .= '		<td class="main">' . tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('new_products_weight['.$new_variant_id.']', '', 'required="required"') . '</td>' . PHP_EOL;
		$new_variant .= '	</tr>' . PHP_EOL;
		
		$new_variant .= '</table>' . PHP_EOL;
	
		$new_variant .= '<table border="0" width="100%" cellspacing="0" cellpadding="2">' . PHP_EOL;
		$new_variant .= '	<tr>' . PHP_EOL;
		$new_variant .= '		<td colspan="2">' . tep_draw_separator('pixel_trans.gif', '1', '10') . '</td>' . PHP_EOL;
		$new_variant .= '	</tr>' . PHP_EOL;	
		$new_variant .= '<table>' . PHP_EOL;
	
	$new_variant .= '<input type="hidden" name="new_variant_id['.$new_variant_id.']" value="'.$new_variant_id.'">' . PHP_EOL;	
	$new_variant .= '<script type="text/javascript">highlight(".variantData:first"); $(".products_date_available").datepicker({dateFormat: "yy-mm-dd"});</script>' . PHP_EOL;
	
	echo $new_variant; 
	
?>