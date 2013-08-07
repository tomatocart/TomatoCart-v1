<?php
/*
  $Id: gift_wrapping.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_OrderTotal_gift_wrapping extends osC_OrderTotal {
    var $output;

    var $_title,
        $_code = 'gift_wrapping',
        $_status = false,
        $_sort_order;

    function osC_OrderTotal_gift_wrapping() {
      global $osC_Language;
      
      $this->output = array();

      $this->_title = $osC_Language->get('order_total_gift_wrapping_title');
      $this->_description = $osC_Language->get('order_total_gift_wrapping_description');
      $this->_status = (defined('MODULE_ORDER_TOTAL_GIFT_WRAPPING_STATUS') && (MODULE_ORDER_TOTAL_GIFT_WRAPPING_STATUS == 'true') ? true : false);
      $this->_sort_order = (defined('MODULE_ORDER_TOTAL_GIFT_WRAPPING_SORT_ORDER') ? MODULE_ORDER_TOTAL_GIFT_WRAPPING_SORT_ORDER : null);
    }

    function process() {
      global $osC_Tax, $osC_ShoppingCart, $osC_Currencies;

      if ($osC_ShoppingCart->isGiftWrapping()) {
        $wrapping_price = MODULE_ORDER_TOTAL_GIFT_WRAPPING_PRICE;
        $osC_ShoppingCart->addToTotal(MODULE_ORDER_TOTAL_GIFT_WRAPPING_PRICE);
        
        if (MODULE_ORDER_TOTAL_GIFT_WRAPPING_TAX > 0) {
          $tax = $osC_Tax->getTaxRate(MODULE_ORDER_TOTAL_GIFT_WRAPPING_TAX, $osC_ShoppingCart->getShippingAddress('country_id'), $osC_ShoppingCart->getShippingAddress('zone_id'));
          $tax_description = $osC_Tax->getTaxRateDescription(MODULE_ORDER_TOTAL_GIFT_WRAPPING_TAX, $osC_ShoppingCart->getShippingAddress('country_id'), $osC_ShoppingCart->getShippingAddress('zone_id'));
        
          $osC_ShoppingCart->addTaxAmount($osC_Tax->calculate($wrapping_price, $tax));
          $osC_ShoppingCart->addTaxGroup($tax_description, $osC_Tax->calculate($wrapping_price, $tax));
        
          //osc3 bug
          $osC_ShoppingCart->addToTotal($osC_Tax->calculate($wrapping_price, $tax));
          
          if (DISPLAY_PRICE_WITH_TAX == '1') {
            $wrapping_price = $wrapping_price * ( 1 + $tax /100);
          }
        }
  
        $this->output[] = array('title' => $this->_title . ':',
                                 'text' => $osC_Currencies->format($wrapping_price),
                                'value' => $wrapping_price);
      }
    }
  }
?>
