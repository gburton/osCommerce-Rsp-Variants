<?php
	/*
		$Id: $
		
		osCommerce, Open Source E-Commerce Solutions
		http://www.oscommerce.com
		
		Copyright (c) 2015 osCommerce
		
		This program is free software; you can redistribute it and/or modify
		it under the terms of the GNU General Public License v2 (1991)
		as published by the Free Software Foundation.
	*/
?>
<div class="page-header">
	<h1><?php echo $page_title; ?></h1>
</div>

<div class="contentContainer">
	<div class="contentText">
		<div class="row">
			<?php
				
				if (isset($cPath) && strpos('_', $cPath)) {
					// check to see if there are deeper categories within the current category
					$category_links = array_reverse($cPath_array);
					for($i=0, $n=sizeof($category_links); $i<$n; $i++) {
						$categories_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$category_links[$i] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "'");
						$categories = tep_db_fetch_array($categories_query);
						if ($categories['total'] < 1) {
							// do nothing, go through the loop
							} else {
							$categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$category_links[$i] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by sort_order, cd.categories_name");
							break; // we've found the deepest category the customer is in
						}
					}
					} else {
					$categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$current_category_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by sort_order, cd.categories_name");
				}
				
				while ($categories = tep_db_fetch_array($categories_query)) {
					$cPath_new = tep_get_path($categories['categories_id']);
					echo '<div class="col-xs-6 col-sm-4">';
					echo '  <div class="text-center">';
					echo '    <a href="' . tep_href_link(FILENAME_DEFAULT, $cPath_new) . '">' . tep_image(DIR_WS_IMAGES . $categories['categories_image'], $categories['categories_name'], SUBCATEGORY_IMAGE_WIDTH, SUBCATEGORY_IMAGE_HEIGHT) . '</a>';
					echo '    <div class="caption text-center">';
					echo '      <h5><a href="' . tep_href_link(FILENAME_DEFAULT, $cPath_new) . '">' . $categories['categories_name'] . '</a></h5>';
					echo '    </div>';
					echo '  </div>';
					echo '</div>';
				}
				
				// needed for the new products module shown below
				$new_products_category_id = $current_category_id;
			?>
		</div>
		
		<br>
		
		<?php include(DIR_WS_MODULES . FILENAME_NEW_PRODUCTS); ?>
		
	</div>
</div>
