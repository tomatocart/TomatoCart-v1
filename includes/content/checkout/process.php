<?php
/*
  $Id: process.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Checkout_Process extends osC_Template {

/* Private variables */

    var $_module = 'process';

/* Class constructor */

    function osC_Checkout_Process() {
      global $osC_Session, $osC_ShoppingCart, $osC_Customer, $osC_NavigationHistory, $osC_Payment;
      
      if ($osC_ShoppingCart->hasContents() === false) {
        osc_redirect(osc_href_link(FILENAME_CHECKOUT, null, 'SSL'));
      }

      // if no shipping method has been selected, redirect the customer to the shipping method selection page
      if (($osC_ShoppingCart->hasShippingMethod() === false) && ($osC_ShoppingCart->getContentType() != 'virtual')) {
        osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'shipping', 'SSL'));
      }

      if ($osC_ShoppingCart->hasBillingMethod()) {
        // load selected payment module
        include('includes/classes/payment.php');
        $osC_Payment = new osC_Payment($osC_ShoppingCart->getBillingMethod('id'));
      }
      
      include('includes/classes/order.php');

      if ($osC_ShoppingCart->hasBillingMethod()) {
        $osC_Payment->process();
      } else {
        $orders_id = osC_Order::insert();
        osC_Order::process($orders_id, ORDERS_STATUS_PAID);
      }

      $osC_ShoppingCart->reset(true);

      // unregister session variables used during checkout
      unset($_SESSION['comments']);

      osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'success', 'SSL'));
    }
  }
?>
