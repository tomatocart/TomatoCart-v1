<?php
/*
  $Id: authorizenet_cc_aim.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

/**
 * The administration side of the authorizenet advance integration method payment module
 */

  class osC_Payment_authorizenet_cc_aim extends osC_Payment_Admin {

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

  var $_code = 'authorizenet_cc_aim';
  
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

  function osC_Payment_authorizenet_cc_aim() {
    global $osC_Language;
    
    $this->_title = $osC_Language->get('payment_authorizenet_cc_aim_title');
    $this->_description = $osC_Language->get('payment_authorizenet_cc_aim_description');
    $this->_method_title = $osC_Language->get('payment_authorizenet_cc_aim_method_title');
    $this->_status = (defined('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_STATUS') && (MODULE_PAYMENT_AUTHORIZENET_CC_AIM_STATUS == '1') ? true : false);
    $this->_sort_order = (defined('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_SORT_ORDER') ? MODULE_PAYMENT_AUTHORIZENET_CC_AIM_SORT_ORDER : null);
  }
  
/**
 * Checks to see if the module has been installed
 *
 * @access public
 * @return boolean
 */

  function isInstalled() {
    return (bool)defined('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_STATUS');
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
    
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Authorize.net Credit Card AIM', 'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_STATUS', '-1', 'Do you want to accept Authorize.net Credit Card AIM payments?', '6', '0', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Api Login ID', 'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_API_LOGIN_ID', '', 'The api login ID used for the Authorize.net service', '6', '0', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Api Transaction Key', 'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_API_TRANSACTION_KEY', '', 'The Api transaction key used for encrypting data', '6', '0', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('MD5 Hash', 'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_MD5_HASH', '', 'The MD5 hash value to verify transactions with', '6', '0', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Credit Cards', 'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ACCEPTED_TYPES', '', 'Accept these credit card types for this payment method.', '6', '0', 'osc_cfg_set_credit_cards_checkbox_field', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Verify With CVC', 'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_VERIFY_WITH_CVC', '1', 'Verify the credit card with the billing address with the Credit Card Verification Checknumber (CVC)?', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Server', 'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_SERVER', 'Live', 'Perform transactions on the live or test server. The test server should only be used by developers with Authorize.net test accounts.', '6', '0', 'osc_cfg_set_boolean_value(array(\'Live\', \'Test\'))', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Mode', 'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_MODE', 'Test', 'Transaction mode used for processing orders', '6', '0', 'osc_cfg_set_boolean_value(array(\'Live\', \'Test\'))', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Method', 'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_METHOD', 'Authorization', 'The processing method to use for each transaction.', '6', '0', 'osc_cfg_set_boolean_value(array(\'Authorization\', \'Capture\'))', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('cURL Program Location', 'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_CURL', '/usr/bin/curl', 'The location to the cURL program application', '6', '0', now())");
  }

/**
 * Return the configuration parameter keys in an array
 *
 * @access public
 * @return array
 */

  function getKeys() {
    if (!isset($this->_keys)) {
      $this->_keys = array('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_STATUS', 
                           'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_API_LOGIN_ID', 
                           'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_API_TRANSACTION_KEY', 
                           'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_MD5_HASH',
                           'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ACCEPTED_TYPES',
                           'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_VERIFY_WITH_CVC', 
                           'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_SERVER', 
                           'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_MODE',
                           'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_METHOD', 
                           'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ZONE', 
                           'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ORDER_STATUS_ID', 
                           'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_SORT_ORDER', 
                           'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_CURL');
    }
  
    return $this->_keys;
 } 
}
?>
