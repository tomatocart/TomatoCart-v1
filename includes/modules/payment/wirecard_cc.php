<?php
/*
  $Id: wirecard_cc.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_wirecard_cc extends osC_Payment {
    var $_title,
        $_code = 'wirecard_cc',
        $_status = false,
        $_sort_order,
        $_order_id,
        $_transaction_response;

    function osC_Payment_wirecard_cc() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;

      $this->_title = $osC_Language->get('payment_wirecard_cc_title');
      $this->_method_title = $osC_Language->get('payment_wirecard_cc_method_title');
      $this->_status = (MODULE_PAYMENT_WIRECARD_CC_STATUS == '1') ? true : false;
      $this->_sort_order = MODULE_PAYMENT_WIRECARD_CC_SORT_ORDER;

      switch (MODULE_PAYMENT_WIRECARD_CC_TRANSACTION_SERVER) {
        case 'production':
          $this->_gateway_url = 'https://' . MODULE_PAYMENT_WIRECARD_CC_USERNAME . ':' . MODULE_PAYMENT_WIRECARD_CC_PASSWORD . '@c3.wirecard.com/secure/ssl-gateway';
          break;

        default:
          $this->_gateway_url = 'https://' . MODULE_PAYMENT_WIRECARD_CC_USERNAME . ':' . MODULE_PAYMENT_WIRECARD_CC_PASSWORD . '@c3-test.wirecard.com/secure/ssl-gateway';
          break;
      }

      if ($this->_status === true) {
        if ((int)MODULE_PAYMENT_WIRECARD_CC_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_WIRECARD_CC_ORDER_STATUS_ID;
        }

        if ((int)MODULE_PAYMENT_WIRECARD_CC_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_WIRECARD_CC_ZONE);
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

          if ($check_flag === false) {
            $this->_status = false;
          }
        }
      }
    }

    function getJavascriptBlock() {
      global $osC_Language, $osC_CreditCard;

      $osC_CreditCard = new osC_CreditCard();

      $js = '  if (payment_value == "' . $this->_code . '") {' . "\n" .
            '    var wirecard_cc_owner = document.checkout_payment.wirecard_cc_owner.value;' . "\n" .
            '    var wirecard_cc_number = document.checkout_payment.wirecard_cc_number.value;' . "\n" .
            '    wirecard_cc_number = wirecard_cc_number.replace(/[^\d]/gi, "");' . "\n";

      if (MODULE_PAYMENT_WIRECARD_CC_VERIFY_WITH_CVC == '1') {
        $js .= '    var wirecard_cc_cvc = document.checkout_payment.wirecard_cc_cvc.value;' . "\n";
      }

      if (CFG_CREDIT_CARDS_VERIFY_WITH_JS == '1') {
        $js .= '    var wirecard_cc_type_match = false;' . "\n";
      }

      $js .= '    if (wirecard_cc_owner == "" || wirecard_cc_owner.length < ' . (int)CC_OWNER_MIN_LENGTH . ') {' . "\n" .
             '      error_message = error_message + "' . sprintf($osC_Language->get('payment_wirecard_cc_js_credit_card_owner'), (int)CC_OWNER_MIN_LENGTH) . '\n";' . "\n" .
             '      error = 1;' . "\n" .
             '    }' . "\n";

      $has_type_patterns = false;

      if ( (CFG_CREDIT_CARDS_VERIFY_WITH_JS == '1') && (osc_empty(MODULE_PAYMENT_WIRECARD_CC_ACCEPTED_TYPES) === false) ) {
        foreach (explode(',', MODULE_PAYMENT_WIRECARD_CC_ACCEPTED_TYPES) as $type_id) {
          if ($osC_CreditCard->typeExists($type_id)) {
            $has_type_patterns = true;

            $js .= '    if ( (wirecard_cc_type_match == false) && (wirecard_cc_number.match(' . $osC_CreditCard->getTypePattern($type_id) . ') != null) ) { ' . "\n" .
                   '      wirecard_cc_type_match = true;' . "\n" .
                   '    }' . "\n";
          }
        }
      }

      if ($has_type_patterns === true) {
        $js .= '    if ((wirecard_cc_type_match == false) || (mod10(wirecard_cc_number) == false)) {' . "\n" .
               '      error_message = error_message + "' . $osC_Language->get('payment_wirecard_cc_js_credit_card_not_accepted') . '\n";' . "\n" .
               '      error = 1;' . "\n" .
               '    }' . "\n";
      } else {
        $js .= '    if (wirecard_cc_number == "" || wirecard_cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
               '      error_message = error_message + "' . sprintf($osC_Language->get('payment_wirecard_cc_js_credit_card_number'), CC_NUMBER_MIN_LENGTH) . '\n";' . "\n" .
               '      error = 1;' . "\n" .
               '    }' . "\n";
      }

      if (MODULE_PAYMENT_WIRECARD_CC_VERIFY_WITH_CVC == '1') {
        $js .= '    if (wirecard_cc_cvc == "" || wirecard_cc_cvc.length < 3) {' . "\n" .
               '      error_message = error_message + "' . sprintf($osC_Language->get('payment_wirecard_cc_js_credit_card_cvc'), 3) . '\n";' . "\n" .
               '      error = 1;' . "\n" .
               '    }' . "\n";
      }

      $js .= '  }' . "\n";

      return $js;
    }

    function selection() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;

      for ($i=1; $i<13; $i++) {
        $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B',mktime(0,0,0,$i,1)));
      }

      $year = date('Y');
      for ($i=$year; $i < $year+10; $i++) {
        $expires_year[] = array('id' => $i, 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
      }

      $selection = array('id' => $this->_code,
                         'module' => $this->_method_title,
                         'fields' => array(array('title' => $osC_Language->get('payment_wirecard_cc_credit_card_owner'),
                                                 'field' => osc_draw_input_field('wirecard_cc_owner', $osC_ShoppingCart->getBillingAddress('firstname') . ' ' . $osC_ShoppingCart->getBillingAddress('lastname'))),
                                           array('title' => $osC_Language->get('payment_wirecard_cc_credit_card_number'),
                                                 'field' => osc_draw_input_field('wirecard_cc_number')),
                                           array('title' => $osC_Language->get('payment_wirecard_cc_credit_card_expiry_date'),
                                                 'field' => osc_draw_pull_down_menu('wirecard_cc_expires_month', $expires_month) . '&nbsp;' . osc_draw_pull_down_menu('wirecard_cc_expires_year', $expires_year))));

     if (MODULE_PAYMENT_WIRECARD_CC_VERIFY_WITH_CVC == '1') {
       $selection['fields'][] = array('title' => $osC_Language->get('payment_wirecard_cc_credit_card_cvc'),
                                      'field' => osc_draw_input_field('wirecard_cc_cvc', null, 'size="5" maxlength="4"'));
     }

      return $selection;
    }

    function pre_confirmation_check() {
      $this->_verifyData();
    }

    function confirmation() {
      global $osC_Language, $osC_CreditCard;

      $confirmation = array('title' => $this->_method_title,
                            'fields' => array(array('title' => $osC_Language->get('payment_wirecard_cc_credit_card_owner'),
                                                    'field' => $osC_CreditCard->getOwner()),
                                              array('title' => $osC_Language->get('payment_wirecard_cc_credit_card_number'),
                                                    'field' => $osC_CreditCard->getSafeNumber()),
                                              array('title' => $osC_Language->get('payment_wirecard_cc_credit_card_expiry_date'),
                                                    'field' => $osC_CreditCard->getExpiryMonth() . ' / ' . $osC_CreditCard->getExpiryYear())));

      if (MODULE_PAYMENT_WIRECARD_CC_VERIFY_WITH_CVC == '1') {
        $confirmation['fields'][] = array('title' => $osC_Language->get('payment_wirecard_cc_credit_card_cvc'),
                                          'field' => $osC_CreditCard->getCVC());
      }

      return $confirmation;
    }

    function process_button() {
      global $osC_CreditCard;

      $fields = osc_draw_hidden_field('wirecard_cc_owner', $osC_CreditCard->getOwner()) .
                osc_draw_hidden_field('wirecard_cc_expires_month', $osC_CreditCard->getExpiryMonth()) .
                osc_draw_hidden_field('wirecard_cc_expires_year', $osC_CreditCard->getExpiryYear()) .
                osc_draw_hidden_field('wirecard_cc_number', $osC_CreditCard->getNumber());

      if (MODULE_PAYMENT_WIRECARD_CC_VERIFY_WITH_CVC == '1') {
        $fields .= osc_draw_hidden_field('wirecard_cc_cvc', $osC_CreditCard->getCVC());
      }

      return $fields;
    }

    function process() {
      global $osC_Database, $osC_Customer, $osC_Currencies, $osC_ShoppingCart, $osC_Language, $messageStack, $osC_CreditCard;

      $this->_verifyData();

      $this->_order_id = osC_Order::insert();

      $post_string = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
                     '<WIRECARD_BXML xmlns:xsi="http://www.w3.org/1999/XMLSchema-instance" xsi:noNamespaceSchemaLocation="wirecard.xsd">' . "\n" .
                     '  <W_REQUEST>' . "\n" .
                     '    <W_JOB>' . "\n" .
                     '      <JobID>Job 1</JobID>' . "\n" .
                     '      <BusinessCaseSignature>' . MODULE_PAYMENT_WIRECARD_CC_BUSINESS_SIGNATURE . '</BusinessCaseSignature>' . "\n" .
                     '      <FNC_CC_PREAUTHORIZATION>' . "\n" .
                     '        <FunctionID>Preauthorization 1</FunctionID>' . "\n" .
                     '        <CC_TRANSACTION mode="' . MODULE_PAYMENT_WIRECARD_CC_TRANSACTION_MODE . '">' . "\n" .
                     '          <TransactionID>' . $this->_order_id . '</TransactionID>' . "\n" .
                     '          <CommerceType>eCommerce</CommerceType>' . "\n" .
                     '          <Amount>' . $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal(), $osC_Currencies->getCode()) * 100 . '</Amount>' . "\n" .
                     '          <Currency>' . $osC_Currencies->getCode() . '</Currency>' . "\n" .
                     '          <CountryCode>' . osC_Address::getCountryIsoCode2(STORE_COUNTRY) . '</CountryCode>' . "\n" .
                     '          <Usage>' . STORE_NAME . '</Usage>' . "\n" .
                     '          <RECURRING_TRANSACTION>' . "\n" .
                     '            <Type>Single</Type>' . "\n" .
                     '          </RECURRING_TRANSACTION>' . "\n" .
                     '          <CREDIT_CARD_DATA>' . "\n" .
                     '            <CreditCardNumber>' . $osC_CreditCard->getNumber() . '</CreditCardNumber>' . "\n";

      if (MODULE_PAYMENT_WIRECARD_CC_VERIFY_WITH_CVC == '1') {
        $post_string .= '            <CVC2>' . $osC_CreditCard->getCVC() . '</CVC2>' . "\n";
      }

      $post_string .= '            <ExpirationYear>' . $osC_CreditCard->getExpiryYear() . '</ExpirationYear>' . "\n" .
                      '            <ExpirationMonth>' . $osC_CreditCard->getExpiryMonth() . '</ExpirationMonth>' . "\n" .
                      '            <CardHolderName>' . $osC_CreditCard->getOwner() . '</CardHolderName>' . "\n" .
                      '          </CREDIT_CARD_DATA>' . "\n" .
                      '          <CONTACT_DATA>' . "\n" .
                      '            <IPAddress>' . osc_get_ip_address() . '</IPAddress>' . "\n" .
                      '          </CONTACT_DATA>' . "\n" .
                      '          <CORPTRUSTCENTER_DATA>' . "\n" .
                      '            <ADDRESS>' . "\n" .
                      '              <FirstName>' . $osC_ShoppingCart->getBillingAddress('firstname') . '</FirstName>' . "\n" .
                      '              <LastName>' . $osC_ShoppingCart->getBillingAddress('lastname') . '</LastName>' . "\n" .
                      '              <Address1>' . $osC_ShoppingCart->getBillingAddress('street_address') . '</Address1>' . "\n" .
                      '              <City>' . $osC_ShoppingCart->getBillingAddress('city') . '</City>' . "\n" .
                      '              <ZipCode>' . $osC_ShoppingCart->getBillingAddress('postcode') . '</ZipCode>' . "\n";

      if (osc_empty($osC_ShoppingCart->getBillingAddress('zone_code')) === false) {
        $post_string .= '              <State>' . $osC_ShoppingCart->getBillingAddress('zone_code') . '</State>' . "\n";
      }

      $post_string .= '              <Country>' . $osC_ShoppingCart->getBillingAddress('country_iso_code_2') . '</Country>' . "\n" .
                      '              <Phone>' . $osC_ShoppingCart->getBillingAddress('telephone_number') . '</Phone>' . "\n" .
                      '              <Email>' . $osC_Customer->getEmailAddress() . '</Email>' . "\n" .
                      '            </ADDRESS>' . "\n" .
                      '          </CORPTRUSTCENTER_DATA>' . "\n" .
                      '        </CC_TRANSACTION>' . "\n" .
                      '      </FNC_CC_PREAUTHORIZATION>' . "\n" .
                      '    </W_JOB>' . "\n" .
                      '  </W_REQUEST>' . "\n" .
                      '</WIRECARD_BXML>';
      
      $this->_transaction_response = $this->sendTransactionToGateway($this->_gateway_url, $post_string, array('Content-type: text/xml'));

      if (empty($this->_transaction_response) === false) {
        $osC_XML = new osC_XML($this->_transaction_response);
        $result = $osC_XML->toArray();
      } else {
        $result = array();
      }
      
      $error = false;      

      if (isset($result['WIRECARD_BXML']['W_RESPONSE']['W_JOB']['FNC_CC_PREAUTHORIZATION']['CC_TRANSACTION']['PROCESSING_STATUS']['FunctionResult'])) {
        if ($result['WIRECARD_BXML']['W_RESPONSE']['W_JOB']['FNC_CC_PREAUTHORIZATION']['CC_TRANSACTION']['PROCESSING_STATUS']['FunctionResult'] != 'ACK') {
          $errno = $result['WIRECARD_BXML']['W_RESPONSE']['W_JOB']['FNC_CC_PREAUTHORIZATION']['CC_TRANSACTION']['PROCESSING_STATUS']['ERROR']['Number'];
          
          echo $result['WIRECARD_BXML']['W_RESPONSE']['W_JOB']['FNC_CC_PREAUTHORIZATION']['CC_TRANSACTION']['PROCESSING_STATUS']['FunctionResult'];
          exit;

          switch ($errno) {
            case '14':
            case '20109':
              $error = $osC_Language->get('payment_wirecard_cc_error_unkown_card');
              break;

            case '33':
            case '20071':
              $error = $osC_Language->get('payment_wirecard_cc_error_' . (int)$errno);
              break;

            default:
              $error = $osC_Language->get('payment_wirecard_cc_error_general');
              break;
          }
        }
      } else {
        $error = $osC_Language->get('payment_wirecard_cc_error_general');
      }

      if ($error === false) {
        osC_Order::process($this->_order_id, $this->order_status);

        $Qtransaction = $osC_Database->query('insert into :table_orders_transactions_history (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added) values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');
        $Qtransaction->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
        $Qtransaction->bindInt(':orders_id', $this->_order_id);
        $Qtransaction->bindInt(':transaction_code', 1);
        $Qtransaction->bindValue(':transaction_return_value', $this->_transaction_response);
        $Qtransaction->bindInt(':transaction_return_status', 1);
        $Qtransaction->execute();
      } else {
        osC_Order::remove($this->_order_id);

        $messageStack->add_session('checkout_payment', $error, 'error');

        osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment&wirecard_cc_owner=' . $osC_CreditCard->getOwner() . '&wirecard_cc_expires_month=' . $osC_CreditCard->getExpiryMonth() . '&wirecard_cc_expires_year=' . $osC_CreditCard->getExpiryYear() . (MODULE_PAYMENT_WIRECARD_CC_VERIFY_WITH_CVC == '1' ? '&wirecard_cc_cvc=' . $osC_CreditCard->getCVC() : ''), 'SSL'));
      }
    }

    function _verifyData() {
      global $osC_Language, $messageStack, $osC_CreditCard;

      $osC_CreditCard = new osC_CreditCard($_POST['wirecard_cc_number'], $_POST['wirecard_cc_expires_month'], $_POST['wirecard_cc_expires_year']);
      $osC_CreditCard->setOwner($_POST['wirecard_cc_owner']);

      if (MODULE_PAYMENT_WIRECARD_CC_VERIFY_WITH_CVC == '1') {
        $osC_CreditCard->setCVC($_POST['wirecard_cc_cvc']);
      }

      if (($result = $osC_CreditCard->isValid(MODULE_PAYMENT_WIRECARD_CC_ACCEPTED_TYPES)) !== true) {
        $error = '';

        switch ($result) {
          case -2:
            $error = $osC_Language->get('payment_wirecard_cc_error_20071');
            break;

          case -3:
            $error = $osC_Language->get('payment_wirecard_cc_error_33');
            break;

          case -5:
            $error = $osC_Language->get('payment_wirecard_cc_error_not_accepted');
            break;

          default:
            $error = $osC_Language->get('payment_wirecard_cc_error_general');
            break;
        }

        $messageStack->add_session('checkout_payment', $error, 'error');

//comment out for one page checkout
        //osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment&wirecard_cc_owner=' . $osC_CreditCard->getOwner() . '&wirecard_cc_expires_month=' . $osC_CreditCard->getExpiryMonth() . '&wirecard_cc_expires_year=' . $osC_CreditCard->getExpiryYear() . (MODULE_PAYMENT_WIRECARD_CC_VERIFY_WITH_CVC == '1' ? '&wirecard_cc_cvc=' . $osC_CreditCard->getCVC() : ''), 'SSL'));
      }
    }
  }
?>
