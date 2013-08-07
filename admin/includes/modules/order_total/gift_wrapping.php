<?php
/*
  $Id: gift_wrapping.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_OrderTotal_gift_wrapping extends osC_OrderTotal_Admin {
    var $_title,
        $_code = 'gift_wrapping',
        $_author_name = 'TomatoCart',
        $_author_www = 'http://www.tomatocart.com',
        $_status = false,
        $_sort_order;

    function osC_OrderTotal_gift_wrapping() {
      global $osC_Language;

      $this->_title = $osC_Language->get('order_total_gift_wrapping_title');
      $this->_description = $osC_Language->get('order_total_gift_wrapping_description');
      $this->_status = (defined('MODULE_ORDER_TOTAL_GIFT_WRAPPING_STATUS') && (MODULE_ORDER_TOTAL_GIFT_WRAPPING_STATUS == 'true') ? true : false);
      $this->_sort_order = (defined('MODULE_ORDER_TOTAL_GIFT_WRAPPING_SORT_ORDER') ? MODULE_ORDER_TOTAL_GIFT_WRAPPING_SORT_ORDER : null);
    }

    function isInstalled() {
      return (bool)defined('MODULE_ORDER_TOTAL_GIFT_WRAPPING_STATUS');
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Wrapping', 'MODULE_ORDER_TOTAL_GIFT_WRAPPING_STATUS', 'true', 'Do you want to enable gift wrapping?', '6', '1', 'osc_cfg_set_boolean_value(array(\'true\', \'false\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_GIFT_WRAPPING_SORT_ORDER', '25', 'Sort order of display.', '6', '2', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Gift Wrapping Price', 'MODULE_ORDER_TOTAL_GIFT_WRAPPING_PRICE', '10', 'The price for the gift wrapping.', '6', '2', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Gift Wrapping Tax', 'MODULE_ORDER_TOTAL_GIFT_WRAPPING_TAX', '0', 'Use the following tax class on the gift wrapping.', '6', '7', 'osc_cfg_use_get_tax_class_title', 'osc_cfg_set_tax_classes_pull_down_menu', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_ORDER_TOTAL_GIFT_WRAPPING_STATUS',
                             'MODULE_ORDER_TOTAL_GIFT_WRAPPING_SORT_ORDER',
                             'MODULE_ORDER_TOTAL_GIFT_WRAPPING_PRICE',
                             'MODULE_ORDER_TOTAL_GIFT_WRAPPING_TAX');
      }

      return $this->_keys;
    }
  }
?>
