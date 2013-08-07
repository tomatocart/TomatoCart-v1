<?php
/*
  $Id: saferpay_elv.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_saferpay_elv extends osC_Payment {
    var $_title,
        $_code = 'saferpay_elv',
        $_status = false,
        $_sort_order,
        $_order_id,
        $_transaction_response;

    function osC_Payment_saferpay_elv() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;

      $this->_title = $osC_Language->get('payment_saferpay_elv_title');
      $this->_method_title = $osC_Language->get('payment_saferpay_elv_method_title');
      $this->_status = (MODULE_PAYMENT_SAFERPAY_ELV_STATUS == '1') ? true : false;
      $this->_sort_order = MODULE_PAYMENT_SAFERPAY_ELV_SORT_ORDER;

      if ($this->_status === true) {
        if ((int)MODULE_PAYMENT_SAFERPAY_ELV_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_SAFERPAY_ELV_ORDER_STATUS_ID;
        }

        if ((int)MODULE_PAYMENT_SAFERPAY_ELV_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_SAFERPAY_ELV_ZONE);
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
      global $osC_Language;

      $js = '  if (payment_value == "' . $this->_code . '") {' . "\n" .
            '    var saferpay_elv_owner = document.checkout_payment.saferpay_elv_owner.value;' . "\n" .
            '    var saferpay_elv_account = document.checkout_payment.saferpay_elv_account.value;' . "\n" .
            '    saferpay_elv_account = saferpay_elv_account.replace(/[^\d]/gi, "");' . "\n" .
            '    if (saferpay_elv_owner == "") {' . "\n" .
            '      error_message = error_message + "' . $osC_Language->get('payment_saferpay_elv_js_error') . '\n";' . "\n" .
            '      error = 1;' . "\n" .
            '    } else if (saferpay_elv_account == "") {' . "\n" .
            '      error_message = error_message + "' . $osC_Language->get('payment_saferpay_elv_js_error') . '\n";' . "\n" .
            '      error = 1;' . "\n" .
            '    }' . "\n" .
            '  }' . "\n";

      return $js;
    }

    function selection() {
      global $osC_Language, $osC_ShoppingCart;

      $selection = array('id' => $this->_code,
                         'module' => $this->_method_title,
                         'fields' => array(array('title' => $osC_Language->get('payment_saferpay_elv_bank_owner'),
                                                 'field' => osc_draw_input_field('saferpay_elv_owner', $osC_ShoppingCart->getBillingAddress('firstname') . ' ' . $osC_ShoppingCart->getBillingAddress('lastname'))),
                                           array('title' => $osC_Language->get('payment_saferpay_elv_bank_account_number'),
                                                 'field' => osc_draw_input_field('saferpay_elv_account')),
                                           array('title' => $osC_Language->get('payment_saferpay_elv_bank_code'),
                                                 'field' => osc_draw_input_field('saferpay_elv_bank'))));

      return $selection;
    }

    function pre_confirmation_check() {
      $this->_verifyData();
    }

    function confirmation() {
      global $osC_Language;

      $confirmation = array('title' => $this->_method_title,
                            'fields' => array(array('title' => $osC_Language->get('payment_saferpay_elv_bank_owner'),
                                                    'field' => $_POST['saferpay_elv_owner']),
                                              array('title' => $osC_Language->get('payment_saferpay_elv_bank_account_number'),
                                                    'field' => str_repeat('X', strlen($_POST['saferpay_elv_account'])-3) . substr($_POST['saferpay_elv_account'], -3)),
                                              array('title' => $osC_Language->get('payment_saferpay_elv_bank_code'),
                                                    'field' => $_POST['saferpay_elv_bank'])));

      return $confirmation;
    }

    function process_button() {
      $fields = osc_draw_hidden_field('saferpay_elv_owner', $_POST['saferpay_elv_owner']) .
                osc_draw_hidden_field('saferpay_elv_account', $_POST['saferpay_elv_account']) .
                osc_draw_hidden_field('saferpay_elv_bank', $_POST['saferpay_elv_bank']);

      return $fields;
    }

    function process() {
      global $osC_Database, $osC_Customer, $osC_Currencies, $osC_ShoppingCart, $osC_Language, $messageStack;

      $this->_verifyData();

      $this->_order_id = osC_Order::insert();

      $params = array('spPassword' => MODULE_PAYMENT_SAFERPAY_CC_PASSWORD,
                      'ACCOUNTID' => MODULE_PAYMENT_SAFERPAY_CC_ACCOUNT_ID,
                      'ORDERID' => $this->_order_id,
                      'NAME' => $_POST['saferpay_elv_owner'],
                      'TRACK2' => ';59' . $_POST['saferpay_elv_bank'] . '=' . str_pad($_POST['saferpay_elv_account'], 10, '0', STR_PAD_LEFT),
                      'AMOUNT' => $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal(), $osC_Currencies->getCode()) * 100,
                      'CURRENCY' => $osC_Currencies->getCode());

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
        
        switch ($result_array['IDP attr']['RESULT']) {
          case '0': //success
            break;

          default:
            $error= $osC_Language->get('payment_saferpay_elv_error_general');
            break;
        }
      } else {
        $error= $osC_Language->get('payment_saferpay_elv_error_general');
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

        osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment&saferpay_elv_owner=' . $_POST['saferpay_elv_owner'] . '&saferpay_elv_bank=' . $_POST['saferpay_elv_bank'], 'SSL'));
      }
    }

    function _verifyData() {
      global $osC_Language, $messageStack;

      $_POST['saferpay_elv_owner'] = trim($_POST['saferpay_elv_owner']);
      $_POST['saferpay_elv_account'] = trim($_POST['saferpay_elv_account']);
      $_POST['saferpay_elv_bank'] = trim($_POST['saferpay_elv_bank']);

      if (empty($_POST['saferpay_elv_owner']) || empty($_POST['saferpay_elv_account']) || (strlen($_POST['saferpay_elv_account']) < 3) || empty($_POST['saferpay_elv_bank']) || (strlen($_POST['saferpay_elv_bank']) !== 8)) {
        $messageStack->add_session('checkout_payment', $osC_Language->get('payment_saferpay_elv_error_general'), 'error');

//comment out for one page checkout
        //osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment&saferpay_elv_owner=' . $_POST['saferpay_elv_owner'] . '&saferpay_elv_bank=' . $_POST['saferpay_elv_bank'], 'SSL'));
      }
    }
  }
?>
