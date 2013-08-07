<?php
/*
  $Id: free.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Shipping_free extends osC_Shipping_Admin {
    var $icon;

    var $_title,
        $_code = 'free',
        $_author_name = 'osCommerce',
        $_author_www = 'http://www.oscommerce.com',
        $_status = false,
        $_sort_order;

// class constructor
    function osC_Shipping_free() {
      global $osC_Language;

      $this->icon = '';

      $this->_title = $osC_Language->get('shipping_free_title');
      $this->_description = $osC_Language->get('shipping_free_description');
      $this->_status = (defined('MODULE_SHIPPING_FREE_STATUS') && (MODULE_SHIPPING_FREE_STATUS == 'True') ? true : false);
    }

// class methods
    function isInstalled() {
      return (bool)defined('MODULE_SHIPPING_FREE_STATUS');
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Free Shipping', 'MODULE_SHIPPING_FREE_STATUS', 'True', 'Do you want to offer flat rate shipping?', '6', '0', 'osc_cfg_set_boolean_value(array(\'True\', \'False\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Shipping Cost', 'MODULE_SHIPPING_FREE_MINIMUM_ORDER', '20', 'The minimum order amount to apply free shipping to.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Shipping Zone', 'MODULE_SHIPPING_FREE_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_SHIPPING_FREE_STATUS',
                             'MODULE_SHIPPING_FREE_MINIMUM_ORDER',
                             'MODULE_SHIPPING_FREE_ZONE');
      }

      return $this->_keys;
    }
  }
?>
