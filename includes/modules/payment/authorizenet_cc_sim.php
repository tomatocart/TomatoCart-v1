<?php
/*
  $Id: authorizenet_cc_sim.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_authorizenet_cc_sim extends osC_Payment {
    var $_title,
        $_code = 'authorizenet_cc_sim',
        $_status = false,
        $_sort_order;
        
    // class constructor
    function osC_Payment_authorizenet_cc_sim() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;

      $this->_title = $osC_Language->get('payment_authorizenet_cc_sim_title');
      $this->_method_title = $osC_Language->get('payment_authorizenet_cc_sim_method_title');
      $this->_sort_order = MODULE_PAYMENT_AUTHORIZENET_CC_SIM_SORT_ORDER;
      $this->_status = ((MODULE_PAYMENT_AUTHORIZENET_CC_SIM_STATUS == '1') ? true : false);

      if (MODULE_PAYMENT_AUTHORIZENET_CC_SIM_TRANSACTION_SERVER == 'Live') {
        $this->form_action_url = 'https://secure.authorize.net/gateway/transact.dll';
      } else {
        $this->form_action_url = 'https://test.authorize.net/gateway/transact.dll';
      }
      
      if ($this->_status === true) {
        $this->order_status = (int)MODULE_PAYMENT_AUTHORIZENET_CC_SIM_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_AUTHORIZENET_CC_SIM_ORDER_STATUS_ID : (int)ORDERS_STATUS_PAID;

        if ((int)MODULE_PAYMENT_AUTHORIZENET_CC_SIM_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_AUTHORIZENET_CC_SIM_ZONE);
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
    
    function pre_confirmation_check() {
      return false;
    }
    
    function confirmation() {
      return false;
    }
    
    function process() {
      global $osC_Currencies, $osC_ShoppingCart, $messageStack, $osC_Session;
      
      header('Processing, please wait..');
      
      $error = false;
      
      if (isset($_POST['x_response_code']) && $_POST['x_response_code'] == '1') {
        if ((MODULE_PAYMENT_AUTHORIZENET_CC_SIM_MD5_HASH != NULL) && (isset($_POST['x_MD5_Hash']) && $_POST['x_MD5_Hash'] != strtoupper(md5(MODULE_PAYMENT_AUTHORIZENET_CC_SIM_MD5_HASH . MODULE_PAYMENT_AUTHORIZENET_CC_SIM_API_LOGIN_ID . $_POST['x_trans_id'] . $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal()))))) {
          $error = 'verification';
        }else if (isset($_POST['x_amount']) && ($_POST['x_amount'] != $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal()))) {
          $error = 'verification';
        }
      }else if ($_POST['x_response_code'] == '2') {
        $error = 'declined';
      }else {
        $error = 'general';
      }
      
      if ($error != false) {
        osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'checkout&sid=' . $_POST['sid'] . '&error=' . $error, '', false ,false, true));
      }else {
        $orders_id = osC_Order::insert();
        
        osC_Order::process($orders_id, $this->order_status);
        
        $osC_ShoppingCart->reset(true);
        
        // unregister session variables used during checkout
        unset($_SESSION['comments']);
        
        osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'success&sid=' . $osC_Session->getID(), 'SSL'));
      }
    }
    
    
    function process_button() {
      global $osC_Customer, $osC_Currencies, $osC_ShoppingCart, $osC_Tax, $osC_Session;
      
      $process_button_string = '';
      
      $params = array('x_login' => substr(MODULE_PAYMENT_AUTHORIZENET_CC_SIM_API_LOGIN_ID, 0, 20), 
                      'x_version' => '3.1', 
                      'x_show_form' => 'PAYMENT_FORM',
                      'x_receipt_link_met' => 'POST',
                      'x_receipt_link_url' =>  osc_href_link(FILENAME_CHECKOUT, 'process', 'SSL', false, false, true), 
                      'x_relay_response' => 'TRUE', 
                      'x_relay_url' => osc_href_link(FILENAME_CHECKOUT, 'process', 'SSL', false, false, true), 
                      'x_first_name' => substr($osC_ShoppingCart->getBillingAddress('firstname'), 0, 50), 
                      'x_last_name' => substr($osC_ShoppingCart->getBillingAddress('lastname'), 0, 50), 
                      'x_company' => substr($osC_ShoppingCart->getBillingAddress('company'), 0, 50), 
                      'x_address' => substr($osC_ShoppingCart->getBillingAddress('street_address'), 0, 60), 
                      'x_city' => substr($osC_ShoppingCart->getBillingAddress('city'), 0, 40), 
                      'x_state' => substr($osC_ShoppingCart->getBillingAddress('state'), 0, 40), 
                      'x_zip' => substr($osC_ShoppingCart->getBillingAddress('postcode'), 0, 20), 
                      'x_country' => substr($osC_ShoppingCart->getBillingAddress('country_iso_code_2'), 0, 60), 
                      'x_phone' => substr($osC_ShoppingCart->getBillingAddress('telephone_number'), 0, 25), 
                      'x_cust_id' => substr($osC_Customer->getID(), 0, 20), 
                      'x_cus_ip' => osc_get_ip_address(), 
                      'x_email' => substr($osC_Customer->getEmailAddress(), 0, 255), 
                      'x_description' => substr(STORE_NAME, 0, 255), 
                      'x_amount' => $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal()), 
                      'x_currency_code' => substr($osC_Currencies->getCode(), 0, 3), 
                      'x_method' => 'CC', 
                      'x_type' => ((MODULE_PAYMENT_AUTHORIZENET_CC_SIM_TRANSACTION_METHOD == 'Capture') ? 'AUTH_CAPTURE' : 'AUTH_ONLY'));
      
      if ($osC_ShoppingCart->hasShippingAddress()) {
        $params['x_ship_to_first_name'] = substr($osC_ShoppingCart->getShippingAddress('firstname'), 0, 50);
        $params['x_ship_to_last_name'] = substr($osC_ShoppingCart->getShippingAddress('lastname'), 0, 50);
        $params['x_ship_to_company'] = substr($osC_ShoppingCart->getShippingAddress('company'), 0, 50);
        $params['x_ship_to_address'] = substr($osC_ShoppingCart->getShippingAddress('street_address'), 0, 60);
        $params['x_ship_to_city'] = substr($osC_ShoppingCart->getShippingAddress('city'), 0, 40);
        $params['x_ship_to_state'] = substr($osC_ShoppingCart->getShippingAddress('zone_code'), 0, 40);
        $params['x_ship_to_zip'] = substr($osC_ShoppingCart->getShippingAddress('postcode'), 0, 20);
        $params['x_ship_to_country'] = substr($osC_ShoppingCart->getShippingAddress('country_iso_code_2'), 0, 60);
      }
      
      $hash_params = $this->_InsertFP(MODULE_PAYMENT_AUTHORIZENET_CC_SIM_API_LOGIN_ID, MODULE_PAYMENT_AUTHORIZENET_CC_SIM_API_TRANSACTION_KEY, $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal()), rand(1, 1000), $osC_Currencies->getCode());
      
      $params = array_merge($params, $hash_params);
      
      if (MODULE_PAYMENT_AUTHORIZENET_CC_SIM_TRANSACTION_MODE == 'Test') {
        $params['x_test_request'] = 'TRUE';
      }
      
      foreach ($params as $key => $value) {
        $process_button_string .= osc_draw_hidden_field($key, $value);
      }
      
      if ($osC_ShoppingCart->hasContents()) {
        foreach($osC_ShoppingCart->getProducts() as $key => $product) {
          $process_button_string .= osc_draw_hidden_field('x_line_item', ($key+1) . '<|>' . substr($product['name'], 0, 31) . '<|>' . substr($product['name'], 0, 255) . '<|>' . $product['quantity'] . '<|>' . $osC_Currencies->formatRaw($product['final_price']) . '<|>' . ($product['tax_class_id'] > 0 ? 'YES' : 'NO'));
        }
      }
      
      $shipping_tax = ($osC_ShoppingCart->getShippingMethod('cost')) * ($osC_Tax->getTaxRate($osC_ShoppingCart->getShippingMethod('tax_class_id'), $osC_ShoppingCart->getTaxingAddress('country_id'), $osC_ShoppingCart->getTaxingAddress('zone_id')) / 100);
      $total_tax = $osC_ShoppingCart->getTax() - $shipping_tax;
      
      if ($total_tax > 0) {
        $process_button_string .= osc_draw_hidden_field('x_tax', $osC_Currencies->formatRaw($total_tax));
      }
      
      $process_button_string .= osc_draw_hidden_field('x_freight', $osC_Currencies->formatRaw($osC_ShoppingCart->getShippingMethod('cost'))) . osc_draw_hidden_field($osC_Session->getName(), $osC_Session->getID());
      
      return $process_button_string;
    }
    
    function _hmac($key, $data) {
      if (function_exists('mhash') && defined('MHASH_MD5')) {
        return bin2hex(mhash(MHASH_MD5, $data, $key));
      }
  
      // RFC 2104 HMAC implementation for php.
      // Creates an md5 HMAC.
      // Eliminates the need to install mhash to compute a HMAC
      // Hacked by Lance Rushing
  
      $b = 64; // byte length for md5
      if (strlen($key) > $b) {
        $key = pack("H*",md5($key));
      }

      $key = str_pad($key, $b, chr(0x00));
      $ipad = str_pad('', $b, chr(0x36));
      $opad = str_pad('', $b, chr(0x5c));
      $k_ipad = $key ^ $ipad ;
      $k_opad = $key ^ $opad;

      return md5($k_opad . pack("H*",md5($k_ipad . $data)));
    }
    
    function _InsertFP($loginid, $x_tran_key, $amount, $sequence, $currency = '') {
      $tstamp = time();

      $fingerprint = $this->_hmac($x_tran_key, $loginid . '^' . $sequence . '^' . $tstamp . '^' . $amount . '^' . $currency);
      
      return array('x_fp_sequence' => $sequence, 'x_fp_timestamp' => $tstamp, 'x_fp_hash' => $fingerprint);
    }
    
    function get_error() {
      global $osC_Language;
      
      $error_message = $osC_Language->get('payment_authorizenet_cc_sim_error_general');
      $ajax_valid_message = '<p><em>' . $osC_Language->get('payment_authorizenet_cc_sim_error_ajax_valid') . '</em></p>';

      if (isset($_GET['error'])) {
        switch ($_GET['error']) {
          case 'verification':
            $error_message = $osC_Language->get('payment_authorizenet_cc_sim_error_verification');
            break;
  
          case 'declined':
            $error_message = $osC_Language->get('payment_authorizenet_cc_sim_error_declined');
            break;
  
          default:
            $error_message = $osC_Language->get('payment_authorizenet_cc_sim_error_general');
            break;
        }
      }

      $error = array('title' => $osC_Language->get('payment_authorizenet_cc_sim_error_title'),
                     'error' => $error_message . $ajax_valid_message);

      return $error;
    }
  }
?>