<?php
/*
  $Id: paypal_uk_direct.php 1803 2008-01-11 18:16:37Z hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2008 osCommerce

  Released under the GNU General Public License
*/

  class osC_Payment_paypal_uk_direct extends osC_Payment {
    var $_title,
        $_code = 'paypal_uk_direct',
        $_status = false,
        $_sort_order,
        $_order_id;
        
    function osC_Payment_paypal_uk_direct() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;
      
      $osC_Language->load('modules-payment');

      $this->_title = $osC_Language->get('payment_paypal_uk_direct_title');
      $this->_method_title = $osC_Language->get('payment_paypal_uk_direct_method_title');
      $this->_status = (MODULE_PAYMENT_PAYPAL_UK_DIRECT_STATUS == '1') ? true : false;
      $this->_sort_order = MODULE_PAYMENT_PAYPAL_UK_DIRECT_SORT_ORDER;

      switch (MODULE_PAYMENT_PAYPAL_UK_DIRECT_TRANSACTION_SERVER) {
        case 'Live':
          $this->api_url = 'https://payflowpro.verisign.com/transaction';
          break;

        default:
          $this->api_url = 'https://pilot-payflowpro.verisign.com/transaction';
          break;
      }

      if ($this->_status === true) {
        if ((int)MODULE_PAYMENT_PAYPAL_UK_DIRECT_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_PAYPAL_UK_DIRECT_ORDER_STATUS_ID;
        }

        if ((int)MODULE_PAYMENT_PAYPAL_UK_DIRECT_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_PAYPAL_UK_DIRECT_ZONE);
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
                              'SWITCH' => 'Maestro',
                              'SOLO' => 'Solo');
    }
    
    function selection() {
      return array('id' => $this->_code,
                   'module' => $this->_method_title);
    }

    function confirmation() {
      global $osC_Customer, $osC_Currencies, $osC_Language, $osC_ShoppingCart;
      
      $types_array = array();
      while (list($key, $value) = each($this->cc_types)) {
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
      
      $confirmation = array('fields' =>array(array('title' => $osC_Language->get('payment_paypal_uk_direct_card_owner'),
                                                   'field' => osc_draw_input_field('cc_owner', $osC_ShoppingCart->getBillingAddress('firstname') . ' ' . $osC_ShoppingCart->getBillingAddress('lastname'))),
                                             array('title' => $osC_Language->get('payment_paypal_uk_direct_card_type'),
                                                   'field' => osc_draw_pull_down_menu('cc_type', $types_array)),
                                             array('title' => $osC_Language->get('payment_paypal_uk_direct_card_number'),
                                                   'field' => osc_draw_input_field('cc_number_nh-dns')),
                                             array('title' => $osC_Language->get('payment_paypal_uk_direct_card_valid_from'),
                                                   'field' => osc_draw_pull_down_menu('cc_starts_month', $months_array) . '&nbsp;' . osc_draw_pull_down_menu('cc_starts_year', $year_valid_from_array) . ' ' . $osC_Language->get('payment_paypal_uk_direct_card_valid_from_info')),
                                             array('title' => $osC_Language->get('payment_paypal_uk_direct_card_expires'),
                                                   'field' => osc_draw_pull_down_menu('cc_expires_month', $months_array) . '&nbsp;' . osc_draw_pull_down_menu('cc_expires_year', $year_expires_array)),
                                             array('title' => $osC_Language->get('payment_paypal_uk_direct_card_cvc'),
                                                   'field' => osc_draw_input_field('cc_cvc_nh-dns', '', 'size="5" maxlength="4"')),
                                             array('title' => $osC_Language->get('payment_paypal_uk_direct_card_issue_number'),
                                                   'field' => osc_draw_input_field('cc_issue_nh-dns', '', 'size="3" maxlength="2"') . ' ' . $osC_Language->get('payment_paypal_uk_direct_card_issue_number_info'))));

      
      return $confirmation;
    }
    
    function process() {
      global $osC_Currencies, $osC_ShoppingCart, $osC_Language, $messageStack;
      
      $currency = $osC_Currencies->getCode();
      
      if (isset($_POST['cc_owner']) && !empty($_POST['cc_owner']) && isset($_POST['cc_type']) && isset($this->cc_types[$_POST['cc_type']]) && isset($_POST['cc_number_nh-dns']) && !empty($_POST['cc_number_nh-dns'])) {
        $params = array('USER' => MODULE_PAYMENT_PAYPAL_UK_DIRECT_USERNAME || MODULE_PAYMENT_PAYPAL_UK_DIRECT_VENDOR,
                        'VENDOR' => MODULE_PAYMENT_PAYPAL_UK_DIRECT_VENDOR,
                        'PARTNER' => MODULE_PAYMENT_PAYPAL_UK_DIRECT_PARTNER,
                        'PWD' => MODULE_PAYMENT_PAYPAL_UK_DIRECT_PASSWORD,
                        'TENDER' => 'C',
                        'TRXTYPE' => ((MODULE_PAYMENT_PAYPAL_UK_DIRECT_TRANSACTION_METHOD == 'Sale') ? 'S' : 'A'),
                        'AMT' => $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal() - $osC_ShoppingCart->getShippingMethod('cost'), $currency),
                        'CURRENCY' => $currency,
                        'NAME' => $_POST['cc_owner'],
                        'STREET' => $osC_ShoppingCart->getBillingAddress('street_address'),
                        'CITY' => $osC_ShoppingCart->getBillingAddress('city'),
                        'STATE' => $osC_ShoppingCart->getBillingAddress('state'),
                        'COUNTRY' => $osC_ShoppingCart->getBillingAddress('country_iso_code_2'),
                        'ZIP' => $osC_ShoppingCart->getBillingAddress('postcode'),
                        'CLIENTIP' => osc_get_ip_address(),
                        'EMAIL' => $osC_ShoppingCart->getBillingAddress('email_address'),
                        'ACCT' => $_POST['cc_number_nh-dns'],
                        'ACCTTYPE' => $_POST['cc_type'],
                        'CARDSTART' => $_POST['cc_starts_month'] . $_POST['cc_starts_year'],
                        'EXPDATE' => $_POST['cc_expires_month'] . $_POST['cc_expires_year'],
                        'CVV2' => $_POST['cc_cvc_nh-dns'],
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
        
        if ($response_array['RESULT'] != '0') {
          switch ($response_array['RESULT']) {
            case '1':
            case '26':
              $error_message = $osC_Language->get('payment_paypal_uk_direct_error_cfg_error');
              break;
              
            case '7':
              $error_message = $osC_Language->get('payment_paypal_uk_direct_error_address');
              break;
            
            case '12':
              $error_message = $osC_Language->get('payment_paypal_uk_direct_error_declined');
              break;
              
            case '23':
            case '24':
              $error_message = $osC_Language->get('payment_paypal_uk_direct_error_invalid_credit_card');
              break;

            default:
              $error_message = $osC_Language->get('payment_paypal_uk_direct_error_general');
              break;
          }
          
          $messageStack->add_session('checkout', $error_message, 'error');
          
          osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'checkout&view=orderConfirmationForm', 'SSL'));
        }else {
          $comments = 'PayPal Website Payments Pro (US) Direct Payments perform successfully.';
          
          $orders_id = osC_Order::insert();
          osC_Order::process($orders_id, ORDERS_STATUS_PAID, $comments);
        }
      }else {
        $messageStack->add_session('checkout', $error_message, 'error');
        
        osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'checkout&view=orderConfirmationForm', 'SSL'));
      }
    }
    
    function callback() {
      return false;
    }
  }
?>