<?php
/*
  $Id: psigate.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_psigate extends osC_Payment {
    var $_title,
        $_code = 'psigate',
        $_author_name = 'osCommerce',
        $_status = false,
        $_sort_order;

    function osC_Payment_psigate() {
      global $osC_Language;

      $this->_title = $osC_Language->get('payment_psigate_title');
      $this->_description = $osC_Language->get('payment_psigate_description');
      $this->_status = (defined('MODULE_PAYMENT_PSIGATE_STATUS') && (MODULE_PAYMENT_PSIGATE_STATUS == 'True') ? true : false);
      $this->_sort_order = (defined('MODULE_PAYMENT_PSIGATE_SORT_ORDER') ? MODULE_PAYMENT_PSIGATE_SORT_ORDER : null);

      if (defined('MODULE_PAYMENT_PSIGATE_STATUS')) {
        $this->initialize();
      }
    }

    function initialize() {
      global $order;

      if ((int)MODULE_PAYMENT_PSIGATE_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_PSIGATE_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

      $this->form_action_url = 'https://order.psigate.com/psigate.asp';
    }

    function update_status() {
      global $osC_Database, $order;

      if ( ($this->_status === true) && ((int)MODULE_PAYMENT_PSIGATE_ZONE > 0) ) {
        $check_flag = false;

        $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
        $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
        $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_PSIGATE_ZONE);
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

      if (MODULE_PAYMENT_PSIGATE_INPUT_MODE == 'Local') {
        $js = 'if (payment_value == "' . $this->_code . '") {' . "\n" .
              '  var psigate_cc_number = document.checkout_payment.psigate_cc_number.value;' . "\n" .
              '  if (psigate_cc_number == "" || psigate_cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
              '    error_message = error_message + "' . $osC_Language->get('payment_psigate_js_credit_card_number') . '\n";' . "\n" .
              '    error = 1;' . "\n" .
              '  }' . "\n" .
              '}' . "\n";

        return $js;
      } else {
        return false;
      }
    }

    function selection() {
      global $osC_Database, $osC_Language, $order;

      if (MODULE_PAYMENT_PSIGATE_INPUT_MODE == 'Local') {
        for ($i=1; $i<13; $i++) {
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
                           'fields' => array(array('title' => $osC_Language->get('payment_psigate_credit_card_owner'),
                                                   'field' => osc_draw_input_field('psigate_cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'])),
                                             array('title' => $osC_Language->get('payment_psigate_credit_card_type'),
                                                   'field' => osc_draw_pull_down_menu('psigate_cc_type', $credit_cards)),
                                             array('title' => $osC_Language->get('payment_psigate_credit_card_number'),
                                                   'field' => osc_draw_input_field('psigate_cc_number')),
                                             array('title' => $osC_Language->get('payment_psigate_credit_card_expiry_date'),
                                                   'field' => osc_draw_pull_down_menu('psigate_cc_expires_month', $expires_month) . '&nbsp;' . osc_draw_pull_down_menu('psigate_cc_expires_year', $expires_year))));
      } else {
        $selection = array('id' => $this->_code,
                           'module' => $this->_title);
      }

      return $selection;
    }

    function pre_confirmation_check() {
      global $osC_Language, $messageStack;

      if (MODULE_PAYMENT_PSIGATE_INPUT_MODE == 'Local') {
        $this->_verifyData();

        $this->cc_card_owner = $_POST['psigate_cc_owner'];
        $this->cc_card_type = $_POST['psigate_cc_type'];
        $this->cc_card_number = $_POST['psigate_cc_number'];
        $this->cc_expiry_month = $_POST['psigate_cc_expires_month'];
        $this->cc_expiry_year = $_POST['psigate_cc_expires_year'];
      } else {
        return false;
      }
    }

    function confirmation() {
      global $osC_Language, $order;

      if (MODULE_PAYMENT_PSIGATE_INPUT_MODE == 'Local') {
        $confirmation = array('title' => $this->_title . ': ' . $this->cc_card_type,
                              'fields' => array(array('title' => $osC_Language->get('payment_psigate_credit_card_owner'),
                                                      'field' => $this->cc_card_owner),
                                                array('title' => $osC_Language->get('payment_psigate_credit_card_number'),
                                                      'field' => substr($this->cc_card_number, 0, 4) . str_repeat('X', (strlen($this->cc_card_number) - 8)) . substr($this->cc_card_number, -4)),
                                                array('title' => $osC_Language->get('payment_psigate_credit_card_expiry_date'),
                                                      'field' => strftime('%B, %Y', mktime(0,0,0,$this->cc_expiry_month, 1, '20' . $this->cc_expiry_year)))));

        return $confirmation;
      } else {
        return false;
      }
    }

    function process_button() {
      global $osC_Database, $order, $osC_Currencies;

      switch (MODULE_PAYMENT_PSIGATE_TRANSACTION_MODE) {
        case 'Always Good':
          $transaction_mode = '1';
          break;
        case 'Always Duplicate':
          $transaction_mode = '2';
          break;
        case 'Always Decline':
          $transaction_mode = '3';
          break;
        case 'Production':
        default:
          $transaction_mode = '0';
          break;
      }

      switch (MODULE_PAYMENT_PSIGATE_TRANSACTION_TYPE) {
        case 'Sale':
          $transaction_type = '0';
          break;
        case 'PostAuth':
          $transaction_type = '2';
          break;
        case 'PreAuth':
        default:
          $transaction_type = '1';
          break;
      }

      if (MODULE_PAYMENT_PSIGATE_INPUT_MODE == 'Local') {
        $payment_error_return = '&psigate_cc_owner=' . urlencode($_POST['psigate_cc_owner']) . '&psigate_cc_expires_month=' . urlencode($_POST['psigate_cc_expires_month']) . '&psigate_cc_expires_year=' . urlencode($_POST['psigate_cc_expires_year']);
      } else {
        $payment_error_return = '';
      }

      $process_button_string = osc_draw_hidden_field('MerchantID', MODULE_PAYMENT_PSIGATE_MERCHANT_ID) .
                               osc_draw_hidden_field('FullTotal', number_format($order->info['total'] * $osC_Currencies->value(MODULE_PAYMENT_PSIGATE_CURRENCY), $osC_Currencies->currencies[MODULE_PAYMENT_PSIGATE_CURRENCY]['decimal_places'])) .
                               osc_draw_hidden_field('ThanksURL', osc_href_link(FILENAME_CHECKOUT, 'process', 'SSL')) .
                               osc_draw_hidden_field('NoThanksURL', osc_href_link(FILENAME_CHECKOUT, 'payment&payment_error=' . $this->_code . $payment_error_return, 'SSL')) .
                               osc_draw_hidden_field('Bname', ((MODULE_PAYMENT_PSIGATE_INPUT_MODE == 'Local') ? $_POST['psigate_cc_owner'] : $order->billing['firstname'] . ' ' . $order->billing['lastname'])) .
                               osc_draw_hidden_field('Baddr1', $order->billing['street_address']) .
                               osc_draw_hidden_field('Bcity', $order->billing['city']);

      if ($order->billing['country']['iso_code_2'] == 'US') {
        $Qstate = $osC_Database->query('select zone_code from :table_zones where zone_id = :zone_id');
        $Qstate->bindTable(':table_zones', TABLE_ZONES);
        $Qstate->bindInt(':zone_id', $order->billing['zone_id']);
        $Qstate->execute();

        $process_button_string .= osc_draw_hidden_field('Bstate', $Qstate->value('zone_code'));
      } else {
        $process_button_string .= osc_draw_hidden_field('Bstate', $order->billing['state']);
      }

      $process_button_string .= osc_draw_hidden_field('Bzip', $order->billing['postcode']) .
                                osc_draw_hidden_field('Bcountry', $order->billing['country']['iso_code_2']) .
                                osc_draw_hidden_field('Phone', $order->customer['telephone']) .
                                osc_draw_hidden_field('Email', $order->customer['email_address']) .
                                osc_draw_hidden_field('Sname', $order->delivery['firstname'] . ' ' . $order->delivery['lastname']) .
                                osc_draw_hidden_field('Saddr1', $order->delivery['street_address']) .
                                osc_draw_hidden_field('Scity', $order->delivery['city']) .
                                osc_draw_hidden_field('Sstate', $order->delivery['state']) .
                                osc_draw_hidden_field('Szip', $order->delivery['postcode']) .
                                osc_draw_hidden_field('Scountry', $order->delivery['country']['iso_code_2']) .
                                osc_draw_hidden_field('ChargeType', $transaction_type) .
                                osc_draw_hidden_field('Result', $transaction_mode) .
                                osc_draw_hidden_field('IP', osc_get_ip_address());

      if (MODULE_PAYMENT_PSIGATE_INPUT_MODE == 'Local') {
        $process_button_string .= osc_draw_hidden_field('CardNumber', $this->cc_card_number) .
                                  osc_draw_hidden_field('ExpMonth', $this->cc_expiry_month) .
                                  osc_draw_hidden_field('ExpYear', substr($this->cc_expiry_year, -2));
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

      if (isset($_GET['ErrMsg']) && !empty($_GET['ErrMsg'])) {
        $error = urldecode($_GET['ErrMsg']);
      } elseif (isset($_GET['Err']) && !empty($_GET['Err'])) {
        $error = urldecode($_GET['Err']);
      } elseif (isset($_GET['error']) && !empty($_GET['error'])) {
        $error = urldecode($_GET['error']);
      } else {
        $error = $osC_Language->get('payment_psigate_error_message');
      }

      return array('title' => $osC_Language->get('payment_psigate_error'),
                   'error' => $error);
    }

    function check() {
      if (!isset($this->_check)) {
        $this->_check = defined('MODULE_PAYMENT_PSIGATE_STATUS');
      }

      return $this->_check;
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable PSiGate Module', 'MODULE_PAYMENT_PSIGATE_STATUS', 'True', 'Do you want to accept PSiGate payments?', '6', '1', 'osc_cfg_set_boolean_value(array(\'True\', \'False\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant ID', 'MODULE_PAYMENT_PSIGATE_MERCHANT_ID', 'teststorewithcard', 'Merchant ID used for the PSiGate service', '6', '2', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Mode', 'MODULE_PAYMENT_PSIGATE_TRANSACTION_MODE', 'Always Good', 'Transaction mode to use for the PSiGate service', '6', '3', 'osc_cfg_set_boolean_value(array(\'Production\', \'Always Good\', \'Always Duplicate\', \'Always Decline\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Type', 'MODULE_PAYMENT_PSIGATE_TRANSACTION_TYPE', 'PreAuth', 'Transaction type to use for the PSiGate service', '6', '4', 'osc_cfg_set_boolean_value(array(\'Sale\', \'PreAuth\', \'PostAuth\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Credit Card Collection', 'MODULE_PAYMENT_PSIGATE_INPUT_MODE', 'Local', 'Should the credit card details be collected locally or remotely at PSiGate?', '6', '5', 'osc_cfg_set_boolean_value(array(\'Local\', \'Remote\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Currency', 'MODULE_PAYMENT_PSIGATE_CURRENCY', 'USD', 'The currency to use for credit card transactions', '6', '6', 'osc_cfg_set_boolean_value(array(\'CAD\', \'USD\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_PSIGATE_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_PSIGATE_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_PSIGATE_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_PAYMENT_PSIGATE_STATUS',
                             'MODULE_PAYMENT_PSIGATE_MERCHANT_ID',
                             'MODULE_PAYMENT_PSIGATE_TRANSACTION_MODE',
                             'MODULE_PAYMENT_PSIGATE_TRANSACTION_TYPE',
                             'MODULE_PAYMENT_PSIGATE_INPUT_MODE',
                             'MODULE_PAYMENT_PSIGATE_CURRENCY',
                             'MODULE_PAYMENT_PSIGATE_ZONE',
                             'MODULE_PAYMENT_PSIGATE_ORDER_STATUS_ID',
                             'MODULE_PAYMENT_PSIGATE_SORT_ORDER');
      }

      return $this->_keys;
    }

    function _verifyData() {
      global $osC_Language, $messageStack, $osC_CreditCard;

      $osC_CreditCard = new osC_CreditCard($_POST['psigate_cc_number'], $_POST['psigate_cc_expires_month'], $_POST['psigate_cc_expires_year']);
      $osC_CreditCard->setOwner($_POST['psigate_cc_owner']);

      if ($result = $osC_CreditCard->isValid() !== true) {
        $messageStack->add_session('checkout_payment', $osC_Language->get('credit_card_number_error'), 'error');

//comment out for one page checkout
        //osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment&psigate_cc_owner=' . $osC_CreditCard->getOwner() . '&psigate_cc_expires_month=' . $osC_CreditCard->getExpiryMonth() . '&psigate_cc_expires_year=' . $osC_CreditCard->getExpiryYear(), 'SSL'));
      }
    }
  }
?>
