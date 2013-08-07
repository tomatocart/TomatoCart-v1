<?php
/*
  $Id: ajax_shopping_cart.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Json_Ajax_shopping_cart {
  	const PRODUCTS_NAME_LENGTH = 14;
  	
    function loadCart() {
      global $osC_ShoppingCart, $osC_Currencies, $toC_Json;
      
      $content = self::_getShoppingCart();
      
      echo $toC_Json->encode($content);
    }
    
    function addProduct() {
      global $osC_ShoppingCart, $toC_Json, $osC_Language, $toC_Customization_Fields;
      
      $osC_Language->load('products');
      
      if ( is_numeric($_REQUEST['pID']) && osC_Product::checkEntry($_REQUEST['pID']) ) {
        $osC_ShoppingCart->resetShippingMethod();
        
        $osC_Product = new osC_Product($_REQUEST['pID']);
        
        //gift certificate check
        if ($osC_Product->isGiftCertificate() && !isset($_POST['senders_name'])) {
          $response = array('success' => false, 
                            'feedback' => $osC_Language->get('error_gift_certificate_data_missing'));
        }
        //customization fields check
         else if ( $osC_Product->hasRequiredCustomizationFields() && !$toC_Customization_Fields->exists($osC_Product->getID()) ) {
          $response = array('success' => false, 
                            'feedback' => $osC_Language->get('error_customization_fields_missing'));
        } else {
          $variants = null;
          if (isset($_REQUEST['variants']) && !empty($_REQUEST['variants'])) {
            $variants = osc_parse_variants_string($_REQUEST['variants']);
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
          
          $osC_ShoppingCart->add($_REQUEST['pID'], $variants, $_REQUEST['pQty'], $gift_certificate_data);
          
          $content = self::_getShoppingCart();
          
          $response = array('success' => true, 'content' => $content);
        }
      } else {
        $response = array('success' => false);
      }
      
      echo $toC_Json->encode($response);
    }
    
    function removeProduct() {
      global $toC_Json, $osC_ShoppingCart;

      $products_id = isset($_REQUEST['pID']) ? $_POST['pID'] : null;

      if ( (!empty($products_id)) && osC_Product::checkEntry($products_id) ) {
        $osC_ShoppingCart->remove($products_id);
        $osC_ShoppingCart->resetShippingMethod();

        if (!$osC_ShoppingCart->hasContents()) {
          $osC_ShoppingCart->reset();
        }
                
        $response = array('success' => true, 'hasContents' => $osC_ShoppingCart->hasContents());
      }else {
        $response = array('success' => false);
      }

      echo $toC_Json->encode($response);
    }
    
    function _getShoppingCart() {
      global $osC_ShoppingCart, $osC_Currencies, $osC_Language, $osC_Customer;
      
      //when the language is changed, it is necessary to update the shopping cart
      if (isset($_SESSION['language_change']) && ($_SESSION['language_change']== true)) {
        if ($osC_Customer->isLoggedOn()) {
          $osC_ShoppingCart->reset();
          $osC_ShoppingCart->synchronizeWithDatabase();
        }else {
          foreach($osC_ShoppingCart->getProducts() as $products_id_string => $data) {
            $osC_Product = new osC_Product($products_id_string);
            $data['name'] = $osC_Product->getTitle();
            $data['keyword'] = $osC_Product->getKeyword();
            
            $_SESSION['osC_ShoppingCart_data']['contents'][$products_id_string] = $data;
          }
          
          $osC_ShoppingCart->_calculate();
        }
        
        unset($_SESSION['language_change']);
      }

      $cart = array();
      
      //products
      $products = array();
      foreach($osC_ShoppingCart->getProducts() as $products_id => $data) {
        $product = array('id' => $products_id,
                         'link' => osc_href_link(FILENAME_PRODUCTS, osc_get_product_id($products_id)),
                         'name' => (substr($data['name'], 0, self::PRODUCTS_NAME_LENGTH)) . (strlen($data['name']) > self::PRODUCTS_NAME_LENGTH ? '..' : ''),
                         'title' => $data['name'],
                         'quantity' => $data['quantity'] . ' x ',
                         'price' => $osC_Currencies->displayPrice($data['price'], $data['tax_class_id'], $data['quantity']));
        
        //variants
        if (is_array($data['variants']) && !empty($data['variants'])) {
          $product['variants'] = array_values($data['variants']);
        }
        
        //customizations
        if (is_array($data['customizations']) && !empty($data['customizations'])) {
          $product['customizations'] = array_values($data['customizations']);
        }
        
        //gift certificate
        if ($data['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
          $gc_data = $osC_Language->get('senders_name') . ': ' . $data['gc_data']['senders_name'];
          
          if ($data['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
            $gc_data .= '<br />- ' . $osC_Language->get('senders_email')  . ': ' . $data['gc_data']['senders_email'];
          }
          
          $gc_data .= '<br />- ' . $osC_Language->get('recipients_name') . ': ' . $data['gc_data']['recipients_name'];
          
          if ($data['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
            $gc_data .= '<br />- ' . $osC_Language->get('recipients_email')  . ': ' . $data['gc_data']['recipients_email'];
          }
          
          $gc_data .= '<br />- ' . $osC_Language->get('message')  . ': ' . $data['gc_data']['message'];
          
          $product['gc_data'] = $gc_data;
        }

        $products[] = $product;
      }
      $cart['products'] = $products;
      
      //order totals
      $order_totals = array();
      foreach ($osC_ShoppingCart->getOrderTotals() as $module) {
        $order_totals[] = array('title' => $module['title'], 'text' => $module['text']);
      }
      
      $cart['orderTotals'] = $order_totals;
      //numberOfItems
      $cart['numberOfItems'] = $osC_ShoppingCart->numberOfItems();
      
      //cart total
      $cart['total'] = $osC_Currencies->format($osC_ShoppingCart->getTotal());
      
      return $cart;
    }
  }
?>