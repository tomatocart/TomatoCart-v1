<?php
/*
  $Id: cart.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Checkout_Cart extends osC_Template {

/* Private variables */

    var $_module = 'cart',
        $_group = 'checkout',
        $_page_title,
        $_page_contents = 'shopping_cart.php',
        $_page_image = 'table_background_cart.gif';

/* Class constructor */

    function osC_Checkout_Cart() {
      global $osC_Services, $osC_Language, $breadcrumb, $payment_modules;

      $this->_page_title = $osC_Language->get('shopping_cart_heading');
       
      if (!class_exists('osC_Payment')) {
        require_once('includes/classes/payment.php');
      }
      
      $payment_modules = new osC_Payment();

      if ($osC_Services->isStarted('breadcrumb')) {
        $breadcrumb->add($osC_Language->get('breadcrumb_checkout_shopping_cart'), osc_href_link(FILENAME_CHECKOUT, null, 'SSL'));
      }

//      if ($_GET[$this->_module] == 'update') {
//        $this->_process();
//      }
    }
  }
?>
