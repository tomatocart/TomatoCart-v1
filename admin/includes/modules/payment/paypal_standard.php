<?php
/*
  $Id: paypal_standard.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

/**
 * The administration side of the PayPal Standard payment module
 */

  class osC_Payment_paypal_standard extends osC_Payment_Admin {

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

  var $_code = 'paypal_standard';
  
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

  function osC_Payment_paypal_standard() {
    global $osC_Language;
    
    $this->_title = $osC_Language->get('payment_paypal_standard_title');
    $this->_description = $osC_Language->get('payment_paypal_standard_description');
    $this->_method_title = $osC_Language->get('payment_paypal_standard_method_title');
    $this->_status = (defined('MODULE_PAYMENT_PAYPAL_STANDARD_STATUS') && (MODULE_PAYMENT_PAYPAL_STANDARD_STATUS == '1') ? true : false);
    $this->_sort_order = (defined('MODULE_PAYMENT_PAYPAL_STANDARD_SORT_ORDER') ? MODULE_PAYMENT_PAYPAL_STANDARD_SORT_ORDER : null);
  }
  
/**
 * Checks to see if the module has been installed
 *
 * @access public
 * @return boolean
 */

  function isInstalled() {
    return (bool)defined('MODULE_PAYMENT_PAYPAL_STANDARD_STATUS');
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
    
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('* Enable PayPal Website Payments Standard', 'MODULE_PAYMENT_PAYPAL_STANDARD_STATUS', '-1', 'Do you want to accept PayPal Website Payments Standard payments?', '6', '0', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('* PayPal E-Mail Address', 'MODULE_PAYMENT_PAYPAL_STANDARD_ID', '', 'The seller e-mail address to use for accepting PayPal payments.', '6', '0', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_PAYPAL_STANDARD_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_PAYPAL_STANDARD_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '0', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('* Set Processing Order Status', 'MODULE_PAYMENT_PAYPAL_STANDARD_PROCESSING_ORDER_STATUS_ID', '" . ORDERS_STATUS_PROCESSING . "', 'When the customer is returned to the Checkout Complete page from papal, this order status should be used', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('* Set PayPal Acknowledged Order Status', 'MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID', '" . ORDERS_STATUS_PAID . "', 'When the paypal payments is successfully made, this order status should be used', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('* Gateway Server', 'MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER', 'Sandbox', 'Use the testing (sandbox) or live gateway server for transactions?', '6', '0', 'osc_cfg_set_boolean_value(array(\'Live\', \'Sandbox\'))', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transfer Cart Line Items', 'MODULE_PAYMENT_PAYPAL_STANDARD_TRANSFER_CART', '1', 'Do you want to transfer the details about the items in the cart to paypal?', '6', '0', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Method', 'MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTION_METHOD', 'Sale', 'The processing method to use for each transaction.', '6', '0', 'osc_cfg_set_boolean_value(array(\'Authorization\', \'Sale\'))', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Page Style', 'MODULE_PAYMENT_PAYPAL_STANDARD_PAGE_STYLE', '', 'The page style to use for the transaction procedure (defined at your PayPal Profile page)', '6', '0', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Debug E-Mail Address', 'MODULE_PAYMENT_PAYPAL_STANDARD_DEBUG_EMAIL', '', 'All parameters of an Invalid IPN notification will be sent to this email address if one is entered.', '6', '0', now())");
  }

/**
 * Return the configuration parameter keys in an array
 *
 * @access public
 * @return array
 */

  function getKeys() {
    if (!isset($this->_keys)) {
      $this->_keys = array('MODULE_PAYMENT_PAYPAL_STANDARD_STATUS', 
                           'MODULE_PAYMENT_PAYPAL_STANDARD_ID', 
                           'MODULE_PAYMENT_PAYPAL_STANDARD_ZONE',
                           'MODULE_PAYMENT_PAYPAL_STANDARD_PROCESSING_ORDER_STATUS_ID',
                           'MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID', 
                           'MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER', 
                           'MODULE_PAYMENT_PAYPAL_STANDARD_TRANSFER_CART',
                           'MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTION_METHOD', 
                           'MODULE_PAYMENT_PAYPAL_STANDARD_PAGE_STYLE',
                           'MODULE_PAYMENT_PAYPAL_STANDARD_DEBUG_EMAIL', 
                           'MODULE_PAYMENT_PAYPAL_STANDARD_SORT_ORDER');
    }
  
    return $this->_keys;
 } 
}
?>
