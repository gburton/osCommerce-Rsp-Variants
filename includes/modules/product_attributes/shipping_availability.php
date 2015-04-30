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
	
	class osC_ProductAttributes_shipping_availability {
		static public function getValue($value) {
			global $osC_Database, $osC_Language;
			
			$string = '';
			
			$Qstatus_Query  = tep_db_query("select title, css_key from shipping_availability where id = '" . $value . "' and languages_id = '1'");
			$Qstatus = tep_db_fetch_array($Qstatus_Query);
			
			/*
				$Qstatus->bindTable(':table_shipping_availability');
				$Qstatus->bindInt(':id', $value);
				$Qstatus->bindInt(':languages_id', $osC_Language->getID());
				$Qstatus->execute();
			*/
			if ( tep_db_num_rows($Qstatus_Query) === 1 ) {
				$string = $Qstatus['title'];
				
				if ( tep_not_null($Qstatus['css_key']) ) {
					$string = '<span class="' . $Qstatus['css_key'] . '">' . $string . '</span>';
				}
			}
			
			return $string;
		}
	}
?>
