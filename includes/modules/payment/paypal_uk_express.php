<?php
/*
  $Id: paypal_uk_express.php 1803 2008-01-11 18:16:37Z hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2008 osCommerce

  Released under the GNU General Public License
*/

  class osC_Payment_paypal_uk_express extends osC_Payment {
    var $_title,
        $_code = 'paypal_uk_express',
        $_status = false,
        $_sort_order,
        $_order_id;
        
    function osC_Payment_paypal_uk_express() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;
      
      $osC_Language->load('modules-payment');
      
      $this->_title = $osC_Language->get('payment_paypal_uk_express_title');
      $this->_method_title = $osC_Language->get('payment_paypal_uk_express_method_title');
      $this->_status = (MODULE_PAYMENT_PAYPAL_UK_EXPRESS_STATUS == '1') ? true : false;
      $this->_sort_order = MODULE_PAYMENT_PAYPAL_UK_EXPRESS_SORT_ORDER;

      switch (MODULE_PAYMENT_PAYPAL_UK_EXPRESS_SERVER) {
        case 'Production':
          $this->api_url = 'https://payflowpro.verisign.com/transaction';
          $this->paypal_url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout';
          break;

        default:
          $this->api_url = 'https://pilot-payflowpro.verisign.com/transaction';
          $this->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout';
          break;
      }

      if ($this->_status === true) {
        if ((int)MODULE_PAYMENT_PAYPAL_UK_EXPRESS_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_PAYPAL_UK_EXPRESS_ORDER_STATUS_ID;
        }

        if ((int)MODULE_PAYMENT_PAYPAL_UK_EXPRESS_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_PAYPAL_UK_EXPRESS_ZONE);
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
  
    function checkout_initialization_method() {
      global $osC_Language;
      
      $string = osc_link_object(osc_href_link(FILENAME_CHECKOUT, 'callback&module=paypal_uk_express'), osc_draw_image_button('button_express.gif', $osC_Language->get('payment_paypal_express_button_title')));

      return $string;
    }
    
    function selection() {
      return array('id' => $this->_code,
                   'module' => $this->_method_title);
    }
    
    function pre_confirmation_check() {
      return osc_href_link(FILENAME_CHECKOUT,'callback&module=paypal_uk_express');
    }

    function confirmation() {
      return false;
    }
    
    function process_button() {
      return false;
    }
    
    function process() {
      global $osC_ShoppingCart, $osC_Currencies, $osC_Customer, $osC_Language, $messageStack;
      
      $params = array('USER' => ((MODULE_PAYMENT_PAYPAL_UK_EXPRESS_USERNAME) ? MODULE_PAYMENT_PAYPAL_UK_EXPRESS_USERNAME : MODULE_PAYMENT_PAYPAL_UK_EXPRESS_VENDOR),
                      'VENDOR' => MODULE_PAYMENT_PAYPAL_UK_EXPRESS_VENDOR,
                      'PARTNER' => MODULE_PAYMENT_PAYPAL_UK_EXPRESS_PARTNER,
                      'PWD' => MODULE_PAYMENT_PAYPAL_UK_EXPRESS_PASSWORD,
                      'TRXTYPE' => ((MODULE_PAYMENT_PAYPAL_UK_EXPRESS_TRANSACTION_METHOD == 'Sale') ? 'S' : 'A'),
                      'TENDER' => 'P',
                      'EMAIL' => $osC_Customer->getEmailAddress(),
                      'TOKEN' => $_SESSION['ppe_token'],
                      'ACTION' => 'D',
                      'PAYERID' => $_SESSION['ppe_payerid'],
                      'AMT' => $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal() - $osC_ShoppingCart->getShippingMethod('cost'), $osC_Currencies->getCode()),
                      'CURRENCY' => $osC_Currencies->getCode(),
                      'BUTTONSOURCE' => PROJECT_VERSION);
      
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
            $error_message = $osC_Language->get('payment_paypal_uk_express_error_cfg_error');
            break;
          
          case '7':
            $error_message = $osC_Language->get('payment_paypal_uk_express_error_address');
            break;

          case '12':
            $error_message = $osC_Language->get('payment_paypal_uk_express_error_declined');
            break;
            
          case '1000':
            $error_message = $osC_Language->get('payment_paypal_uk_express_error_express_disabled');
            break;
            
          default:
            $error_message = $osC_Language->get('payment_paypal_uk_express_error_general');
            break;
        }
        
        $messageStack->add_session('shopping_cart', $error_message, 'error');
        
        osc_redirect(osc_href_link(FILENAME_CHECKOUT, '', 'SSL'));
      }else {
        $orders_id = osC_Order::insert();
        
        osC_Order::process($orders_id, $this->order_status);
      }
    }
    
    function callback() {
      global $osC_Database, $osC_ShoppingCart, $osC_Currencies;
      
      if (!$osC_ShoppingCart->hasContents()) {
        osc_redirect(osc_href_link(FILENAME_CHECKOUT, null, 'SSL'));
      }
      
      $params = array('USER' => (MODULE_PAYMENT_PAYPAL_UK_EXPRESS_USERNAME) ? MODULE_PAYMENT_PAYPAL_UK_EXPRESS_USERNAME : MODULE_PAYMENT_PAYPAL_UK_EXPRESS_VENDOR,
                      'VENDOR' => MODULE_PAYMENT_PAYPAL_UK_EXPRESS_VENDOR,
                      'PARTNER' => MODULE_PAYMENT_PAYPAL_UK_EXPRESS_PARTNER,
                      'PWD' => MODULE_PAYMENT_PAYPAL_UK_EXPRESS_PASSWORD,
                      'TENDER' => 'P',
                      'TRXTYPE' => ((MODULE_PAYMENT_PAYPAL_UK_EXPRESS_TRANSACTION_METHOD == 'Sale') ? 'S' : 'A'));
      
      if (isset($_GET['express_action']) && ($_GET['express_action'] == 'retrieve')) {
        self::_get_uk_express_checkout_details($params);
      } else {
        self::_set_uk_express_checkout($params);
      }
      
      exit;
    }
    
    function _get_uk_express_checkout_details($params) {
      global $osC_ShoppingCart, $osC_Database, $osC_Language, $osC_Customer, $messageStack;
      
      $params['ACTION'] = 'G';
      $params['TOKEN'] = $_GET['TOKEN'];
      
      $post_string = '';
      foreach ($params as $key => $value) {
        $post_string .= $key . '=' . urlencode(trim($value)) . '&';
      }
      $post_string = substr($post_string, 0, -1);
      
      $response = $this->sendTransactionToGateway($this->api_url, $post_string);
     
      $response_array = array();
      parse_str($response, $response_array);
      
      if ($response_array['RESULT'] == '0') {
        if ($osC_ShoppingCart->getContentType() != 'virtual') {
          $country_query = $osC_Database->query('select countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format from :table_countries where countries_iso_code_2 = :country_iso_code_2');
          $country_query->bindTable(':table_countries', TABLE_COUNTRIES);
          $country_query->bindValue(':country_iso_code_2', $response_array['SHIPTOCOUNTRYCODE']);
          $country_query->execute();
          
          $country = $country_query->toArray();
          
          $zone_name = $response_array['SHIPTOSTATE'];
          $zone_id = 0;
          
          $zone_query = $osC_Database->query('select zone_id, zone_name from :table_zones where zone_country_id = :zone_country_id and zone_code = :zone_code');
          $zone_query->bindTable(':table_zones', TABLE_ZONES);
          $zone_query->bindInt(':zone_country_id', $country['countries_id']);
          $zone_query->bindValue(':zone_code', $response_array['SHIPTOSTATE']);
          $zone_query->execute();
          
          if ($zone_query->numberOfRows()) {
            $zone = $zone_query->toArray();
            $zone_name = $zone['zone_name'];
            $zone_id = $zone['zone_id'];
          }
          
          $sendto = array('firstname' => substr($response_array['SHIPTONAME'], 0, strpos($response_array['SHIPTONAME'], ' ')),
                          'lastname' => substr($response_array['SHIPTONAME'], strpos($response_array['SHIPTONAME'], ' ')+1),
                          'company' => '',
                          'street_address' => $response_array['SHIPTOSTREET'],
                          'suburb' => '',
                          'email_address' => $response_array['EMAIL'],
                          'postcode' => $response_array['SHIPTOZIP'],
                          'city' => $response_array['SHIPTOCITY'],
                          'zone_id' => $zone_id,
                          'zone_name' => $zone_name,
                          'country_id' => $country['countries_id'],
                          'country_name' => $country['countries_name'],
                          'country_iso_code_2' => $country['countries_iso_code_2'],
                          'country_iso_code_3' => $country['countries_iso_code_3'],
                          'address_format_id' => ($country['address_format_id'] > 0 ? $country['address_format_id'] : '1'));
          
          $osC_ShoppingCart->setRawShippingAddress($sendto);
          $osC_ShoppingCart->setRawBillingAddress($sendto);
          $osC_ShoppingCart->setBillingMethod(array('id' => $this->getCode(), 'title' => $this->getMethodTitle()));
          
          if (!isset($_SESSION['payment'])) {
            $_SESSION['payment'] = $this->getCode();
          }
          
          if (!isset($_SESSION['ppe_token'])) {
            $_SESSION['ppe_token'] = $response_array['TOKEN'];
          }
          
          if (!isset($_SESSION['ppe_payerid'])) {
            $_SESSION['ppe_payerid'] = $response_array['PAYERID'];
          }
          
          if ($osC_Customer->isLoggedOn() === true) {
            osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'checkout&express=active&view=shippingMethodForm', 'SSL'));
          } else if ($this->_findEmail($response_array['EMAIL'])) {
            osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'checkout', 'SSL'));
          } else {
            osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'checkout&express=active&view=billingInformationForm', 'SSL'));
          }
        }
      }else {
        switch ($response_array['RESULT']) {
            case '1':
            case '26':
              $error_message = $osC_Language->get('payment_paypal_uk_express_error_cfg_error');
              break;
  
            case '7':
              $error_message = $osC_Language->get('payment_paypal_uk_express_error_address');
              break;
  
            case '12':
              $error_message = $osC_Language->get('payment_paypal_uk_express_error_declined');
              break;
  
            case '1000':
              $error_message = $osC_Language->get('payment_paypal_uk_express_error_express_disabled');
              break;
  
            default:
              $error_message = $osC_Language->get('payment_paypal_uk_express_error_general');
              break;
        }
        
        $messageStack->add_session('shopping_cart', $error_message, 'error');
        
        osc_redirect(osc_href_link(FILENAME_CHECKOUT, '', 'SSL'));
      }
    }
    
    function _set_uk_express_checkout($params) {
      global $osC_ShoppingCart, $osC_Customer, $osC_Language, $osC_Currencies, $messageStack;
      
      $params['ACTION'] = 'S';
      $params['CURRENCY'] = $osC_Currencies->getCode();
      $params['EMAIL'] = $osC_Customer->getEmailAddress();
      $params['AMT'] = $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal() - $osC_ShoppingCart->getShippingMethod('cost'), $osC_Currencies->getCode());
      $params['RETURNURL'] = osc_href_link(FILENAME_CHECKOUT, 'callback&module=paypal_uk_express&express_action=retrieve', 'NONSSL', true, true, true);
      $params['CANCELURL'] = osc_href_link(FILENAME_CHECKOUT, '', 'NONSSL', true, true, true);
      
      if ($osC_ShoppingCart->getContentType() == 'virtual') {
        $params['NOSHIPPING'] = '1';
      }
      
      $post_string = '';
      foreach ($params as $key => $value) {
        $post_string .= $key . '=' . urlencode(trim($value)) . '&';
      }
      $post_string = substr($post_string, 0, -1);
      
      $response = $this->sendTransactionToGateway($this->api_url, $post_string);
      
      $response_array = array();
      parse_str($response, $response_array);
      exit;
      
      if ($response_array['RESULT'] != '0') {
        switch($reponse_array['RESULT']) {
          case '1':
          case '26':
            $error_message = $osC_Language->get('payment_paypal_uk_express_error_cfg_error');
            break;
            
          case '1000':
            $error_message = $osC_Language->get('payment_paypal_uk_express_error_express_disabled');
            break;

          default:
            $error_message = $osC_Language->get('payment_paypal_uk_express_error_general');
            break;
        }
        
        $messageStack->add_session('shopping_cart', $error_message, 'error');
        
        osc_redirect(osc_href_link(FILENAME_CHECKOUT, '', 'SSL'));
      }else {
        osc_redirect($this->paypal_url . '&token=' . $response_array['TOKEN']);
      }
    }
  }
?>