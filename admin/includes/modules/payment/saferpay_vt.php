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

/**
 * The administration side of the Saferpay Virtual Terminal payment module
 */

  class osC_Payment_saferpay_vt extends osC_Payment_Admin {

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

    var $_code = 'saferpay_vt';

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

    function osC_Payment_saferpay_vt() {
      global $osC_Language;

      $this->_title = $osC_Language->get('payment_saferpay_vt_title');
      $this->_description = $osC_Language->get('payment_saferpay_vt_description');
      $this->_method_title = $osC_Language->get('payment_saferpay_vt_method_title');
      $this->_status = (defined('MODULE_PAYMENT_SAFERPAY_VT_STATUS') && (MODULE_PAYMENT_SAFERPAY_VT_STATUS == '1') ? true : false);
      $this->_sort_order = (defined('MODULE_PAYMENT_SAFERPAY_VT_SORT_ORDER') ? MODULE_PAYMENT_SAFERPAY_VT_SORT_ORDER : '');
    }

/**
 * Checks to see if the module has been installed
 *
 * @access public
 * @return boolean
 */

    function isInstalled() {
      return (bool)defined('MODULE_PAYMENT_SAFERPAY_VT_STATUS');
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

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Enable Saferpay Virtual Terminal Module', 'MODULE_PAYMENT_SAFERPAY_VT_STATUS', '-1', 'Do you want to accept Saferpay Virtual Terminal payments?', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Account ID', 'MODULE_PAYMENT_SAFERPAY_VT_ACCOUNT_ID', '', 'The account ID of the Saferpay account to use.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Special Hosting Password', 'MODULE_PAYMENT_SAFERPAY_VT_PASSWORD', '', 'The special hosting password to use when connecting to the payment gateway.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_SAFERPAY_VT_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_SAFERPAY_VT_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_SAFERPAY_VT_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0' , now())");
    }

