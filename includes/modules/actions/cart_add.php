<?php
/*
  $Id: cart_add.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Actions_cart_add {
    function execute() {
      global $osC_Session, $osC_ShoppingCart, $osC_Product, $osC_Language, $messageStack, $toC_Customization_Fields;

      if (!isset($osC_Product)) {
        $id = false;
        
        foreach ($_GET as $key => $value) {
          if ( (ereg('^[0-9]+(_?([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)*$', $key) || ereg('^[a-zA-Z0-9 -_]*$', $key)) && ($key != $osC_Session->getName()) ) {
            $id = $key;
          }

          break;
        }
        
        if (strpos( $id, '_') !== false) {
          $id = str_replace('_', '#', $id);
        }
        
        if (($id !== false) && osC_Product::checkEntry($id)) {
          $osC_Product = new osC_Product($id);
        }
      }

      if (isset($osC_Product)) {
        //customization fields check
        if ($osC_Product->hasRequiredCustomizationFields()) {
          if ( !$toC_Customization_Fields->exists($osC_Product->getID()) ) {
            $osC_Language->load('products');
            
            $messageStack->add_session('products', $osC_Language->get('error_customization_fields_missing'), 'error');
            
            osc_redirect(osc_href_link(FILENAME_PRODUCTS, $osC_Product->getID()));  
          }
        }
        
        $variants = null;
        
        if (isset($_POST['variants']) && is_array($_POST['variants'])) {
          $variants = $_POST['variants'];
        } else if (isset($_GET['variants']) && !empty($_GET['variants'])) {
          $variants = osc_parse_variants_string($_GET['variants']);
        }
        
        $gift_certificate_data = null;
        if($osC_Product->isGiftCertificate() && isset($_POST['senders_name']) && isset($_POST['recipients_name']) && isset($_POST['message'])) {
          if ($osC_Product->isEmailGiftCertificate()) {
            $gift_certificate_data = array('senders_name' => $_POST['senders_name'],
                                           'senders_email' => $_POST['senders_email'],
                                           'recipients_name' => $_POST['recipients_name'],
                                           'recipients_email' => $_POST['recipients_email'],
                                           'message' => $_POST['message']);
          } else {
            $gift_certificate_data = array('senders_name' => $_POST['senders_name'],
                                           'recipients_name' => $_POST['recipients_name'],
                                           'message' => $_POST['message']);
          }
          
          if ($osC_Product->isOpenAmountGiftCertificate()) {
            $gift_certificate_data['price'] = $_POST['gift_certificate_amount']; 
          }
          
          $gift_certificate_data['type'] = $osC_Product->getGiftCertificateType();
        }

        $quantity = null;
        if (isset($_POST['quantity']) && is_numeric($_POST['quantity'])) {
          $quantity = $_POST['quantity'];
        }
        
        if ( $osC_Product->isGiftCertificate() && ($gift_certificate_data == null) ) {
          osc_redirect(osc_href_link(FILENAME_PRODUCTS, $osC_Product->getID()));
          
          return false;
        } else {
          $osC_ShoppingCart->add($osC_Product->getID(), $variants, $quantity, $gift_certificate_data);
        }
      }

      osc_redirect(osc_href_link(FILENAME_CHECKOUT));
    }
  }
?>