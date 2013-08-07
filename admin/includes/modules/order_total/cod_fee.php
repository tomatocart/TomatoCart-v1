<?php
/*
  $Id: cod_fee.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_OrderTotal_cod_fee extends osC_OrderTotal_Admin {
    var $_title,
        $_code = 'cod_fee',
        $_author_name = 'TomatoCart',
        $_author_www = 'http://www.tomatocart.com',
        $_status = false,
        $_sort_order;

    function osC_OrderTotal_cod_fee() {
      global $osC_Language, $osC_ShoppingCart;

      $this->_title = $osC_Language->get('order_total_cod_title');
      $this->_description = $osC_Language->get('order_total_cod_description');
      $this->_status = (defined('MODULE_ORDER_TOTAL_COD_STATUS') && (MODULE_ORDER_TOTAL_COD_STATUS == 'true') ? true : false);
      $this->_sort_order = (defined('MODULE_ORDER_TOTAL_COD_SORT_ORDER') ? MODULE_ORDER_TOTAL_COD_SORT_ORDER : null);
    }

    function isInstalled() {
      return (bool)defined('MODULE_ORDER_TOTAL_COD_STATUS');
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display COD FEE', 'MODULE_ORDER_TOTAL_COD_STATUS', 'true', 'Do you want this module to display?', '6', '1', 'osc_cfg_set_boolean_value(array(\'true\', \'false\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_COD_SORT_ORDER', '4', 'Sort order of display.', '6', '2', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for FLAT', 'MODULE_ORDER_TOTAL_COD_FEE_FLAT', 'AT:3.00,DE:3.58,00:9.99', 'FLAT: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '3', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for ITEM', 'MODULE_ORDER_TOTAL_COD_FEE_ITEM', 'AT:3.00,DE:3.58,00:9.99', 'ITEM: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '4', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for TABLE', 'MODULE_ORDER_TOTAL_COD_FEE_TABLE', 'AT:3.00,DE:3.58,00:9.99', 'TABLE: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '5', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for UPS', 'MODULE_ORDER_TOTAL_COD_FEE_UPS', 'CA:4.50,US:3.00,00:9.99', 'UPS: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '6', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for USPS', 'MODULE_ORDER_TOTAL_COD_FEE_USPS', 'CA:4.50,US:3.00,00:9.99', 'USPS: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '7', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for ZONES', 'MODULE_ORDER_TOTAL_COD_FEE_ZONES', 'CA:4.50,US:3.00,00:9.99', 'ZONES: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '8', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for Austrian Post', 'MODULE_ORDER_TOTAL_COD_FEE_AP', 'AT:3.63,00:9.99', 'Austrian Post: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '9', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for German Post', 'MODULE_ORDER_TOTAL_COD_FEE_DP', 'DE:3.58,00:9.99', 'German Post: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '10', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for Servicepakke', 'MODULE_ORDER_TOTAL_COD_FEE_SERVICEPAKKE', 'NO:69', 'Servicepakke: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '11', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for FedEx', 'MODULE_ORDER_TOTAL_COD_FEE_FEDEX', 'US:3.00', 'FedEx: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '12', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for DHL Worldwide', 'MODULE_ORDER_TOTAL_COD_FEE_DHL', 'DE:3.58,00:9.99', 'DHL Worldwide: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '13', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_ORDER_TOTAL_COD_TAX_CLASS', '0', 'Use the following tax class on the COD fee.', '6', '0', 'osc_cfg_use_get_tax_class_title', 'osc_cfg_set_tax_classes_pull_down_menu', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_ORDER_TOTAL_COD_STATUS',
                             'MODULE_ORDER_TOTAL_COD_SORT_ORDER', 
                             'MODULE_ORDER_TOTAL_COD_FEE_FLAT', 
                             'MODULE_ORDER_TOTAL_COD_FEE_ITEM', 
                             'MODULE_ORDER_TOTAL_COD_FEE_TABLE', 
                             'MODULE_ORDER_TOTAL_COD_FEE_UPS', 
                             'MODULE_ORDER_TOTAL_COD_FEE_USPS', 
                             'MODULE_ORDER_TOTAL_COD_FEE_ZONES', 
                             'MODULE_ORDER_TOTAL_COD_FEE_AP', 
                             'MODULE_ORDER_TOTAL_COD_FEE_DP', 
                             'MODULE_ORDER_TOTAL_COD_FEE_DHL', 
                             'MODULE_ORDER_TOTAL_COD_TAX_CLASS');
      }

      return $this->_keys;
    }
  }
?>


