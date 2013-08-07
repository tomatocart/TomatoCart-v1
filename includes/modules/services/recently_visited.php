<?php
/*
  $Id: recently_visited.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Services_recently_visited {
    function start() {
      global $osC_Services, $osC_RecentlyVisited;

      include('includes/classes/recently_visited.php');

      $osC_RecentlyVisited = new osC_RecentlyVisited();

      $osC_Services->addCallBeforePageContent('osC_RecentlyVisited', 'initialize');

      return true;
    }

    function stop() {
      return true;
    }
  }
?>
