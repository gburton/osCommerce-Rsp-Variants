<?php
	/*
		$Id$
		
		osCommerce, Open Source E-Commerce Solutions
		http://www.oscommerce.com
		
		Copyright (c) 2014 osCommerce
		
		Released under the GNU General Public License
	*/
	
	require('includes/application_top.php');
	
	require(DIR_WS_CLASSES . 'currencies.php');
	$currencies = new currencies();
	
	$action = (isset($_GET['action']) ? $_GET['action'] : '');
	
	if (tep_not_null($action)) {
		switch ($action) {

			case 'insert_product':
			case 'update_product':

				//We renamed original product_id for tep_get_pvariants($parameters_products_id)
				if (isset($_GET['pID'])) {
				    
					$parameters_products_id = tep_db_prepare_input($_GET['pID']);
				    
					// We remove all variant values for each product_id where parent_id = $parameters_products_id
					tep_truncate_products_variants_values($parameters_products_id);
				}
                // We check if a product_variant is set to be the default selected in product_info.php
				$defaultCombo = tep_db_prepare_input($_POST['default_combo']);
				
				//We check for variant products, if not exist we ignore
				if (isset($_POST['new_variant_id'])) $new_vproducts = tep_db_prepare_input($_POST['new_variant_id']);				

				if ($action == 'update_product'){			
					
					// Before we post, we must first exclude existing product variants we want to be removed.
					if (isset($_POST['check_products_id'])) $check_products_id = tep_db_prepare_input($_POST['check_products_id']);
					
					if (!empty($check_products_id)){
						tep_db_query('delete from products where parent_id = "' . (int)$parameters_products_id . '" and products_id not in("' . implode('", "', $check_products_id) . '")');
					}elseif(empty($check_products_id)){
						tep_db_query('delete from products where parent_id = "' . (int)$parameters_products_id . '"');
					}
				}
				
				// $n=sizeof($vproducts) needs to be bigger as 0, else the code not runs
	            if ($action == 'insert_product') {
                    
					$vproducts = 1;				
				
				}elseif($action == 'update_product') {
					
					//We check for variant products, if not has any, simply return default product_id (general.php)
					$vproducts = tep_get_pvariants($parameters_products_id);			
				}
				
				// We itterate over any product variants INCLUDING the original product_id, "$vproducts" does that for us.
				// else if action = insert_product we set the size of $vproducts to 1
				for ($i=0, $n=sizeof($vproducts); $i<$n; $i++) {
					
				    // We not want to write AGAIN the code chunck for the sql_array when action= insert_product, so we just check what the $action is
					if($action == 'update_product') {
						
						$products_id = $vproducts[$i]['variant_products_id'];			
					
					}else{
					    $products_id = 0;
					}
					
					$products_date_available[$products_id] = tep_db_prepare_input($_POST['products_date_available'][$products_id]);
                    $products_date_available[$products_id] = (date('Y-m-d') < $products_date_available[$products_id]) ? $products_date_available[$products_id] : 'null';
			
					$sql_data_array = array('products_quantity' => (int)tep_db_prepare_input($_POST['products_quantity'][$products_id]),
					'products_model' => tep_db_prepare_input($_POST['products_model'][$products_id]),
					'products_price' => tep_db_prepare_input($_POST['products_price'][$products_id]),
					'products_date_available' => $products_date_available[$products_id],
					'products_weight' => (float)tep_db_prepare_input($_POST['products_weight'][$products_id]),
					'products_status' => tep_db_prepare_input($_POST['products_status'][$products_id]),
					'products_tax_class_id' => tep_db_prepare_input($_POST['products_tax_class_id'][$products_id]),
					'manufacturers_id' => (int)tep_db_prepare_input($_POST['manufacturers_id'][$products_id]));


					if ($action == 'insert_product') {
						$insert_sql_data = array('products_date_added' => 'now()');
						
						$sql_data_array = array_merge($sql_data_array, $insert_sql_data);
					
						tep_db_perform('products', $sql_data_array);
						$products_id = tep_db_insert_id();
					
						// When we insert a new product PLUS new variants, we MUST have a parent_id for reference.
						if (empty($parameters_products_id))	$parameters_products_id = $products_id;
					
						//tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int)$products_id . "', '" . (int)$current_category_id . "')");
					}elseif($action == 'update_product') {
						
						$update_sql_data = array('products_last_modified' => 'now()');
						
						$sql_data_array = array_merge($sql_data_array, $update_sql_data);					
						var_dump($sql_data_array);
						tep_db_perform('products', $sql_data_array, 'update', "products_id = '" . (int)$products_id . "'");					
					}
                }


				// We prepare an array to store the REAL products_id's returned from database via tep_db_insert_id();
				$products_db_id = array();
				
				// We check if we clicked to add new variant products
				if (isset($new_vproducts)) {
				
					foreach($new_vproducts as $new_products_id){
				
						$products_date_available = tep_db_prepare_input($_POST['new_products_date_available'][$new_products_id]);
						
						$products_date_available = (date('Y-m-d') < $products_date_available) ? $products_date_available : 'null';
				
						$new_sql_data_array = array('parent_id' => (int)$parameters_products_id,
						'products_quantity' => (int)tep_db_prepare_input($_POST['new_products_quantity'][$new_products_id]),
						'products_model' => tep_db_prepare_input($_POST['new_products_model'][$new_products_id]),
						'products_date_added' => 'now()',
						'products_price' => tep_db_prepare_input($_POST['new_products_price'][$new_products_id]),
						'products_date_available' => $products_date_available,
						'products_weight' => (float)tep_db_prepare_input($_POST['new_products_weight'][$new_products_id]),
						'products_status' => tep_db_prepare_input($_POST['new_products_status'][$new_products_id]),
						'products_tax_class_id' => tep_db_prepare_input($_POST['new_products_tax_class_id'][$new_products_id]),
						'manufacturers_id' => (int)tep_db_prepare_input($_POST['new_manufacturers_id'][$new_products_id]));
						
						// We insert each new variant product to the db
						tep_db_perform('products', $new_sql_data_array);
						
						// The returned $products_org_id replaces the $new_products_id
						$products_org_id = tep_db_insert_id();
						$products_db_id[$new_products_id] = $products_org_id ;

					}

					// We go tell parent product_id, we have variants
					tep_has_variants($parameters_products_id);
				}
				
				// We go check parent product_id IF we have any product_variants at all, if not, set has_child to 0
				tep_has_still_variants($parameters_products_id);

				// Standard data for products description
				$languages = tep_get_languages();
				for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
					
					$language_id = $languages[$i]['id'];

					$sql_data_array = array('products_name' => tep_db_prepare_input($_POST['products_name'][$language_id]),
										  'products_description' => tep_db_prepare_input($_POST['products_description'][$language_id]),
										  'products_url' => tep_db_prepare_input($_POST['products_url'][$language_id]));

					if ($action == 'insert_product') {
						$insert_sql_data = array('products_id' => $products_id,
												 'language_id' => $language_id);

						$sql_data_array = array_merge($sql_data_array, $insert_sql_data);

						tep_db_perform('products_description', $sql_data_array);
					
					} elseif ($action == 'update_product') {
						
						tep_db_perform('products_description', $sql_data_array, 'update', "products_id = '" . (int)$parameters_products_id . "' and language_id = '" . (int)$language_id . "'");
					
					}
				}
				
								foreach($defaultCombo as $default_combo_id => $value){
								    
									if (array_key_exists($default_combo_id,$products_db_id)){
								        $default_combo_id = $products_db_id[$default_combo_id];
							        }
								}

				
				// We go insert the assigned variant_values to the corresponding product_id's
				// Remember we DELETED already existing values, we simply re-insert them again
				if ( ( isset($_POST['products_variants_values_id']) && !empty($_POST['products_variants_values_id']) ) ){

					foreach ($_POST['products_variants_values_id'] as $products_id => $value){
						
						foreach ($_POST['products_variants_values_id'][$products_id] as $products_variants_values_id => $value){

							// We let lookup if there is a match between the virtual id's (created when clicked +insert), and the previous builded array $products_db_id
							// When there is a match, we have the correct products_id to be inserted into products_variant db table.
							if (array_key_exists($products_id,$products_db_id)){
								$products_id = $products_db_id[$products_id];
							}
							
							if($products_id == $default_combo_id){
                                $default_combo = 1;
							}else{
							    $default_combo = 0;
							}
							// We re-insert all pér product_id, some variant_values could been deleted or changed, that is why.					
							tep_db_query("insert into products_variants (products_id, products_variants_values_id, default_combo) values ('" . (int)$products_id . "', '" . (int)$products_variants_values_id . "', '" . (int)$default_combo . "')");	
						
						}	
				
					}
				}
				
			    /*
				$pi_sort_order = 0;
				$piArray = array(0);
				
				foreach ($_POST_FILES as $key => $value) {
					// Update existing large product images
					if (preg_match('/^products_image_large_([0-9]+)$/', $key, $matches)) {
						$pi_sort_order++;
						
						$sql_data_array = array('htmlcontent' => tep_db_prepare_input($_POST['products_image_htmlcontent_' . $matches[1]]),
						'sort_order' => $pi_sort_order);
						
						$t = new upload($key);
						$t->set_destination(DIR_FS_CATALOG_IMAGES);
						if ($t->parse() && $t->save()) {
							$sql_data_array['image'] = tep_db_prepare_input($t->filename);
						}
						
						tep_db_perform(TABLE_PRODUCTS_IMAGES, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "' and id = '" . (int)$matches[1] . "'");
						
						$piArray[] = (int)$matches[1];
						} elseif (preg_match('/^products_image_large_new_([0-9]+)$/', $key, $matches)) {
						// Insert new large product images
						$sql_data_array = array('products_id' => (int)$products_id,
						'htmlcontent' => tep_db_prepare_input($_POST['products_image_htmlcontent_new_' . $matches[1]]));
						
						$t = new upload($key);
						$t->set_destination(DIR_FS_CATALOG_IMAGES);
						if ($t->parse() && $t->save()) {
							$pi_sort_order++;
							
							$sql_data_array['image'] = tep_db_prepare_input($t->filename);
							$sql_data_array['sort_order'] = $pi_sort_order;
							
							tep_db_perform(TABLE_PRODUCTS_IMAGES, $sql_data_array);
							
							$piArray[] = tep_db_insert_id();
						}
					}
				}
				
				$product_images_query = tep_db_query("select image from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$products_id . "' and id not in (" . implode(',', $piArray) . ")");
				if (tep_db_num_rows($product_images_query)) {
					while ($product_images = tep_db_fetch_array($product_images_query)) {
						$duplicate_image_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_IMAGES . " where image = '" . tep_db_input($product_images['image']) . "'");
						$duplicate_image = tep_db_fetch_array($duplicate_image_query);
						
						if ($duplicate_image['total'] < 2) {
							if (file_exists(DIR_FS_CATALOG_IMAGES . $product_images['image'])) {
								@unlink(DIR_FS_CATALOG_IMAGES . $product_images['image']);
							}
						}
					}
					
					tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$products_id . "' and id not in (" . implode(',', $piArray) . ")");
				}
				
				if (USE_CACHE == 'true') {
					tep_reset_cache_block('categories');
					tep_reset_cache_block('also_purchased');
				}*/
				
				tep_redirect(tep_href_link('products_product_add_variants.php', 'pID=' . $parameters_products_id . '&action=new_product'));
	        break;
		}
	}
	
	require(DIR_WS_INCLUDES . 'template_top.php');


	if ($action == 'new_product') {
		$parameters = array(
		'products_name' => '', 
		'products_description' => '', 
		'products_url' => '',
		'products_id' => '', 
		'parent_id' => '', 		
		'products_quantity' => '', 
		'products_model' => '', 
		'products_price' => '', 
		'products_weight' => '', 
		'products_weight_class' => '', 		
		'products_date_added' => '', 
		'products_last_modified' => '', 
		'products_status' => '', 
		'products_tax_class_id' => '', 
		'manufacturers_id' => '', 
		'has_children'	=> '',
		'products_keyword' => '', 
		'products_tags' => '', 
		'products_date_available' => '',		
		'products_larger_images' => array());
		
		$pInfo = new objectInfo($parameters);
		
		if (isset($_GET['pID']) && empty($_POST)) {
			
			$product_query = tep_db_query("select pd.products_name, pd.products_description, pd.products_url, p.products_id, p.parent_id, p.products_quantity, p.products_model, p.products_price, p.products_weight, p.products_weight_class, p.products_date_added, p.products_last_modified,  p.products_status, p.products_tax_class_id, p.manufacturers_id, p.has_children, pd.products_keyword, pd.products_tags, date_format(p.products_date_available, '%Y-%m-%d') as products_date_available from products p, products_description pd where p.parent_id = 0 and p.products_id = '" . (int)$_GET['pID'] . "' and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "'");			
			$product = tep_db_fetch_array($product_query);
			
			$pInfo->objectInfo($product);
			
			$product_images_query = tep_db_query("select id, image, htmlcontent, sort_order from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$product['products_id'] . "' order by sort_order");
			while ($product_images = tep_db_fetch_array($product_images_query)) {
				$pInfo->products_larger_images[] = array('id' => $product_images['id'],
				'image' => $product_images['image'],
				'htmlcontent' => $product_images['htmlcontent'],
				'sort_order' => $product_images['sort_order']);
			}
		}
		
		// We check if product has variant products
		if (($pInfo->has_children == 1) && empty($_POST)) {		
			
			// We go lookup product variants data
			$vparameters = array(
			'products_id' => '', 
			'products_quantity' => '', 
			'products_model' => '', 
			'products_price' => '', 
			'products_weight' => '', 
			'products_weight_class' => '', 		
			'products_date_added' => '', 
			'products_last_modified' => '', 
			'products_status' => '', 
			'products_tax_class_id' => '', 
			'products_date_available' => '');
			
			$pvInfo = new objectInfo($vparameters);


			
			$product_variants_query = tep_db_query("select products_id, products_quantity, products_model, products_price, products_weight, products_weight_class, products_date_added, products_last_modified, products_status, products_tax_class_id,  date_format(products_date_available, '%Y-%m-%d') as products_date_available from products where parent_id = '" . (int)$_GET['pID'] . "'");			
	
		}
		
		// We MUST get an internal for the javascript's to work
		if($pInfo->products_id == '') $pInfo->products_id = 0;

		$manufacturers_array = array(array('id' => '', 'text' => TEXT_NONE));
		$manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS . " order by manufacturers_name");
		while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
			$manufacturers_array[] = array('id' => $manufacturers['manufacturers_id'],
			'text' => $manufacturers['manufacturers_name']);
		}
		
		$tax_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
		$tax_class_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS . " order by tax_class_title");
		while ($tax_class = tep_db_fetch_array($tax_class_query)) {
			$tax_class_array[] = array('id' => $tax_class['tax_class_id'],
			'text' => $tax_class['tax_class_title']);
		}
		
		$languages = tep_get_languages();
		
		$form_action = (isset($_GET['pID'])) ? 'update_product' : 'insert_product';
	?>
