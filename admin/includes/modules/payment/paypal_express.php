<?php
/*
  $Id: paypal_express.php $
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

  class osC_Payment_paypal_express extends osC_Payment_Admin {
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

    var $_code = 'paypal_express';
    
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
    
    function osC_Payment_paypal_express() {
      global $osC_Language;
      
      $this->_title = $osC_Language->get('payment_paypal_express_title');
      $this->_description = $osC_Language->get('payment_paypal_express_description');
      $this->_method_title = $osC_Language->get('payment_paypal_express_method_title');
      $this->_status = ( defined('MODULE_PAYMENT_PAYPAL_EXPRESS_STATUS') && (MODULE_PAYMENT_PAYPAL_EXPRESS_STATUS == '1') ) ? true: false;
      $this->_sort_order = (defined('MODULE_PAYMENT_PAYPAL_EXPRESS_SORT_ORDER') ? MODULE_PAYMENT_PAYPAL_EXPRESS_SORT_ORDER : null);
      
      if (defined('MODULE_PAYMENT_PAYPAL_EXPRESS_SERVER')) {
        switch (MODULE_PAYMENT_PAYPAL_EXPRESS_SERVER) {
          case 'Production':
            $this->_gateway_server = 'https://api.paypal.com/2.0/';
            break;

          default:
            $this->_gateway_server = 'https://api.sandbox.paypal.com/2.0/';
            break;
        }
      }
    }
    
/**
 * Checks to see if the module has been installed
 *
 * @access public
 * @return boolean
 */
    function isInstalled() {
      return (bool)defined('MODULE_PAYMENT_PAYPAL_EXPRESS_STATUS');
    }

/**
 * Installs the module
 *
 * @access public
 * @see osC_Payment_Admin::install()
 */ 
    function install() {
      global $osC_Database, $osC_Language;
      
      $Qcheck = $osC_Database->query('select orders_status_id from :table_orders_status where orders_status_name = :orders_status_name limit 1');
      $Qcheck->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
      $Qcheck->bindValue(':orders_status_name', 'PayPal [Transactions]');
      $Qcheck->execute();
      
      if ($Qcheck->numberOfRows() < 1) {
        $Qstatus = $osC_Database->query('select max(orders_status_id) as status_id from :table_orders_status');
        $Qstatus->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
        $Qstatus->execute();
        
        $status = $Qstatus->toArray();
        $status_id = $status['status_id'] + 1;
        
        foreach($osC_Language->getAll() as $lang) {
          $osC_Database->simpleQuery("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id . "', '" . $lang['id'] . "', 'PayPal [Transactions]')");
        }
        
        $Qstatus->freeResult();
        
        $Qflags = $osC_Database->query('describe :table_orders_status public_flag');
        $Qflags->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
        $Qflags->execute();
        
        if ($Qflags->numberOfRows() == 1) {
          $osC_Database->simpleQuery("update " . TABLE_ORDERS_STATUS . " set public_flag = 0 and downloads_flag = 0 where orders_status_id = '" . $status_id . "'");
        }
        
        $Qflags->freeResult();
      }else {
        $check = $Qcheck->toArray();
        
        $status_id = $check['orders_status_id'];
      }
      
      $Qcheck->freeResult();
      

      parent::install();
      
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Enable PayPal Express Checkout', 'MODULE_PAYMENT_PAYPAL_EXPRESS_STATUS', '-1', 'Do you want to accept PayPal EXPRESS Checkout payments?', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Seller Account', 'MODULE_PAYMENT_PAYPAL_EXPRESS_SELLER_ACCOUNT', '', 'The email address of the seller account if no API credentials has been setup.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('API Username', 'MODULE_PAYMENT_PAYPAL_EXPRESS_API_USERNAME', '', 'The username to use for the PayPal API service.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('API Password', 'MODULE_PAYMENT_PAYPAL_EXPRESS_API_PASSWORD', '', 'The password to use for the PayPal API service.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('API Signature', 'MODULE_PAYMENT_PAYPAL_EXPRESS_API_SIGNATURE', '', 'The signature to use for the PayPal API service.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Server', 'MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_SERVER', 'Live', 'Use the live or testing (sandbox) gateway server to process transactions?', '6', '0', 'osc_cfg_set_boolean_value(array(\'Live\',\'Sandbox\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Method', 'MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_METHOD', 'Sale', 'The processing method to use for each transaction.', '6', '0', 'osc_cfg_set_boolean_value(array(\'Athorization\',\'Sale\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('PayPal Checkout Image', 'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_IMAGE', 'Static', 'Use static or dynamic Express Checkout image buttons. Dynamic images are used with PayPal campaigns.', '6', '0', 'osc_cfg_set_boolean_value(array(\'Static\',\'Dynamic\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_PAYPAL_EXPRESS_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '0', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_PAYPAL_EXPRESS_ORDER_STATUS_ID', '" . ORDERS_STATUS_PAID . "', 'Set the status of orders made with this payment module to this value', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('PayPal Transactions Order Status Level', 'MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTIONS_ORDER_STATUS_ID', '" . $status_id . "', 'Include PayPal transaction information in this order status level', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_PAYPAL_EXPRESS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('cURL Program Location', 'MODULE_PAYMENT_PAYPAL_EXPRESS_CURL', '/usr/bin/curl', 'The location of the cURL Program Location.', '6', '0', now())");
    }

/**
 * Return the configuration parameter keys in an array
 *
 * @access public
 * @return array
 */  
  function getKeys() {
    if (!isset($this->_keys)) {
      $this->_keys = array('MODULE_PAYMENT_PAYPAL_EXPRESS_STATUS',
                           'MODULE_PAYMENT_PAYPAL_EXPRESS_SELLER_ACCOUNT',
                           'MODULE_PAYMENT_PAYPAL_EXPRESS_API_USERNAME',
                           'MODULE_PAYMENT_PAYPAL_EXPRESS_API_PASSWORD',
                           'MODULE_PAYMENT_PAYPAL_EXPRESS_API_SIGNATURE',
                           'MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_SERVER',
                           'MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_METHOD',
                           'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_IMAGE',
                           'MODULE_PAYMENT_PAYPAL_EXPRESS_ZONE',
                           'MODULE_PAYMENT_PAYPAL_EXPRESS_ORDER_STATUS_ID',
                           'MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTIONS_ORDER_STATUS_ID',
                           'MODULE_PAYMENT_PAYPAL_EXPRESS_SORT_ORDER',
                           'MODULE_PAYMENT_PAYPAL_EXPRESS_CURL');
      }

      return $this->_keys;
    }   
  }
?>