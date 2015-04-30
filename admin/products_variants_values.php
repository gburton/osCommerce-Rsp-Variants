<?php
	/*
		$Id$
		
		osCommerce, Open Source E-Commerce Solutions
		http://www.oscommerce.com
		
		Copyright (c) 2014 osCommerce
		
		Released under the GNU General Public License
	*/
	
	require('includes/application_top.php');
	$languages = tep_get_languages();
	
	$action = (isset($_GET['action']) ? $_GET['action'] : '');
	
	// We ONLY want to show results pér variants_group_id
	// For it, we must build an error section: "no results found for variants_group_id"
	if (isset($_GET['variants_group_id']) && tep_not_null($_GET['variants_group_id'])) {
		$variants_group_id	= $_GET['variants_group_id'];
	}
	
	$value_page = (isset($_GET['value_page']) && is_numeric($_GET['value_page'])) ? $_GET['value_page'] : 1;
	
	$page_info = 'variants_group_id=' . $variants_group_id . '&value_page=' . $value_page;
	
	if (tep_not_null($action)) {
		switch ($action) {
			
			case 'add_product_option_values':
			$value_name_array = $_POST['value_name'];
			$variants_value_id = tep_db_prepare_input($_POST['value_id']);
			
			//We getting the value via url, might not be ideal way
			$variants_group_id = tep_db_prepare_input($_GET['variants_group_id']);
			$variants_sort_order = tep_db_prepare_input($_POST['sort_order']);
			
			for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
				$value_name = tep_db_prepare_input($value_name_array[$languages[$i]['id']]);
				
				tep_db_query("insert into products_variants_values (languages_id, products_variants_groups_id, title, sort_order) values ('" . (int)$languages[$i]['id'] . "', '" . tep_db_input($variants_group_id) . "', '" . tep_db_input($value_name) . "', '" . tep_db_input($variants_sort_order) . "')");
			}
			
			tep_redirect(tep_href_link('products_variants_values.php', $page_info));
			break;
			case 'update_variant_value':
			$value_name_array = $_POST['value_name'];
			$variants_value_id = tep_db_prepare_input($_POST['value_id']);
			
			//We getting the value via url, might not be ideal way
			$variants_group_id = tep_db_prepare_input($_GET['variants_group_id']);			
			$variants_sort_order = tep_db_prepare_input($_POST['sort_order']);
			
			for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
				$value_name = tep_db_prepare_input($value_name_array[$languages[$i]['id']]);
				
				tep_db_query("update products_variants_values set products_variants_groups_id = '" . tep_db_input($variants_group_id) . "', title = '" . tep_db_input($value_name) . "',  sort_order = '" . tep_db_input($variants_sort_order) . "'  where id = '" . tep_db_input($variants_value_id) . "' and languages_id = '" . (int)$languages[$i]['id'] . "'");
			}
			
			tep_redirect(tep_href_link('products_variants_values.php', $page_info));
			break;
			case 'delete_value':
			$variants_value_id = tep_db_prepare_input($_GET['value_id']);
			
			tep_db_query("delete from products_variants_values where id = '" . (int)$variants_value_id . "'");
			
			tep_redirect(tep_href_link('products_variants_values.php', $page_info));
			break;
		}
	}
	
	//We lookup the next id for new inserts
	$next_id = 1;
	$max_values_id_query = tep_db_query(" select max(id) + 1 as next_id from products_variants_values ");
	$max_values_id_values = tep_db_fetch_array($max_values_id_query);
	$next_id = $max_values_id_values['next_id'];
	
	require(DIR_WS_INCLUDES . 'template_top.php');
?>

