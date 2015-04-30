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
	
	require(DIR_WS_INCLUDES . 'template_top.php');
?>

<table width="80%" border="0" cellspacing="0" cellpadding="2" style="margin-left: 10%; margin-top: 20px;">	
		<tr>
			<td colspan="5" class="pageHeading"><?php echo HEADING_TITLE_VARIANT_VALUES; ?></td>
		</tr>
		
		<tr>
			<td colspan="5" class="smallText">
				
				<?php
					$variant_group_tree_raw = tep_db_query("select * from osc_products_variants_groups where languages_id = '" . (int)$languages_id . "'");
					while ($variant_group_tree = tep_db_fetch_array($variant_group_tree_raw)) {
						$variant_group_tree_array[] = array('group_id' => $variant_group_tree['id'],
															'group_title' => $variant_group_tree['title']);
					
					}
					echo '<select style="width: 100%" size="20">';
					foreach($variant_group_tree_array as $variant_group_tree_id){
					echo '<optgroup label="'.$variant_group_tree_id['group_title'] . '">';
					
						$variant_tree_raw = tep_db_query("select * from osc_products_variants_values where products_variants_groups_id = '" . (int)$variant_group_tree_id['group_id'] . "' and languages_id = '" . (int)$languages_id . "' order by sort_order");					
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

<?php
	require(DIR_WS_INCLUDES . 'template_bottom.php');
	require(DIR_WS_INCLUDES . 'application_bottom.php');
?>