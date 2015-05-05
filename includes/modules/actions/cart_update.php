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
	
	class osC_Actions_cart_update {
		function execute() {
			global $PHP_SELF, $cart;
			
			if ( isset($_POST['products']) && is_array($_POST['products']) && !empty($_POST['products']) ) {
				foreach ( $_POST['products'] as $item_id => $quantity ) {
					if ( !is_numeric($item_id) || !is_numeric($quantity) ) {
						return false;
					}
					
					$cart->update($item_id, $quantity);
					
				}
			}
			
			if (DISPLAY_CART == 'true') {
				$goto =  FILENAME_SHOPPING_CART;
				$parameters = array('action', 'cPath', 'products_id', 'pid');
				} else {
				$goto = $PHP_SELF;
				$parameters = array('action', 'pid');
				
			}
			tep_redirect(tep_href_link($goto, tep_get_all_get_params($parameters)));
		}
	}
?>