/**
 * Return the configuration parameter keys in an array
 *
 * @access public
 * @return array
 */

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_PAYMENT_SAFERPAY_VT_STATUS',
                             'MODULE_PAYMENT_SAFERPAY_VT_ACCOUNT_ID',
                             'MODULE_PAYMENT_SAFERPAY_VT_PASSWORD',
                             'MODULE_PAYMENT_SAFERPAY_VT_ZONE',
                             'MODULE_PAYMENT_SAFERPAY_VT_ORDER_STATUS_ID',
                             'MODULE_PAYMENT_SAFERPAY_VT_SORT_ORDER');
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
      $actions = array(4 => 'inquiryTransaction');

      if ( (in_array('3', $history) === false) && (in_array('2', $history) === false) ) {
        $actions[3] = 'approveTransaction';
      }

      if (in_array('2', $history) === false) {
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
      global $osC_Database;

      $Qorder = $osC_Database->query('select transaction_return_value from :table_orders_transactions_history where orders_id = :orders_id and transaction_code = 1 order by date_added limit 1');
      $Qorder->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
      $Qorder->bindInt(':orders_id', $id);
      $Qorder->execute();

      if ($Qorder->numberOfRows() === 1) {
        $osC_XML = new osC_XML($Qorder->value('transaction_return_value'));
        $result = $osC_XML->toArray();

        if (isset($result['IDP attr']['ID'])) {
          $params = array('spPassword' => MODULE_PAYMENT_SAFERPAY_VT_PASSWORD,
                          'ACCOUNTID' => MODULE_PAYMENT_SAFERPAY_VT_ACCOUNT_ID,
                          'ID' => $result['IDP attr']['ID']);

          $post_string = '';

          foreach ($params as $key => $value) {
            $post_string .= $key . '=' . urlencode(trim($value)) . '&';
          }

          $post_string = substr($post_string, 0, -1);

          $this->_transaction_response = $this->sendTransactionToGateway('https://support.saferpay.de/scripts/PayComplete.asp', $post_string);

          $Qtransaction = $osC_Database->query('insert into :table_orders_transactions_history (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added) values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');
          $Qtransaction->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
          $Qtransaction->bindInt(':orders_id', $id);
          $Qtransaction->bindInt(':transaction_code', 3);
          $Qtransaction->bindValue(':transaction_return_value', $this->_transaction_response);
          $Qtransaction->bindInt(':transaction_return_status', ($this->_transaction_response == 'OK') ? 1 : 0);
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
      global $osC_Database;

      $Qorder = $osC_Database->query('select transaction_return_value from :table_orders_transactions_history where orders_id = :orders_id and transaction_code = 1 order by date_added limit 1');
      $Qorder->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
      $Qorder->bindInt(':orders_id', $id);
      $Qorder->execute();

      if ($Qorder->numberOfRows() === 1) {
        $osC_XML = new osC_XML($Qorder->value('transaction_return_value'));
        $result = $osC_XML->toArray();

        if (isset($result['IDP attr']['ID'])) {
          $params = array('spPassword' => MODULE_PAYMENT_SAFERPAY_VT_PASSWORD,
                          'ACCOUNTID' => MODULE_PAYMENT_SAFERPAY_VT_ACCOUNT_ID,
                          'ID' => $result['IDP attr']['ID'],
                          'ACTION' => 'Cancel');

          $post_string = '';

          foreach ($params as $key => $value) {
            $post_string .= $key . '=' . urlencode(trim($value)) . '&';
          }

          $post_string = substr($post_string, 0, -1);

          $this->_transaction_response = $this->sendTransactionToGateway('https://support.saferpay.de/scripts/PayComplete.asp', $post_string);

          $Qtransaction = $osC_Database->query('insert into :table_orders_transactions_history (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added) values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');
          $Qtransaction->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
          $Qtransaction->bindInt(':orders_id', $id);
          $Qtransaction->bindInt(':transaction_code', 2);
          $Qtransaction->bindValue(':transaction_return_value', $this->_transaction_response);
          $Qtransaction->bindInt(':transaction_return_status', ($this->_transaction_response == 'OK') ? 1 : 0);
          $Qtransaction->execute();
        }
      }
    }

/**
 * Send a status enquiry of the transaction to the gateway server
 *
 * @access public
 * @param $id The ID of the order
 */

    function inquiryTransaction($id) {
      global $osC_Database;

      $Qorder = $osC_Database->query('select transaction_return_value from :table_orders_transactions_history where orders_id = :orders_id and transaction_code = 1 order by date_added limit 1');
      $Qorder->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
      $Qorder->bindInt(':orders_id', $id);
      $Qorder->execute();

      if ($Qorder->numberOfRows() === 1) {
        $osC_XML = new osC_XML($Qorder->value('transaction_return_value'));
        $result = $osC_XML->toArray();

        if (isset($result['IDP attr']['ID'])) {
          $params = array('spPassword' => MODULE_PAYMENT_SAFERPAY_VT_PASSWORD,
                          'ACCOUNTID' => MODULE_PAYMENT_SAFERPAY_VT_ACCOUNT_ID,
                          'ID' => $result['IDP attr']['ID'],
                          'ORDERID' => $id);

          $post_string = '';

          foreach ($params as $key => $value) {
            $post_string .= $key . '=' . urlencode(trim($value)) . '&';
          }

          $post_string = substr($post_string, 0, -1);

          $this->_transaction_response = $result_string = $this->sendTransactionToGateway('https://support.saferpay.de/scripts/Inquiry.asp', $post_string);

          $pass = false;

          if (substr($this->_transaction_response, 0, 3) == 'OK:') {
            $pass = true;

            $this->_transaction_response = substr($this->_transaction_response, 3);
          }

          $Qtransaction = $osC_Database->query('insert into :table_orders_transactions_history (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added) values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');
          $Qtransaction->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
          $Qtransaction->bindInt(':orders_id', $id);
          $Qtransaction->bindInt(':transaction_code', 4);
          $Qtransaction->bindValue(':transaction_return_value', $this->_transaction_response);
          $Qtransaction->bindInt(':transaction_return_status', ($pass === true) ? 1 : 0);
          $Qtransaction->execute();
        }
      }
    }
  }
?>