<table width="80%" border="0" cellspacing="0" cellpadding="2" style="margin-left: 10%; margin-top: 20px;">
	<!-- value //-->
	<?php
		if ($action == 'delete_option_value') { // delete product option value
			$variant_values_query = tep_db_query("select 
			id, 
			title 
			from 
			products_variants_values 
			where 
			id = '" . (int)$_GET['value_id'] . "' 
			and 
			languages_id = '" . (int)$languages_id . "'");
			
			$variant_values = tep_db_fetch_array($variant_values_query);
		?>
		<tr>
			<td colspan="3" class="pageHeading"><?php echo $variant_values['title']; ?></td>
		</tr>
		<tr>
			<td><table border="0" width="100%" cellspacing="0" cellpadding="2">
				<tr>
					<td colspan="3"><?php echo tep_black_line(); ?></td>
				</tr>
				<?php
					$products = tep_db_query("select 
					p.products_id,
					p.parent_id,
					pd.products_name, 
					pvv.title 
					from 
					products p, 
					products_variants pv, 
					products_variants_values pvv, 
					products_description pd 
					where 
					pd.products_id = p.parent_id 
					and 
					pd.language_id = '" . (int)$languages_id . "' 
					and 
					pvv.languages_id = '" . (int)$languages_id . "' 
					and 
					pv.products_id = p.products_id 
					and 
					pv.products_variants_values_id='" . (int)$_GET['value_id'] . "' 
					and 
					pvv.id = pv.products_variants_values_id 
					order by 
					pd.products_name");
					
					if (tep_db_num_rows($products)) {
					?>
					
					<tr class="dataTableHeadingRow">
						<td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_ID; ?></td>
						<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCT; ?></td>
						<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_VARIANTS_VALUE_NAME; ?></td>
					</tr>
					
					<tr>
						<td colspan="3"><?php echo tep_black_line(); ?></td>
					</tr>
					
					<?php
						while ($products_values = tep_db_fetch_array($products)) {
							$rows++;
						?>
						
						<tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
							<td align="center" class="smallText"><?php echo $products_values['products_id']; ?></td>
							<td class="smallText"><?php echo $products_values['products_name']; ?></td>
							<td class="smallText"><?php echo $products_values['title']; ?></td>
						</tr>
						
						<?php
						}
					?>
					<tr>
						<td colspan="3"><?php echo tep_black_line(); ?></td>
					</tr>
					<tr>
						<td class="main" colspan="3"><br /><?php echo TEXT_WARNING_OF_DELETE; ?></td>
					</tr>
					<tr>
						<td class="smallText" align="right" colspan="3"><br /><?php echo tep_draw_button(IMAGE_BACK, 'triangle-1-w', tep_href_link('products_variants_values.php', $page_info)); ?></td>
					</tr>
					<?php
						} else {
					?>
					<tr>
						<td class="main" colspan="3"><br /><?php echo TEXT_OK_TO_DELETE; ?></td>
					</tr>
					<tr>
						<td class="smallText" align="right" colspan="3"><br /><?php echo tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link('products_variants_values.php', 'action=delete_value&value_id=' . $_GET['value_id'] . '&' . $page_info), 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('products_variants_values.php', $page_info)); ?></td>
					</tr>
					<?php
					}
				?>
			</table></td>
		</tr>
		
		<?php
			} else {
		?>
		
		<tr>
			<td colspan="5" class="pageHeading"><?php echo HEADING_TITLE_VARIANT_VALUES; ?></td>
		</tr>
		
		<tr>
			<td colspan="5" class="smallText" align="right">
				
				<?php
					
					$variant_values_raw = "select pvv.id, pvv.title, pvv.sort_order, pvg.title as variants_group_title from products_variants_values pvv left join products_variants_groups pvg on pvv.products_variants_groups_id = pvg.id where pvv.products_variants_groups_id = '" . (int)$variants_group_id . "'and pvv.languages_id = '" . (int)$languages_id . "' order by pvv.id";					
					$variant_values_split = new splitPageResults($value_page, MAX_ROW_LISTS_OPTIONS, $variant_values_raw, $variant_values_query_numrows);
					
					$variant_values_query = tep_db_query($variant_values_raw);
					
					// Only to get the product variants group name...bah
					$get_product_variants_group_query = tep_db_query($variant_values_raw);
					$get_product_variants_group_title = tep_db_fetch_array($get_product_variants_group_query);
					
					echo $variant_values_split->display_links($variant_values_query_numrows, MAX_ROW_LISTS_OPTIONS, MAX_DISPLAY_PAGE_LINKS, $value_page, 'variants_group_id=' . $variants_group_id, 'value_page');
					
				?>
			
			</td>
		</tr>
		
		
		<tr class="dataTableHeadingRow">
			<td align="center" class="dataTableHeadingContent"><?php echo TABLE_HEADING_ID; ?></td>
			<td align="center" class="dataTableHeadingContent"><?php echo TABLE_HEADING_VARIANTS_GROUP_NAME; ?></td>
			<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_VARIANTS_VALUE_NAME; ?></td>
			<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SORT_ORDER; ?></td>
			<td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_ACTION; ?></td>
		</tr>
		
		<tr>
			<td colspan="5"><?php echo tep_black_line(); ?></td>
		</tr>
		
		<?php
			if ($action != 'edit_variant_value') {// this alread must say enough.... why we tell on such a page as this, to check if we NOT doing an update to someting
			?>
			
			<tr class="attributes-new">
				<?php
					echo '<form name="values" action="' . tep_href_link('products_variants_values.php', 'action=add_product_option_values&' . $page_info) . '" method="post">';
					
					$inputs = '';
					for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
						$inputs .= $languages[$i]['code'] . ':<input type="text" name="value_name[' . $languages[$i]['id'] . ']" size="15"><br />';
					}
					$sort_order = '<input type="number" min="10" step="10" value="10" name="sort_order" size="5">';
				?>
				
				<td align="center" class="smallText"><?php echo $next_id; ?></td>					
				<td align="center" class="smallText"><?php echo $get_product_variants_group_title['variants_group_title']; ?> </td>
				<td class="smallText"><?php echo $inputs; ?> </td>
				<td class="smallText"><?php echo $sort_order; ?> </td>
				<td align="center" class="smallText"><?php echo tep_draw_button(IMAGE_INSERT, 'plus'); ?></td>
				
				<?php
					echo '</form>';
				?>
				
			</tr>
			
			<tr>
				<td colspan="5"><?php echo tep_black_line(); ?></td>
			</tr>
			
			<?php
			}
			
			$rows = 0;
			while ($variant_values = tep_db_fetch_array($variant_values_query)) {

			$rows++;
			?>
			
			
			
			<tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
				
				<?php
					if (($action == 'edit_variant_value') && ($_GET['value_id'] == $variant_values['id'])) {
						echo '<form name="values" action="' . tep_href_link('products_variants_values.php', 'action=update_variant_value&' . $page_info) . '" method="post">';
						$inputs = '';
						for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
							$variant_value_query = tep_db_query("select title, sort_order from products_variants_values where id = '" . (int)$variant_values['id'] . "' and languages_id = '" . (int)$languages[$i]['id'] . "'");
							$variant_value_result = tep_db_fetch_array($variant_value_query);
							$inputs .= $languages[$i]['code'] . ':<input type="text" name="value_name[' . $languages[$i]['id'] . ']" size="15" value="' . $variant_value_result['title'] . '"><br />';
						}
                        $sort_order = '<input type="number" min="10" step="10" name="sort_order" size="20" value="' . $variant_value_result['sort_order'] . '">';		
						
					?>
					
				    <td align="center" class="smallText"><?php echo $variant_values['id']; ?><input type="hidden" name="value_id" value="<?php echo $variant_values['id']; ?>"></td>
                    <td align="center" class="smallText"><?php echo $variant_values['variants_group_title']; ?> </td>
					<td class="smallText"><?php echo $inputs; ?></td>
					<td class="smallText"><?php echo  $sort_order; ?></td>
					<td align="center" class="smallText"><?php echo tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('products_variants_values.php', $page_info)); ?></td>
					
					<?php
						echo '</form>';
						} else {
					?>
					
					<td align="center" class="smallText"><?php echo $variant_values["id"]; ?></td>
					<td align="center" class="smallText"><?php echo $variant_values['variants_group_title'];; ?></td>
					<td class="smallText"><?php echo  $variant_values['title']; ?></td>
					<td class="smallText"><?php echo  $variant_values['sort_order']; ?></td>
					<td align="center" class="smallText"><?php echo tep_draw_button(IMAGE_EDIT, 'document', tep_href_link('products_variants_values.php', 'action=edit_variant_value&value_id=' . $variant_values['id'] . '&' . $page_info)) . tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link('products_variants_values.php', 'action=delete_option_value&value_id=' . $variant_values['id'] . '&' . $page_info)); ?></td>
					
					<?php
					}
					
				}
			?>
		</tr>
        <?php
		}
	?>
</table>

<?php
	require(DIR_WS_INCLUDES . 'template_bottom.php');
	require(DIR_WS_INCLUDES . 'application_bottom.php');
?>