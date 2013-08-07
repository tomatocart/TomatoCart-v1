<?php
/*
  $Id: store_credit.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_OrderTotal_store_credit extends osC_OrderTotal {
    var $output;

    var $_title,
        $_code = 'store_credit',
        $_status = false,
        $_sort_order;

    function osC_OrderTotal_store_credit() {
      global $osC_Language;

      $this->output = array();

      $this->_title = $osC_Language->get('order_total_store_credit_title');
      $this->_description = $osC_Language->get('order_total_store_credit_description');
      $this->_status = (defined('MODULE_ORDER_TOTAL_STORE_CREDIT_STATUS') && (MODULE_ORDER_TOTAL_STORE_CREDIT_STATUS == 'true') ? true : false);
      $this->_sort_order = (defined('MODULE_ORDER_TOTAL_STORE_CREDIT_SORT_ORDER') ? MODULE_ORDER_TOTAL_STORE_CREDIT_SORT_ORDER : null);
    }

    function process() {
      global $osC_ShoppingCart, $osC_Customer, $osC_Currencies;
      
      if ($osC_ShoppingCart->isUseStoreCredit() && $osC_Customer->isLoggedOn() && ($osC_ShoppingCart->getTotal() > 0)) {
      
        if ($osC_Customer->getStoreCredit() > $osC_ShoppingCart->getTotal()) {
          $store_credit = $osC_ShoppingCart->getTotal();
        } else {
          $store_credit = $osC_Customer->getStoreCredit();
        }
        
        $osC_ShoppingCart->addToTotal($store_credit * (-1));
        $osC_ShoppingCart->setStoreCredit($store_credit);
        
        $this->output[] = array('title' => $this->_title . ' (' . $osC_Currencies->format($store_credit) . '):',
                                'text' => $osC_Currencies->format((-1) * $store_credit),
                                'value' => $store_credit * (-1));
      }
    }
  }
?>
