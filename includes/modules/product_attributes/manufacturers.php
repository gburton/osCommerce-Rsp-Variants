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
	
	class osC_ProductAttributes_manufacturers {
		static public function getValue($value) {
			global $osC_Database;
			
			$Qmanufacturer_Query = tep_db_query("select manufacturers_name from manufacturers where manufacturers_id = '" . $value . "' ");
			$Qmanufacturer = tep_db_fetch_array($Qmanufacturer_Query);
			if ( tep_db_num_rows($Qmanufacturer_Query) === 1 ) {
				return $Qmanufacturer['manufacturers_name'];
			}
		}
	}
?>
