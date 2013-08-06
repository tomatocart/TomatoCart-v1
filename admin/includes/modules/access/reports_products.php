<?php
/*
  $Id: reports_products.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Reports_Products extends osC_Access {
    var $_module = 'reports_products',
        $_group = 'reports',
        $_icon = 'products.png',
        $_title,
        $_sort_order = 100;

    function osC_Access_Reports_Products() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_reports_products_title');

      $this->_subgroups = array(array('iconCls' => 'icon-reports-products-purchased-win',
                                      'shortcutIconCls' => 'icon-reports-products-purchased-shortcut',
                                      'title' => $osC_Language->get('access_products_purchased_title'),
                                      'identifier' => 'reports_products-purchased-win',
                                      'params' => array('report' => 'products-purchased')),
                                array('iconCls' => 'icon-reports-products-viewed-win',
                                      'shortcutIconCls' => 'icon-reports-products-viewed-shortcut',
                                      'title' => $osC_Language->get('access_products_viewed_title'),
                                      'identifier' => 'reports_products-viewed-win',
                                      'params' => array('report' => 'products-viewed')),
                                array('iconCls' => 'icon-reports-products-categories-win',
                                      'shortcutIconCls' => 'icon-reports-products-categories-shortcut',
                                      'title' => $osC_Language->get('access_categories_purchased_title'),
                                      'identifier' => 'reports_products-categories-purchased-win',
                                      'params' => array('report' => 'categories-purchased')),
                                array('iconCls' => 'icon-reports-products-low-stock-win',
                                      'shortcutIconCls' => 'icon-reports-products-low-stock-shortcut',
                                      'title' => $osC_Language->get('access_low_stock_title'),
                                      'identifier' => 'reports_products-low-stock-win',
                                      'params' => array('report' => 'low-stock')));
    }
  }
?>
