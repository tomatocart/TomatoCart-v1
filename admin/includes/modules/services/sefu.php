<?php
/*
  $Id: sefu.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Services_sefu_Admin {
    var $title,
        $description,
        $uninstallable = true,
        $depends = 'language',
        $precedes = 'session';

    function osC_Services_sefu_Admin() {
      global $osC_Language;

      $osC_Language->loadIniFile('modules/services/sefu.php');

      $this->title = $osC_Language->get('services_sefu_title');
      $this->description = $osC_Language->get('services_sefu_description');
    }

    function install() {
      global $osC_Database;

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ,date_added) values ('Create keyword-rich URLs', 'SERVICES_KEYWORD_RICH_URLS', '1', 'Create keyword-rich URLs for categories, products, articles and faqs.', '6', '7', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
    }

    function remove() {
      global $osC_Database;

      $osC_Database->simpleQuery("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('SERVICES_KEYWORD_RICH_URLS');
    }
  }
?>
