<?php
/*
  $Id: abandoned_cart.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Abandoned_cart extends osC_Access {
    var $_module = 'recorvered_cart',
        $_group = 'customers',
        $_icon = 'abandoned_cart.png',
        $_title,
        $_sort_order = 1100;

    function osC_Access_Abandoned_cart() {
      global $osC_Language;
            
      $this->_title = $osC_Language->get('access_abandoned_cart_title');
    }
  }
?>
