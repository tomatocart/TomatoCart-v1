<?php
/*
  $Id: selfpickup.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Shipping_selfpickup extends osC_Shipping_Admin {
    var $icon;

    var $_title,
        $_code = 'selfpickup',
        $_author_name = 'TomatoCart',
        $_author_www = 'http://www.tomartocart.com',
        $_status = false,
        $_sort_order;

// class constructor
    function osC_Shipping_selfpickup() {
      global $osC_Language;

      $this->icon = '';

      $this->_title = $osC_Language->get('shipping_self_pickup_title');
      $this->_description = $osC_Language->get('shipping_self_pickup_description');
      $this->_status = (defined('MODULE_SHIPPING_SELF_PICKUP_STATUS') && (MODULE_SHIPPING_SELF_PICKUP_STATUS == 'True') ? true : false);
      $this->_sort_order = (defined('MODULE_SHIPPING_SELF_PICKUP_SORT_ORDER') ? MODULE_SHIPPING_SELF_PICKUP_SORT_ORDER : null);
    }

// class methods
    function isInstalled() {
      return (bool)defined('MODULE_SHIPPING_SELF_PICKUP_STATUS');
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Item Shipping', 'MODULE_SHIPPING_SELF_PICKUP_STATUS', 'True', 'Do you want to offer self pickup shipping?', '6', '0', 'osc_cfg_set_boolean_value(array(\'True\', \'False\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Shipping Zone', 'MODULE_SHIPPING_SELF_PICKUP_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_SELF_PICKUP_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_SHIPPING_SELF_PICKUP_STATUS',
                             'MODULE_SHIPPING_SELF_PICKUP_ZONE',
                             'MODULE_SHIPPING_SELF_PICKUP_SORT_ORDER');
      }

      return $this->_keys;
    }
  }
?>
