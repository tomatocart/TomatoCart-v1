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

/**
 * The administration side of the Saferpay Credit Card payment module
 */

  class osC_Payment_payquake_cc extends osC_Payment_Admin {

/**
 * The administrative title of the payment module
 *
 * @var string
 * @access private
 */

    var $_title;

/**
 * The code of the payment module
 *
 * @var string
 * @access private
 */

    var $_code = 'payquake_cc';

/**
 * The developers name
 *
 * @var string
 * @access private
 */

    var $_author_name = 'osCommerce';

/**
 * The developers address
 *
 * @var string
 * @access private
 */

  var $_author_www = 'http://www.oscommerce.com';

/**
 * The status of the module
 *
 * @var boolean
 * @access private
 */

    var $_status = false;

/**
 * Constructor
 */

    function osC_Payment_payquake_cc() {
      global $osC_Language;

      $this->_title = $osC_Language->get('payment_payquake_cc_title');
      $this->_description = $osC_Language->get('payment_payquake_cc_description');
      $this->_method_title = $osC_Language->get('payment_payquake_cc_method_title');
      $this->_status = (defined('MODULE_PAYMENT_PAYQUAKE_CC_STATUS') && (MODULE_PAYMENT_PAYQUAKE_CC_STATUS == '1') ? true : false);
      $this->_sort_order = (defined('MODULE_PAYMENT_PAYQUAKE_CC_SORT_ORDER') ? MODULE_PAYMENT_PAYQUAKE_CC_SORT_ORDER : '');
    }

/**
 * Checks to see if the module has been installed
 *
 * @access public
 * @return boolean
 */

    function isInstalled() {
      return (bool)defined('MODULE_PAYMENT_PAYQUAKE_CC_STATUS');
    }

/**
 * Installs the module
 *
 * @access public
 * @see osC_Payment_Admin::install()
 */

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Enable PayQuake Credit Card Module', 'MODULE_PAYMENT_PAYQUAKE_CC_STATUS', '-1', 'Do you want to accept PayQuake credit card payments?', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Account ID', 'MODULE_PAYMENT_PAYQUAKE_CC_ACCOUNT_ID', '', 'The account ID of the PayQuake account to use.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('3DES Encryption', 'MODULE_PAYMENT_PAYQUAKE_CC_3DES', '', 'Use this 3DES encryption key if it is enabled on the PayQuake Online Merchant Center.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant PIN', 'MODULE_PAYMENT_PAYQUAKE_CC_MERCHANT_PIN', '', 'Use this Merchant PIN if it is enabled on the PayQuake Online Merchant Center.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Credit Cards', 'MODULE_PAYMENT_PAYQUAKE_CC_ACCEPTED_TYPES', '', 'Accept these credit card types for this payment method.', '6', '0', 'osc_cfg_set_credit_cards_checkbox_field', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Verify With CVC', 'MODULE_PAYMENT_PAYQUAKE_CC_VERIFY_WITH_CVC', '1', 'Verify the credit card with the billing address with the Credit Card Verification Checknumber (CVC)?', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_PAYQUAKE_CC_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_PAYQUAKE_CC_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_PAYQUAKE_CC_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0' , now())");
    }

/**
 * Return the configuration parameter keys in an array
 *
 * @access public
 * @return array
 */

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_PAYMENT_PAYQUAKE_CC_STATUS',
                             'MODULE_PAYMENT_PAYQUAKE_CC_ACCOUNT_ID',
                             'MODULE_PAYMENT_PAYQUAKE_CC_3DES',
                             'MODULE_PAYMENT_PAYQUAKE_CC_MERCHANT_PIN',
                             'MODULE_PAYMENT_PAYQUAKE_CC_ACCEPTED_TYPES',
                             'MODULE_PAYMENT_PAYQUAKE_CC_VERIFY_WITH_CVC',
                             'MODULE_PAYMENT_PAYQUAKE_CC_ZONE',
                             'MODULE_PAYMENT_PAYQUAKE_CC_ORDER_STATUS_ID',
                             'MODULE_PAYMENT_PAYQUAKE_CC_SORT_ORDER');
      }

      return $this->_keys;
    }

/**
 * Returns the available post transaction actions in an array
 *
 * @access public
 * @param $history An array of transaction actions already processed
 * @return array
 */

    function getPostTransactionActions($history) {
      $actions = array();

      if (!in_array('3', $history) && !in_array('2', $history)) {
        $actions[3] = 'approveTransaction';
      }

      if (in_array('3', $history) && !in_array('2', $history)) {
        $actions[2] = 'cancelTransaction';
      }

      return $actions;
    }

