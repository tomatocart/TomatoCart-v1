<?php
/*
  $Id: total.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_OrderTotal_total extends osC_OrderTotal {
    var $output;

    var $_title,
        $_code = 'total',
        $_status = false,
        $_sort_order;

    function osC_OrderTotal_total() {
      global $osC_Language;

      $this->output = array();

      $this->_title = $osC_Language->get('order_total_total_title');
      $this->_description = $osC_Language->get('order_total_total_description');
      $this->_status = (defined('MODULE_ORDER_TOTAL_TOTAL_STATUS') && (MODULE_ORDER_TOTAL_TOTAL_STATUS == 'true') ? true : false);
      $this->_sort_order = (defined('MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER') ? MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER : null);
    }

    function process() {
      global $osC_ShoppingCart, $osC_Currencies;

      $this->output[] = array('title' => $this->_title . ':',
                              'text' => '<b>' . $osC_Currencies->format($osC_ShoppingCart->getTotal()) . '</b>',
                              'value' => $osC_ShoppingCart->getTotal());
    }
  }
?>
