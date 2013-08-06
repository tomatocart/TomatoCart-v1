<?php
/*
  $Id: western_union.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

/**
 * The administration side of the wangyingzaixian payment module
 */

  class osC_Payment_western_union extends osC_Payment_Admin {

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

    var $_code = 'western_union';

/**
 * The developers name
 *
 * @var string
 * @access private
 */

    var $_author_name = 'elootec';

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

    function osC_Payment_western_union() {
      global $osC_Language;

      $this->_title = $osC_Language->get('payment_western_union_title');
      $this->_description = $osC_Language->get('payment_western_union_description');
      $this->_method_title = $osC_Language->get('payment_western_union_method_title');
      $this->_status = (defined('MODULE_PAYMENT_WESTERN_UNION_STATUS') && (MODULE_PAYMENT_WESTERN_UNION_STATUS == '1') ? true : false);
      $this->_sort_order = (defined('MODULE_PAYMENT_WESTERN_UNION_SORT_ORDER') ? MODULE_PAYMENT_WESTERN_UNION_SORT_ORDER : null);
      
    }

/**
 * Checks to see if the module has been installed
 *
 * @access public
 * @return boolean
 */

    function isInstalled() {
      return (bool)defined('MODULE_PAYMENT_WESTERN_UNION_STATUS');
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

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Enable Western Union Module', 'MODULE_PAYMENT_WESTERN_UNION_STATUS', '-1', 'Do you want to accept west union payments?', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_WESTERN_UNION_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_WESTERN_UNION_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '0', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_WESTERN_UNION_ORDER_STATUS_ID', '" . DEFAULT_ORDERS_STATUS_ID . "', 'Set the status of orders made with this payment module to this value', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Name of store owner', 'MODULE_PAYMENT_WESTERN_UNION_STORE_ONWER_NAME', '', 'The name of store onwer', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Country', 'MODULE_PAYMENT_WESTERN_UNION_STORE_ONWER_COUNTRY', '', 'The country of store onwer', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('City', 'MODULE_PAYMENT_WESTERN_UNION_STORE_ONWER_CITY', '', 'The city of store onwer', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Telephone', 'MODULE_PAYMENT_WESTERN_UNION_STORE_ONWER_TELEPHONE', '', 'The telephone of store onwer by which the customer can contact to you.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Email', 'MODULE_PAYMENT_WESTERN_UNION_STORE_ONWER_EMAIL', '', 'The email of store onwer', '6', '0', now())");  
    }

/**
 * Return the configuration parameter keys in an array
 *
 * @access public
 * @return array
 */

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_PAYMENT_WESTERN_UNION_STATUS',
                             'MODULE_PAYMENT_WESTERN_UNION_SORT_ORDER',
                             'MODULE_PAYMENT_WESTERN_UNION_ZONE',
                             'MODULE_PAYMENT_WESTERN_UNION_ORDER_STATUS_ID',
                             'MODULE_PAYMENT_WESTERN_UNION_STORE_ONWER_NAME',
                             'MODULE_PAYMENT_WESTERN_UNION_STORE_ONWER_COUNTRY',
                             'MODULE_PAYMENT_WESTERN_UNION_STORE_ONWER_CITY',
                             'MODULE_PAYMENT_WESTERN_UNION_STORE_ONWER_TELEPHONE',
                             'MODULE_PAYMENT_WESTERN_UNION_STORE_ONWER_EMAIL'
                             );
      }

      return $this->_keys;
    }

  }
?>
