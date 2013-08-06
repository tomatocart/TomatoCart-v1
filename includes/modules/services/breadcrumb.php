<?php
/*
  $Id: breadcrumb.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Services_breadcrumb {
    function start() {
      global $breadcrumb, $osC_Database, $osC_Language, $cPath, $cPath_array;

      include('includes/classes/breadcrumb.php');
      $breadcrumb = new breadcrumb;

      $breadcrumb->add($osC_Language->get('breadcrumb_shop'), osc_href_link(FILENAME_DEFAULT));

      return true;
    }

    function stop() {
      return true;
    }
  }
?>
