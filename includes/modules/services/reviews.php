<?php
/*
  $Id: reviews.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Services_reviews {
    function start() {
      global $osC_Reviews;
      include('includes/classes/reviews.php');

      $osC_Reviews = new osC_Reviews();
      return true;
    }

    function stop() {
      return true;
    }
  }
?>
