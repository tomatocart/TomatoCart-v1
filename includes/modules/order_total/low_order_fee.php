<?php
/*
  $Id: low_order_fee.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_OrderTotal_low_order_fee extends osC_OrderTotal {
    var $output;

    var $_title,
        $_code = 'low_order_fee',
        $_status = false,
        $_sort_order;

    function osC_OrderTotal_low_order_fee() {
      global $osC_Language;

      $this->output = array();

      $this->_title = $osC_Language->get('order_total_loworderfee_title');
      $this->_description = $osC_Language->get('order_total_loworderfee_description');
      $this->_status = (defined('MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS') && (MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS == 'true') ? true : false);
      $this->_sort_order = (defined('MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER') ? MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER : null);
    }

    function process() {
      global $osC_Tax, $osC_ShoppingCart, $osC_Currencies;

      if (MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE == 'true') {
        switch (MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION) {
          case 'national':
            if ($osC_ShoppingCart->getShippingAddress('country_id') == STORE_COUNTRY) {
              $pass = true;
            }
            break;

          case 'international':
            if ($osC_ShoppingCart->getShippingAddress('country_id') != STORE_COUNTRY) {
              $pass = true;
            }
            break;

          case 'both':
            $pass = true;
            break;

          default:
            $pass = false;
        }

        if ( ($pass == true) && ($osC_ShoppingCart->getSubTotal() < MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER) ) {
          $tax = $osC_Tax->getTaxRate(MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS, $osC_ShoppingCart->getTaxingAddress('country_id'), $osC_ShoppingCart->getTaxingAddress('zone_id'));
          $tax_description = $osC_Tax->getTaxRateDescription(MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS, $osC_ShoppingCart->getTaxingAddress('country_id'), $osC_ShoppingCart->getTaxingAddress('zone_id'));

          $osC_ShoppingCart->addTaxAmount($osC_Tax->calculate(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE, $tax));
          $osC_ShoppingCart->addTaxGroup($tax_description, $osC_Tax->calculate(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE, $tax));
          $osC_ShoppingCart->addToTotal(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE + $osC_Tax->calculate(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE, $tax));

          $this->output[] = array('title' => $this->_title . ':',
                                  'text' => $osC_Currencies->displayPriceWithTaxRate(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE, $tax),
                                  'value' => $osC_Currencies->addTaxRateToPrice(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE, $tax));
        }
      }
    }
  }
?>
