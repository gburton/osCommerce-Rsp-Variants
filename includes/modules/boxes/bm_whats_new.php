<?php
	/*
		$Id$
		
		osCommerce, Open Source E-Commerce Solutions
		http://www.oscommerce.com
		
		Copyright (c) 2010 osCommerce
		
		Released under the GNU General Public License
	*/
	
	class bm_whats_new {
		var $code = 'bm_whats_new';
		var $group = 'boxes';
		var $title;
		var $description;
		var $sort_order;
		var $enabled = false;
		
		function bm_whats_new() {
			$this->title = MODULE_BOXES_WHATS_NEW_TITLE;
			$this->description = MODULE_BOXES_WHATS_NEW_DESCRIPTION;
			
			if ( defined('MODULE_BOXES_WHATS_NEW_STATUS') ) {
				$this->sort_order = MODULE_BOXES_WHATS_NEW_SORT_ORDER;
				$this->enabled = (MODULE_BOXES_WHATS_NEW_STATUS == 'True');
				
				$this->group = ((MODULE_BOXES_WHATS_NEW_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
			}
		}
		
		function execute() {
			global $currencies, $oscTemplate;
			
			$Qnew = tep_random_select("select products_id from products where parent_id = '0' and products_status = '1' order by products_date_added desc limit " . MAX_RANDOM_SELECT_NEW);

			if ( $Qnew['products_id'] ) {
				$osC_WhatsNew = new osC_Product($Qnew['products_id']);
				
				$WhatsNewID = $osC_WhatsNew->getMasterID();
				$WhatsNewName = $osC_WhatsNew->getTitle();
				$WhatsNewPrice = $osC_WhatsNew->getPriceFormated(true);
				$WhatsNewImage = $osC_WhatsNew->getImage();				
			}
		
			
			ob_start();
			include(DIR_WS_MODULES . 'boxes/templates/whats_new.php');
			$data = ob_get_clean();
			
			$oscTemplate->addBlock($data, $this->group);
		
		}
	
	
    function isEnabled() {
		return $this->enabled;
	}
	
    function check() {
		return defined('MODULE_BOXES_WHATS_NEW_STATUS');
	}
	
    function install() {
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable What\'s New Module', 'MODULE_BOXES_WHATS_NEW_STATUS', 'True', 'Do you want to add the module to your shop?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Placement', 'MODULE_BOXES_WHATS_NEW_CONTENT_PLACEMENT', 'Left Column', 'Should the module be loaded in the left or right column?', '6', '1', 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_BOXES_WHATS_NEW_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
	}
	
    function remove() {
		tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
	}
	
    function keys() {
		return array('MODULE_BOXES_WHATS_NEW_STATUS', 'MODULE_BOXES_WHATS_NEW_CONTENT_PLACEMENT', 'MODULE_BOXES_WHATS_NEW_SORT_ORDER');
	}
}

