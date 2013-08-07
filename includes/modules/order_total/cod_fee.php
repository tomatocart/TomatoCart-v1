<?php
/*
  $Id: cod_fee.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_OrderTotal_cod_fee extends osC_OrderTotal {
    var $output;

    var $_title,
        $_code = 'cod_fee',
        $_status = false,
        $_sort_order;
        
    function osC_OrderTotal_cod_fee() {
      global $osC_Language;
      
      $this->output = array();

      $this->_title = $osC_Language->get('order_total_cod_title');
      $this->_description = $osC_Language->get('order_total_cod_description');
      $this->_status = (defined('MODULE_ORDER_TOTAL_COD_STATUS') && (MODULE_ORDER_TOTAL_COD_STATUS == 'true') ? true : false);
      $this->_sort_order = (defined('MODULE_ORDER_TOTAL_COD_SORT_ORDER') ? MODULE_ORDER_TOTAL_COD_SORT_ORDER : null);
    }
    
    function process() {
      global $osC_Tax, $osC_ShoppingCart, $osC_Currencies;
      
      if ($osC_ShoppingCart->getContentType() == 'virtual') {
        $this->output[] = null;
      }else {
        if ($this->_status == 'true') {
          $tax = $osC_Tax->getTaxRate(MODULE_ORDER_TOTAL_COD_TAX_CLASS, $osC_ShoppingCart->getShippingAddress('country_id'), $osC_ShoppingCart->getShippingAddress('zone_id'));
          
          //Will become true, if cod can be processed.
          $cod_country = false;
          $cod_zones = array();
          $cod_cost = 0;
          
          //check if payment method is cod. If yes, check if cod is possible.
          if ($osC_ShoppingCart->getBillingMethod('id') == 'cod') {
            //process installed shipping modules
            switch($osC_ShoppingCart->getShippingMethod('id')) {
              case 'flat_flat':
                $cod_zones = split("[:,]", MODULE_ORDER_TOTAL_COD_FEE_FLAT);
                break;
                
              case 'item_item':
                $cod_zones = split("[:,]", MODULE_ORDER_TOTAL_COD_FEE_ITEM);
                break;
                
              case 'table_table':
                $cod_zones = split("[:,]", MODULE_ORDER_TOTAL_COD_FEE_TABLE);
                break;
                
              case 'ups_ups':
                $cod_zones = split("[:,]", MODULE_ORDER_TOTAL_COD_FEE_UPS);
                break;
                
              case 'usps_usps':
                $cod_zones = split("[:,]", MODULE_ORDER_TOTAL_COD_FEE_USPS);
                break;
                
              case 'zones_zones':
                $cod_zones = split("[:,]", MODULE_ORDER_TOTAL_COD_FEE_ZONES);
                break;
                
              case 'ap_ap':
                $cod_zones = split("[:,]", MODULE_ORDER_TOTAL_COD_FEE_AP);
                break;
                
              case 'dp_dp':
                $cod_zones = split("[:,]", MODULE_ORDER_TOTAL_COD_FEE_DP);
                break;
                
              case 'dhl_dhl':
                $cod_zones = split("[:,]", MODULE_ORDER_TOTAL_COD_FEE_DHL);
                break;
            }
            
            if (substr_count($osC_ShoppingCart->getShippingMethod('id'), 'servicepakke') != 0) {
              $cod_zones = split("[:,]", MODULE_ORDER_TOTAL_COD_FEE_SERVICEPAKKE);
            }
            
            if (substr_count($osC_ShoppingCart->getShippingMethod('id'), 'canadapost') != 0) {
              $cod_zones = split("[:,]", MODULE_ORDER_TOTAL_COD_FEE_CANADA_POST);
            }
            
            //get the cod_fee
            if (!empty($cod_zones)) {
              for ($i = 0; $i < count($cod_zones); $i++) {
                if ($cod_zones[$i] == $osC_ShoppingCart->getShippingAddress('country_iso_code_2')) {
                  $cod_cost = $cod_zones[$i + 1];
                  $cod_country = true;
                  
                  break;
                }else if ($cod_zones[$i] == '00') {
                  $cod_cost = $cod_zones[$i + 1];
                  $cod_country = true;
                  
                  break;
                }
              }
            }
          }
          
          if ($cod_country) {
            $tax_description = $osC_Tax->getTaxRateDescription(MODULE_ORDER_TOTAL_COD_TAX_CLASS, $osC_ShoppingCart->getShippingAddress('country_id'), $osC_ShoppingCart->getShippingAddress('zone_id'));
            
            $osC_ShoppingCart->addTaxAmount($osC_Tax->calculate($cod_cost, $tax));
            $osC_ShoppingCart->addTaxGroup($tax_description, $osC_Tax->calculate($cod_cost, $tax));
            
            //osc3 bug
            $osC_ShoppingCart->addToTotal($cod_cost + $osC_Tax->calculate($cod_cost, $tax));
            
            if (DISPLAY_PRICE_WITH_TAX == '1') {
              $this->output[] = array('title' => $this->_title . ':',
                                      'text' => $osC_Currencies->format($cod_cost + $osC_Tax->calculate($cod_cost, $tax)),
                                      'value' => $cod_cost + $osC_Tax->calculate($cod_cost, $tax));
            }else {
              $this->output[] = array('title' => $this->_title . ':',
                                      'text' => $osC_Currencies->format($cod_cost),
                                      'value' => $cod_cost);
            }
          }
        }
      }
    }
  }
?>