<?php
/*
  $Id: secpay.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_secpay extends osC_Payment {
    var $_title,
        $_code = 'secpay',
        $_author_name = 'osCommerce',
        $_status = false,
        $_sort_order;

    function osC_Payment_secpay() {
      global $osC_Language;

      $this->_title = $osC_Language->get('payment_secpay_title');
      $this->_description = $osC_Language->get('payment_secpay_description');
      $this->_status = (defined('MODULE_PAYMENT_SECPAY_STATUS') && (MODULE_PAYMENT_SECPAY_STATUS == 'True') ? true : false);
      $this->_sort_order = (defined('MODULE_PAYMENT_SECPAY_SORT_ORDER') ? MODULE_PAYMENT_SECPAY_SORT_ORDER : null);

      if (defined('MODULE_PAYMENT_SECPAY_STATUS')) {
        $this->initialize();
      }
    }

    function initialize() {
      global $order;

      if ((int)MODULE_PAYMENT_SECPAY_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_SECPAY_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

      $this->form_action_url = 'https://www.secpay.com/java-bin/ValCard';
    }

    function update_status() {
      global $osC_Database, $order;

      if ( ($this->_status === true) && ((int)MODULE_PAYMENT_SECPAY_ZONE > 0) ) {
        $check_flag = false;

        $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
        $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
        $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_SECPAY_ZONE);
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

    function selection() {
      return array('id' => $this->_code,
                   'module' => $this->_title);
    }

    function pre_confirmation_check() {
      return false;
    }

    function confirmation() {
      return false;
    }

    function process_button() {
      global $order, $osC_Currencies;

      switch (MODULE_PAYMENT_SECPAY_CURRENCY) {
        case 'Default Currency':
          $sec_currency = DEFAULT_CURRENCY;
          break;
        case 'Any Currency':
        default:
          $sec_currency = $_SESSION['currency'];
          break;
      }

      switch (MODULE_PAYMENT_SECPAY_TEST_STATUS) {
        case 'Always Fail':
          $test_status = 'false';
          break;
        case 'Production':
          $test_status = 'live';
          break;
        case 'Always Successful':
        default:
          $test_status = 'true';
          break;
      }

      for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
        $order_details .= 'prod=' . $order->products[$i]['name'] . ',item_amout=' . number_format($order->products[$i]['final_price'] * $osC_Currencies->value($sec_currency), $osC_Currencies->currencies[$sec_currency]['decimal_places'], '.', '') . 'x' . $order->products[$i]['qty'] . ';';
      }

      $order_details .= 'TAX=' . number_format($order->info['tax'] * $osC_Currencies->value($sec_currency), $osC_Currencies->currencies[$sec_currency]['decimal_places'], '.', '') . ';';
      $order_details .= 'SHIPPING=' . number_format($order->info['shipping_cost'] * $osC_Currencies->value($sec_currency), $osC_Currencies->currencies[$sec_currency]['decimal_places'], '.', '') . ';';

      $trans_id = STORE_NAME . date('Ymdhis');
      $digest = md5($trans_id . number_format($order->info['total'] * $osC_Currencies->value($sec_currency), $osC_Currencies->currencies[$sec_currency]['decimal_places'], '.', '') . MODULE_PAYMENT_SECPAY_DIGEST_KEY);

      $process_button_string = osc_draw_hidden_field('merchant', MODULE_PAYMENT_SECPAY_MERCHANT_ID) .
                               osc_draw_hidden_field('trans_id', $trans_id) .
                               osc_draw_hidden_field('amount', number_format($order->info['total'] * $osC_Currencies->value($sec_currency), $osC_Currencies->currencies[$sec_currency]['decimal_places'], '.', '')) .
                               osc_draw_hidden_field('bill_name', $order->billing['firstname'] . ' ' . $order->billing['lastname']) .
                               osc_draw_hidden_field('bill_addr_1', $order->billing['street_address']) .
                               osc_draw_hidden_field('bill_addr_2', $order->billing['suburb']) .
                               osc_draw_hidden_field('bill_city', $order->billing['city']) .
                               osc_draw_hidden_field('bill_state', $order->billing['state']) .
                               osc_draw_hidden_field('bill_post_code', $order->billing['postcode']) .
                               osc_draw_hidden_field('bill_country', $order->billing['country']['title']) .
                               osc_draw_hidden_field('bill_tel', $order->customer['telephone']) .
                               osc_draw_hidden_field('bill_email', $order->customer['email_address']) .
                               osc_draw_hidden_field('ship_name', $order->delivery['firstname'] . ' ' . $order->delivery['lastname']) .
                               osc_draw_hidden_field('ship_addr_1', $order->delivery['street_address']) .
                               osc_draw_hidden_field('ship_addr_2', $order->delivery['suburb']) .
                               osc_draw_hidden_field('ship_city', $order->delivery['city']) .
                               osc_draw_hidden_field('ship_state', $order->delivery['state']) .
                               osc_draw_hidden_field('ship_post_code', $order->delivery['postcode']) .
                               osc_draw_hidden_field('ship_country', $order->delivery['country']['title']) .
                               osc_draw_hidden_field('currency', $sec_currency) .
                               osc_draw_hidden_field('order', $order_details) .
                               osc_draw_hidden_field('digest', $digest) .
                               osc_draw_hidden_field('callback', osc_href_link(FILENAME_CHECKOUT, 'process', 'SSL', false) . ';' . osc_href_link(FILENAME_CHECKOUT, 'payment&payment_error=' . $this->_code, 'SSL', false)) .
                               osc_draw_hidden_field('backcallback', osc_href_link(FILENAME_CHECKOUT, 'payment', 'SSL', false)) .
                               osc_draw_hidden_field(session_name(), session_id()) .
                               osc_draw_hidden_field('options', 'test_status=' . $test_status . ',dups=false,cb_flds=' . session_name());

      return $process_button_string;
    }

    function before_process() {
      if ($_GET['valid'] == 'true') {
        list($REQUEST_URI) = split("hash=", $_SERVER['REQUEST_URI']);
        if ($_GET['hash'] != MD5($REQUEST_URI . MODULE_PAYMENT_SECPAY_DIGEST_KEY)) {
          osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment&' . session_name() . '=' . $_GET[session_name()] . '&payment_error=' . $this->_code, 'SSL', false, false));
        }
      } else {
        osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment&' . session_name() . '=' . $_GET[session_name()] . '&payment_error=' . $this->_code, 'SSL', false, false));
      }
    }

    function after_process() {
      return false;
    }

    function get_error() {
      global $osC_Language;

      if (isset($_GET['message']) && (strlen($_GET['message']) > 0)) {
        $error = urldecode($_GET['message']);
      } else {
        $error = $osC_Language->get('payment_secpay_error_message');
      }

      return array('title' => $osC_Language->get('payment_secpay_error'),
                   'error' => $error);
    }

    function check() {
      if (!isset($this->_check)) {
        $this->_check = defined('MODULE_PAYMENT_SECPAY_STATUS');
      }

      return $this->_check;
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable SECpay Module', 'MODULE_PAYMENT_SECPAY_STATUS', 'True', 'Do you want to accept SECPay payments?', '6', '1', 'osc_cfg_set_boolean_value(array(\'True\', \'False\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant ID', 'MODULE_PAYMENT_SECPAY_MERCHANT_ID', 'secpay', 'Merchant ID to use for the SECPay service', '6', '2', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Currency', 'MODULE_PAYMENT_SECPAY_CURRENCY', 'Any Currency', 'The currency to use for credit card transactions', '6', '3', 'osc_cfg_set_boolean_value(array(\'Any Currency\', \'Default Currency\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Mode', 'MODULE_PAYMENT_SECPAY_TEST_STATUS', 'Always Successful', 'Transaction mode to use for the SECPay service', '6', '4', 'osc_cfg_set_boolean_value(array(\'Always Successful\', \'Always Fail\', \'Production\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_SECPAY_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '5', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_SECPAY_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '6', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_SECPAY_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '7', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Digest Key', 'MODULE_PAYMENT_SECPAY_DIGEST_KEY', 'secpay', 'Key to use for the digest functionality', '6', '8', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_PAYMENT_SECPAY_STATUS',
                             'MODULE_PAYMENT_SECPAY_MERCHANT_ID',
                             'MODULE_PAYMENT_SECPAY_CURRENCY',
                             'MODULE_PAYMENT_SECPAY_TEST_STATUS',
                             'MODULE_PAYMENT_SECPAY_ZONE',
                             'MODULE_PAYMENT_SECPAY_ORDER_STATUS_ID',
                             'MODULE_PAYMENT_SECPAY_SORT_ORDER',
                             'MODULE_PAYMENT_SECPAY_DIGEST_KEY');
      }

      return $this->_keys;
    }
  }
?>
