<?php
/*
  $Id: saferpay_cc.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_saferpay_cc extends osC_Payment {
    var $_title,
        $_code = 'saferpay_cc',
        $_status = false,
        $_sort_order,
        $_order_id,
        $_transaction_response;

    function osC_Payment_saferpay_cc() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;

      $this->_title = $osC_Language->get('payment_saferpay_cc_title');
      $this->_method_title = $osC_Language->get('payment_saferpay_cc_method_title');
      $this->_status = (MODULE_PAYMENT_SAFERPAY_CC_STATUS == '1') ? true : false;
      $this->_sort_order = MODULE_PAYMENT_SAFERPAY_CC_SORT_ORDER;

      if ($this->_status === true) {
        if ((int)MODULE_PAYMENT_SAFERPAY_CC_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_SAFERPAY_CC_ORDER_STATUS_ID;
        }

        if ((int)MODULE_PAYMENT_SAFERPAY_CC_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_SAFERPAY_CC_ZONE);
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
            '    var saferpay_cc_owner = document.checkout_payment.saferpay_cc_owner.value;' . "\n" .
            '    var saferpay_cc_number = document.checkout_payment.saferpay_cc_number.value;' . "\n" .
            '    saferpay_cc_number = saferpay_cc_number.replace(/[^\d]/gi, "");' . "\n";

      if (MODULE_PAYMENT_SAFERPAY_CC_VERIFY_WITH_CVC == '1') {
        $js .= '    var saferpay_cc_cvc = document.checkout_payment.saferpay_cc_cvc.value;' . "\n";
      }

      if (CFG_CREDIT_CARDS_VERIFY_WITH_JS == '1') {
        $js .= '    var saferpay_cc_type_match = false;' . "\n";
      }

      $js .= '    if (saferpay_cc_owner == "" || saferpay_cc_owner.length < ' . (int)CC_OWNER_MIN_LENGTH . ') {' . "\n" .
             '      error_message = error_message + "' . sprintf($osC_Language->get('payment_saferpay_cc_js_credit_card_owner'), (int)CC_OWNER_MIN_LENGTH) . '\n";' . "\n" .
             '      error = 1;' . "\n" .
             '    }' . "\n";

      $has_type_patterns = false;

      if ( (CFG_CREDIT_CARDS_VERIFY_WITH_JS == '1') && (osc_empty(MODULE_PAYMENT_SAFERPAY_CC_ACCEPTED_TYPES) === false) ) {
        foreach (explode(',', MODULE_PAYMENT_SAFERPAY_CC_ACCEPTED_TYPES) as $type_id) {
          if ($osC_CreditCard->typeExists($type_id)) {
            $has_type_patterns = true;

            $js .= '    if ( (saferpay_cc_type_match == false) && (saferpay_cc_number.match(' . $osC_CreditCard->getTypePattern($type_id) . ') != null) ) { ' . "\n" .
                   '      saferpay_cc_type_match = true;' . "\n" .
                   '    }' . "\n";
          }
        }
      }

      if ($has_type_patterns === true) {
        $js .= '    if ((saferpay_cc_type_match == false) || (mod10(saferpay_cc_number) == false)) {' . "\n" .
               '      error_message = error_message + "' . $osC_Language->get('payment_saferpay_cc_js_credit_card_not_accepted') . '\n";' . "\n" .
               '      error = 1;' . "\n" .
               '    }' . "\n";
      } else {
        $js .= '    if (saferpay_cc_number == "" || saferpay_cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
               '      error_message = error_message + "' . sprintf($osC_Language->get('payment_saferpay_cc_js_credit_card_number'), CC_NUMBER_MIN_LENGTH) . '\n";' . "\n" .
               '      error = 1;' . "\n" .
               '    }' . "\n";
      }

      if (MODULE_PAYMENT_SAFERPAY_CC_VERIFY_WITH_CVC == '1') {
        $js .= '    if (saferpay_cc_cvc == "" || saferpay_cc_cvc.length < 3) {' . "\n" .
               '      error_message = error_message + "' . sprintf($osC_Language->get('payment_saferpay_cc_js_credit_card_cvc'), 3) . '\n";' . "\n" .
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
                         'fields' => array(array('title' => $osC_Language->get('payment_saferpay_cc_credit_card_owner'),
                                                 'field' => osc_draw_input_field('saferpay_cc_owner', $osC_ShoppingCart->getBillingAddress('firstname') . ' ' . $osC_ShoppingCart->getBillingAddress('lastname'))),
                                           array('title' => $osC_Language->get('payment_saferpay_cc_credit_card_number'),
                                                 'field' => osc_draw_input_field('saferpay_cc_number')),
                                           array('title' => $osC_Language->get('payment_saferpay_cc_credit_card_expiry_date'),
                                                 'field' => osc_draw_pull_down_menu('saferpay_cc_expires_month', $expires_month) . '&nbsp;' . osc_draw_pull_down_menu('saferpay_cc_expires_year', $expires_year))));

      if (MODULE_PAYMENT_SAFERPAY_CC_VERIFY_WITH_CVC == '1') {
        $selection['fields'][] = array('title' => $osC_Language->get('payment_saferpay_cc_credit_card_cvc'),
                                       'field' => osc_draw_input_field('saferpay_cc_cvc', null, 'size="5" maxlength="4"'));
      }

      return $selection;
    }

    function pre_confirmation_check() {
      $this->_verifyData();
    }

    function confirmation() {
      global $osC_Language, $osC_CreditCard;

      $confirmation = array('title' => $this->_method_title,
                            'fields' => array(array('title' => $osC_Language->get('payment_saferpay_cc_credit_card_owner'),
                                                    'field' => $osC_CreditCard->getOwner()),
                                              array('title' => $osC_Language->get('payment_saferpay_cc_credit_card_number'),
                                                    'field' => $osC_CreditCard->getSafeNumber()),
                                              array('title' => $osC_Language->get('payment_saferpay_cc_credit_card_expiry_date'),
                                                    'field' => $osC_CreditCard->getExpiryMonth() . ' / ' . $osC_CreditCard->getExpiryYear())));

      if (MODULE_PAYMENT_SAFERPAY_CC_VERIFY_WITH_CVC == '1') {
        $confirmation['fields'][] = array('title' => $osC_Language->get('payment_saferpay_cc_credit_card_cvc'),
                                          'field' => $osC_CreditCard->getCVC());
      }

      return $confirmation;
    }

    function process_button() {
      global $osC_CreditCard;

      $fields = osc_draw_hidden_field('saferpay_cc_owner', $osC_CreditCard->getOwner()) .
                osc_draw_hidden_field('saferpay_cc_expires_month', $osC_CreditCard->getExpiryMonth()) .
                osc_draw_hidden_field('saferpay_cc_expires_year', $osC_CreditCard->getExpiryYear()) .
                osc_draw_hidden_field('saferpay_cc_number', $osC_CreditCard->getNumber());

      if (MODULE_PAYMENT_SAFERPAY_CC_VERIFY_WITH_CVC == '1') {
        $fields .= osc_draw_hidden_field('saferpay_cc_cvc', $osC_CreditCard->getCVC());
      }

      return $fields;
    }

    function process() {
      global $osC_Database, $osC_Customer, $osC_Currencies, $osC_ShoppingCart, $osC_Language, $messageStack, $osC_CreditCard;

      $this->_verifyData();

      $this->_order_id = osC_Order::insert();

      $params = array('spPassword' => MODULE_PAYMENT_SAFERPAY_CC_PASSWORD,
                      'ACCOUNTID' => MODULE_PAYMENT_SAFERPAY_CC_ACCOUNT_ID,
                      'ORDERID' => $this->_order_id,
                      'NAME' => $osC_CreditCard->getOwner(),
                      'PAN' => $osC_CreditCard->getNumber(),
                      'EXP' => $osC_CreditCard->getExpiryMonth() . substr($osC_CreditCard->getExpiryYear(), -2),
                      'AMOUNT' => $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal(), $osC_Currencies->getCode()) * 100,
                      'CURRENCY' => $osC_Currencies->getCode());

      if (MODULE_PAYMENT_SAFERPAY_CC_VERIFY_WITH_CVC == '1') {
        $params['CVC'] = $osC_CreditCard->getCVC();
      }

      $post_string = '';

      foreach ($params as $key => $value) {
        $post_string .= $key . '=' . urlencode(trim($value)) . '&';
      }

      $post_string = substr($post_string, 0, -1);

      $this->_transaction_response = $this->sendTransactionToGateway('https://support.saferpay.de/scripts/Execute.asp', $post_string);

      $error = false;

      if (substr($this->_transaction_response, 0, 3) == 'OK:') {
        $this->_transaction_response = trim(substr($this->_transaction_response, 3));

        $osC_XML = new osC_XML($this->_transaction_response);
        $result = $osC_XML->toArray();

        switch ($result['IDP attr']['RESULT']) {
          case '0': //success
            break;

          case '62':
          case '63':
          case '64':
            $error = $osC_Language->get('payment_saferpay_cc_error_' . (int)$result['IDP attr']['RESULT']);
            break;

          default:
            $error= $osC_Language->get('payment_saferpay_cc_error_general');
            break;
        }
      } else {
        $error= $osC_Language->get('payment_saferpay_cc_error_general');
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

        osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment&saferpay_cc_owner=' . $osC_CreditCard->getOwner() . '&saferpay_cc_expires_month=' . $osC_CreditCard->getExpiryMonth() . '&saferpay_cc_expires_year=' . $osC_CreditCard->getExpiryYear() . (MODULE_PAYMENT_SAFERPAY_CC_VERIFY_WITH_CVC == '1' ? '&saferpay_cc_cvc=' . $osC_CreditCard->getCVC() : ''), 'SSL'));
      }
    }

    function _verifyData() {
      global $osC_Language, $messageStack, $osC_CreditCard;

      $osC_CreditCard = new osC_CreditCard($_POST['saferpay_cc_number'], $_POST['saferpay_cc_expires_month'], $_POST['saferpay_cc_expires_year']);
      $osC_CreditCard->setOwner($_POST['saferpay_cc_owner']);

      if (MODULE_PAYMENT_SAFERPAY_CC_VERIFY_WITH_CVC == '1') {
        $osC_CreditCard->setCVC($_POST['saferpay_cc_cvc']);
      }

      if (($result = $osC_CreditCard->isValid(MODULE_PAYMENT_SAFERPAY_CC_ACCEPTED_TYPES)) !== true) {
        $error = '';

        switch ($result) {
          case -2:
            $error = $osC_Language->get('payment_saferpay_cc_error_62');
            break;

          case -3:
            $error = $osC_Language->get('payment_saferpay_cc_error_63');
            break;

          case -5:
            $error = $osC_Language->get('payment_saferpay_cc_error_not_accepted');
            break;

          default:
            $error = $osC_Language->get('payment_saferpay_cc_error_general');
            break;
        }

        $messageStack->add_session('checkout_payment', $error, 'error');

//comment out for one page checkout
        //osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment&saferpay_cc_owner=' . $osC_CreditCard->getOwner() . '&saferpay_cc_expires_month=' . $osC_CreditCard->getExpiryMonth() . '&saferpay_cc_expires_year=' . $osC_CreditCard->getExpiryYear() . (MODULE_PAYMENT_SAFERPAY_CC_VERIFY_WITH_CVC == '1' ? '&saferpay_cc_cvc=' . $osC_CreditCard->getCVC() : ''), 'SSL'));
      }
    }
  }
?>
