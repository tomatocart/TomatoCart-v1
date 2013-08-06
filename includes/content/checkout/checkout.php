<?php
/*
  $Id: checkout.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/classes/address_book.php');

  class osC_Checkout_Checkout extends osC_Template {

/* Private variables */

    var $_module = 'checkout',
        $_group = 'checkout',
        $_page_title,
        $_page_contents = 'checkout.php',
        $_page_image = 'table_background_delivery.gif';

/* Class constructor */

    function osC_Checkout_Checkout() {
      global $osC_ShoppingCart, $osC_Customer, $osC_NavigationHistory, $messageStack, $osC_Language;
      
      if ($osC_Customer->isLoggedOn() === false) {
        $osC_NavigationHistory->setSnapshot();

        osc_redirect(osc_href_link(FILENAME_ACCOUNT, 'login', 'SSL'));
      }
      
      if ($osC_ShoppingCart->hasContents() === false) {
        osc_redirect(osc_href_link(FILENAME_CHECKOUT, null, 'SSL'));
      }else {
        //check the products stock in the cart
        if (STOCK_ALLOW_CHECKOUT == '-1') {
          foreach($osC_ShoppingCart->getProducts() as $product) {
            if ($osC_ShoppingCart->isInStock($product['id']) === false) {
              osc_redirect(osc_href_link(FILENAME_CHECKOUT, null, 'SSL'));
            }
          }
        //add the out of stock message for the checkout one page  
        }else {
          foreach($osC_ShoppingCart->getProducts() as $product) {
            //it's gift certificate
            if ($product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
              if ($product['quantity'] < 1) {
                $messageStack->add('checkout', STOCK_MARK_PRODUCT_OUT_OF_STOCK . $product['name']);
              }
            }else {
              if ($osC_ShoppingCart->isInStock($product['id']) === false) {
                $messageStack->add('checkout', STOCK_MARK_PRODUCT_OUT_OF_STOCK . $product['name']);
              }
            }
          }
          
          if ($osC_ShoppingCart->hasStock() === false) {
            $messageStack->add('checkout', sprintf($osC_Language->get('products_out_of_stock_checkout_possible'), STOCK_MARK_PRODUCT_OUT_OF_STOCK));
          }
        }
      }
      
      if ($osC_ShoppingCart->hasBillingMethod()) {
          // load selected payment module
        include('includes/classes/payment.php');
        $osC_Payment = new osC_Payment($osC_ShoppingCart->getBillingMethod('id'));
        
        $payment_error = $osC_Payment->get_error();
        
        if (is_array($payment_error) && !empty($payment_error)) {
          $messageStack->add('payment_error_msg', '<strong>' . $payment_error['title'] . '</strong> ' . $payment_error['error']);
        }
      }
      
      $this->addHeaderJavascriptFilename('includes/javascript/checkout.js');
    } 
  }
?>
