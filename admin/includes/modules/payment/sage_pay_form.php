<?php
/*
  $Id: sage_pay_form.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

/**
 * The administration side of the sage pay form payment module
 */

  class osC_Payment_sage_pay_form extends osC_Payment_Admin {

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

  var $_code = 'sage_pay_form';
  
/**
 * The developers name
 *
 * @var string
 * @access private
 */

  var $_author_name = 'Jack.Yin';
  
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

  function osC_Payment_sage_pay_form() {
    global $osC_Language;
    
    $this->_title = $osC_Language->get('payment_sage_pay_form_title');
    $this->_description = $osC_Language->get('payment_sage_pay_form_description');
    $this->_method_title = $osC_Language->get('payment_sage_pay_form_method_title');
    $this->_status = (defined('MODULE_PAYMENT_SAGE_PAY_FORM_STATUS') && (MODULE_PAYMENT_SAGE_PAY_FORM_STATUS == '1') ? true : false);
    $this->_sort_order = (defined('MODULE_PAYMENT_SAGE_PAY_FORM_SORT_ORDER') ? MODULE_PAYMENT_SAGE_PAY_FORM_SORT_ORDER : null);
  }
  
/**
 * Checks to see if the module has been installed
 *
 * @access public
 * @return boolean
 */

  function isInstalled() {
    return (bool)defined('MODULE_PAYMENT_SAGE_PAY_FORM_STATUS');
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
    
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Sage Pay Form Module', 'MODULE_PAYMENT_SAGE_PAY_FORM_STATUS', '-1', 'Do you want to accept Sage Pay Form payments?', '6', '0', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Vendor Login Name', 'MODULE_PAYMENT_SAGE_PAY_FORM_VENDOR_LOGIN_NAME', '', 'The vendor login name to connect to the gateway with.', '6', '0', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Encryption Password', 'MODULE_PAYMENT_SAGE_PAY_FORM_ENCRYPTION_PASSWORD', '', 'The encrpytion password to secure transactions with.', '6', '0', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Method', 'MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_METHOD', 'Authenticate', 'The processing method to use for each transaction.', '6', '0', 'osc_cfg_set_boolean_value(array(\'Authenticate\', \'Deferred\', \'Payment\'))', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Server', 'MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_SERVER', 'Simulator', 'Perform transactions on the production server or on the testing server.', '6', '0', 'osc_cfg_set_boolean_value(array(\'Live\', \'Test\', \'Simulator\'))', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Vendor E-Mail', 'MODULE_PAYMENT_SAGE_PAY_FORM_VENDOR_EMAIL', '', 'An e-mail address on which you can be contacted when a transaction completes. NOTE: If you wish to use multiple email addresses, you should add them using the : (colon) character as a separator. e.g. me@mail1.com:me@mail2.com', '6', '0', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Send E-Mail', 'MODULE_PAYMENT_SAGE_PAY_FORM_SEND_EMAIL', 'Customer and Vendor', 'Who to send e-mails to.', '6', '0', 'osc_cfg_set_boolean_value(array(\'No One\', \'Customer and Vendor\', \'Vendor Only\'))', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Customer E-Mail Message', 'MODULE_PAYMENT_SAGE_PAY_FORM_CUSTOMER_EMAIL_MESSAGE', '', 'A message to the customer which is inserted into the successful transaction e-mails only.', '6', '0', 'osc_cfg_set_textarea_field', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_SAGE_PAY_FORM_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_SAGE_PAY_FORM_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
    $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_SAGE_PAY_FORM_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
  }

/**
 * Return the configuration parameter keys in an array
 *
 * @access public
 * @return array
 */

  function getKeys() {
    if (!isset($this->_keys)) {
      $this->_keys = array('MODULE_PAYMENT_SAGE_PAY_FORM_STATUS', 
                           'MODULE_PAYMENT_SAGE_PAY_FORM_VENDOR_LOGIN_NAME', 
                           'MODULE_PAYMENT_SAGE_PAY_FORM_ENCRYPTION_PASSWORD', 
                           'MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_METHOD', 
                           'MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_SERVER', 
                           'MODULE_PAYMENT_SAGE_PAY_FORM_VENDOR_EMAIL',
                           'MODULE_PAYMENT_SAGE_PAY_FORM_SEND_EMAIL', 
                           'MODULE_PAYMENT_SAGE_PAY_FORM_CUSTOMER_EMAIL_MESSAGE', 
                           'MODULE_PAYMENT_SAGE_PAY_FORM_SORT_ORDER', 
                           'MODULE_PAYMENT_SAGE_PAY_FORM_ZONE', 
                           'MODULE_PAYMENT_SAGE_PAY_FORM_ORDER_STATUS_ID');
    }
  
    return $this->_keys;
 } 
}
?>
