<?php
	/*
		$Id: account.php 207 2005-09-26 01:29:31 +0200 (Mo, 26 Sep 2005) hpdl $
		
		osCommerce, Open Source E-Commerce Solutions
		http://www.oscommerce.com
		
		Copyright (c) 2005 osCommerce
		
		This program is free software; you can redistribute it and/or modify
		it under the terms of the GNU General Public License v2 (1991)
		as published by the Free Software Foundation.
	*/
	
	class osC_Manufacturer {
		var $_data = array();
		
		function osC_Manufacturer($id) {
			global $osC_Database;
			
			$Qmanufacturer_Query = tep_db_query("select manufacturers_id as id, manufacturers_name as name, manufacturers_image as image from manufacturers where manufacturers_id = '" . (int)$id . "' ");
			$Qmanufacturer = tep_db_fetch_array($Qmanufacturer_Query);
			
			if ( tep_db_num_rows($Qmanufacturer_Query) === 1 ) {
				$this->_data = $Qmanufacturer;
			}
		}
		
		function getID() {
			if (isset($this->_data['id'])) {
				return $this->_data['id'];
			}
			
			return false;
		}
		
		function getTitle() {
			if (isset($this->_data['name'])) {
				return $this->_data['name'];
			}
			
			return false;
		}
		
		function getImage() {
			if (isset($this->_data['image'])) {
				return $this->_data['image'];
			}
			
			return false;
		}
	}
?>
