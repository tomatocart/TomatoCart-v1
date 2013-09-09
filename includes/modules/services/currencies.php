<?php
/*
 $Id: currencies.php $
TomatoCart Open Source Shopping Cart Solutions
http://www.tomatocart.com

Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License v2 (1991)
as published by the Free Software Foundation.
*/

class osC_Services_currencies {
    function start() {
        global $osC_Language, $osC_Currencies;

        include('includes/classes/currencies.php');
        $osC_Currencies = new osC_Currencies();
        
        //Keep currency selection from currency box for the following requests
        if (isset($_GET['currency']) && $osC_Currencies->exists($_GET['currency'])) {
        	$_SESSION['currency'] = $_GET['currency'];
        	$_SESSION['currency_set'] = true;
        	
        	if ( isset($_SESSION['cartID']) ) {
        		unset($_SESSION['cartID']);
        	}
        }
        
        //set the currency with default language currency or default currency
        if (!isset($_SESSION['currency_set'])) {
        	if ((isset($_SESSION['currency']) == false) || ( (USE_DEFAULT_LANGUAGE_CURRENCY == '1') && ($osC_Currencies->getCode($osC_Language->getCurrencyID()) != $_SESSION['currency']) ) || ((USE_DEFAULT_LANGUAGE_CURRENCY != '1') && (DEFAULT_CURRENCY !=$_SESSION['currency'] )) ) {
				$_SESSION['currency'] = (USE_DEFAULT_LANGUAGE_CURRENCY == '1') ? $osC_Currencies->getCode($osC_Language->getCurrencyID()) : DEFAULT_CURRENCY;
        	
        		if ( isset($_SESSION['cartID']) ) {
        			unset($_SESSION['cartID']);
        		}
        	}
        }
        
        return true;
    }

    function stop() {
        return true;
    }
}
?>
