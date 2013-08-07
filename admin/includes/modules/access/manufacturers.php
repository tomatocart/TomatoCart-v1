<?php
/*
  $Id: manufacturers.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Manufacturers extends osC_Access {
    var $_module = 'manufacturers',
        $_group = 'content',
        $_icon = 'home.png',
        $_title,
        $_sort_order = 600;

    function osC_Access_Manufacturers() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_manufacturers_title');
    }
  }
?>
