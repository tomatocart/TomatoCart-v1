<?php
/*
  $Id: inpay.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

/**
 * The administration side of the inpay payment module
 */
  
  class osC_Payment_inpay extends osC_Payment_Admin {
  
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
    var $_code = 'inpay';
    
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
    
    function osC_Payment_inpay() {
      global $osC_Language;

      $this->_title = $osC_Language->get('payment_inpay_title');
      $this->_description = $osC_Language->get('payment_inpay_description');
      $this->_method_title = $osC_Language->get('payment_inpay_method_title');
      $this->_status = defined('MODULE_PAYMENT_INPAY_STATUS') && (MODULE_PAYMNET_INPAY_STATUS == '1') ? true : false;
      $this->_sort_order = defined('MODULE_PAYMENT_INPAY_SORT_ORDER') ? MODULE_PAYMENT_INPAY_SORT_ORDER : null;
    }
    
/**
 * Checks to see if the module has been installed
 *
 * @access public
 * @return boolean
 */ 
    
    function isInstalled() {
      return (bool)defined('MODULE_PAYMENT_INPAY_STATUS');
    }
    
/**
 * Installs the module
 *
 * @access public
 * @see osC_Payment_Admin::install()
 */
    
    function install() {
      global $osC_Database, $osC_Language;
      
      parent::install();
      
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('* Enable inpay on your webshop?', 'MODULE_PAYMENT_INPAY_STATUS', '-1', '', '6', '1', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('* Gateway Server', 'MODULE_PAYMENT_INPAY_GATEWAY_SERVER', 'Production', 'Use the testing or production gateway server for transactions', '6', '2', 'osc_cfg_set_boolean_value(array(\'Production\', \'Test\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('* Your merchant id', 'MODULE_PAYMENT_INPAY_MERCHANT_ID', '', 'Your merchant unique identifier (supplied by inpay)', '6', '3', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('* Your secret key', 'MODULE_PAYMENT_INPAY_SECRET_KEY', '', 'Your secret key (supplied by inpay)', '6', '4', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Flow Layout', 'MODULE_PAYMENT_INPAY_FLOW_LAYOUT', 'multi_page', 'YLayout for the buyer flow', '6', '5', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Debug E-Mail Address', 'MODULE_PAYMENT_INPAY_DEBUG_EMAIL', '', 'All parameters of an Invalid IPN notification will be sent to this email address if one is entered.', '6', '7', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_INPAY_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '8', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_INPAY_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '9', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('* Set inpay Acknowledged Order Status', 'MODULE_PAYMENT_INPAY_CREATE_ORDER_STATUS_ID', '" . ORDERS_STATUS_PENDING . "', 'Set the status of orders made with this payment module to this value', '6', '10', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('* Set inpay sum too low Order Status', 'MODULE_PAYMENT_INPAY_SUM_TOO_LOW_ORDER_STATUS_ID', '" . ORDERS_STATUS_PARTLY_PAID . "', 'Set the status of orders which are paid with insufficient fund (sum too low) to this value', '6', '11', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('* Set inpay Completed Order Status', 'MODULE_PAYMENT_INPAY_COMP_ORDER_STATUS_ID', '" . ORDERS_STATUS_PAID . "', 'Set the status of orders which are confirmed as paid (approved) to this value', '6', '12', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
    }
    
/**
 * Return the configuration parameter keys in an array
 *
 * @access public
 * @return array
 */

  function getKeys() {
    if (!isset($this->_keys)) {
      $this->_keys = array('MODULE_PAYMENT_INPAY_STATUS', 
                           'MODULE_PAYMENT_INPAY_GATEWAY_SERVER', 
                           'MODULE_PAYMENT_INPAY_MERCHANT_ID', 
                           'MODULE_PAYMENT_INPAY_SECRET_KEY', 
                           'MODULE_PAYMENT_INPAY_FLOW_LAYOUT', 
                           'MODULE_PAYMENT_INPAY_DEBUG_EMAIL', 
                           'MODULE_PAYMENT_INPAY_ZONE', 
                           'MODULE_PAYMENT_INPAY_SORT_ORDER',
                           'MODULE_PAYMENT_INPAY_CREATE_ORDER_STATUS_ID',
                           'MODULE_PAYMENT_INPAY_SUM_TOO_LOW_ORDER_STATUS_ID', 
                           'MODULE_PAYMENT_INPAY_COMP_ORDER_STATUS_ID');
    }
  
    return $this->_keys;
 } 
  }

?>