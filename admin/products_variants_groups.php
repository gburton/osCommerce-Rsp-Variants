<?php
	/*
		$Id$
		
		osCommerce, Open Source E-Commerce Solutions
		http://www.oscommerce.com
		
		Copyright (c) 2015 osCommerce
		
		Released under the GNU General Public License
	*/
	
	require('includes/application_top.php');
	$languages = tep_get_languages();
	
	
	// We read the files as variant_groups from directory.		
	function tep_get_variants_groups() {
		global $PHP_SELF;
		
		$variants_array = array();
		$files_array = array();		
		$file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
		
		if ($dir = @dir(DIR_FS_CATALOG_MODULES . 'variants/')) {
			while ($file = $dir->read()) {
				if (!is_dir(DIR_FS_CATALOG_MODULES . 'variants/' . $file)) {
					if (substr($file, strrpos($file, '.')) == $file_extension) {
						$files_array[] = $file;
					}
				}
			}
			sort($files_array);
			$dir->close();
		} 
		foreach ($files_array as $file) {
			$module = substr($file, 0, strrpos($file, '.'));
			$variants_array[] = array('id' => $module,
			'text' => $module);
		}
		
		return $variants_array;
	}	
	
	$action = (isset($_GET['action']) ? $_GET['action'] : '');
	
	$variants_groups_page = (isset($_GET['variants_groups_page']) && is_numeric($_GET['variants_groups_page'])) ? $_GET['variants_groups_page'] : 1;
	
	$page_info = 'variants_groups_page=' . $variants_groups_page;
	
	if (tep_not_null($action)) {
		switch ($action) {
			case 'add_variants_group':
			$variants_group_name_array = $_POST['variants_group_name'];
			$sort_order = tep_db_prepare_input($_POST['sort_order']);
			$variants_group_module = tep_db_prepare_input($_POST['variants_group_module']);
			
			for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
				$variants_group_name = tep_db_prepare_input($variants_group_name_array[$languages[$i]['id']]);
				
				tep_db_query("insert into products_variants_groups (languages_id, title, sort_order, module) values ('" . (int)$languages[$i]['id'] . "', '" . tep_db_input($variants_group_name) . "', '" . tep_db_input($sort_order) . "', '" . tep_db_input($variants_group_module) . "')");
			}
			tep_redirect(tep_href_link('products_variants_groups.php', $page_info));
			break;
			case 'update_variants_group':
			$variants_group_id = tep_db_prepare_input($_POST['variants_group_id']);
			$variants_group_name_array = $_POST['variants_group_name'];
			$sort_order = tep_db_prepare_input($_POST['sort_order']);
			$variants_group_module = tep_db_prepare_input($_POST['variants_group_module']);
			
			for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
				$variants_group_name = tep_db_prepare_input($variants_group_name_array[$languages[$i]['id']]);
				
				tep_db_query("update products_variants_groups set title = '" . tep_db_input($variants_group_name) . "',   sort_order = '" . tep_db_input($sort_order) . "',  module = '" . tep_db_input($variants_group_module) . "' where id = '" . (int)$variants_group_id . "' and languages_id = '" . (int)$languages[$i]['id'] . "'");
			}
			
			tep_redirect(tep_href_link('products_variants_groups.php', $page_info));
			break;
			case 'delete_vgroup':
			$variants_group_id = tep_db_prepare_input($_GET['variants_group_id']);
			
			tep_db_query("delete from products_variants_groups where id = '" . (int)$variants_group_id . "'");
			
			tep_redirect(tep_href_link('products_variants_groups.php', $page_info));
			break;
		}
	}
	
	//We lookup the next id for new inserts
	$next_id = 1;
	$max_options_id_query = tep_db_query("select max(id) + 1 as next_id from products_variants_groups");
	$max_options_id_values = tep_db_fetch_array($max_options_id_query);
	$next_id = $max_options_id_values['next_id'];	
	
	require(DIR_WS_INCLUDES . 'template_top.php');
?>

