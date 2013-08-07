<?php
/*
  $Id: customers.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Customers extends osC_Access {
    var $_module = 'customers',
        $_group = 'customers',
        $_icon = 'personal.png',
        $_title,
        $_sort_order = 100;

    function osC_Access_Customers() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_customers_title');
    }
  }
?>