<script type="text/javascript">
	var tax_rates = new Array();
	<?php
		for ($i=0, $n=sizeof($tax_class_array); $i<$n; $i++) {
			if ($tax_class_array[$i]['id'] > 0) {
				echo 'tax_rates["' . $tax_class_array[$i]['id'] . '"] = ' . tep_get_tax_rate_value($tax_class_array[$i]['id']) . ';' . "\n";
			}
		}
	?>

	function doRound(x, places) {
		return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
	}
	
	function getTaxRate(ID) {

		var rCal = document.getElementById("products_tax_class_"+ID);
		var selected_value = rCal.selectedIndex;				
		var parameterVal = rCal[selected_value].value;
		
		if ( (parameterVal > 0) && (tax_rates[parameterVal] > 0) ) {
			return tax_rates[parameterVal];
			} else {
			return 0;
		}
	}
	
	function updateGross(ID) {
		
		var taxRate = getTaxRate(ID);
		var grossValue = document.getElementById("products_price_"+ID).value;
		
		if (taxRate > 0) {
			grossValue = grossValue * ((taxRate / 100) + 1);
		}
		document.getElementById("products_price_gross_"+ID).value = doRound(grossValue, 4);
		
	}
	
	function updateNet(ID) {
		
		var taxRate = getTaxRate(ID);
		var netValue = document.getElementById("products_price_gross_"+ID).value;
		
		if (taxRate > 0) {
			netValue = netValue / ((taxRate / 100) + 1);
		}
		document.getElementById("products_price_"+ID).value = doRound(netValue, 4);			
	}