/**
 * Approves the transaction at the gateway server
 *
 * @access public
 * @param $id The ID of the order
 */

    function approveTransaction($id) {
      global $osC_Database, $osC_Currencies;

      $Qorder = $osC_Database->query('select transaction_return_value from :table_orders_transactions_history where orders_id = :orders_id and transaction_code = 1 order by date_added limit 1');
      $Qorder->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
      $Qorder->bindInt(':orders_id', $id);
      $Qorder->execute();

      if ($Qorder->numberOfRows() === 1) {
        $regs = explode("\n", $Qorder->value('transaction_return_value'));
        array_shift($regs);

        $result = array();

        foreach ($regs as $response) {
          $res = explode('=', $response, 2);

          $result[strtolower(trim($res[0]))] = trim($res[1]);
        }

        if (isset($result['historyid'])) {
          $Qtotal = $osC_Database->query('select value from :table_orders_total where orders_id = :orders_id and class = :class');
          $Qtotal->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
          $Qtotal->bindInt(':orders_id', $id);
          $Qtotal->bindValue(':class', 'total');
          $Qtotal->execute();

          $params = array('action' => 'ns_quicksale_cc',
                          'acctid' => MODULE_PAYMENT_PAYQUAKE_CC_ACCOUNT_ID,
                          'amount' => number_format($Qtotal->value('value'), 2) * 100,
                          'postonly' => $result['historyid']);

          $post_string = '';

          foreach ($params as $key => $value) {
            $post_string .= $key . '=' . urlencode(trim($value)) . '&';
          }

          $post_string = substr($post_string, 0, -1);

          $this->_transaction_response = $this->sendTransactionToGateway('https://trans.merchantpartners.com/cgi-bin/process.cgi', $post_string);

          $result = array('status' => '');

          if (!empty($this->_transaction_response)) {
            $regs = explode("\n", trim($this->_transaction_response));
            array_shift($regs);

            $result = array();

            foreach ($regs as $response) {
              $res = explode('=', $response, 2);

              $result[strtolower(trim($res[0]))] = trim($res[1]);
            }
          }

          $Qtransaction = $osC_Database->query('insert into :table_orders_transactions_history (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added) values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');
          $Qtransaction->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
          $Qtransaction->bindInt(':orders_id', $id);
          $Qtransaction->bindInt(':transaction_code', 3);
          $Qtransaction->bindValue(':transaction_return_value', $this->_transaction_response);
          $Qtransaction->bindInt(':transaction_return_status', ($result['status'] == 'Accepted') ? 1 : 0);
          $Qtransaction->execute();
        }
      }
    }

/**
 * Cancels the transaction at the gateway server
 *
 * @access public
 * @param $id The ID of the order
 */

    function cancelTransaction($id) {
      global $osC_Database, $osC_Currencies;

      $Qorder = $osC_Database->query('select transaction_return_value from :table_orders_transactions_history where orders_id = :orders_id and transaction_code = 3 order by date_added limit 1');
      $Qorder->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
      $Qorder->bindInt(':orders_id', $id);
      $Qorder->execute();

      if ($Qorder->numberOfRows() === 1) {
        $regs = explode("\n", $Qorder->value('transaction_return_value'));
        array_shift($regs);

        $result = array();

        foreach ($regs as $response) {
          $res = explode('=', $response, 2);

          $result[strtolower(trim($res[0]))] = trim($res[1]);
        }

        if (isset($result['historyid'])) {
          $Qtotal = $osC_Database->query('select value from :table_orders_total where orders_id = :orders_id and class = :class');
          $Qtotal->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
          $Qtotal->bindInt(':orders_id', $id);
          $Qtotal->bindValue(':class', 'total');
          $Qtotal->execute();

          $params = array('action' => 'ns_void',
                          'acctid' => MODULE_PAYMENT_PAYQUAKE_CC_ACCOUNT_ID,
                          'amount' => number_format($Qtotal->value('value'), 2) * 100,
                          'orderkeyid' => $result['orderid'],
                          'historykeyid' => $result['historyid']);

          $post_string = '';

          foreach ($params as $key => $value) {
            $post_string .= $key . '=' . urlencode(trim($value)) . '&';
          }

          $post_string = substr($post_string, 0, -1);

          $this->_transaction_response = $this->sendTransactionToGateway('https://trans.merchantpartners.com/cgi-bin/process.cgi', $post_string);

          $result = array('status' => '');

          if (!empty($this->_transaction_response)) {
            $regs = explode("\n", trim($this->_transaction_response));
            array_shift($regs);

            $result = array();

            foreach ($regs as $response) {
              $res = explode('=', $response, 2);

              $result[strtolower(trim($res[0]))] = trim($res[1]);
            }
          }

          $Qtransaction = $osC_Database->query('insert into :table_orders_transactions_history (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added) values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');
          $Qtransaction->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
          $Qtransaction->bindInt(':orders_id', $id);
          $Qtransaction->bindInt(':transaction_code', 2);
          $Qtransaction->bindValue(':transaction_return_value', $this->_transaction_response);
          $Qtransaction->bindInt(':transaction_return_status', ($result['status'] == 'Accepted') ? 1 : 0);
          $Qtransaction->execute();
        }
      }
    }
  }
?>
