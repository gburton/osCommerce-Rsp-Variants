<?php
	/*
		$Id$
		
		osCommerce, Open Source E-Commerce Solutions
		http://www.oscommerce.com
		
		Copyright (c) 2010 osCommerce
		
		Released under the GNU General Public License
	*/
	
	require('includes/application_top.php');
	require(DIR_WS_INCLUDES . 'template_top.php');
	require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_DEFAULT);

		if ($messageStack->size('product_action') > 0) {
			echo $messageStack->output('product_action');
		}
	echo $oscTemplate->getContent('index');
	
	require(DIR_WS_INCLUDES . 'template_bottom.php');
	require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
