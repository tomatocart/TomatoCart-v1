<?php
/*
  $Id: whos_online.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Whos_Online extends osC_Access {
    var $_module = 'whos_online',
        $_group = 'tools',
        $_icon = 'whos_online.png',
        $_title,
        $_sort_order = 1300;

    function osC_Access_Whos_Online() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_whos_online_title');
    }
  }
?>
