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
	
	class osC_Actions_cart_add {
		function execute() {
			global $PHP_SELF, $cPath, $messageStack, $osC_Session, $cart, $osC_Product;
			
			if ( !isset($osC_Product) ) {
				$id = false;
				
				foreach ( $_GET as $key => $value ) {
					//if ( (is_numeric($key) || preg_match('/^[a-zA-Z0-9 -_]*$/', $key)) && ($key != $osC_Session->getName()) ) {
					if ( (is_numeric($key) || preg_match('/^[a-zA-Z0-9 -_]*$/', $key)) ) {
						$id = $key;
					}
					
					break;
				}
				
				if ( ($id !== false) && osC_Product::checkEntry($id) ) {
					$osC_Product = new osC_Product($id);
				}
			}
			
			if ( isset($osC_Product) ) {
				if ( $osC_Product->hasVariants() ) {
					if ( isset($_POST['variants']) && is_array($_POST['variants']) && !empty($_POST['variants']) ) {
						if ( $osC_Product->variantExists($_POST['variants']) ) {
							$cart->add_cart($osC_Product->getProductVariantID($_POST['variants']));
							} else {
							tep_redirect(tep_href_link(FILENAME_PRODUCT_INFO, $osC_Product->getKeyword()));
							
							return false;
						}
						} else {
						tep_redirect(tep_href_link(FILENAME_PRODUCT_INFO, $osC_Product->getKeyword()));
						
						return false;
					}
					} else {
					$cart->add_cart($osC_Product->getID());
				}
			}
			$messageStack->add_session('product_action', sprintf(PRODUCT_ADDED, $osC_Product->getTitle()), 'success');
			
			if (DISPLAY_CART == 'true') {
				$goto =  FILENAME_SHOPPING_CART;
				tep_redirect(tep_href_link($goto, null));
			} else {
				$goto = $PHP_SELF;			
				if(isset($_GET['cPath'])){
					tep_redirect(tep_href_link($goto.'?cPath='.$_GET['cPath'], null));
				}else{
					tep_redirect(tep_href_link($goto, $osC_Product->getKeyword()));
				}
			}
			
		}
	}
?>
