<?php
/*
  $Id: reports_customers.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Reports_Customers extends osC_Access {
    var $_module = 'reports_customers',
        $_group = 'reports',
        $_icon = 'money.png',
        $_title,
        $_sort_order = 200;

    function osC_Access_Reports_Customers() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_reports_customers_title');

      $this->_subgroups = array(array('iconCls' => 'icon-reports-customers-purchased-win',
                                      'shortcutIconCls' => 'icon-reports-customers-purchased-shortcut',
                                      'title' => $osC_Language->get('access_best_orders_title'),
                                      'identifier' => 'reports_customers-purchased-win',
                                      'params' => array('report' => 'customers-purchased')),
                                array('iconCls' => 'icon-reports-customers-orders-total-win',
                                      'shortcutIconCls' => 'icon-reports-customers-orders-total-shortcut',
                                      'title' => $osC_Language->get('access_orders_total_title'),
                                      'identifier' => 'reports_customers-orders-total-win',
                                      'params' => array('report' => 'orders-total')));
    }
  }
?>
