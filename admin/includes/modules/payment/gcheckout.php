<?php
/*
  $Id: gcheckout.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

/**
 * The administration side of the gcheckout payment module
 */

  class osC_Payment_gcheckout extends osC_Payment_Admin {

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

    var $_code = 'gcheckout';

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

    function osC_Payment_gcheckout() {
      global $osC_Language;

      $this->_title = $osC_Language->get('payment_gcheckout_title');
      $this->_description = $osC_Language->get('payment_gcheckout_description');
      $this->_method_title = $osC_Language->get('payment_gcheckout_method_title');
      $this->_status = (defined('MODULE_PAYMENT_GCHECKOUT_STATUS') && (MODULE_PAYMENT_GCHECKOUT_STATUS == '1') ? true : false);
      $this->_sort_order = (defined('MODULE_PAYMENT_GCHECKOUT_SORT_ORDER') ? MODULE_PAYMENT_GCHECKOUT_SORT_ORDER : null);
    }

/**
 * Checks to see if the module has been installed
 *
 * @access public
 * @return boolean
 */

    function isInstalled() {
      return (bool)defined('MODULE_PAYMENT_GCHECKOUT_STATUS');
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

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Enable Google Checkout Module', 'MODULE_PAYMENT_GCHECKOUT_STATUS', '-1', 'Do you want to accept google checkout payments?', '6', '1', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_GCHECKOUT_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_GCHECKOUT_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '3', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_GCHECKOUT_ORDER_STATUS_ID', '" . DEFAULT_ORDERS_STATUS_ID . "', 'Set the status of orders made with this payment module to this value', '6', '4', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transfer Cart Line Items', 'MODULE_PAYMENT_GCHECKOUT_TRANSFER_CART', '1', 'Do you want to transfer the details about the items in the cart to google checkout?', '6', '5', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Currency', 'MODULE_PAYMENT_GCHECKOUT_CURRENCY', 'Selected Currency', 'The currency to use for credit card transactions', '6', '6', 'osc_cfg_set_boolean_value(array(\'Selected Currency\',\'USD\',\'CAD\',\'EUR\',\'GBP\',\'JPY\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Server', 'MODULE_PAYMENT_GCHECKOUT_SERVER', 'Sandbox', 'The server to perform transactions in.', '6', '7', 'osc_cfg_set_boolean_value(array(\'Production\',\'Sandbox\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant ID', 'MODULE_PAYMENT_GCHECKOUT_MERCHANT_ID', '', 'The ID supplied by google checkout.', '6', '8', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant Key', 'MODULE_PAYMENT_GCHECKOUT_MERCHANT_KEY', '', 'The key supplied by google checkout.', '6', '9', now())");
  }

/**
 * Return the configuration parameter keys in an array
 *
 * @access public
 * @return array
 */

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_PAYMENT_GCHECKOUT_STATUS',
                             'MODULE_PAYMENT_GCHECKOUT_ZONE',
                             'MODULE_PAYMENT_GCHECKOUT_SORT_ORDER',
                             'MODULE_PAYMENT_GCHECKOUT_ORDER_STATUS_ID',
                             'MODULE_PAYMENT_GCHECKOUT_TRANSFER_CART',
                             'MODULE_PAYMENT_GCHECKOUT_CURRENCY',
                             'MODULE_PAYMENT_GCHECKOUT_SERVER',
                             'MODULE_PAYMENT_GCHECKOUT_MERCHANT_ID',
                             'MODULE_PAYMENT_GCHECKOUT_MERCHANT_KEY');
      }

      return $this->_keys;
    }
  }
?>

