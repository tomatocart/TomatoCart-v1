<?php
/*
  $Id: eway_au.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

/**
 * The administration side of the eway payment module
 */

  class osC_Payment_eway_au extends osC_Payment_Admin {

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

    var $_code = 'eway_au';

/**
 * The developers name
 *
 * @var string
 * @access private
 */

    var $_author_name = 'TomatoCart';

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

    function osC_Payment_eway_au() {
      global $osC_Language;

      $this->_title = $osC_Language->get('payment_eway_au_title');
      $this->_description = $osC_Language->get('payment_eway_au_description');
      $this->_method_title = $osC_Language->get('payment_cod_method_title');
      $this->_status = (defined('MODULE_PAYMENT_EWAY_AU_STATUS') && (MODULE_PAYMENT_EWAY_AU_STATUS == '1') ? true : false);
      $this->_sort_order = (defined('MODULE_PAYMENT_EWAY_AU_SORT_ORDER') ? MODULE_PAYMENT_EWAY_AU_SORT_ORDER : null);
    }

/**
 * Checks to see if the module has been installed
 *
 * @access public
 * @return boolean
 */

    function isInstalled() {
      return (bool)defined('MODULE_PAYMENT_EWAY_AU_STATUS');
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

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Enable eWay Payment Module', 'MODULE_PAYMENT_EWAY_AU_STATUS', '-1', 'Do you want to authorize payments through eWay Payment?', '6', '1', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_EWAY_AU_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('eWay Customer ID', 'MODULE_PAYMENT_EWAY_AU_CUSTOMER_ID', '', 'Your unique eWay customer ID assigned to you when you join eWay.', '6', '3', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Geteway', 'MODULE_PAYMENT_EWAYPAYMENT_GATEWAY_MODE', 'Live gateway', 'You can set to go to testing mode here.', '6', '4', 'osc_cfg_set_boolean_value(array(\'Live gateway\', \'Test gateway\'))', now())");      
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Processing', 'MODULE_PAYMENT_EWAYPAYMENT_PROCESSING_METHOD', 'Real-Time CVN', 'Set the eWay processing.', '6', '5', 'osc_cfg_set_boolean_value(array(\'Real-Time\',\'Real-Time CVN\',\'Geo-IP Anti Fraud\',\'Real-Time Hosted\',\'Real-Time CVN Hosted\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Credit Card Validation', 'MODULE_PAYMENT_EWAYPAYMENT_CREDIT_CARD_VALIDATION', '1', 'Turn \'on\' or \'off\' validation for Credit Cart info.', '6', '6', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");      
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('SSL Verifier', 'MODULE_PAYMENT_EWAY_AU_SSL_VERIFIER', '1', 'Turn \'on\' or \'off\' server SSL verifier.', '6', '7', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_EWAY_AU_ORDER_STATUS_ID', '" . DEFAULT_ORDERS_STATUS_ID . "', 'Set the status of orders made with this payment module to this value', '6', '8', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Curl Proxy', 'MODULE_PAYMENT_EWAY_AU_CURL_PROXY', '', 'Set url for Curl Proxy or leave blank if is server default.', '6', '9', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_EWAY_AU_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '10', now())");
    }

/**
 * Return the configuration parameter keys in an array
 *
 * @access public
 * @return array
 */

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_PAYMENT_EWAY_AU_STATUS',
                             'MODULE_PAYMENT_EWAY_AU_ZONE',
                             'MODULE_PAYMENT_EWAY_AU_CUSTOMER_ID',
                             'MODULE_PAYMENT_EWAYPAYMENT_GATEWAY_MODE',
                             'MODULE_PAYMENT_EWAYPAYMENT_PROCESSING_METHOD',
                             'MODULE_PAYMENT_EWAYPAYMENT_CREDIT_CARD_VALIDATION',
                             'MODULE_PAYMENT_EWAY_AU_SSL_VERIFIER',
                             'MODULE_PAYMENT_EWAY_AU_ORDER_STATUS_ID',
                             'MODULE_PAYMENT_EWAY_AU_CURL_PROXY',
                             'MODULE_PAYMENT_EWAY_AU_SORT_ORDER');
      }

      return $this->_keys;
    }
  }
?>
