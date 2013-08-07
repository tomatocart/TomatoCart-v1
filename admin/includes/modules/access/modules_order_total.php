<?php
/*
  $Id: modules_order_total.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Modules_order_total extends osC_Access {
    var $_module = 'modules_order_total',
        $_group = 'modules',
        $_icon = 'calculator.png',
        $_title,
        $_sort_order = 300;

    function osC_Access_Modules_order_total() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_modules_order_total_title');
    }
  }
?>
