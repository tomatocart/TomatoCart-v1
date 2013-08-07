<?php
/*
  $Id: nochex.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_nochex extends osC_Payment {
    var $_title,
        $_code = 'nochex',
        $_author_name = 'osCommerce',
        $_status = false,
        $_sort_order;

    function osC_Payment_nochex() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;

      $this->_title = $osC_Language->get('payment_nochex_title');
      $this->_method_title = $osC_Language->get('payment_nochex_method_title');
      $this->_description = $osC_Language->get('payment_nochex_description');
      $this->_status = (defined('MODULE_PAYMENT_NOCHEX_STATUS') && (MODULE_PAYMENT_NOCHEX_STATUS == '1') ? true : false);
      $this->_sort_order = (defined('MODULE_PAYMENT_NOCHEX_SORT_ORDER') ? MODULE_PAYMENT_NOCHEX_SORT_ORDER : null);
      
      $this->form_action_url = 'https://secure.nochex.com';
      $this->apc_url = 'https://www.nochex.com/nochex.dll/apc/apc';

      if ($this->_status === true) {
        $this->order_status = MODULE_PAYMENT_NOCHEX_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_NOCHEX_ORDER_STATUS_ID : (int)ORDERS_STATUS_PAID;
        
        if ((int)MODULE_PAYMENT_NOCHEX_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_NOCHEX_ZONE);
          $Qcheck->bindInt(':zone_country_id', $osC_ShoppingCart->getBillingAddress('country_id'));
          $Qcheck->execute();

          while ($Qcheck->next()) {
            if ($Qcheck->valueInt('zone_id') < 1) {
              $check_flag = true;
              break;
            } elseif ($Qcheck->valueInt('zone_id') == $osC_ShoppingCart->getBillingAddress('zone_id')) {
              $check_flag = true;
              break;
            }
          }

          if ($check_flag == false) {
            $this->_status = false;
          }
        }
      }
    }

    function selection() {
      return array('id' => $this->_code,
                   'module' => $this->_method_title);
    }
    
    function confirmation() {
      $this->_order_id = osC_Order::insert(ORDERS_STATUS_PREPARING);
    }

    function process_button() {
      global $osC_ShoppingCart, $osC_Currencies, $osC_Customer, $osC_Tax;
      
      $process_button_string = '';
      if (MODULE_PAYMENT_NOCHEX_GATEWAY_MODE == 'Live') {
        $params = array('merchant_id' => MODULE_PAYMENT_NOCHEX_ID,
                        'success_url' => osc_href_link(FILENAME_CHECKOUT, 'process', 'SSL'), 
                        'cancel_url' => osc_href_link(FILENAME_CHECKOUT, 'checkout', 'SSL'), 
                        'declined_url' => osc_href_link(FILENAME_CHECKOUT, 'checkout', 'SSL'));
        
      }else if (MODULE_PAYMENT_NOCHEX_GATEWAY_MODE == 'Test') {
        $params = array('merchant_id' => 'nochex_test',
                        'test_transaction' => '100', 
                        'test_success_url' => osc_href_link(FILENAME_CHECKOUT, 'process', 'SSL'), 
                        'test_cancel_url' => osc_href_link(FILENAME_CHECKOUT, 'checkout', 'SSL'),
                        'declined_url' => osc_href_link(FILENAME_CHECKOUT, 'checkout', 'SSL'));
      }
      
      $params['callback_url'] = osc_href_link(FILENAME_CHECKOUT, 'callback&module=' . $this->_code, 'SSL', false, false, true);
      $params['amount'] = number_format($osC_ShoppingCart->getTotal() * $osC_Currencies->currencies['GBP']['value'], $osC_Currencies->currencies['GBP']['decimal_places']);
      $params['order_id'] = $this->_order_id;
      $params['billing_fullname'] = $osC_ShoppingCart->getBillingAddress('firstname') . ' ' . $osC_ShoppingCart->getBillingAddress('lastname');
      $params['billing_address'] = $osC_ShoppingCart->getBillingAddress('street_address');
      $params['billing_postcode'] = $osC_ShoppingCart->getBillingAddress('postcode');
      $params['customer_phone_number'] = $osC_ShoppingCart->getBillingAddress('telephone_number');
      $params['email_address'] = $osC_Customer->getEmailAddress();
      $params['hide_billing_details'] = 'true';
      
      if ($osC_ShoppingCart->hasShippingAddress()) {
        $params['delivery_fullname'] = $osC_ShoppingCart->getShippingAddress('firstname') . ' ' . $osC_ShoppingCart->getShippingAddress('lastname');
        $params['delivery_address'] = $osC_ShoppingCart->getShippingAddress('street_address');
        $params['delivery_postcode'] = $osC_ShoppingCart->getShippingAddress('postcode');
      }else {
        $params['delivery_fullname'] = $params['billing_fullname'];
        $params['delivery_address'] = $params['billing_address'];
        $params['delivery_postcode'] = $params['billing_postcode'];
      }
      
     //products
      $products_description = array();
      if ($osC_ShoppingCart->hasContents()) {
        $products = $osC_ShoppingCart->getProducts();
        
        foreach($products as $product) {
          $product_name = $product['quantity'] . 'x ' . $product['name'];
          
          //gift certificate
          if ($product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
            $product_name .= "\n" . ' - ' . $osC_Language->get('senders_name') . ': ' . $product['gc_data']['senders_name'];
            
            if ($product['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
              $product_name .= "\n" . ' - ' . $osC_Language->get('senders_email')  . ': ' . $product['gc_data']['senders_email'];
            }
            
            $product_name .= "\n" . ' - ' . $osC_Language->get('recipients_name') . ': ' . $product['gc_data']['recipients_name'];
            
            if ($product['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
              $product_name .= "\n" . ' - ' . $osC_Language->get('recipients_email')  . ': ' . $product['gc_data']['recipients_email'];
            }
            
            $product_name .= "\n" . ' - ' . $osC_Language->get('message')  . ': ' . $product['gc_data']['message'];
          }
          
          if ($osC_ShoppingCart->hasVariants($product['id'])) {
            foreach ($osC_ShoppingCart->getVariants($product['id']) as $variant) {
              $product_name .= ' - ' . $variant['groups_name'] . ': ' . $variant['values_name'];
            }
          }
          
          $products_description[] = $product_name;
        }
        
        $params['description'] = implode('<br />', $products_description);
      }
      
      
      foreach($params as $key => $value) {
        $process_button_string .= osc_draw_hidden_field($key, $value);
      }
      
      return $process_button_string;
    }
    
    function callback() {
      global $osC_Database, $osC_Currencies;
      
      foreach ($_POST as $key => $value) {
        $post_string .= $key . '=' . urlencode($value) . '&';
      } 
      
      $post_string = substr($post_string, 0, -1);
      
      $this->_transaction_response = $this->sendTransactionToGateway($this->apc_url, $post_string);
      
      if ( strstr($this->_transaction_response, 'AUTHORISED') ) {
        if ( !isset($_POST["status"]) || strtolower($_POST["status"]) == "live" ) {
          $comments = 'Nochex payment of ' .sprintf("%01.2f", $_POST["amount"]) . ' received at ' . $_POST['transaction_date'] . ' with transaction ID:' . $_POST['transaction_id'];
        }else{
          $comments = 'TEST PAYMENT of ' . sprintf("%01.2f", $_POST["amount"]) . ' received at ' . $_POST['transaction_date'] . ' with transaction ID:' . $_POST['transaction_id'];
        }
        
        osC_Order::process($_POST['order_id'], $this->order_status, $comments);
      }else {
        if ( !isset($_POST["status"]) || strtolower($_POST["status"]) == "live" ) {
          $comments = 'Nochex payment of ' . sprintf("%01.2f", $_POST["amount"]) . ' received at ' . $_POST['transaction_date'] . ' with transaction ID:' . $_POST['transaction_id'] . ' is invalid';
        }else{
          $comments = 'TEST PAYMENT of ' . sprintf("%01.2f", $_POST["amount"]) . ' received at ' . $_POST['transaction_date'] . ' with transaction ID:' . $_POST['transaction_id'] . ' is invalid';
        }
        
        osC_Order::insertOrderStatusHistory($_POST['order_id'], $this->order_status, $comments);
      }  
    }
  }
?>
