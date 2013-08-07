<?php
/*
  $Id: usps.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Shipping_usps extends osC_Shipping_Admin {
    var $icon;

    var $_title,
        $_code = 'usps',
        $_author_name = 'Jack Yin',
        $_author_www = 'http://www.tomatocart.com',
        $_status = false,
        $_sort_order;

// class constructor
    function osC_Shipping_usps() {
      global $osC_Language;

      $this->icon = DIR_WS_IMAGES . 'icons/shipping_usps.gif';

      $this->_title = $osC_Language->get('shipping_usps_title');
      $this->_description = $osC_Language->get('shipping_usps_description');
      $this->_status = (defined('MODULE_SHIPPING_USPS_STATUS') && (MODULE_SHIPPING_USPS_STATUS == 'True') ? true : false);
      $this->_sort_order = (defined('MODULE_SHIPPING_USPS_SORT_ORDER') ? MODULE_SHIPPING_USPS_SORT_ORDER : null);
    }

// class methods
    function isInstalled() {
      return (bool)defined('MODULE_SHIPPING_USPS_STATUS');
    }

    function install() {
      global $osC_Language, $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('" . $osC_Language->get('shipping_usps_status_text') . "', 'MODULE_SHIPPING_USPS_STATUS', 'Disabled', '" . $osC_Language->get('shipping_usps_status_description') . "', '6', '0', 'osc_cfg_set_boolean_value(array(\'Enabled\', \'Disabled\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . $osC_Language->get('shipping_usps_user_id_text') . "', 'MODULE_SHIPPING_USPS_USERID', '', '" . $osC_Language->get('shipping_usps_user_id_description') . "', '6', '1', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . $osC_Language->get('shipping_usps_user_password_text') . "', 'MODULE_SHIPPING_USPS_USERPASSWORD', '', '" . $osC_Language->get('shipping_usps_user_password_description') . "', '6', '2', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('". $osC_Language->get('shipping_usps_zip_code_text') . "', 'MODULE_SHIPPING_USPS_ZIPCODE', '', '" . $osC_Language->get('shipping_usps_zip_code_description') . "', '6', '3', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('" . $osC_Language->get('shipping_usps_domestic_services_text') . "', 'MODULE_SHIPPING_USPS_DEOMESTIC_SERVICES', '', '', '6', '4', 'toc_cfg_set_usps_domestic_services_checkbox_field', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('" . $osC_Language->get('shipping_usps_international_services_text') . "', 'MODULE_SHIPPING_USPS_INTERNATIONAL_SERVICES', '', '', '6', '5', 'toc_cfg_set_usps_international_services_checkbox_field', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('" . $osC_Language->get('shipping_usps_size_text') . "', 'MODULE_SHIPPING_USPS_SIZE', 'REGULAR', '', '6', '6', 'osc_cfg_set_boolean_value(array(\'REGULAR\', \'LARGE\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('" . $osC_Language->get('shipping_usps_container_text') . "', 'MODULE_SHIPPING_USPS_CONTAINER', 'RECTANGULAR', '', '6', '7', 'osc_cfg_set_boolean_value(array(\'RECTANGULAR\', \'NONRECTANGULAR\', \'VARIABLE\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('" . $osC_Language->get('shipping_usps_machinable_text') . "', 'MODULE_SHIPPING_USPS_MACHINABLE', 'Yes', '', '6', '8', 'osc_cfg_set_boolean_value(array(\'Yes\', \'No\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . $osC_Language->get('shipping_usps_dimensions_width_text') . "', 'MODULE_SHIPPING_USPS_DIMENSIONS_WIDTH', '', '" . $osC_Language->get('shipping_usps_dimensions_width_description') . "', '6', '9', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . $osC_Language->get('shipping_usps_dimensions_height_text') . "', 'MODULE_SHIPPING_USPS_DIMENSIONS_HEIGHT', '', '" . $osC_Language->get('shipping_usps_dimensions_height_description') . "', '6', '10', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . $osC_Language->get('shipping_usps_dimensions_length_text') . "', 'MODULE_SHIPPING_USPS_DIMENSIONS_LENGTH', '', '" . $osC_Language->get('shipping_usps_dimensions_length_description') . "', '6', '11', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . $osC_Language->get('shipping_usps_girth_text') . "', 'MODULE_SHIPPING_USPS_GIRTH', '', '" . $osC_Language->get('shipping_usps_girth_description') . "', '6', '12', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('" . $osC_Language->get('shipping_usps_display_delivery_time_text') . "', 'MODULE_SHIPPING_USPS_DISPLAY_DELIVERY_TIME', 'No', '" . $osC_Language->get('shipping_usps_display_delivery_time_description') . "', '6', '13', 'osc_cfg_set_boolean_value(array(\'Yes\', \'No\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('" . $osC_Language->get('shipping_usps_display_delivery_weight_text') . "', 'MODULE_SHIPPING_USPS_DISPLAY_DELIVERY_WEIGHT', 'No', '" . $osC_Language->get('shipping_usps_display_delivery_weight_description') . "', '6', '14', 'osc_cfg_set_boolean_value(array(\'Yes\', \'No\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('" . $osC_Language->get('shipping_usps_weight_class_text') . "', 'MODULE_SHIPPING_USPS_WEIGHT_CLASS_ID', '4', '" . $osC_Language->get('shipping_usps_weight_class_description') . "', '6', '15', 'toc_cfg_use_get_weight_class_title', 'toc_cfg_set_weight_class_pull_down_menu', now())");
      
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . $osC_Language->get('shipping_usps_handling_fee_text') . "', 'MODULE_SHIPPING_USPS_HANDLING', '0', '" . $osC_Language->get('shipping_usps_handling_fee_description') . "', '6', '17', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('" . $osC_Language->get('shipping_usps_tax_classes_text') . "', 'MODULE_SHIPPING_USPS_TAX_CLASS', '0', '" . $osC_Language->get('shipping_usps_tax_classes_description') . "', '6', '18', 'osc_cfg_use_get_tax_class_title', 'osc_cfg_set_tax_classes_pull_down_menu', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('" . $osC_Language->get('shipping_usps_zone_text') . "', 'MODULE_SHIPPING_USPS_ZONE', '0', '" . $osC_Language->get('shipping_usps_zone_description') . "', '6', '19', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . $osC_Language->get('shipping_usps_sort_order_text') . "', 'MODULE_SHIPPING_USPS_SORT_ORDER', '0', '" . $osC_Language->get('shipping_usps_sort_order_description') . "', '6', '20', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('" . $osC_Language->get('shipping_usps_debug_mode_text') . "', 'MODULE_SHIPPING_USPS_DEBUG_MODE', 'Disabled', '" . $osC_Language->get('shipping_usps_debug_mode_description') . "', '6', '21', 'osc_cfg_set_boolean_value(array(\'Enabled\', \'Disabled\'))', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_SHIPPING_USPS_STATUS',
                             'MODULE_SHIPPING_USPS_USERID',
                             'MODULE_SHIPPING_USPS_USERPASSWORD',
                             'MODULE_SHIPPING_USPS_ZIPCODE',
                             'MODULE_SHIPPING_USPS_DEOMESTIC_SERVICES',
                             'MODULE_SHIPPING_USPS_INTERNATIONAL_SERVICES',
                             'MODULE_SHIPPING_USPS_SIZE',
                             'MODULE_SHIPPING_USPS_CONTAINER',
                             'MODULE_SHIPPING_USPS_MACHINABLE',
                             'MODULE_SHIPPING_USPS_DIMENSIONS_WIDTH',
                             'MODULE_SHIPPING_USPS_DIMENSIONS_HEIGHT',
                             'MODULE_SHIPPING_USPS_DIMENSIONS_LENGTH',
                             'MODULE_SHIPPING_USPS_GIRTH',
                             'MODULE_SHIPPING_USPS_DISPLAY_DELIVERY_TIME',
                             'MODULE_SHIPPING_USPS_DISPLAY_DELIVERY_WEIGHT',
                             'MODULE_SHIPPING_USPS_WEIGHT_CLASS_ID',
                             'MODULE_SHIPPING_USPS_HANDLING',
                             'MODULE_SHIPPING_USPS_TAX_CLASS',
                             'MODULE_SHIPPING_USPS_ZONE',
                             'MODULE_SHIPPING_USPS_SORT_ORDER',
                             'MODULE_SHIPPING_USPS_DEBUG_MODE');
      }

      return $this->_keys;
    }
  }
?>
