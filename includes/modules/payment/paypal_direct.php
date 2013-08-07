<?php
/*
  $Id: paypal_direct.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_paypal_direct extends osC_Payment {
    var $_title,
        $_code = 'paypal_direct',
        $_status = false,
        $_sort_order,
        $_order_id;

    function osC_Payment_paypal_direct() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;
      
      $osC_Language->load('modules-payment');

      $this->_title = $osC_Language->get('payment_paypal_direct_title');
      $this->_method_title = $osC_Language->get('payment_paypal_direct_method_title');
      $this->_status = (MODULE_PAYMENT_PAYPAL_DIRECT_STATUS == '1') ? true : false;
      $this->_sort_order = MODULE_PAYMENT_PAYPAL_DIRECT_SORT_ORDER;

      switch (MODULE_PAYMENT_PAYPAL_DIRECT_SERVER) {
        case 'Production':
          $this->api_url = 'https://api-3t.paypal.com/nvp';
          break;

        default:
          $this->api_url = 'https://api-3t.sandbox.paypal.com/nvp';
          break;
      }

      if ($this->_status === true) {
        if ((int)MODULE_PAYMENT_PAYPAL_DIRECT_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_PAYPAL_DIRECT_ORDER_STATUS_ID;
        }

        if ((int)MODULE_PAYMENT_PAYPAL_DIRECT_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_PAYPAL_DIRECT_ZONE);
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
      
      $this->cc_types = array('VISA' => 'Visa',
                              'MASTERCARD' => 'MasterCard',
                              'DISCOVER' => 'Discover Card',
                              'AMEX' => 'American Express',
                              'SWITCH' => 'Maestro',
                              'SOLO' => 'Solo');
    }

    function selection() {
      return array('id' => $this->_code,
                   'module' => $this->_method_title);
    }

    function confirmation() {
      global $osC_ShoppingCart, $osC_Language;
      
      $types_array = array();
      foreach($this->cc_types as $key => $value) {
        $types_array[] = array('id' => $key,
                               'text' => $value);
      }

      $today = getdate();

      $months_array = array();
      for ($i=1; $i<13; $i++) {
        $months_array[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B',mktime(0,0,0,$i,1,2000)));
      }

      $year_valid_from_array = array();
      for ($i=$today['year']-10; $i < $today['year']+1; $i++) {
        $year_valid_from_array[] = array('id' => strftime('%Y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
      }

      $year_expires_array = array();
      for ($i=$today['year']; $i < $today['year']+10; $i++) {
        $year_expires_array[] = array('id' => strftime('%Y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
      }
      
      $confirmation = array('fields' => array(array('title' => $osC_Language->get('payment_paypal_direct_card_owner'),
                                                    'field' => osc_draw_input_field('cc_owner', $osC_ShoppingCart->getBillingAddress('firstname') . ' ' . $osC_ShoppingCart->getBillingAddress('lastname'))),
                                              array('title' => $osC_Language->get('payment_paypal_direct_card_type'),
                                                    'field' => osc_draw_pull_down_menu('cc_type', $types_array)),
                                              array('title' => $osC_Language->get('payment_paypal_direct_card_number'),
                                                    'field' => osc_draw_input_field('cc_number_nh-dns')),
                                              array('title' => $osC_Language->get('payment_paypal_direct_card_valid_from'),
                                                    'field' => osc_draw_pull_down_menu('cc_starts_month', $months_array) . '&nbsp;' . osc_draw_pull_down_menu('cc_starts_year', $year_valid_from_array) . ' ' . $osC_Language->get('payment_paypal_direct_card_valid_from_info')),
                                              array('title' => $osC_Language->get('payment_paypal_direct_card_expires'),
                                                    'field' => osc_draw_pull_down_menu('cc_expires_month', $months_array) . '&nbsp;' . osc_draw_pull_down_menu('cc_expires_year', $year_expires_array)),
                                              array('title' => $osC_Language->get('payment_paypal_direct_card_cvc'),
                                                    'field' => osc_draw_input_field('cc_cvc_nh-dns', '', 'size="5" maxlength="4"')),
                                              array('title' => $osC_Language->get('payment_paypal_direct_card_issue_number'),
                                                    'field' => osc_draw_input_field('cc_issue_nh-dns', '', 'size="3" maxlength="2"') . ' ' . $osC_Language->get('payment_paypal_direct_card_issue_number_info'))));
    
      return $confirmation;
    }

    function process_button() {
      return false;
    }
    
    function process() {
      global $osC_Currencies, $osC_ShoppingCart, $osC_Customer, $osC_Language, $messageStack;
      
      $currency = $osC_Currencies->getCode();
      
      if (isset($_POST['cc_owner']) && !empty($_POST['cc_owner']) && isset($_POST['cc_type']) && isset($this->cc_types[$_POST['cc_type']]) && isset($_POST['cc_number_nh-dns']) && !empty($_POST['cc_number_nh-dns'])) {
        $params = array('USER' => MODULE_PAYMENT_PAYPAL_DIRECT_API_USERNAME,
                        'PWD' => MODULE_PAYMENT_PAYPAL_DIRECT_API_PASSWORD,
                        'VERSION' => '3.2',
                        'SIGNATURE' => MODULE_PAYMENT_PAYPAL_DIRECT_API_SIGNATURE,
                        'METHOD' => 'DoDirectPayment',
                        'PAYMENTACTION' => ((MODULE_PAYMENT_PAYPAL_DIRECT_METHOD == 'Sale') ? 'Sale' : 'Authorization'),
                        'IPADDRESS' => osc_get_ip_address(),
                        'AMT' => $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal() - $osC_ShoppingCart->getShippingMethod('cost'), $currency),
                        'CREDITCARDTYPE' => $_POST['cc_type'],
                        'ACCT' => $_POST['cc_number_nh-dns'],
                        'STARTDATE' => $_POST['cc_starts_month'] . $_POST['cc_starts_year'],
                        'EXPDATE' => $_POST['cc_expires_month'] . $_POST['cc_expires_year'],
                        'CVV2' => $_POST['cc_cvc_nh-dns'],
                        'FIRSTNAME' => substr($_POST['cc_owner'], 0, strpos($_POST['cc_owner'], ' ')),
                        'LASTNAME' => substr($_POST['cc_owner'], strpos($_POST['cc_owner'], ' ') + 1),
                        'STREET' => $osC_ShoppingCart->getBillingAddress('street_address'),
                        'CITY' => $osC_ShoppingCart->getBillingAddress('city'),
                        'STATE' => $osC_ShoppingCart->getBillingAddress('state'),
                        'COUNTRYCODE' => $osC_ShoppingCart->getBillingAddress('country_iso_code_2'),
                        'ZIP' => $osC_ShoppingCart->getBillingAddress('postcode'),
                        'EMAIL' => $osC_Customer->getEmailAddress(),
                        'PHONENUM' => $osC_ShoppingCart->getBillingAddress('telephone_number'),
                        'CURRENCYCODE' => $currency,
                        'BUTTONSOURCE' => 'tomatcart');
        
        if ( ($_POST['cc_type'] == 'SWITCH') || ($_POST['cc_type'] == 'SOLO') ) {
          $params['ISSUENUMBER'] = $_POST['cc_issue_nh-dns'];
        }
        
        if ($osC_ShoppingCart->hasShippingAddress()) {
          $params['SHIPTONAME'] = $osC_ShoppingCart->getShippingAddress('firstname') . ' ' . $osC_ShoppingCart->getShippingAddress('lastname');
          $params['SHIPTOSTREET'] = $osC_ShoppingCart->getShippingAddress('street_address');
          $params['SHIPTOCITY'] = $osC_ShoppingCart->getShippingAddress('city');
          $params['SHIPTOSTATE'] = $osC_ShoppingCart->getShippingAddress('zone_code');
          $params['SHIPTOCOUNTRYCODE'] = $osC_ShoppingCart->getShippingAddress('country_iso_code_2');
          $params['SHIPTOZIP'] = $osC_ShoppingCart->getShippingAddress('postcode');
        }
        
        $post_string = '';
        foreach ($params as $key => $value) {
          $post_string .= $key . '=' . urlencode(trim($value)) . '&';
        }
        $post_string = substr($post_string, 0, -1);

        $response = $this->sendTransactionToGateway($this->api_url, $post_string);
        
        $response_array = array();
        parse_str($response, $response_array);
        
        if (($response_array['ACK'] != 'Success') && ($response_array['ACK'] != 'SuccessWithWarning')) {
          $messageStack->add_session('checkout', stripslashes($response_array['L_LONGMESSAGE0']), 'error');
          
          osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'checkout&view=orderConfirmationForm', 'SSL'));
        }else {
          $orders_id = osC_Order::insert();
            
          $comments = 'PayPal Website Payments Pro (US) Direct Payments [' . 'ACK: ' . $response_array['ACK'] . '; TransactionID: ' . $response_array['TRANSACTIONID'] . ';' . ']';
          osC_Order::process($orders_id, ORDERS_STATUS_PAID, $comments);
        }
      }else {
        $messageStack->add_session('checkout', $osC_Language->get('payment_paypal_direct_error_all_fields_required'), 'error');
        
        osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'checkout&view=orderConfirmationForm', 'SSL'));
      }
    }

    function callback() {
      return false;
    }
  }
?>
