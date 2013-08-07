<?php
/*
  $Id: paypal_uk_express.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

/**
 * The administration side of the PayPal EXPRESS payment module
 */

  class osC_Payment_paypal_uk_express extends osC_Payment_Admin {
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

    var $_code = 'paypal_uk_express';
    
/**
 * The developers name
 *
 * @var string
 * @access private
 */

    var $_author_name = 'tomatocart';
    
/**
 * The developers address
 *
 * @var string
 * @access private
 */

    var $_author_www = 'http://www.tomatocart.com';
  
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
    
    function osC_Payment_paypal_uk_express() {
      global $osC_Language;
      
      $this->_title = $osC_Language->get('payment_paypal_uk_express_title');
      $this->_description = $osC_Language->get('payment_paypal_uk_express_description');
      $this->_method_title = $osC_Language->get('payment_paypal_uk_express_method_title');
      $this->_status = (defined('MODULE_PAYMENT_PAYPAL_UK_EXPRESS_STATUS') && (MODULE_PAYMENT_PAYPAL_UK_EXPRESS_STATUS == '1') ) ? true: false;
      $this->_sort_order = (defined('MODULE_PAYMENT_PAYPAL_UK_EXPRESS_SORT_ORDER') ? MODULE_PAYMENT_PAYPAL_UK_EXPRESS_SORT_ORDER : null);
    }
    
/**
 * Checks to see if the module has been installed
 *
 * @access public
 * @return boolean
 */
    function isInstalled() {
      return (bool)defined('MODULE_PAYMENT_PAYPAL_UK_EXPRESS_STATUS');
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

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Enable PayPal Express Checkout(UK)', 'MODULE_PAYMENT_PAYPAL_UK_EXPRESS_STATUS', '-1', 'Do you want to accept PayPal Express Checkout (UK) payments?', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Vendor', 'MODULE_PAYMENT_PAYPAL_UK_EXPRESS_VENDOR', '', 'Your merchant login ID that you created when you registered for the Website Payments Pro account.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('User', 'MODULE_PAYMENT_PAYPAL_UK_EXPRESS_USERNAME', '', 'If you set up one or more additional users on the account, this value is the ID of the user authorised to process transactions. If, however, you have not set up additional users on the account, USER has the same value as VENDOR.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Password', 'MODULE_PAYMENT_PAYPAL_UK_EXPRESS_PASSWORD', '', 'The 6- to 32-character password that you defined while registering for the account.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Partner', 'MODULE_PAYMENT_PAYPAL_UK_EXPRESS_PARTNER', 'PayPalUK', 'The ID provided to you by the authorised PayPal Reseller who registered you for the Payflow SDK. If you purchased your account directly from PayPal, use PayPalUK.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Server', 'MODULE_PAYMENT_PAYPAL_UK_EXPRESS_TRANSACTION_SERVER', 'Sandbox', 'The server to perform transactions in.', '6', '0', 'osc_cfg_set_boolean_value(array(\'Production\',\'Sandbox\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Method', 'MODULE_PAYMENT_PAYPAL_UK_EXPRESS_TRANSACTION_METHOD', 'Sale', 'The method to perform transactions in.', '6', '0', 'osc_cfg_set_boolean_value(array(\'Athorization\',\'Sale\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_PAYPAL_UK_EXPRESS_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '0', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_PAYPAL_UK_EXPRESS_ORDER_STATUS_ID', '" . ORDERS_STATUS_PAID . "', 'Set the status of orders made with this payment module to this value', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_PAYPAL_UK_EXPRESS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('cURL Program Location', 'MODULE_PAYMENT_PAYPAL_UK_EXPRESS_CURL', '/usr/bin/curl', 'The location of the cURL Program Location.', '6', '4', now())");
    }

/**
 * Return the configuration parameter keys in an array
 *
 * @access public
 * @return array
 */  
  function getKeys() {
    if (!isset($this->_keys)) {
      $this->_keys = array('MODULE_PAYMENT_PAYPAL_UK_EXPRESS_STATUS',
                           'MODULE_PAYMENT_PAYPAL_UK_EXPRESS_VENDOR',
                           'MODULE_PAYMENT_PAYPAL_UK_EXPRESS_USERNAME',
                           'MODULE_PAYMENT_PAYPAL_UK_EXPRESS_PASSWORD',
                           'MODULE_PAYMENT_PAYPAL_UK_EXPRESS_PARTNER',
                           'MODULE_PAYMENT_PAYPAL_UK_EXPRESS_TRANSACTION_SERVER',
                           'MODULE_PAYMENT_PAYPAL_UK_EXPRESS_TRANSACTION_METHOD',
                           'MODULE_PAYMENT_PAYPAL_UK_EXPRESS_ZONE',
                           'MODULE_PAYMENT_PAYPAL_UK_EXPRESS_ORDER_STATUS_ID',
                           'MODULE_PAYMENT_PAYPAL_UK_EXPRESS_SORT_ORDER',
                           'MODULE_PAYMENT_PAYPAL_UK_EXPRESS_CURL');
      }

      return $this->_keys;
    }   
  }
?>