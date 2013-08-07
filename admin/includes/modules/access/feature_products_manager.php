<?php
/*
  $Id: feature_products_manager.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Feature_products_manager extends osC_Access {
    var $_module = 'feature_products_manager',
        $_group = 'content',
        $_icon = 'home.png',
        $_title,
        $_sort_order = 1200;

    function osC_Access_Feature_products_manager() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_feature_product_manager_title');
    }
  }
?>
