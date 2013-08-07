<?php
/*
  $Id: gift_certificate.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_OrderTotal_gift_certificate extends osC_OrderTotal_Admin {
    var $_title,
        $_code = 'gift_certificate',
        $_author_name = 'TomatoCart',
        $_author_www = 'http://www.tomatocart.com',
        $_status = false,
        $_sort_order;

    function osC_OrderTotal_gift_certificate() {
      global $osC_Language;

      $this->_title = $osC_Language->get('order_total_gift_certificate_title');
      $this->_description = $osC_Language->get('order_total_gift_certificate_description');
      $this->_status = (defined('MODULE_ORDER_TOTAL_GIFT_CERTIFICATE_STATUS') && (MODULE_ORDER_TOTAL_GIFT_CERTIFICATE_STATUS == 'true') ? true : false);
      $this->_sort_order = (defined('MODULE_ORDER_TOTAL_GIFT_CERTIFICATE_SORT_ORDER') ? MODULE_ORDER_TOTAL_GIFT_CERTIFICATE_SORT_ORDER : null);
    }

    function isInstalled() {
      return (bool)defined('MODULE_ORDER_TOTAL_GIFT_CERTIFICATE_STATUS');
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Gift Certificate', 'MODULE_ORDER_TOTAL_GIFT_CERTIFICATE_STATUS', 'true', 'Do you want to enable gift certificate?', '6', '1', 'osc_cfg_set_boolean_value(array(\'true\', \'false\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_GIFT_CERTIFICATE_SORT_ORDER', '70', 'Sort order of display.', '6', '2', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_ORDER_TOTAL_GIFT_CERTIFICATE_STATUS',
                             'MODULE_ORDER_TOTAL_GIFT_CERTIFICATE_SORT_ORDER');
      }

      return $this->_keys;
    }
  }
?>