</script>
<script>	
	function addVariant(){
		$.post('addVariant.php',
			function(data, textStatus) {
				newVariant = $('<div />').html(data);	
				$(newVariant).insertBefore('.variantData:first');
				
				countVariants();
			}
		);
		

	}
	
	function removeVariant(element){
		$(element).closest("table").remove();
		countVariants();
		
		setTimeout(function() {
			
			if($( "table" ).hasClass( "variantData" ) && !$( "table" ).hasClass( "highlight" )){
				highlight('.variantData:first');
			}
			
		}, 100);		

	}
	
	$(function() {
		highlight('.variantData:first');
	});
	
	function highlight(element){
		$("table").removeClass("highlight");
		$('ul.variantsOptionValues').removeClass("activeTags");
		$(element).toggleClass('highlight');
		$(element).find('ul.variantsOptionValues').toggleClass('activeTags');
}
	function countVariants(){
		var countVariants = $('.countVariants').length;

		if(countVariants == 0){
			$('td.primaryDetail select').prop('disabled', false);
			$('td.primaryDetail input').prop('disabled', false);
		}
		if(countVariants >= 1){
			$('td.primaryDetail select').prop('disabled', true);
			$('td.primaryDetail input').prop('disabled', true);
		}	
	}
	
	$(function() {
		countVariants();
	});
	
	$(document).on('change', 'input.default_combo', function() {
		if($(this).is(':checked')){
			$(this).prop('checked', true).attr('checked', 'checked');
			$('input.default_combo').not(this).prop('checked', false).removeAttr('checked');			
		}else{
			$('input.default_combo').prop('checked', false).removeAttr('checked');	
		}
		
	});

	$(function() {
	
$('#noVariantOptions').dialog({
	autoOpen: false,
	resizable: false,
	draggable: false,
	modal: true,
	buttons: {Close: function() {
				$(this).dialog('close');
				}
			}
	});	
	});	
	
	$(document).on('submit', 'body form', function(e) {	
		var test = 'false';
		$(".variantsOptionValues").each(function () {

			if ($(this).find("li.tagit-choice").length >= 1) {
				//we do nothing
			} else {
				e.preventDefault();
				$('#noVariantOptions').dialog('open');
			}
				
		});
    });	
