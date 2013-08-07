<?php
/*
  $Id: simple_counter.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Services_simple_counter_Admin {
    var $title,
        $description,
        $uninstallable = true,
        $depends,
        $precedes;

    function osC_Services_simple_counter_Admin() {
      global $osC_Language;

      $osC_Language->loadIniFile('modules/services/simple_counter.php');

      $this->title = $osC_Language->get('services_simple_counter_title');
      $this->description = $osC_Language->get('services_simple_counter_description');
    }

    function install() {
      return false;
    }

    function remove() {
      return false;
    }

    function keys() {
      return false;
    }
  }
?>