<!-- listing variants groups-->
<table width="80%" border="0" cellspacing="0" cellpadding="2" style="margin-left: 10%; margin-top: 20px;">
	
	<?php
		//Notice the original variants db tables use: languageS_id instead of language_id
		if ($action == 'delete_variants_group') { // delete variant group
			$options = tep_db_query("select 
			id, 
			title 
			from 
			products_variants_groups 
			where 
			id = '" . (int)$_GET['variants_group_id'] . "' 
			and 
			languages_id = '" . (int)$languages_id . "'");
			
			$variants_group_values = tep_db_fetch_array($options);
		?>
		
		<tr>
			<td class="pageHeading"><?php echo $variants_group_values['title']; ?></td>
		</tr>
		
		<tr>
			<td colspan="3"><?php echo tep_black_line(); ?></td>
		</tr>
		
		<?php
			
			//Notice the original variants db tables use: languageS_id instead of language_id
			$products = tep_db_query("select 
			p.products_id,
			p.parent_id,
			pd.products_name, 
			pvv.title
			FROM 
			products p, 
			products_description pd, 
			products_variants_values pvv, 
			products_variants pv 
			WHERE 
			pd.products_id = p.parent_id 
			AND 
			pvv.languages_id = '" . (int)$languages_id . "' 
			AND 
			pd.language_id = '" . (int)$languages_id . "' 
			AND 
			pv.products_id = p.products_id 
			AND 
			pvv.products_variants_groups_id='" . (int)$_GET['variants_group_id'] . "' 
			AND 
			pvv.id = pv.products_variants_values_id	
			ORDER BY 
			pd.products_name");
			
			// We show that it is not save to delete a variant group and list the products and variant values connected to it.
			// For a nice GUI we might can do this via a pop-up or hide the full page content and show this.
			// If we then click back... we show again the main page via simple show/hidden.
			// It might also be a good idea to have a button in each row of the below data table to redirect to edit that variant value.
			
			if (tep_db_num_rows($products)) {
			?>
			
			<tr class="dataTableHeadingRow">
				<td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_ID; ?></td>
				<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCT; ?></td>
				<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_OPT_VALUE; ?></td>
			</tr>
			
			<tr>
				<td colspan="3"><?php echo tep_black_line(); ?></td>
			</tr>
			
			<?php
				$rows = 0;
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
				<td colspan="3" class="main"><br /><?php echo TEXT_WARNING_OF_DELETE; ?></td>
			</tr>
			<tr>
				<td align="right" colspan="3" class="smallText"><br /><?php echo tep_draw_button(IMAGE_BACK, 'triangle-1-w', tep_href_link('products_variants_groups.php', $page_info)); ?></td>
			</tr>
			<?php
				} else {
				
				//Here we notice the user it is save to delete the selected variant group
			?>
			<tr>
				<td class="main" colspan="3"><br /><?php echo TEXT_OK_TO_DELETE; ?></td>
			</tr>
			<tr>
				<td class="smallText" align="right" colspan="3"><br /><?php echo tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link('products_variants_groups.php', 'action=delete_vgroup&variants_group_id=' . $_GET['variants_group_id'] . '&' . $page_info), 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('products_variants_groups.php', $page_info)); ?></td>
			</tr>
			<?php
			}
			
		}
	?>
	
	<tr>
		<td colspan="5" class="pageHeading"><?php echo HEADING_TITLE_VARIANTS_GROUPS; ?></td>
	</tr>
	
	<tr>
		<td colspan="5" class="smallText" align="right">
			<?php
			    
				//keep in mind : MAX_ROW_LISTS_VARIANT_GROUPS is defined in the language file
				
				$variants_groups_raw = "select * from products_variants_groups where languages_id = '" . (int)$languages_id . "' order by id";
				$variants_groups_split = new splitPageResults($variants_groups_page, MAX_ROW_LISTS_VARIANT_GROUPS, $variants_groups_raw, $variants_query_numrows);
				
				$variants_groups = tep_db_query($variants_groups_raw);
				
				echo $variants_groups_split->display_links($variants_query_numrows, MAX_ROW_LISTS_VARIANT_GROUPS, MAX_DISPLAY_PAGE_LINKS, $variants_groups_page, '', 'variants_groups_page');
			?>
		</td>
	</tr>
	
	<tr>
		<td colspan="5"></td>
	</tr>
	
	<tr class="dataTableHeadingRow">
		<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ID; ?></td>
		<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PVG_TITLE; ?></td>
		<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PVG_MODULE; ?></td>
		<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SORT_ORDER; ?></td>
		<td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_ACTION; ?></td>
	</tr>
	
	<tr>
		<td colspan="5"><?php echo tep_black_line(); ?></td>
	</tr>
	
	<!-- new variant group -->
	<?php
		if ($action != 'edit_variants_groups') {
		?>
		<tr class="attributes-new">
			<?php
				echo '<form name="options" action="' . tep_href_link('products_variants_groups.php', 'action=add_variants_group&' . $page_info) . '" method="post"><input type="hidden" name="id" value="' . $next_id . '">';
				$variant_groups_title_inputs = '';
				for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
					$variant_groups_title_inputs .= $languages[$i]['code'] . ':<input type="text" name="variants_group_name[' . $languages[$i]['id'] . ']" size="20" required><br />';
				}
				$sort_order = '<input type="number" min="10" step="10" value="10" name="sort_order" size="5">';
				
			?>
			<td align="center" class="smallText"><?php echo $next_id; ?></td>
			<td class="smallText"><?php echo $variant_groups_title_inputs; ?></td>
			<td class="smallText"><?php echo tep_draw_pull_down_menu('variants_group_module', tep_get_variants_groups()); ?></td>
			<td class="smallText"><?php echo $sort_order; ?></td>
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
	?>	
	<!-- new variant group eof //-->
	
	<tr>
		<td colspan="5"></td>
	</tr>
	
	<!-- edit variant group-->
	<?php
		
		$rows = 0;
		while ($variants_group_values = tep_db_fetch_array($variants_groups)) {
			$rows++;
		?>
		<tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
			<?php
				if (($action == 'edit_variants_groups') && ($_GET['variants_group_id'] == $variants_group_values['id'])) {
					echo '<form name="option" action="' . tep_href_link('products_variants_groups.php', 'action=update_variants_group&' . $page_info) . '" method="post">';
					$variant_groups_title_inputs = '';
					for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
						$variants_group_query = tep_db_query("select title, module, sort_order from products_variants_groups where id = '" . $variants_group_values['id'] . "' and languages_id = '" . $languages[$i]['id'] . "'");
						$variants_group_name = tep_db_fetch_array($variants_group_query);
						$variant_groups_title_inputs .= $languages[$i]['code'] . ':<input type="text" name="variants_group_name[' . $languages[$i]['id'] . ']" size="20" value="' . $variants_group_name['title'] . '" required><br />';
					}
					$sort_order = '<input type="number" min="10" step="10" name="sort_order" size="20" value="' . $variants_group_name['sort_order'] . '">';
					
				?>
				
				<td align="center" class="smallText"><?php echo $variants_group_values['id']; ?><input type="hidden" name="variants_group_id" value="<?php echo $variants_group_values['id']; ?>"></td>
				<td class="smallText"><?php echo $variant_groups_title_inputs; ?></td>
				<td class="smallText"><?php echo tep_draw_pull_down_menu('variants_group_module', tep_get_variants_groups(), $variants_group_name['module']); ?></td>
				<td class="smallText"><?php echo $sort_order; ?></td>
				<td align="center" class="smallText"><?php echo tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('products_variants_groups.php', $page_info)); ?></td>
				
				<?php
					echo '</form>' . "\n";
					} else {
				?>
				
				<td align="center" class="smallText"><?php echo $variants_group_values["id"]; ?></td>
				<td class="smallText"><?php echo $variants_group_values["title"]; ?></td>
				<td class="smallText"><?php echo $variants_group_values["module"]; ?></td>
				<td class="smallText"><?php echo $variants_group_values["sort_order"]; ?></td>
				<td align="center" class="smallText">
					
					<?php 
						echo tep_draw_button(IMAGE_EDIT, 'pencil', tep_href_link('products_variants_groups.php', 'action=edit_variants_groups&variants_group_id=' . $variants_group_values['id'] . '&' . $page_info)); 
						echo ' ';
						echo tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link('products_variants_groups.php', 'action=delete_variants_group&variants_group_id=' . $variants_group_values['id'] . '&' . $page_info)); 
						echo ' ';
						echo tep_draw_button(IMAGE_VARIANTS_VALUES, 'disk', tep_href_link('products_variants_values.php', 'variants_group_id=' . $variants_group_values['id'])); 
					?>
					
				</td>
				
				<?php
				}
			?>
		</tr>
		<?php
		}
	?>
	<!-- edit variant group eof //-->
	
</table>
<!-- listing variants groups eof //-->


<?php
	require(DIR_WS_INCLUDES . 'template_bottom.php');
	require(DIR_WS_INCLUDES . 'application_bottom.php');
?>