<?php
/*
  $Id: coupon.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_OrderTotal_coupon extends osC_OrderTotal {
    var $output;

    var $_title,
        $_code = 'coupon',
        $_status = false,
        $_include_shipping = false,
        $_include_tax = false,
        $_calculate_tax = false,
        $_tax_class = null,
        $_sort_order;

    function osC_OrderTotal_coupon() {
      global $osC_Language;
      $this->output = array();

      $this->_title = $osC_Language->get('order_total_coupon_title');
      $this->_description = $osC_Language->get('order_total_coupon_description');
      $this->_status = (defined('MODULE_ORDER_TOTAL_COUPON_STATUS') && (MODULE_ORDER_TOTAL_COUPON_STATUS == 'true') ? true : false);
      $this->_sort_order = (defined('MODULE_ORDER_TOTAL_COUPON_SORT_ORDER') ? MODULE_ORDER_TOTAL_COUPON_SORT_ORDER : null);
    }

    function process() {
      global $osC_ShoppingCart, $osC_Currencies, $osC_Tax;

      $coupon_code = $osC_ShoppingCart->getCouponCode();
      if(empty($coupon_code))
        return;

      require_once(realpath(dirname(__FILE__) . '/../../') . '/classes/coupon.php');
      
      $toC_Coupon = new toC_Coupon($coupon_code);

      $coupon_amount = 0;
      $coupon_tax = 0;

      if($toC_Coupon->isFreeShippingCoupon()){
        $coupon_amount = $osC_ShoppingCart->getShippingMethod('cost');
        $osC_ShoppingCart->addToTotal($coupon_amount * (-1));

        if ($osC_ShoppingCart->getShippingMethod('tax_class_id') > 0) {
          $tax = $osC_Tax->getTaxRate($osC_ShoppingCart->getShippingMethod('tax_class_id'), $osC_ShoppingCart->getShippingAddress('country_id'), $osC_ShoppingCart->getShippingAddress('zone_id'));
          $tax_description = $osC_Tax->getTaxRateDescription($osC_ShoppingCart->getShippingMethod('tax_class_id'), $osC_ShoppingCart->getShippingAddress('country_id'), $osC_ShoppingCart->getShippingAddress('zone_id'));

          if (DISPLAY_PRICE_WITH_TAX == '1') {
            $coupon_tax = $osC_Tax->calculate($osC_ShoppingCart->getShippingMethod('cost') / (1 + $tax/100), $tax);
          }else{
            $coupon_tax = $osC_Tax->calculate($osC_ShoppingCart->getShippingMethod('cost'), $tax);
            $osC_ShoppingCart->addToTotal($coupon_tax * (-1));
          }

          $osC_ShoppingCart->addTaxAmount($coupon_tax * (-1));
          $osC_ShoppingCart->addTaxGroup($tax_description, $coupon_tax * (-1));

          if (DISPLAY_PRICE_WITH_TAX == '1') {
            $coupon_amount += $coupon_tax;
          }
        }
      }else{
        $valid_order_total = 0;
        $valid_tax = 0;
        $tax_groups = array();
        $products = array();

        $has_restrict_products = $toC_Coupon->hasRestrictProducts();

        if($has_restrict_products)
          $products = $toC_Coupon->getRestrictProducts();

        foreach($osC_ShoppingCart->getProducts() as $product){
          if( (in_array($product['id'], $products) && ($has_restrict_products === true)) || ($has_restrict_products === false) ){
            $valid_order_total += $product['final_price'] * $product['quantity'];

            $products_tax = $osC_Tax->getTaxRate($product['tax_class_id'], $osC_ShoppingCart->getTaxingAddress('country_id'), $osC_ShoppingCart->getTaxingAddress('zone_id'));
            $products_tax_description = $osC_Tax->getTaxRateDescription($product['tax_class_id'], $osC_ShoppingCart->getTaxingAddress('country_id'), $osC_ShoppingCart->getTaxingAddress('zone_id'));
            
            $valid_tax += $osC_Tax->calculate($product['final_price'], $products_tax) * $product['quantity'];
            if(isset($tax_groups[$products_tax_description]))
              $tax_groups[$products_tax_description] += $osC_Tax->calculate($product['final_price'], $products_tax) * $product['quantity'];
            else
              $tax_groups[$products_tax_description] = $osC_Tax->calculate($product['final_price'], $products_tax) * $product['quantity'];
            if ($toC_Coupon->isIncludeTax() == true) {
              $valid_order_total += $osC_Tax->calculate($product['final_price'], $products_tax) * $product['quantity'];
            }
          }
        }

        if ($toC_Coupon->isIncludeShipping() == true){
          $valid_order_total += $osC_ShoppingCart->getShippingMethod('cost');

          if ($osC_ShoppingCart->getShippingMethod('tax_class_id') > 0) {
            $tax = $osC_Tax->getTaxRate($osC_ShoppingCart->getShippingMethod('tax_class_id'), $osC_ShoppingCart->getTaxingAddress('country_id'), $osC_ShoppingCart->getTaxingAddress('zone_id'));

            if (DISPLAY_PRICE_WITH_TAX == '1') {
              $shipping_tax = $osC_Tax->calculate($osC_ShoppingCart->getShippingMethod('cost') / (1 + $tax/100), $tax);
            }else{
              $shipping_tax = $osC_Tax->calculate($osC_ShoppingCart->getShippingMethod('cost'), $tax);
            }

            $tax_description = $osC_Tax->getTaxRateDescription($osC_ShoppingCart->getShippingMethod('tax_class_id'), $osC_ShoppingCart->getShippingAddress('country_id'), $osC_ShoppingCart->getShippingAddress('zone_id'));
            $valid_tax += $shipping_tax;
            $tax_groups[$products_tax_description] += $shipping_tax;

            if ($toC_Coupon->isIncludeTax() == true) {
              $valid_order_total += $shipping_tax;
            }
          }
        }

        if($toC_Coupon->isAmountCoupon()){
          $coupon_amount = min( $valid_order_total, $toC_Coupon->getCouponAmount() );
        }else if($toC_Coupon->isPercentageCoupon()){
          $coupon_amount = round( $toC_Coupon->getCouponAmount() * $valid_order_total / 100,2 );
        }

        $osC_ShoppingCart->addToTotal($coupon_amount * (-1));

        if($toC_Coupon->isIncludeTax() == true) {
          $ratio = ($valid_order_total == 0) ? 0 : ($coupon_amount / $valid_order_total);

          foreach ($osC_ShoppingCart->_tax_groups as $key => $value) {
            if(isset($tax_groups[$key])){
              $coupon_tax += $tax_groups[$key] * $ratio ;
              $osC_ShoppingCart->addTaxAmount($coupon_tax * -1);
              $osC_ShoppingCart->addTaxGroup($key, $coupon_tax * -1);
              $coupon_amount -= $coupon_tax;
            }

            if (DISPLAY_PRICE_WITH_TAX == '1')
              $coupon_amount += $coupon_tax;
          }
        } else if($toC_Coupon->isIncludeTax() == false) {
          $ratio = $coupon_amount / $valid_order_total;
          foreach ($osC_ShoppingCart->_tax_groups as $key => $value) {
            if(isset($tax_groups[$key])){
              $coupon_tax += $tax_groups[$key] * $ratio;
              $osC_ShoppingCart->addTaxAmount($coupon_tax * -1);
              $osC_ShoppingCart->addTaxGroup($key, $coupon_tax * -1);
              $osC_ShoppingCart->addToTotal($coupon_tax * -1);
            }

            if (DISPLAY_PRICE_WITH_TAX == '1')
              $coupon_amount += $coupon_tax;
          }
        }
      }
      
      if ($osC_ShoppingCart->isTotalZero()) {
        $osC_ShoppingCart->resetBillingMethod(false);
      }
      $osC_ShoppingCart->setCouponAmount($coupon_amount);

			if ($coupon_amount > 0) {
	      $this->output[] = array('title' => $this->_title . ' (' . $coupon_code . ') : ',
	                              'text' => '-' . $osC_Currencies->format($coupon_amount),
	                              'value' => $coupon_amount * (-1));
		  } else {
		  	$osC_ShoppingCart->setCouponCode(null, false);
		  }

    }
  }
?>
