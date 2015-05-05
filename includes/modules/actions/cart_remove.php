<?php
	/*
		$Id: $
		
		osCommerce, Open Source E-Commerce Solutions
		http://www.oscommerce.com
		
		Copyright (c) 2007 osCommerce
		
		This program is free software; you can redistribute it and/or modify
		it under the terms of the GNU General Public License v2 (1991)
		as published by the Free Software Foundation.
	*/
	
	class osC_Actions_cart_remove {
		function execute() {
			global $PHP_SELF, $messageStack, $cart;
			
			
			
			if ( is_numeric($_GET['item']) ) {
				$messageStack->add_session('product_action', sprintf(PRODUCT_REMOVED, $cart->contents[$_GET['item']]['name']), 'warning');			
				$cart->remove($_GET['item']);
			}
			
			if (DISPLAY_CART == 'true') {
				$goto =  FILENAME_SHOPPING_CART;
				$parameters = array('action', 'cPath', 'products_id', 'pid');
				} else {
				$goto = $PHP_SELF;
				$parameters = array('action', 'pid');
				
			}
			
			tep_redirect(tep_href_link($goto, null));
		}
	}
?>