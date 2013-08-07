<?php
/*
  $Id: ipayment.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_ipayment extends osC_Payment {
    var $_title,
        $_code = 'ipayment',
        $_author_name = 'osCommerce',
        $_status = false,
        $_sort_order;

    function osC_Payment_ipayment() {
      global $osC_Language;

      $this->_title = $osC_Language->get('payment_ipayment_title');
      $this->_description = $osC_Language->get('payment_ipayment_description');
      $this->_status = (defined('MODULE_PAYMENT_IPAYMENT_STATUS') && (MODULE_PAYMENT_IPAYMENT_STATUS == 'True') ? true : false);
      $this->_sort_order = (defined('MODULE_PAYMENT_IPAYMENT_SORT_ORDER') ? MODULE_PAYMENT_IPAYMENT_SORT_ORDER : null);

      if (defined('MODULE_PAYMENT_IPAYMENT_STATUS')) {
        $this->initialize();
      }
    }

    function initialize() {
      global $order;

      if ((int)MODULE_PAYMENT_IPAYMENT_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_IPAYMENT_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

      $this->form_action_url = 'https://ipayment.de/merchant/' . MODULE_PAYMENT_IPAYMENT_ID . '/processor.php';
    }

    function update_status() {
      global $osC_Database, $order;

      if ( ($this->_status === true) && ((int)MODULE_PAYMENT_IPAYMENT_ZONE > 0) ) {
        $check_flag = false;

        $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
        $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
        $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_IPAYMENT_ZONE);
        $Qcheck->bindInt(':zone_country_id', $order->billing['country']['id']);
        $Qcheck->execute();

        while ($Qcheck->next()) {
          if ($Qcheck->valueInt('zone_id') < 1) {
            $check_flag = true;
            break;
          } elseif ($Qcheck->valueInt('zone_id') == $order->billing['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->_status = false;
        }
      }
    }

    function getJavascriptBlock() {
      global $osC_Language;

      $js = '  if (payment_value == "' . $this->_code . '") {' . "\n" .
            '    var cc_owner = document.checkout_payment.ipayment_cc_owner.value;' . "\n" .
            '    var cc_number = document.checkout_payment.ipayment_cc_number.value;' . "\n" .
            '    if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
            '      error_message = error_message + "' . sprintf($osC_Language->get('payment_ipayment_js_credit_card_owner'), CC_OWNER_MIN_LENGTH) . '\n";' . "\n" .
            '      error = 1;' . "\n" .
            '    }' . "\n" .
            '    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
            '      error_message = error_message + "' . sprintf($osC_Language->get('payment_ipayment_js_credit_card_number'), CC_NUMBER_MIN_LENGTH) . '\n";' . "\n" .
            '      error = 1;' . "\n" .
            '    }' . "\n" .
            '  }' . "\n";

      return $js;
    }

    function selection() {
      global $osC_Database, $osC_Language, $order;

      for ($i=1; $i < 13; $i++) {
        $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B',mktime(0,0,0,$i,1,2000)));
      }

      $today = getdate();
      for ($i=$today['year']; $i < $today['year']+10; $i++) {
        $expires_year[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
      }

      $Qcredit_cards = $osC_Database->query('select credit_card_name, credit_card_code from :table_credit_cards where credit_card_status = :credit_card_status');

      $Qcredit_cards->bindRaw(':table_credit_cards', TABLE_CREDIT_CARDS);
      $Qcredit_cards->bindInt(':credit_card_status', '1');
      $Qcredit_cards->setCache('credit-cards');
      $Qcredit_cards->execute();

      while ($Qcredit_cards->next()) {
        $credit_cards[] = array('id' => $Qcredit_cards->value('credit_card_code'), 'text' => $Qcredit_cards->value('credit_card_name'));
      }

      $Qcredit_cards->freeResult();

      $selection = array('id' => $this->_code,
                         'module' => $this->_title,
                         'fields' => array(array('title' => $osC_Language->get('payment_ipayment_credit_card_owner'),
                                                 'field' => osc_draw_input_field('ipayment_cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'])),
                                           array('title' => $osC_Language->get('payment_ipayment_credit_card_type'),
                                                 'field' => osc_draw_pull_down_menu('ipayment_cc_type', $credit_cards)),
                                           array('title' => $osC_Language->get('payment_ipayment_credit_card_number'),
                                                 'field' => osc_draw_input_field('ipayment_cc_number')),
                                           array('title' => $osC_Language->get('payment_ipayment_credit_card_expiry_date'),
                                                 'field' => osc_draw_pull_down_menu('ipayment_cc_expires_month', $expires_month) . '&nbsp;' . osc_draw_pull_down_menu('ipayment_cc_expires_year', $expires_year)),
                                           array('title' => $osC_Language->get('payment_ipayment_credit_card_checknumber'),
                                                 'field' => osc_draw_input_field('ipayment_cc_checkcode', null, 'size="4" maxlength="4"') . '&nbsp;<small>' . $osC_Language->get('payment_ipayment_credit_card_checknumber_location') . '</small>')));

      return $selection;
    }

    function pre_confirmation_check() {
      global $osC_Language, $messageStack;

      $this->_verifyData();

      $this->cc_card_owner = $_POST['ipayment_cc_owner'];
      $this->cc_card_type = $_POST['ipayment_cc_type'];
      $this->cc_card_number = $_POST['ipayment_cc_number'];
      $this->cc_expiry_month = $_POST['ipayment_cc_expires_month'];
      $this->cc_expiry_year = $_POST['ipayment_cc_expires_year'];
      $this->cc_checkcode = $_POST['ipayment_cc_checkcode'];
    }

    function confirmation() {
      global $osC_Language;

      $confirmation = array('title' => $this->_title . ': ' . $this->cc_card_type,
                            'fields' => array(array('title' => $osC_Language->get('payment_ipayment_credit_card_owner'),
                                                    'field' => $this->cc_card_owner),
                                              array('title' => $osC_Language->get('payment_ipayment_credit_card_number'),
                                                    'field' => substr($this->cc_card_number, 0, 4) . str_repeat('X', (strlen($this->cc_card_number) - 8)) . substr($this->cc_card_number, -4)),
                                              array('title' => $osC_Language->get('payment_ipayment_credit_card_expiry_date'),
                                                    'field' => strftime('%B, %Y', mktime(0,0,0,$this->cc_expiry_month, 1, '20' . $this->cc_expiry_year)))));

      if (!empty($this->cc_checkcode)) {
        $confirmation['fields'][] = array('title' => $osC_Language->get('payment_ipayment_credit_card_checknumber'),
                                          'field' => $this->cc_checkcode);
      }

      return $confirmation;
    }

    function process_button() {
      global $order, $osC_Currencies, $osC_Language;

      switch (MODULE_PAYMENT_IPAYMENT_CURRENCY) {
        case 'Always EUR':
          $trx_currency = 'EUR';
          break;
        case 'Always USD':
          $trx_currency = 'USD';
          break;
        case 'Either EUR or USD, else EUR':
          if ( ($_SESSION['currency'] == 'EUR') || ($_SESSION['currency'] == 'USD') ) {
            $trx_currency = $_SESSION['currency'];
          } else {
            $trx_currency = 'EUR';
          }
          break;
        case 'Either EUR or USD, else USD':
          if ( ($_SESSION['currency'] == 'EUR') || ($_SESSION['currency'] == 'USD') ) {
            $trx_currency = $_SESSION['currency'];
          } else {
            $trx_currency = 'USD';
          }
          break;
      }

      $payment_error_return = 'ipayment_cc_owner=' . urlencode($_POST['ipayment_cc_owner']) . '&ipayment_cc_expires_month=' . urlencode($_POST['ipayment_cc_expires_month']) . '&ipayment_cc_expires_year=' . urlencode($_POST['ipayment_cc_expires_year']) . '&ipayment_cc_checkcode=' . urlencode($_POST['ipayment_cc_checkcode']);

      $process_button_string = osc_draw_hidden_field('trxuser_id', MODULE_PAYMENT_IPAYMENT_USER_ID) .
                               osc_draw_hidden_field('trxpassword', MODULE_PAYMENT_IPAYMENT_PASSWORD) .
                               osc_draw_hidden_field('trx_amount', number_format($order->info['total'] * 100 * $osC_Currencies->value($trx_currency), 0, '','')) .
                               osc_draw_hidden_field('trx_currency', $trx_currency) .
                               osc_draw_hidden_field('trx_paymenttyp', 'cc') .
                               osc_draw_hidden_field('addr_name', $this->cc_card_owner) .
                               osc_draw_hidden_field('addr_street', $order->billing['street_address']) .
                               osc_draw_hidden_field('addr_city', $order->billing['city']) .
                               osc_draw_hidden_field('addr_zip', $order->billing['postcode']) .
                               osc_draw_hidden_field('addr_country', $order->billing['country']['iso_code_2']) .
                               osc_draw_hidden_field('addr_telefon', $order->customer['telephone']) .
                               osc_draw_hidden_field('addr_email', $order->customer['email_address']) .
                               osc_draw_hidden_field('error_lang', ($osC_Language->getCode() == 'en') ? 'en' : 'de') .
                               osc_draw_hidden_field('silent', '1') .
                               osc_draw_hidden_field('silent_error_url', osc_href_link(FILENAME_CHECKOUT, 'payment&payment_error=' . $this->_code . '&' . $payment_error_return, 'SSL')) .
                               osc_draw_hidden_field('redirect_url', osc_href_link(FILENAME_CHECKOUT, 'process', 'SSL')) .
                               osc_draw_hidden_field('cc_number', $this->cc_card_number) .
                               osc_draw_hidden_field('cc_expdate_month', $this->cc_expiry_month) .
                               osc_draw_hidden_field('cc_expdate_year', $this->cc_expiry_year);


      if (!empty($this->cc_checkcode)) {
        $process_button_string .= osc_draw_hidden_field('cc_checkcode', $this->cc_checkcode);
      }

      if (!osc_empty(MODULE_PAYMENT_IPAYMENT_SECURITY_KEY)) {
        $process_button_string .= osc_draw_hidden_field('trx_securityhash', md5(MODULE_PAYMENT_IPAYMENT_USER_ID . number_format($order->info['total'] * 100 * $osC_Currencies->value($trx_currency), 0, '','') . $trx_currency . MODULE_PAYMENT_IPAYMENT_PASSWORD . MODULE_PAYMENT_IPAYMENT_SECURITY_KEY));
      }

      return $process_button_string;
    }

    function before_process() {
      return false;
    }

    function after_process() {
      return false;
    }

    function get_error() {
      global $osC_Language;

      $error = array('title' => $osC_Language->get('payment_ipayment_error_heading'),
                     'error' => (isset($_GET['ret_errormsg']) ? urldecode($_GET['ret_errormsg']) : $osC_Language->get('payment_ipayment_error_message')));

      return $error;
    }

    function check() {
      if (!isset($this->_check)) {
        $this->_check = defined('MODULE_PAYMENT_IPAYMENT_STATUS');
      }

      return $this->_check;
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable iPayment Module', 'MODULE_PAYMENT_IPAYMENT_STATUS', 'True', 'Do you want to accept iPayment payments?', '6', '1', 'osc_cfg_set_boolean_value(array(\'True\', \'False\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Account Number', 'MODULE_PAYMENT_IPAYMENT_ID', '99999', 'The account number used for the iPayment service', '6', '2', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('User ID', 'MODULE_PAYMENT_IPAYMENT_USER_ID', '99999', 'The user ID for the iPayment service', '6', '3', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('User Password', 'MODULE_PAYMENT_IPAYMENT_PASSWORD', '0', 'The user password for the iPayment service', '6', '4', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Security Key', 'MODULE_PAYMENT_IPAYMENT_SECURITY_KEY', '', 'The security key used to generate the security hash', '6', '5', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Currency', 'MODULE_PAYMENT_IPAYMENT_CURRENCY', 'Either EUR or USD, else EUR', 'The currency to use for credit card transactions', '6', '6', 'osc_cfg_set_boolean_value(array(\'Always EUR\', \'Always USD\', \'Either EUR or USD, else EUR\', \'Either EUR or USD, else USD\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_IPAYMENT_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '7', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_IPAYMENT_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '8', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_IPAYMENT_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '9', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_PAYMENT_IPAYMENT_STATUS',
                             'MODULE_PAYMENT_IPAYMENT_ID',
                             'MODULE_PAYMENT_IPAYMENT_USER_ID',
                             'MODULE_PAYMENT_IPAYMENT_PASSWORD',
                             'MODULE_PAYMENT_IPAYMENT_SECURITY_KEY',
                             'MODULE_PAYMENT_IPAYMENT_CURRENCY',
                             'MODULE_PAYMENT_IPAYMENT_ZONE',
                             'MODULE_PAYMENT_IPAYMENT_ORDER_STATUS_ID',
                             'MODULE_PAYMENT_IPAYMENT_SORT_ORDER');
      }

      return $this->_keys;
    }

    function _verifyData() {
      global $osC_Language, $messageStack, $osC_CreditCard;

      $osC_CreditCard = new osC_CreditCard($_POST['ipayment_cc_number'], $_POST['ipayment_cc_expires_month'], $_POST['ipayment_cc_expires_year']);
      $osC_CreditCard->setOwner($_POST['ipayment_cc_owner']);

      if ($result = $osC_CreditCard->isValid() !== true) {
        $messageStack->add_session('checkout_payment', $osC_Language->get('credit_card_number_error'), 'error');

//comment out for one page checkout
        //osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment&ipayment_cc_owner=' . $osC_CreditCard->getOwner() . '&ipayment_cc_expires_month=' . $osC_CreditCard->getExpiryMonth() . '&ipayment_cc_expires_year=' . $osC_CreditCard->getExpiryYear(), 'SSL'));
      }
    }
  }
?>
