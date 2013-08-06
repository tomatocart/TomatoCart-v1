<?php
/*
  $Id: payquake_cc.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_payquake_cc extends osC_Payment {
    var $_title,
        $_code = 'payquake_cc',
        $_status = false,
        $_sort_order,
        $_order_id,
        $_transaction_response;

    function osC_Payment_payquake_cc() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;

      $this->_title = $osC_Language->get('payment_payquake_cc_title');
      $this->_method_title = $osC_Language->get('payment_payquake_cc_method_title');
      $this->_status = (MODULE_PAYMENT_PAYQUAKE_CC_STATUS == '1') ? true : false;
      $this->_sort_order = MODULE_PAYMENT_PAYQUAKE_CC_SORT_ORDER;

      if ($this->_status === true) {
        if ((int)MODULE_PAYMENT_PAYQUAKE_CC_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_PAYQUAKE_CC_ORDER_STATUS_ID;
        }

        if ((int)MODULE_PAYMENT_PAYQUAKE_CC_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_PAYQUAKE_CC_ZONE);
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
            '    var payquake_cc_owner = document.checkout_payment.payquake_cc_owner.value;' . "\n" .
            '    var payquake_cc_number = document.checkout_payment.payquake_cc_number.value;' . "\n" .
            '    payquake_cc_number = payquake_cc_number.replace(/[^\d]/gi, "");' . "\n";

      if (MODULE_PAYMENT_PAYQUAKE_CC_VERIFY_WITH_CVC == '1') {
        $js .= '    var payquake_cc_cvc = document.checkout_payment.payquake_cc_cvc.value;' . "\n";
      }

      if (CFG_CREDIT_CARDS_VERIFY_WITH_JS == '1') {
        $js .= '    var payquake_cc_type_match = false;' . "\n";
      }

      $js .= '    if (payquake_cc_owner == "" || payquake_cc_owner.length < ' . (int)CC_OWNER_MIN_LENGTH . ') {' . "\n" .
             '      error_message = error_message + "' . sprintf($osC_Language->get('payment_payquake_cc_js_credit_card_owner'), (int)CC_OWNER_MIN_LENGTH) . '\n";' . "\n" .
             '      error = 1;' . "\n" .
             '    }' . "\n";

      $has_type_patterns = false;

      if ( (CFG_CREDIT_CARDS_VERIFY_WITH_JS == '1') && (osc_empty(MODULE_PAYMENT_PAYQUAKE_CC_ACCEPTED_TYPES) === false) ) {
        foreach (explode(',', MODULE_PAYMENT_PAYQUAKE_CC_ACCEPTED_TYPES) as $type_id) {
          if ($osC_CreditCard->typeExists($type_id)) {
            $has_type_patterns = true;

            $js .= '    if ( (payquake_cc_type_match == false) && (payquake_cc_number.match(' . $osC_CreditCard->getTypePattern($type_id) . ') != null) ) { ' . "\n" .
                   '      payquake_cc_type_match = true;' . "\n" .
                   '    }' . "\n";
          }
        }
      }

      if ($has_type_patterns === true) {
        $js .= '    if ((payquake_cc_type_match == false) || (mod10(payquake_cc_number) == false)) {' . "\n" .
               '      error_message = error_message + "' . $osC_Language->get('payment_payquake_cc_js_credit_card_not_accepted') . '\n";' . "\n" .
               '      error = 1;' . "\n" .
               '    }' . "\n";
      } else {
        $js .= '    if (payquake_cc_number == "" || payquake_cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
               '      error_message = error_message + "' . sprintf($osC_Language->get('payment_payquake_cc_js_credit_card_number'), CC_NUMBER_MIN_LENGTH) . '\n";' . "\n" .
               '      error = 1;' . "\n" .
               '    }' . "\n";
      }

      if (MODULE_PAYMENT_PAYQUAKE_CC_VERIFY_WITH_CVC == '1') {
        $js .= '    if (payquake_cc_cvc == "" || payquake_cc_cvc.length < 3) {' . "\n" .
               '      error_message = error_message + "' . sprintf($osC_Language->get('payment_payquake_cc_js_credit_card_cvc'), 3) . '\n";' . "\n" .
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
                         'fields' => array(array('title' => $osC_Language->get('payment_payquake_cc_credit_card_owner'),
                                                 'field' => osc_draw_input_field('payquake_cc_owner', $osC_ShoppingCart->getBillingAddress('firstname') . ' ' . $osC_ShoppingCart->getBillingAddress('lastname'))),
                                           array('title' => $osC_Language->get('payment_payquake_cc_credit_card_number'),
                                                 'field' => osc_draw_input_field('payquake_cc_number')),
                                           array('title' => $osC_Language->get('payment_payquake_cc_credit_card_expiry_date'),
                                                 'field' => osc_draw_pull_down_menu('payquake_cc_expires_month', $expires_month) . '&nbsp;' . osc_draw_pull_down_menu('payquake_cc_expires_year', $expires_year))));

      if (MODULE_PAYMENT_PAYQUAKE_CC_VERIFY_WITH_CVC == '1') {
        $selection['fields'][] = array('title' => $osC_Language->get('payment_payquake_cc_credit_card_cvc'),
                                       'field' => osc_draw_input_field('payquake_cc_cvc', null, 'size="5" maxlength="4"'));
      }

      return $selection;
    }

    function pre_confirmation_check() {
      $this->_verifyData();
    }

    function confirmation() {
      global $osC_Language, $osC_CreditCard;

      $confirmation = array('title' => $this->_method_title,
                            'fields' => array(array('title' => $osC_Language->get('payment_payquake_cc_credit_card_owner'),
                                                    'field' => $osC_CreditCard->getOwner()),
                                              array('title' => $osC_Language->get('payment_payquake_cc_credit_card_number'),
                                                    'field' => $osC_CreditCard->getSafeNumber()),
                                              array('title' => $osC_Language->get('payment_payquake_cc_credit_card_expiry_date'),
                                                    'field' => $osC_CreditCard->getExpiryMonth() . ' / ' . $osC_CreditCard->getExpiryYear())));

      if (MODULE_PAYMENT_PAYQUAKE_CC_VERIFY_WITH_CVC == '1') {
        $confirmation['fields'][] = array('title' => $osC_Language->get('payment_payquake_cc_credit_card_cvc'),
                                          'field' => $osC_CreditCard->getCVC());
      }

      return $confirmation;
    }

    function process_button() {
      global $osC_CreditCard;

      $fields = osc_draw_hidden_field('payquake_cc_owner', $osC_CreditCard->getOwner()) .
                osc_draw_hidden_field('payquake_cc_expires_month', $osC_CreditCard->getExpiryMonth()) .
                osc_draw_hidden_field('payquake_cc_expires_year', $osC_CreditCard->getExpiryYear()) .
                osc_draw_hidden_field('payquake_cc_number', $osC_CreditCard->getNumber());

      if (MODULE_PAYMENT_PAYQUAKE_CC_VERIFY_WITH_CVC == '1') {
        $fields .= osc_draw_hidden_field('payquake_cc_cvc', $osC_CreditCard->getCVC());
      }

      return $fields;
    }

    function process() {
      global $osC_Database, $osC_Customer, $osC_Currencies, $osC_ShoppingCart, $osC_Language, $messageStack, $osC_CreditCard;

      $this->_verifyData();

      $this->_order_id = osC_Order::insert();

      $params = array('action' => 'ns_quicksale_cc',
                      'acctid' => MODULE_PAYMENT_PAYQUAKE_CC_ACCOUNT_ID,
                      'amount' => $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal(), 'USD'),
                      'ccname' => $osC_CreditCard->getOwner(),
                      'expmon' => $osC_CreditCard->getExpiryMonth(),
                      'expyear' => $osC_CreditCard->getExpiryYear(),
                      'authonly' => '1',
                      'ci_companyname' => $osC_ShoppingCart->getBillingAddress('company'),
                      'ci_billaddr1' => $osC_ShoppingCart->getBillingAddress('street_address'),
                      'ci_billcity' => $osC_ShoppingCart->getBillingAddress('city'),
                      'ci_billstate' => $osC_ShoppingCart->getBillingAddress('zone_code'),
                      'ci_billzip' => $osC_ShoppingCart->getBillingAddress('postcode'),
                      'ci_billcountry' => $osC_ShoppingCart->getBillingAddress('country_title'),
                      'ci_shipaddr1' => $osC_ShoppingCart->getShippingAddress('street_address'),
                      'ci_shipcity' => $osC_ShoppingCart->getShippingAddress('city'),
                      'ci_shipstate' => $osC_ShoppingCart->getShippingAddress('zone_code'),
                      'ci_shipzip' => $osC_ShoppingCart->getShippingAddress('postcode'),
                      'ci_shipcountry' => $osC_ShoppingCart->getShippingAddress('country_title'),
                      'ci_phone' => $osC_ShoppingCart->getBillingAddress('telephone_number'),
                      'ci_email' => $osC_Customer->getEmailAddress(),
                      'email_from' => STORE_OWNER_EMAIL_ADDRESS,
                      'ci_ipaddress' => osc_get_ip_address(),
                      'merchantordernumber' => $osC_Customer->getID(),
                      'pocustomerrefid' => $this->_order_id);

      if (!osc_empty(MODULE_PAYMENT_PAYQUAKE_CC_3DES)) {
        $key = pack('H48', MODULE_PAYMENT_PAYQUAKE_CC_3DES);
        $data = bin2hex(mcrypt_encrypt(MCRYPT_3DES, $key, $osC_CreditCard->getNumber(), MCRYPT_MODE_ECB));

        $params['ccnum'] = $data;

        unset($key);
        unset($data);
      } else {
        $params['ccnum'] = $osC_CreditCard->getNumber();
      }

      if (MODULE_PAYMENT_PAYQUAKE_CC_VERIFY_WITH_CVC == '1') {
        $params['cvv2'] = $osC_CreditCard->getCVC();
      }

      if (!osc_empty(MODULE_PAYMENT_PAYQUAKE_CC_MERCHANT_PIN)) {
        $params['merchantPIN'] = MODULE_PAYMENT_PAYQUAKE_CC_MERCHANT_PIN;
      }

      $post_string = '';

      foreach ($params as $key => $value) {
        $post_string .= $key . '=' . urlencode(trim($value)) . '&';
      }

      $post_string = substr($post_string, 0, -1);

      $this->_transaction_response = $this->sendTransactionToGateway('https://trans.merchantpartners.com/cgi-bin/process.cgi', $post_string);

      $error = false;

      if (!empty($this->_transaction_response)) {
        $regs = explode("\n", trim($this->_transaction_response));
        array_shift($regs);

        $result = array();

        foreach ($regs as $response) {
          $res = explode('=', $response, 2);

          $result[strtolower(trim($res[0]))] = trim($res[1]);
        }

        if ($result['status'] != 'Accepted') {
          $error = explode(':', $result['reason'], 3);
          $error = $error[2];

          if (empty($error)) {
            $error = $osC_Language->get('payment_payquake_cc_error_general');
          }
        }
      } else {
        $error = $osC_Language->get('payment_payquake_cc_error_general');
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

        osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment&payquake_cc_owner=' . $osC_CreditCard->getOwner() . '&payquake_cc_expires_month=' . $osC_CreditCard->getExpiryMonth() . '&payquake_cc_expires_year=' . $osC_CreditCard->getExpiryYear() . (MODULE_PAYMENT_PAYQUAKE_CC_VERIFY_WITH_CVC == '1' ? '&payquake_cc_cvc=' . $osC_CreditCard->getCVC() : ''), 'SSL'));
      }
    }

    function _verifyData() {
      global $osC_Language, $messageStack, $osC_CreditCard;

      $osC_CreditCard = new osC_CreditCard($_POST['payquake_cc_number'], $_POST['payquake_cc_expires_month'], $_POST['payquake_cc_expires_year']);
      $osC_CreditCard->setOwner($_POST['payquake_cc_owner']);

      if (MODULE_PAYMENT_PAYQUAKE_CC_VERIFY_WITH_CVC == '1') {
        $osC_CreditCard->setCVC($_POST['payquake_cc_cvc']);
      }

      if (($result = $osC_CreditCard->isValid(MODULE_PAYMENT_PAYQUAKE_CC_ACCEPTED_TYPES)) !== true) {
        $error = '';

        switch ($result) {
          case -2:
            $error = $osC_Language->get('payment_payquake_cc_error_invalid_expiry_date');
            break;

          case -3:
            $error = $osC_Language->get('payment_payquake_cc_error_expired');
            break;

          case -5:
            $error = $osC_Language->get('payment_payquake_cc_error_not_accepted');
            break;

          default:
            $error = $osC_Language->get('payment_payquake_cc_error_general');
            break;
        }

        $messageStack->add_session('checkout_payment', $error, 'error');

//comment out for one page checkout
        //osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment&payquake_cc_owner=' . $osC_CreditCard->getOwner() . '&payquake_cc_expires_month=' . $osC_CreditCard->getExpiryMonth() . '&payquake_cc_expires_year=' . $osC_CreditCard->getExpiryYear() . (MODULE_PAYMENT_PAYQUAKE_CC_VERIFY_WITH_CVC == '1' ? '&payquake_cc_cvc=' . $osC_CreditCard->getCVC() : ''), 'SSL'));
      }
    }
  }
?>
