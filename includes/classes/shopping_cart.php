<?php
	/*
		$Id$
		
		osCommerce, Open Source E-Commerce Solutions
		http://www.oscommerce.com
		
		Copyright (c) 2012 osCommerce
		
		Released under the GNU General Public License
	*/
	
	class shoppingCart {
		private $_contents = array();		
		var $contents, $total, $weight, $cartID, $content_type;
		
		function shoppingCart() {
			$this->reset();
		}
		
		function restore_contents() {
			global $languages_id, $customer_id;
			
			if ( !tep_session_is_registered('customer_id') ) {
				return false;
			}
			
			foreach ( $this->contents as $item_id => $data ) {
				$db_action = 'check';
				
				if ( isset($data['variants']) ) {
					foreach ( $data['variants'] as $variant ) {
						if ( $variant['has_custom_value'] === true ) {
							$db_action = 'insert';
							
							break;
						}
					}
				}
				
				if ( $db_action == 'check' ) {
					$Qproduct_Query = tep_db_query("select item_id, quantity from customers_basket where customers_id = '" . (int)$customer_id . "' and products_id = '" . (int)$data['id'] . "'");
					
					if ( tep_db_num_rows($Qproduct_Query) > 0 ) {
						
						$Qproduct = tep_db_fetch_array($Qproduct_Query);
						$Qupdate_Quantity = (int)$data['quantity'] + (int)$Qproduct['quantity'];
						
						tep_db_query("update customers_basket set quantity = '" . (int)$Qupdate_Quantity . "' where customers_id = '" . (int)$customer_id . "' and item_id = '" . (int)$Qproduct['item_id'] . "'");
					
					} else {
						$db_action = 'insert';
					}
				}
				
				if ( $db_action == 'insert') {
					$Qid_Query = tep_db_query("select max(item_id) as item_id from customers_basket where customers_id = '" . (int)$customer_id . "'");
					$Qid = tep_db_fetch_array($Qid_Query);
					
					$db_item_id = $Qid['item_id'] + 1;
					
					tep_db_query("insert into customers_basket (customers_id, item_id, products_id, quantity, date_added) values ('" . (int)$customer_id . "', '" . (int)$db_item_id . "', '" . (int)$data['id'] . "', '" . (int)$data['quantity'] . "', '" . date('Ymd') . "')");
					
					if ( isset($data['variants']) ) {
						foreach ( $data['variants'] as $variant ) {
							if ( $variant['has_custom_value'] === true ) {
								
								tep_db_query("insert into shopping_carts_custom_variants_values (shopping_carts_item_id, customers_id, products_id, products_variants_values_id, products_variants_values_text) values ('" . (int)$db_item_id . "', '" . (int)$customer_id . "', '" . (int)$data['id'] . "', '" . (int)$variant['value_id'] . "', '" . $variant['value_title'] . "')");

							}
						}
					}
				}
			}
			
			// reset per-session cart contents, but not the database contents
			$this->reset(false);
			
			$_delete_array = array();
			
			$Qproducts_Query = tep_db_query("select cb.item_id, cb.products_id, cb.quantity, cb.date_added, p.parent_id, p.products_price, p.products_model, p.products_tax_class_id, p.products_weight, p.products_weight_class, p.products_status from customers_basket cb, products p where cb.customers_id = '" . (int)$customer_id . "' and cb.products_id = p.products_id order by cb.date_added desc");
			
			while ( $Qproducts = tep_db_fetch_array($Qproducts_Query) ) {
				
				if($Qproducts['parent_id'] > 0){
					$product_id = $Qproducts['parent_id'];
				}else{
					$product_id = $Qproducts['products_id'];
				}
			
				if ( $Qproducts['products_status'] == 1 ) {
					$Qdesc_Query = tep_db_query("select products_name, products_keyword from products_description where products_id = '" . (int)$product_id . "' and language_id = '" . (int)$languages_id . "'");
					$Qdesc = tep_db_fetch_array($Qdesc_Query);

					
					$Qimage_Query = tep_db_query("select image from products_images where products_id = '" . (int)$product_id . "' and default_flag = '1'");
					$Qimage = tep_db_fetch_array($Qimage_Query);

					
					$price = $Qproducts['products_price'];
					
					$Qspecials_Query = tep_db_query("select specials_new_products_price from specials where products_id = '" . (int)$Qproducts['products_id'] . "' and status = '1'");
					if (tep_db_num_rows($Qspecials_Query)) {
						$Qspecials = tep_db_fetch_array($Qspecials_Query);
						$price = $Qspecials['specials_new_products_price'];
					}
					
					$this->contents[$Qproducts['item_id']] = array('item_id' => $Qproducts['item_id'],
					'id' => $Qproducts['products_id'],
					'parent_id' => $Qproducts['parent_id'],
					'model' => $Qproducts['products_model'],
					'name' => $Qdesc['products_name'],
					'keyword' => $Qdesc['products_keyword'],
					'image' => (tep_db_num_rows($Qimage_Query) == 1) ? $Qimage['image'] : '',
					'price' => $price,
					'quantity' => $Qproducts['quantity'],
					'weight' => $Qproducts['products_weight'],
					'tax_class_id' => $Qproducts['products_tax_class_id'],
					//'date_added' => osC_DateTime::getShort($Qproducts->value('date_added')),
					'weight_class_id' => $Qproducts['products_weight_class']);
					
					if ( $Qproducts['parent_id'] > 0 ) {
						$Qcheck_Query = tep_db_query("select products_status from products where products_id = '" . (int)$Qproducts['parent_id'] . "'");
						$Qcheck = tep_db_fetch_array($Qcheck_Query);
						if ( $Qcheck['products_status'] == 1 ) {
							$Qvariant_Query = tep_db_query("select pvg.id as group_id, pvg.title as group_title, pvg.module, pvv.id as value_id, pvv.title as value_title from products_variants pv, products_variants_values pvv, products_variants_groups pvg where pv.products_id = '" . (int)$Qproducts['products_id'] . "' and pv.products_variants_values_id = pvv.id and pvv.languages_id = '" . (int)$languages_id . "' and pvv.products_variants_groups_id = pvg.id and pvg.languages_id = '" . (int)$languages_id . "'");
							
							if ( tep_db_num_rows($Qvariant_Query) > 0 ) {
								while ( $Qvariant = tep_db_fetch_array($Qvariant_Query) ) {
									$group_title = osC_Variants::getGroupTitle($Qvariant['module'], $Qvariant);
									$value_title = $Qvariant['value_title'];
									$has_custom_value = false;
									
									$Qcvv_Query = tep_db_query("select products_variants_values_text from shopping_carts_custom_variants_values where customers_id = '" . (int)$customer_id . "' and shopping_carts_item_id = '" . (int)$Qproducts['item_id'] . "' and products_id = '" . (int)$Qproducts['products_id'] . "' and products_variants_values_id = '" . (int)$Qvariant['value_id'] . "'");
									
									if ( tep_db_num_rows($Qcvv_Query) === 1 ) {
										$Qcvv = tep_db_fetch_array($Qcvv_Query);
										$value_title = $Qcvv['products_variants_values_text'];
										$has_custom_value = true;
									}
									
									$this->contents[$Qproducts['item_id']]['variants'][] = array('group_id' => $Qvariant['group_id'],
									'value_id' => $Qvariant['value_id'],
									'group_title' => $group_title,
									'value_title' => $value_title,
									'has_custom_value' => $has_custom_value);
								}
							} else {
								$_delete_array[] = $Qproducts['item_id'];
							}
						} else {
							$_delete_array[] = $Qproducts['item_id'];
						}
					}
				} else {
					$_delete_array[] = $Qproducts['item_id'];
				}
			}
			
			if ( !empty($_delete_array) ) {
				foreach ( $_delete_array as $id ) {
					unset($this->contents[$id]);
				}
				
				tep_db_query('delete from customers_basket where customers_id = "' . (int)$customer_id . '" and item_id in ("' . implode('", "', $_delete_array) . '")');				
				tep_db_query('delete from shopping_carts_custom_variants_values where customers_id = "' . (int)$customer_id . '" and shopping_carts_item_id in ("' . implode('", "', $_delete_array) . '")');
			}
			
			$this->cleanup();
			$this->calculate();
			// assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
			$this->cartID = $this->generate_cart_id();
		}
		
		function reset($reset_database = false) {
			global $customer_id;
			
			$this->contents = array();
			$this->total = 0;
			$this->weight = 0;
			$this->content_type = false;
			
			if (tep_session_is_registered('customer_id') && ($reset_database == true)) {
				tep_db_query("delete from customers_basket where customers_id = '" . (int)$customer_id . "'");
			}
			
			unset($this->cartID);
			if (tep_session_is_registered('cartID')) tep_session_unregister('cartID');
		}
		
		function add_cart($product_id, $quantity = null) {
			global $languages_id, $customer_id;
			
			$product_variant_id = $product_id;

			if ( !is_numeric($product_id) ) {
				return false;
			}
			
			$Qproduct_Query = tep_db_query("select p.parent_id, p.products_price, p.products_tax_class_id, p.products_model, p.products_weight, p.products_weight_class, p.products_status, i.image from products p left join products_images i on (p.products_id = i.products_id and i.default_flag = '1') where p.products_id = '" . (int)$product_id . "'");
			$Qproduct = tep_db_fetch_array($Qproduct_Query);
		
			if($Qproduct['parent_id'] > 0){
				$product_id = $Qproduct['parent_id'];
			}else{
				$product_id = $product_id;
			}			
			
			$Qimage_Query = tep_db_query("select image from products_images where products_id = '" . (int)$product_id . "' and default_flag = '1'");
			$Qimage = tep_db_fetch_array($Qimage_Query);
			
			
			if ( $Qproduct['products_status'] == 1 ) {

				if ( $this->exists($product_id) ) {
					$item_id = $this->getBasketID($product_id);
					
					if ( !is_numeric($quantity) ) {
						$quantity = $this->getQuantity($item_id) + 1;
					}
					
					$this->contents[$item_id]['quantity'] = $quantity;
					
					if (tep_session_is_registered('customer_id')) {
						tep_db_query("update customers_basket set quantity = '" . (int)$quantity . "' where customers_id = '" . (int)$customer_id . "' and item_id = '" . (int)$item_id . "'");
					}
				} else {
					if ( !is_numeric($quantity) ) {
						$quantity = 1;
					}
					
					$Qdescription_Query = tep_db_query("select products_name, products_keyword from products_description where products_id = '" . (int)$product_id . "' and language_id = '" . (int)$languages_id . "'");
					$Qdescription = tep_db_fetch_array($Qdescription_Query);
				
					$price = $Qproduct['products_price'];
					

					$Qspecials_Query = tep_db_query("select specials_new_products_price from specials where products_id = '" . (int)$product_id . "' and status = '1'");
					if (tep_db_num_rows($Qspecials_Query)) {
						$Qspecials = tep_db_fetch_array($Qspecials_Query);
						$price = $Qspecials['specials_new_products_price'];
					}
		
					if (tep_session_is_registered('customer_id')) {
						$Qid_Query = tep_db_query("select max(item_id) as item_id from customers_basket where customers_id = '" . (int)$customer_id . "'");
						$Qid = tep_db_fetch_array($Qid_Query);
						
						$item_id = $Qid['item_id'] + 1;
						} else {
						if ( empty($this->contents) ) {
							$item_id = 1;
							} else {
							$item_id = max(array_keys($this->contents)) + 1;
						}
					}
					
					$this->contents[$item_id] = array('item_id' => $item_id,
					'id' => $product_variant_id,
					'parent_id' => $Qproduct['parent_id'],
					'name' => $Qdescription['products_name'],
					'model' => $Qproduct['products_model'],
					'keyword' => $Qdescription['products_keyword'],
					'image' => (tep_db_num_rows($Qimage_Query) == 1) ? $Qimage['image'] : '',
					'price' => $price,
					'quantity' => $quantity,
					'weight' => $Qproduct['products_weight'],
					'tax_class_id' => $Qproduct['products_tax_class_id'],
					'date_added' => date('Ymd'),
					'weight_class_id' => $Qproduct['products_weight_class']);

					if (tep_session_is_registered('customer_id')) {
						tep_db_query("insert into customers_basket (customers_id, item_id, products_id, quantity, date_added) values ('" . (int)$customer_id . "', '" . (int)$item_id . "', '" . (int)$product_variant_id . "', '" . (int)$quantity . "', '" . date('Ymd') . "')");

					}				
					
					if ( $Qproduct['parent_id'] > 0 ) {
						$Qvariant_Query = tep_db_query("select pvg.id as group_id, pvg.title as group_title, pvg.module, pvv.id as value_id, pvv.title as value_title from products_variants pv, products_variants_values pvv, products_variants_groups pvg where pv.products_id = '" . (int)$product_variant_id . "' and pv.products_variants_values_id = pvv.id and pvv.languages_id = '" . (int)$languages_id . "' and pvv.products_variants_groups_id = pvg.id and pvg.languages_id = '" . (int)$languages_id . "'");
						while ( $Qvariant = tep_db_fetch_array($Qvariant_Query) ) {
							
							$group_title = osC_Variants::getGroupTitle($Qvariant['module'], $Qvariant);
							$value_title = osC_Variants::getValueTitle($Qvariant['module'], $Qvariant);
							$has_custom_value = osC_Variants::hasCustomValue($Qvariant['module']);
							
							$this->contents[$item_id]['variants'][] = array('group_id' => $Qvariant['group_id'],
							'value_id' => $Qvariant['value_id'],
							'group_title' => $group_title,
							'value_title' => $value_title,
							'has_custom_value' => $has_custom_value);
							
							if ( tep_session_is_registered('customer_id') && ($has_custom_value === true) ) {
								$Qnew_Query = tep_db_query("insert into shopping_carts_custom_variants_values (shopping_carts_item_id, customers_id, products_id, products_variants_values_id, products_variants_values_text) values ('" . (int)$item_id . "', '" . (int)$customer_id . "', '" . (int)$product_variant_id . "', '" . $Qvariant['value_id'] . "', '" . $value_title . "')");

							}
							
						}
						
					}
	
				}
				$this->cleanup();
				$this->calculate();
				// assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
				$this->cartID = $this->generate_cart_id();				
			}
		}
		
		function getBasketID($product_id) {
			foreach ( $this->contents as $item_id => $product ) {
				if ( $product['id'] === $product_id ) {
					return $item_id;
				}
			}
		}
		
		function getQuantity($item_id) {
			return ( isset($this->contents[$item_id]) ) ? $this->contents[$item_id]['quantity'] : 0;
		}
		
		function exists($product_id) {
			foreach ( $this->contents as $product ) {
				if ( $product['id'] === $product_id ) {
					if ( isset($product['variants']) ) {
						foreach ( $product['variants'] as $variant ) {
							if ( $variant['has_custom_value'] === true ) {
								return false;
							}
						}
					}
					
					return true;
				}
			}
			
			return false;
		}
		
		function update_quantity($products_id, $quantity = '') {
			global $customer_id;
			
			$products_id_string = $products_id;
			$products_id = $products_id_string;
			
			if (defined('MAX_QTY_IN_CART') && (MAX_QTY_IN_CART > 0) && ((int)$quantity > MAX_QTY_IN_CART)) {
				$quantity = MAX_QTY_IN_CART;
			}
			
			
			if (is_numeric($products_id) && isset($this->contents[$products_id_string]) && is_numeric($quantity)) {
				$this->contents[$products_id_string] = array('qty' => (int)$quantity);
				// update database
				if (tep_session_is_registered('customer_id')) tep_db_query("update customers_basket set quantity = '" . (int)$quantity . "' where customers_id = '" . (int)$customer_id . "' and products_id = '" . tep_db_input($products_id_string) . "'");
				
				// assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
				$this->cartID = $this->generate_cart_id();
			}
		}
		private function cleanup() {
			global $customer_id;
			
			foreach ( $this->contents as $item_id => $data ) {
				if ( $data['quantity'] < 1 ) {
					unset($this->contents[$item_id]);
					
					if (tep_session_is_registered('customer_id')) {
						tep_db_query("delete from customers_basket where customers_id = '" . (int)$customer_id . "' and item_id = '" . (int)$item_id . "'");						
						tep_db_query("delete from shopping_carts_custom_variants_values where customers_id = '" . (int)$customer_id . "'and shopping_carts_item_id = '" . (int)$item_id . "'");
					}
				}
			}
		}
		
		function count_contents() {  // get total number of items in cart 
			$total = 0;
			
			foreach ( $this->contents as $product ) {
				$total += $product['quantity'];
			}
			
			return $total;
		}
		
		function get_quantity($products_id) {
			if (isset($this->contents[$products_id])) {
				return $this->contents[$products_id]['qty'];
				} else {
				return 0;
			}
		}
		public function isVariant($item_id) {
			return isset($this->contents[$item_id]['variants']) && !empty($this->contents[$item_id]['variants']);
		}
		
		public function getVariant($item_id) {
			if ( isset($this->contents[$item_id]['variants']) && !empty($this->contents[$item_id]['variants']) ) {
				return $this->contents[$item_id]['variants'];
			}
		}		
		function in_cart($products_id) {
			if (isset($this->contents[$products_id])) {
				return true;
				} else {
				return false;
			}
		}
		
		function remove($item_id) {
			global $customer_id;
			
			unset($this->contents[$item_id]);
			// remove from database
			if (tep_session_is_registered('customer_id')) {
				tep_db_query("delete from customers_basket where customers_id = '" . (int)$customer_id . "' and item_id = '" . (int)$item_id . "'");
				tep_db_query("delete from shopping_carts_custom_variants_values where customers_id = '" . (int)$customer_id . "' and shopping_carts_item_id = '" . (int)$item_id . "'");			
			}
			
			$this->calculate();
		}
		
		function remove_all() {
			$this->reset();
		}
		
		function get_product_id_list() {
			$product_id_list = '';
			if (is_array($this->contents)) {
				reset($this->contents);
				while (list($products_id, ) = each($this->contents)) {
					$product_id_list .= ', ' . $products_id;
				}
			}
			
			return substr($product_id_list, 2);
		}
		
		function calculate() {
			global $currencies;
			
			$this->total = 0;
			$this->weight = 0;
			if (!is_array($this->contents)) return 0;
			
			//reset($this->contents);
			//$this->cartID = $this->generate_cart_id();
			
			foreach ( $this->contents as $data ) {
				$qty = $data['quantity'];

				$products_tax = tep_get_tax_rate($data['tax_class_id']);
				$products_price = $data['price'];
				$products_weight = $data['weight'];
				
				$this->total += $currencies->calculate_price($products_price, $products_tax, $qty);
				$this->weight += ($qty * $products_weight);

			}
		}
		
		function show_total() {
			$this->calculate();
			
			return $this->total;
		}
		
		function show_weight() {
			$this->calculate();
			
			return $this->weight;
		}
		
		function generate_cart_id($length = 5) {
			return tep_create_random_value($length, 'digits');
		}
		
		function get_content_type() {
			$this->content_type = false;
			
			if ( (DOWNLOAD_ENABLED == 'true') && ($this->count_contents() > 0) ) {
				reset($this->contents);
				while (list($products_id, ) = each($this->contents)) {

					switch ($this->content_type) {
						case 'virtual':
						$this->content_type = 'mixed';
						
						return $this->content_type;
						break;
						default:
						$this->content_type = 'physical';
						break;
					}
					
				}
				} else {
				$this->content_type = 'physical';
			}
			
			return $this->content_type;
		}
		
		function unserialize($broken) {
			for(reset($broken);$kv=each($broken);) {
				$key=$kv['key'];
				if (gettype($this->$key)!="user function")
				$this->$key=$kv['value'];
			}
		}
		public function get_products() {
			static $_is_sorted = false;
			
			if ( $_is_sorted === false ) {
				$_is_sorted = true;
				
				uasort($this->contents, array('shoppingCart', '_uasortProductsByDateAdded'));
			}
			
			return $this->contents;
		}
		static private function _uasortProductsByDateAdded($a, $b) {
			if ($a['date_added'] == $b['date_added']) {
				return strnatcasecmp($a['name'], $b['name']);
			}
			
			return ($a['date_added'] > $b['date_added']) ? -1 : 1;
		}		
	}
?>
