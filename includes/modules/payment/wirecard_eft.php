<?php
/*
  $Id: wirecard_eft.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_wirecard_eft extends osC_Payment {
    var $_title,
        $_code = 'wirecard_eft',
        $_status = false,
        $_sort_order,
        $_order_id,
        $_transaction_response;

    function osC_Payment_wirecard_eft() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;

      $this->_title = $osC_Language->get('payment_wirecard_eft_title');
      $this->_method_title = $osC_Language->get('payment_wirecard_eft_method_title');
      $this->_status = (MODULE_PAYMENT_WIRECARD_EFT_STATUS == '1') ? true : false;
      $this->_sort_order = MODULE_PAYMENT_WIRECARD_EFT_SORT_ORDER;

      switch (MODULE_PAYMENT_WIRECARD_EFT_TRANSACTION_SERVER) {
        case 'production':
          $this->_gateway_url = 'https://' . MODULE_PAYMENT_WIRECARD_EFT_USERNAME . ':' . MODULE_PAYMENT_WIRECARD_EFT_PASSWORD . '@c3.wirecard.com/secure/ssl-gateway';
          break;

        default:
          $this->_gateway_url = 'https://' . MODULE_PAYMENT_WIRECARD_EFT_USERNAME . ':' . MODULE_PAYMENT_WIRECARD_EFT_PASSWORD . '@c3-test.wirecard.com/secure/ssl-gateway';
          break;
      }

      if ($this->_status === true) {
        if ((int)MODULE_PAYMENT_WIRECARD_EFT_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_WIRECARD_EFT_ORDER_STATUS_ID;
        }

        if ((int)MODULE_PAYMENT_WIRECARD_EFT_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_WIRECARD_EFT_ZONE);
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
            '    var wirecard_eft_owner_first = document.checkout_payment.wirecard_eft_owner_first.value;' . "\n" .
            '    var wirecard_eft_owner_last = document.checkout_payment.wirecard_eft_owner_last.value;' . "\n" .
            '    var wirecard_eft_account = document.checkout_payment.wirecard_eft_account.value;' . "\n" .
            '    wirecard_eft_account = wirecard_eft_account.replace(/[^\d]/gi, "");' . "\n" .
            '    if ( (wirecard_eft_owner_first == "") || (wirecard_eft_owner_last == "") || (wirecard_eft_account == "") ) {' . "\n" .
            '      error_message = error_message + "' . $osC_Language->get('payment_wirecard_eft_js_error') . '\n";' . "\n" .
            '      error = 1;' . "\n" .
            '    }' . "\n" .
            '  }' . "\n";

      return $js;
    }

    function selection() {
      global $osC_Language, $osC_ShoppingCart;

      $account_types_array = array();
      foreach ($this->_getAccountTypes() as $key => $type) {
        $account_types_array[] = array('id' => $key,
                                       'text' => $type);
      }

      $selection = array('id' => $this->_code,
                         'module' => $this->_method_title,
                         'fields' => array(array('title' => $osC_Language->get('payment_wirecard_eft_bank_owner_firstname'),
                                                 'field' => osc_draw_input_field('wirecard_eft_owner_first', $osC_ShoppingCart->getBillingAddress('firstname'))),
                                           array('title' => $osC_Language->get('payment_wirecard_eft_bank_owner_lastname'),
                                                 'field' => osc_draw_input_field('wirecard_eft_owner_last', $osC_ShoppingCart->getBillingAddress('lastname'))),
                                           array('title' => $osC_Language->get('payment_wirecard_eft_account_type'),
                                                 'field' => osc_draw_pull_down_menu('wirecard_eft_account_type', $account_types_array)),
                                           array('title' => $osC_Language->get('payment_wirecard_eft_bank_account_number'),
                                                 'field' => osc_draw_input_field('wirecard_eft_account')),
                                           array('title' => $osC_Language->get('payment_wirecard_eft_bank_code'),
                                                 'field' => osc_draw_input_field('wirecard_eft_bank')),
                                           array('title' => $osC_Language->get('payment_wirecard_eft_check_number'),
                                                 'field' => osc_draw_input_field('wirecard_eft_check_number'))));

      if ($osC_ShoppingCart->getBillingAddress('country_iso_code_2') == 'IT') {
        $selection['fields'][] = array('title' => $osC_Language->get('payment_wirecard_eft_id_number'),
                                       'field' => osc_draw_input_field('wirecard_eft_id_number'));
      }

      return $selection;
    }

    function pre_confirmation_check() {
      $this->_verifyData();
    }

    function confirmation() {
      global $osC_Language, $osC_ShoppingCart;

      $confirmation = array('title' => $this->_method_title,
                            'fields' => array(array('title' => $osC_Language->get('payment_wirecard_eft_bank_owner'),
                                                    'field' => $_POST['wirecard_eft_owner_first'] . ' ' . $_POST['wirecard_eft_owner_last']),
                                              array('title' => $osC_Language->get('payment_wirecard_eft_account_type'),
                                                    'field' => $this->_getAccountTypes($_POST['wirecard_eft_account_type'])),
                                              array('title' => $osC_Language->get('payment_wirecard_eft_bank_account_number'),
                                                    'field' => str_repeat('X', strlen($_POST['wirecard_eft_account'])-3) . substr($_POST['wirecard_eft_account'], -3)),
                                              array('title' => $osC_Language->get('payment_wirecard_eft_bank_code'),
                                                    'field' => $_POST['wirecard_eft_bank']),
                                              array('title' => $osC_Language->get('payment_wirecard_eft_check_number'),
                                                    'field' => $_POST['wirecard_eft_check_number'])));

      if ($osC_ShoppingCart->getBillingAddress('country_iso_code_2') == 'IT') {
        $confirmation['fields'][] = array('title' => $osC_Language->get('payment_wirecard_eft_id_number'),
                                          'field' => $_POST['wirecard_eft_id_number']);
      }

      return $confirmation;
    }

    function process_button() {
      global $osC_ShoppingCart;

      $fields = osc_draw_hidden_field('wirecard_eft_owner_first', $_POST['wirecard_eft_owner_first']) .
                osc_draw_hidden_field('wirecard_eft_owner_last', $_POST['wirecard_eft_owner_last']) .
                osc_draw_hidden_field('wirecard_eft_account_type', $_POST['wirecard_eft_account_type']) .
                osc_draw_hidden_field('wirecard_eft_account', $_POST['wirecard_eft_account']) .
                osc_draw_hidden_field('wirecard_eft_bank', $_POST['wirecard_eft_bank']) .
                osc_draw_hidden_field('wirecard_eft_check_number', $_POST['wirecard_eft_check_number']);

      if ($osC_ShoppingCart->getBillingAddress('country_iso_code_2') == 'IT') {
        $fields .= osc_draw_hidden_field('wirecard_eft_id_number', $_POST['wirecard_eft_id_number']);
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
                     '      <BusinessCaseSignature>' . MODULE_PAYMENT_WIRECARD_EFT_BUSINESS_SIGNATURE . '</BusinessCaseSignature>' . "\n" .
                     '      <FNC_FT_DEBIT>' . "\n" .
                     '        <FunctionID>Debit 1</FunctionID>' . "\n" .
                     '        <FT_TRANSACTION mode="' . MODULE_PAYMENT_WIRECARD_EFT_TRANSACTION_MODE . '">' . "\n" .
                     '          <TransactionID>' . $this->_order_id . '</TransactionID>' . "\n" .
                     '          <EXTERNAL_ACCOUNT>' . "\n" .
                     '            <FirstName>' . $_POST['wirecard_eft_owner_first'] . '</FirstName>' . "\n" .
                     '            <LastName>' . $_POST['wirecard_eft_owner_last'] . '</LastName>' . "\n" .
                     '            <AccountNumber>' . $_POST['wirecard_eft_account'] . '</AccountNumber>' . "\n" .
                     '            <AccountType>' . $_POST['wirecard_eft_account_type'] . '</AccountType>' . "\n" .
                     '            <BankCode>' . $_POST['wirecard_eft_bank_code'] . '</BankCode>' . "\n" .
                     '            <Country>' . $osC_ShoppingCart->getBillingAddress('country_iso_code_2') . '</Country>' . "\n" .
                     '            <CheckNumber>' . $_POST['wirecard_eft_check_number'] . '</CheckNumber>' . "\n";

      if ($osC_ShoppingCart->getBillingAddress('country_iso_code_2') == 'IT') {
        $post_string .= '            <COUNTRY_SPECIFIC>' . "\n" .
                        '              <IdentificationNumber>' . $_POST['wirecard_eft_id_number'] . '</IdentificationNumber>' . "\n" .
                        '            </COUNTRY_SPECIFIC>' . "\n";
      }

      $post_string .= '          </EXTERNAL_ACCOUNT>' . "\n" .
                      '          <Amount>' . $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal(), $osC_Currencies->getCode()) * 100 . '</Amount>' . "\n" .
                      '          <Currency>' . $osC_Currencies->getCode() . '</Currency>' . "\n" .
                      '          <CORPTRUSTCENTER_DATA>' . "\n" .
                      '            <ADDRESS>' . "\n" .
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
                      '        </FT_TRANSACTION>' . "\n" .
                      '      </FNC_FT_DEBIT>' . "\n" .
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

      if (isset($result['WIRECARD_BXML']['W_RESPONSE']['W_JOB']['FNC_FT_DEBIT']['FT_TRANSACTION']['PROCESSING_STATUS']['FunctionResult'])) {
        if ($result['WIRECARD_BXML']['W_RESPONSE']['W_JOB']['FNC_FT_DEBIT']['FT_TRANSACTION']['PROCESSING_STATUS']['FunctionResult'] != 'ACK') {
//          $errno = $result['WIRECARD_BXML']['W_RESPONSE']['W_JOB']['FNC_FT_DEBIT']['FT_TRANSACTION']['PROCESSING_STATUS']['DETAIL']['ReturnCode'];

//          switch ($errno) {
//            default:
              $error = $osC_Language->get('payment_wirecard_eft_error_general');
//              break;
//          }
        }
      } else {
        $error = $osC_Language->get('payment_wirecard_eft_error_general');
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

        osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment&wirecard_eft_owner_first=' . $_POST['wirecard_eft_owner_first'] . '&wirecard_eft_owner_last=' . $_POST['wirecard_eft_owner_last'] . '&wirecard_eft_account_type=' . $_POST['wirecard_eft_account_type'] . '&wirecard_eft_bank=' . $_POST['wirecard_eft_bank'] . '&wirecard_eft_check_number=' . $_POST['wirecard_eft_check_number'] . (($osC_ShoppingCart->getBillingAddress('country_iso_code_2') == 'IT') ? '&wirecard_eft_id_number=' . $_POST['wirecard_eft_id_number'] : ''), 'SSL'));
      }
    }

    function _verifyData() {
      global $osC_Language, $messageStack, $osC_ShoppingCart;

      $_POST['wirecard_eft_owner_first'] = trim($_POST['wirecard_eft_owner_first']);
      $_POST['wirecard_eft_owner_last'] = trim($_POST['wirecard_eft_owner_last']);
      $_POST['wirecard_eft_account'] = trim($_POST['wirecard_eft_account']);
      $_POST['wirecard_eft_bank'] = trim($_POST['wirecard_eft_bank']);
      $_POST['wirecard_eft_check_number'] = trim($_POST['wirecard_eft_check_number']);

      if ($osC_ShoppingCart->getBillingAddress('country_iso_code_2') == 'IT') {
        $_POST['wirecard_eft_id_number'] = trim($_POST['wirecard_eft_id_number']);
      }

      if (empty($_POST['wirecard_eft_owner_first']) || empty($_POST['wirecard_eft_owner_last']) || empty($_POST['wirecard_eft_account']) || (strlen($_POST['wirecard_eft_account']) < 3) || empty($_POST['wirecard_eft_bank']) || (strlen($_POST['wirecard_eft_bank']) !== 8) || (in_array($_POST['wirecard_eft_account_type'], array('C', 'S')) === false)) {
        $messageStack->add_session('checkout_payment', $osC_Language->get('payment_wirecard_eft_error_general'), 'error');

//comment out for one page checkout
        //osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment&wirecard_eft_owner_first=' . $_POST['wirecard_eft_owner_first'] . '&wirecard_eft_owner_last=' . $_POST['wirecard_eft_owner_last'] . '&wirecard_eft_account_type=' . $_POST['wirecard_eft_account_type'] . '&wirecard_eft_bank=' . $_POST['wirecard_eft_bank'] . '&wirecard_eft_check_number=' . $_POST['wirecard_eft_check_number'] . (($osC_ShoppingCart->getBillingAddress('country_iso_code_2') == 'IT') ? '&wirecard_eft_id_number=' . $_POST['wirecard_eft_id_number'] : ''), 'SSL'));
      }
    }

    function _getAccountTypes($key = '') {
      global $osC_Language;

      $types = array('C' => $osC_Language->get('payment_wirecard_eft_account_type_checking'),
                     'S' => $osC_Language->get('payment_wirecard_eft_account_type_savings'));

      if ( (empty($key) === false) && isset($types[$key]) ) {
        return $types[$key];
      }

      return $types;
    }
  }
?>