</script>

<table border="0" width="100%" cellspacing="0" cellpadding="2">
	<tr>
		<td><table border="0" width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<td class="pageHeading"><?php echo sprintf(TEXT_NEW_PRODUCT, tep_output_generated_category_path($current_category_id)); ?></td>
				<td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
			</tr>
		</table></td>
	</tr>
	<tr>
		<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
	</tr>
</table>

<div id="tabs" style="overflow:hidden;">
	<?php echo tep_draw_form('new_product', 'products_product_add_variants.php', 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . '&action=' . $form_action, 'post', 'enctype="multipart/form-data"'); ?>

		<ul>
			<li><a href="#tabs-1">General</a></li>
			<li><a href="#tabs-2">Images</a></li>
			<li><a href="#tabs-3">Variants</a></li>
			<li><a href="#tabs-4">Categories</a></li>
		</ul>
		<div id="tabs-1">			
			
			<table border="0" width="100%" cellspacing="0" cellpadding="2">
			
				<tr>	
					<td><table border="0" cellspacing="0" cellpadding="2">						
						
						<?php
							for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
							?>
							<tr>
								<td class="main"><?php if ($i == 0) echo TEXT_PRODUCTS_NAME; ?></td>
								<td class="main"><?php echo tep_image(tep_catalog_href_link(DIR_WS_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('products_name[' . $languages[$i]['id'] . ']', (empty($pInfo->products_id) ? '' : tep_get_vproducts_name($pInfo->products_id, $languages[$i]['id']))); ?></td>
							</tr>							
							<tr>
								<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
							</tr>							
							<tr>
								<td class="main" valign="top"><?php if ($i == 0) echo TEXT_PRODUCTS_DESCRIPTION; ?></td>
								<td><table border="0" cellspacing="0" cellpadding="0">
									<tr>
										<td class="main" valign="top"><?php echo tep_image(tep_catalog_href_link(DIR_WS_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name']); ?>&nbsp;</td>
										<td class="main"><?php echo tep_draw_textarea_field('products_description[' . $languages[$i]['id'] . ']', 'soft', '70', '15', (empty($pInfo->products_id) ? '' : tep_get_vproducts_description($pInfo->products_id, $languages[$i]['id']))); ?></td>
									</tr>
								</table></td>
							</tr>							
							<tr>
								<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
							</tr>							
							<tr>
								<td class="main"><?php if ($i == 0) echo TEXT_PRODUCTS_URL . '<br /><small>' . TEXT_PRODUCTS_URL_WITHOUT_HTTP . '</small>'; ?></td>
								<td class="main"><?php echo tep_image(tep_catalog_href_link(DIR_WS_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('products_url[' . $languages[$i]['id'] . ']', (isset($products_url[$languages[$i]['id']]) ? stripslashes($products_url[$languages[$i]['id']]) : tep_get_vproducts_url($pInfo->products_id, $languages[$i]['id']))); ?></td>
							</tr>							
							
							<?php
							}
						?>
						<tr>
							<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
						</tr>
						<tr>
							<td class="main"><?php echo TEXT_PRODUCTS_MANUFACTURER; ?></td>
							<td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_pull_down_menu('manufacturers_id['. $pInfo->products_id .']', $manufacturers_array, $pInfo->manufacturers_id); ?></td>
						</tr>						
						<tr>
							<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
						</tr>
					</table></td>
					<td><table border="0" cellspacing="0" cellpadding="2" class="defaultProductData">
							<tr>
								<td class="main"><?php echo TEXT_PRODUCTS_STATUS; ?></td>
								<td>
									<?php 
										if ($pInfo->products_status == 1) { 
								
											echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_radio_field('products_status['. $pInfo->products_id .']', '1', 'checked') . '&nbsp;' . TEXT_PRODUCT_AVAILABLE . '&nbsp;' . tep_draw_radio_field('products_status['. $pInfo->products_id .']', '0', NULL) . '&nbsp;' . TEXT_PRODUCT_NOT_AVAILABLE; 
											
											}else{
											
											echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_radio_field('products_status['. $pInfo->products_id .']', '1', NULL) . '&nbsp;' . TEXT_PRODUCT_AVAILABLE . '&nbsp;' . tep_draw_radio_field('products_status['. $pInfo->products_id .']', '0', 'checked') . '&nbsp;' . TEXT_PRODUCT_NOT_AVAILABLE;
										}
									?>
								</td>
							</tr>
							<tr>
								<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
							</tr>
							<tr>
								<td class="main"><?php echo TEXT_PRODUCTS_DATE_AVAILABLE; ?></td>
								<td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_date_available['. $pInfo->products_id .']', $pInfo->products_date_available, 'class="products_date_available"') . ' <small>(YYYY-MM-DD)</small>'; ?></td>
							</tr>
							<tr>
								<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
							</tr>							
							<tr bgcolor="#ebebff">
								<td class="main"><?php echo TEXT_PRODUCTS_TAX_CLASS; ?></td>
								<td class="main primaryDetail"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_pull_down_menu('products_tax_class_id['. $pInfo->products_id .']', $tax_class_array, $pInfo->products_tax_class_id, 'id="products_tax_class_' . $pInfo->products_id . '" onchange="updateGross(' . $pInfo->products_id . ')"'); ?></td>
							</tr>
							<tr bgcolor="#ebebff">
								<td class="main"><?php echo TEXT_PRODUCTS_PRICE_NET; ?></td>
								<td class="main primaryDetail"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_price['. $pInfo->products_id .']', $pInfo->products_price, 'id="products_price_' . $pInfo->products_id . '" onkeyup="updateGross(' . $pInfo->products_id . ')"'); ?></td>
							</tr>
							<tr bgcolor="#ebebff">
								<td class="main"><?php echo TEXT_PRODUCTS_PRICE_GROSS; ?></td>
								<td class="main primaryDetail"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_price_gross['. $pInfo->products_id .']', $pInfo->products_price, 'id="products_price_gross_' . $pInfo->products_id . '" onkeyup="updateNet(' . $pInfo->products_id . ')"'); ?></td>
							</tr>
							<tr>
								<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
							</tr>
							<script type="text/javascript"> updateGross(<?php echo $pInfo->products_id; ?>); </script>
							
							<tr>
								<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
							</tr>
							<tr>
								<td class="main"><?php echo TEXT_PRODUCTS_QUANTITY; ?></td>
								<td class="main primaryDetail"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_quantity['. $pInfo->products_id .']', $pInfo->products_quantity); ?></td>
							</tr>
							<tr>
								<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
							</tr>
							<tr>
								<td class="main"><?php echo TEXT_PRODUCTS_MODEL; ?></td>
								<td class="main primaryDetail"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_model['. $pInfo->products_id .']', $pInfo->products_model); ?></td>
							</tr>
							<tr>
								<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
							</tr>							
							<tr>
								<td class="main"><?php echo TEXT_PRODUCTS_WEIGHT; ?></td>
								<td class="main primaryDetail"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_weight['. $pInfo->products_id .']', $pInfo->products_weight); ?></td>
							</tr>							
							<tr>
								<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
							</tr>												
					</table></td>					
					
					
				</tr>
				<tr>
					<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
				</tr>
			</table>
			
		</div>

		<div id="tabs-2">
			<table border="0" width="100%" cellspacing="0" cellpadding="2">
				
				<tr>
					<td><table border="0" cellspacing="0" cellpadding="2">
					<td class="main" valign="top"><?php echo TEXT_PRODUCTS_IMAGE; ?></td>
					<td class="main" style="padding-left: 30px;">
						<div><?php echo '<strong>' . TEXT_PRODUCTS_MAIN_IMAGE . ' <small>(' . SMALL_IMAGE_WIDTH . ' x ' . SMALL_IMAGE_HEIGHT . 'px)</small></strong><br />' . (tep_not_null($pInfo->products_image) ? '<a href="' . HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . $pInfo->products_image . '" target="_blank">' . $pInfo->products_image . '</a> &#124; ' : '') . tep_draw_file_field('products_image'); ?></div>
						
						<ul id="piList">
							<?php
								$pi_counter = 0;
								
								foreach ($pInfo->products_larger_images as $pi) {
									$pi_counter++;
									
									echo '<li id="piId' . $pi_counter . '" class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s" style="float: right;"></span><a href="#" onclick="showPiDelConfirm(' . $pi_counter . ');return false;" class="ui-icon ui-icon-trash" style="float: right;"></a><strong>' . TEXT_PRODUCTS_LARGE_IMAGE . '</strong><br />' . tep_draw_file_field('products_image_large_' . $pi['id']) . '<br /><a href="' . HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . $pi['image'] . '" target="_blank">' . $pi['image'] . '</a><br /><br />' . TEXT_PRODUCTS_LARGE_IMAGE_HTML_CONTENT . '<br />' . tep_draw_textarea_field('products_image_htmlcontent_' . $pi['id'], 'soft', '70', '3', $pi['htmlcontent']) . '</li>';
								}
							?>
						</ul>
						
						<a href="#" onclick="addNewPiForm();return false;"><span class="ui-icon ui-icon-plus" style="float: left;"></span><?php echo TEXT_PRODUCTS_ADD_LARGE_IMAGE; ?></a>
						
						<div id="piDelConfirm" title="<?php echo TEXT_PRODUCTS_LARGE_IMAGE_DELETE_TITLE; ?>">
							<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo TEXT_PRODUCTS_LARGE_IMAGE_CONFIRM_DELETE; ?></p>
						</div>
						
						<style type="text/css">#piList { list-style-type: none; margin: 0; padding: 0; }#piList li { margin: 5px 0; padding: 2px; }</style>
						

						
					</td>

					</table></td>
				</tr>
				<tr>
					<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
				</tr>
				
			</table>
		</div>
		<div id="tabs-3">
			<table border="0" width="100%" cellspacing="0" cellpadding="2">			
				<tr>
					<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
				</tr>
				<tr>
					<td class="smallText" align="right"><?php echo tep_draw_button(IMAGE_INSERT, 'plusthick', null, null, array('type' => 'button'), 'onclick="addVariant();"'); ?></td>
				</tr>
				<tr>
					<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
				</tr>			
			</table>
			
			<table border="0" width="100%" cellspacing="0" cellpadding="2">			
				<tr>
					<td valign="top" align="left" width="50%">
						<table border="0" width="100%" cellspacing="0" cellpadding="2">
							<tr>
								<td>							
									
									<?php
										$variant_group_tree_raw = tep_db_query("select * from products_variants_groups where languages_id = '" . (int)$languages_id . "'");
										while ($variant_group_tree = tep_db_fetch_array($variant_group_tree_raw)) {
											$variant_group_tree_array[] = array('group_id' => $variant_group_tree['id'],
																				'group_title' => $variant_group_tree['title']);
										
										}
										echo '<select id="sector_select" class="variantsTree" style="width: 80%" size="15">';
										foreach($variant_group_tree_array as $variant_group_tree_id){
										echo '<optgroup id="'.$variant_group_tree_id['group_id'] . '" label="'.$variant_group_tree_id['group_title'] . '">';
										
											$variant_tree_raw = tep_db_query("select * from products_variants_values where products_variants_groups_id = '" . (int)$variant_group_tree_id['group_id'] . "' and languages_id = '" . (int)$languages_id . "' order by sort_order");					
											while ($variant_tree = tep_db_fetch_array($variant_tree_raw)) {

											echo ' <option value="'.$variant_tree['id'] .'">'.$variant_tree['title'] .'</option>';	
											}
										echo '</optgroup>';
											
										}
										echo '</select>';
									?>
									
								</td>
							</tr>
						</table>
					</td>
					<td valign="top" align="right" width="50%">

						<?php
						// We check if have product variants, it is strange we cannot do an ELSE on this IF?
						if (($pInfo->has_children == 1)){ 
					
							while ($product_variants = tep_db_fetch_array($product_variants_query)) {
								
								$pvInfo->objectInfo($product_variants);
								?>
								
								<table border="0" width="100%" cellspacing="0" cellpadding="2" class="variantData countVariants" onclick="highlight(this);">
									<tr>
										<td><?php echo tep_draw_hidden_field('check_products_id['. $pvInfo->products_id .']', $pvInfo->products_id);  ?></td>
									</tr>
									<tr>
										
										<td>
											<ul id="variantValuesList_<?php echo $pvInfo->products_id; ?>" class="variantsOptionValues activeTags"><?php echo tep_get_pvariants_options($pvInfo->products_id); ?></ul>
										</td>									
									
										<td colspan="2" class="smallText" align="right">
	                                        <?php echo tep_get_default_combo($pvInfo->products_id); ?>
										</td>									
										<td colspan="2" class="smallText" align="right">
											<?php echo tep_draw_button(IMAGE_DELETE, 'trash', null, null, array('type' => 'button'), 'onclick="removeVariant(this);"'); ?>
										</td>
									</tr>											
									<tr>
										<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
									</tr>

									<tr>
										<td class="main"><?php echo TEXT_PRODUCTS_STATUS; ?></td>
										<td>
											<?php 
												if ($pvInfo->products_status == 1) { 
										
													echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_radio_field('products_status['. $pvInfo->products_id .']', '1', 'checked') . '&nbsp;' . TEXT_PRODUCT_AVAILABLE . '&nbsp;' . tep_draw_radio_field('products_status['. $pvInfo->products_id .']', '0', NULL) . '&nbsp;' . TEXT_PRODUCT_NOT_AVAILABLE; 
													
													}else{
													
													echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_radio_field('products_status['. $pvInfo->products_id .']', '1', NULL) . '&nbsp;' . TEXT_PRODUCT_AVAILABLE . '&nbsp;' . tep_draw_radio_field('products_status['. $pvInfo->products_id .']', '0', 'checked') . '&nbsp;' . TEXT_PRODUCT_NOT_AVAILABLE;
												}
											?>
										</td>
									</tr>
									<tr>
										<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
									</tr>
									<tr>
										<td class="main"><?php echo TEXT_PRODUCTS_DATE_AVAILABLE; ?></td>
										<td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_date_available['. $pvInfo->products_id .']', $pvInfo->products_date_available, 'class="products_date_available"') . ' <small>(YYYY-MM-DD)</small>'; ?></td>
									</tr>

									<tr>
										<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
									</tr>
									<tr bgcolor="#ebebff">
										<td class="main"><?php echo TEXT_PRODUCTS_TAX_CLASS; ?></td>
										<td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_pull_down_menu('products_tax_class_id['. $pvInfo->products_id .']', $tax_class_array, $pvInfo->products_tax_class_id, 'id="products_tax_class_' . $pvInfo->products_id . '" onchange="updateGross(' . $pvInfo->products_id . ')"'); ?></td>
									</tr>
									<tr bgcolor="#ebebff">
										<td class="main"><?php echo TEXT_PRODUCTS_PRICE_NET; ?></td>
										<td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_price['. $pvInfo->products_id .']', $pvInfo->products_price, 'id="products_price_' . $pvInfo->products_id . '" onkeyup="updateGross(' . $pvInfo->products_id . ')" required="required"'); ?></td>
									</tr>
									<tr bgcolor="#ebebff">
										<td class="main"><?php echo TEXT_PRODUCTS_PRICE_GROSS; ?></td>
										<td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_price_gross['. $pvInfo->products_id .']', $pvInfo->products_price, 'id="products_price_gross_' . $pvInfo->products_id . '" onkeyup="updateNet(' . $pvInfo->products_id . ')" required="required"'); ?></td>
									</tr>
									<tr>
										<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
									</tr>
									<script type="text/javascript"> updateGross(<?php echo $pvInfo->products_id; ?>); </script>
									<tr>
										<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
									</tr>
									<tr>
										<td class="main"><?php echo TEXT_PRODUCTS_QUANTITY; ?></td>
										<td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_quantity['. $pvInfo->products_id .']', $pvInfo->products_quantity, 'required="required"'); ?></td>
									</tr>
									<tr>
										<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
									</tr>
									<tr>
										<td class="main"><?php echo TEXT_PRODUCTS_MODEL; ?></td>
										<td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_model['. $pvInfo->products_id .']', $pvInfo->products_model, 'required="required"'); ?></td>
									</tr>
									<tr>
										<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
									</tr>
									<tr>
										<td class="main"><?php echo TEXT_PRODUCTS_WEIGHT; ?></td>
										<td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_weight['. $pvInfo->products_id .']', $pvInfo->products_weight, 'required="required"'); ?></td>
									</tr>
								</table>
								
								<table border="0" width="100%" cellspacing="0" cellpadding="2">								
									<tr>
										<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
									</tr>
								</table>
								<?php
							}
						} // We try an else here but that fails? So we just use an empty placeholder, so we can add a new variant product via js BEFORE varantData
						?>
						<table border="0" width="100%" cellspacing="0" cellpadding="2" class="variantData">
							<tr hidden>
								<td></td>
							</tr>											
						</table>
					</td>
				</tr>
				<tr>
					<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
				</tr>		
			</table>	

<div id="noVariantOptions" title="<?php echo TEXT_NO_OPTIONS_SELECTED_TITLE; ?>">
   <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo TEXT_NO_OPTIONS_SELECTED; ?></p>
</div>			
		</div>
		<div id="tabs-4">
			
			
		</div>		
		<table border="0" width="100%" cellspacing="0" cellpadding="2">			
			<tr>
				<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
			</tr>
			<tr>
				<td class="smallText" align="right"><?php echo tep_draw_hidden_field('products_date_added', (tep_not_null($pInfo->products_date_added) ? $pInfo->products_date_added : date('Y-m-d'))) . tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('products_product_add_variants.php', 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : ''))); ?></td>
			</tr>
		</table>	


	</form>	
</div>
  

<script type="text/javascript">
	$('#piList').sortable({
		containment: 'parent'
	});
	
	var piSize = <?php echo $pi_counter; ?>;
	
	function addNewPiForm() {
		piSize++;
		
		$('#piList').append('<li id="piId' + piSize + '" class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s" style="float: right;"></span><a href="#" onclick="showPiDelConfirm(' + piSize + ');return false;" class="ui-icon ui-icon-trash" style="float: right;"></a><strong><?php echo TEXT_PRODUCTS_LARGE_IMAGE; ?></strong><br /><input type="file" name="products_image_large_new_' + piSize + '" /><br /><br /><?php echo TEXT_PRODUCTS_LARGE_IMAGE_HTML_CONTENT; ?><br /><textarea name="products_image_htmlcontent_new_' + piSize + '" wrap="soft" cols="70" rows="3"></textarea></li>');
	}
	
	var piDelConfirmId = 0;
	
	$('#piDelConfirm').dialog({
		autoOpen: false,
		resizable: false,
		draggable: false,
		modal: true,
		buttons: {
			'Delete': function() {
				$('#piId' + piDelConfirmId).effect('blind').remove();
				$(this).dialog('close');
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		}
	});
	
	function showPiDelConfirm(piId) {
		piDelConfirmId = piId;
		
		$('#piDelConfirm').dialog('open');
	}
</script>

<script type="text/javascript">
	$('.products_date_available').datepicker({
		dateFormat: 'yy-mm-dd'
	});
</script>
<script>
	// fix jQuery base tag bug
	$.fn.__tabs = $.fn.tabs;
	$.fn.tabs = function (a, b, c, d, e, f) {
		var base = location.href.replace(/#.*$/, '');
		$('ul>li>a[href^="#"]', this).each(function () {
		var href = $(this).attr('href');
		$(this).attr('href', base + href);
	});
	$(this).__tabs(a, b, c, d, e, f);
	};
		
	$(function() {
		$( "#tabs" ).tabs();
	});
	
	$('.variantsTree').change(function() {
		var product_id = $('.activeTags').attr('id').replace('variantValuesList_','');
		
		var selected = $(':selected', this);
		var label_id = selected.parent().attr('id');
		var label = selected.parent().attr('label');
		var value_id = selected.attr('value');	
		var value = selected.text();


        $('.activeTags li[id='+label_id+']').remove();

		$(".activeTags").tagit({listId: label_id, fieldName: 'products_variants_values_id['+product_id+']['+value_id+']['+label_id+']'});
		$(".activeTags").tagit("createTag", label+' : '+value);
	});
</script>
<?php
	}

require(DIR_WS_INCLUDES . 'template_bottom.php');
require(DIR_WS_INCLUDES . 'application_bottom.php');
?>