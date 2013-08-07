<?php
/*
  $Id: google_analytics.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Services_google_analytics_Admin {
    var $title,
        $description,
        $uninstallable = true,
        $depends,
        $precedes;

    function osC_Services_google_analytics_Admin() {
      global $osC_Language;

      $osC_Language->loadIniFile('modules/services/google_analytics.php');

      $this->title = $osC_Language->get('services_google_analytics_title');
      $this->description = $osC_Language->get('services_google_analytics_description');
    }

    function install() {
      global $osC_Database;

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Add google analytics code', 'SERVICES_GOOGLE_ANALYTICS_CODE', '', 'Google analytics code used to track visitor data on the site.', '6', '0', 'osc_cfg_set_textarea_field', now())");
    }

    function remove() {
      global $osC_Database;

      $osC_Database->simpleQuery("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('SERVICES_GOOGLE_ANALYTICS_CODE');
    }
  }
?>
