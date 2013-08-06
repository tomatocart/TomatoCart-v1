<?php
/*
  $Id: saferpay_vt.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_saferpay_vt extends osC_Payment {
    var $_title,
        $_code = 'saferpay_vt',
        $_status = false,
        $_sort_order,
        $_order_id,
        $_transaction_response;

    function osC_Payment_saferpay_vt() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;

      $this->_title = $osC_Language->get('payment_saferpay_vt_title');
      $this->_method_title = $osC_Language->get('payment_saferpay_vt_method_title');
      $this->_status = (MODULE_PAYMENT_SAFERPAY_VT_STATUS == '1') ? true : false;
      $this->_sort_order = MODULE_PAYMENT_SAFERPAY_VT_SORT_ORDER;

      if ($this->_status === true) {
        if ((int)MODULE_PAYMENT_SAFERPAY_VT_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_SAFERPAY_VT_ORDER_STATUS_ID;
        }

        if ((int)MODULE_PAYMENT_SAFERPAY_VT_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_SAFERPAY_VT_ZONE);
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

    function selection() {
      return array('id' => $this->_code,
                   'module' => $this->_method_title);
    }

    function pre_confirmation_check() {
      global $osC_Customer, $osC_Currencies, $osC_ShoppingCart;

      $this->_order_id = osC_Order::insert();

      $params = array('ACCOUNTID' => MODULE_PAYMENT_SAFERPAY_CC_ACCOUNT_ID,
                      'ORDERID' => $this->_order_id,
                      'SUCCESSLINK' => osc_href_link(FILENAME_CHECKOUT, 'process', 'SSL', null, null, true),
                      'FAILLINK' => osc_href_link(FILENAME_CHECKOUT, 'payment', 'SSL', null, null, true),
                      'BACKLINK' => osc_href_link(FILENAME_CHECKOUT, 'payment', 'SSL', null, null, true),
                      'AMOUNT' => $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal(), $osC_Currencies->getCode()) * 100,
                      'CURRENCY' => $osC_Currencies->getCode(),
                      'DESCRIPTION' => STORE_NAME,
                      'ALLOWCOLLECT' => 'no',
                      'DELIVERY' => 'no');

      $post_string = '';

      foreach ($params as $key => $value) {
        $post_string .= $key . '=' . urlencode(trim($value)) . '&';
      }

      $post_string = substr($post_string, 0, -1);

      $this->_transaction_response = $this->sendTransactionToGateway('http://support.saferpay.de/scripts/CreatePayInit.asp', $post_string);

      $this->form_action_url = $this->_transaction_response;
    }

    function process() {
      global $osC_Database, $osC_Language, $messageStack;

      $this->_verifyData();

      $params = array('DATA' => $_GET['DATA'],
                      'SIGNATURE' => $_GET['SIGNATURE']);

      $post_string = '';

      foreach ($params as $key => $value) {
        $post_string .= $key . '=' . urlencode(trim($value)) . '&';
      }

      $post_string = substr($post_string, 0, -1);

      $this->_transaction_response = $this->sendTransactionToGateway('http://support.saferpay.de/scripts/VerifyPayConfirm.asp', $post_string);

      $error = false;

      if (substr($this->_transaction_response, 0, 3) != 'OK:') {
        $error = true;
      }

/* HPDL; performs capture
      if (substr($this->_transaction_response, 0, 3) == 'OK:') {
        $result = array();
        parse_str(substr($this->_transaction_response, 3), $result);

        $params = array('ACCOUNTID' => MODULE_PAYMENT_SAFERPAY_CC_ACCOUNT_ID,
                        'ID' => $result['ID'],
                        'TOKEN' => $result['TOKEN']);

        $post_string = '';

        foreach ($params as $key => $value) {
          $post_string .= $key . '=' . urlencode(trim($value)) . '&';
        }

        $post_string = substr($post_string, 0, -1);

        $this->_transaction_response = $this->sendTransactionToGateway('http://support.saferpay.de/scripts/PayComplete.asp', $post_string);

        if ($this->_transaction_response != 'OK') {
          $error = true;
        }
      } else {
        $error = true;
      }
*/

      if ($error === false) {
        $osC_XML = new osC_XML($_GET['DATA']);
        $result = $osC_XML->toArray();

        $this->_order_id = $result['IDP attr']['ORDERID'];

        osC_Order::process($this->_order_id, $this->order_status);

        $Qtransaction = $osC_Database->query('insert into :table_orders_transactions_history (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added) values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');
        $Qtransaction->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
        $Qtransaction->bindInt(':orders_id', $this->_order_id);
        $Qtransaction->bindInt(':transaction_code', 1);
        $Qtransaction->bindValue(':transaction_return_value', $_GET['DATA']);
        $Qtransaction->bindInt(':transaction_return_status', 1);
        $Qtransaction->execute();
      } else {
        osC_Order::remove($this->_order_id);

        $messageStack->add_session('checkout_payment', $osC_Language->get('payment_saferpay_vt_error_general'), 'error');

        osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment', 'SSL'));
      }
    }

    function _verifyData() {
      global $osC_Language, $messageStack;

      if (isset($_GET['DATA']) && (empty($_GET['DATA']) === false) && isset($_GET['SIGNATURE']) && (empty($_GET['SIGNATURE']) === false)) {
        if (ereg("^[a-zA-Z0-9]+$", $_GET['SIGNATURE']) && preg_match('/^<IDP\s+([a-zA-Z0-9]+="[a-zA-Z0-9().\s-]*"\s*)*\/>$/', $_GET['DATA'])) {
          return true;
        }
      }

      $messageStack->add_session('checkout_payment', $osC_Language->get('payment_saferpay_vt_error_general'), 'error');

//comment out for one page checkout
      //osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment', 'SSL'));
    }
  }
?>
