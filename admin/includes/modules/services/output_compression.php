<?php
/*
  $Id: output_compression.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Services_output_compression_Admin {
    var $title,
        $description,
        $uninstallable = true,
        $depends,
        $precedes = 'session';

    function osC_Services_output_compression_Admin() {
      global $osC_Language;

      $osC_Language->loadIniFile('modules/services/output_compression.php');

      $this->title = $osC_Language->get('services_output_compression_title');
      $this->description = $osC_Language->get('services_output_compression_description');
    }

    function install() {
      global $osC_Database;

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('GZIP Compression Level', 'SERVICE_OUTPUT_COMPRESSION_GZIP_LEVEL', '5', 'Set the GZIP compression level to this value (0=min, 9=max).', '6', '0', 'osc_cfg_set_boolean_value(array(\'0\', \'1\', \'2\', \'3\', \'4\', \'5\', \'6\', \'7\', \'8\', \'9\'))', now())");
    }

    function remove() {
      global $osC_Database;

      $osC_Database->simpleQuery("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('SERVICE_OUTPUT_COMPRESSION_GZIP_LEVEL');
    }
  }
?>
