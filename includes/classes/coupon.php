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

  class toC_Coupon {
    var $_data = array();
    var $_categories = array();
    var $_products = array();

    function toC_Coupon($coupon_code) {
      global $osC_Database, $osC_Language, $osC_CategoryTree;

      if (!empty($coupon_code)) {
        $Qcoupon = $osC_Database->query('select * from :table_coupons c, :table_coupons_description cd where c.coupons_id = cd.coupons_id and c.coupons_code = :coupons_code and cd.language_id = :language_id');
        $Qcoupon->bindTable(':table_coupons', TABLE_COUPONS);
        $Qcoupon->bindTable(':table_coupons_description', TABLE_COUPONS_DESCRIPTION);
        $Qcoupon->bindValue(':coupons_code', $coupon_code);
        $Qcoupon->bindInt(':language_id', $osC_Language->getID());
        $Qcoupon->execute();

        if ($Qcoupon->numberOfRows() === 1) {
          $this->_data = $Qcoupon->toArray();

          //categories
          $QcouponCategories = $osC_Database->query('select categories_id from :table_coupons_to_categories where coupons_id = :coupons_id');
          $QcouponCategories->bindTable(':table_coupons_to_categories', TABLE_COUPONS_TO_CATEGORIES);
          $QcouponCategories->bindInt(':coupons_id', $this->_data['coupons_id']);
          $QcouponCategories->execute();

          while($QcouponCategories->next()){
            $this->_categories[] = $QcouponCategories->valueInt('categories_id');
          }
          $QcouponCategories->freeResult();

          //get all sub categories
          if(!empty($this->_categories)){
            $children = array();
            foreach($this->_categories as $categories_id){
              $osC_CategoryTree->getChildren($categories_id,$children);
            }
            $this->_categories = array_merge($this->_categories,$children);

            //products
            $QcouponProducts = $osC_Database->query('select products_id from :table_products_to_categories where categories_id in (' . implode(',',$this->_categories) . ')');
            $QcouponProducts->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
            $QcouponProducts->execute();

            while($QcouponProducts->next()){
              $this->_products[] =  $QcouponProducts->valueInt('products_id');
            }
            $QcouponProducts->freeResult();
          }else{
            //products
            $QcouponProducts = $osC_Database->query('select products_id from :coupons_to_products where coupons_id = :coupons_id');
            $QcouponProducts->bindTable(':coupons_to_products', TABLE_COUPONS_TO_PRODUCTS);
            $QcouponProducts->bindInt(':coupons_id', $this->_data['coupons_id']);
            $QcouponProducts->execute();

            while($QcouponProducts->next()){
              $this->_products[] = $QcouponProducts->valueInt('products_id');
            }
            $QcouponProducts->freeResult();
          }
        }
        $Qcoupon->freeResult();
      }
    }

    function isAmountCoupon(){
      if($this->_data['coupons_type'] == '0')
        return true;
      return false;
    }

    function isPercentageCoupon(){
      if($this->_data['coupons_type'] == '1')
        return true;
      return false;
    }

    function isFreeShippingCoupon(){
      if($this->_data['coupons_type'] == '2')
        return true;
      return false;
    }

    function isIncludeShipping(){
      if($this->_data['coupons_include_shipping'] == '1')
        return true;
      return false;
    }

    function isIncludeTax(){
      if($this->_data['coupons_include_tax'] == '1')
        return true;
      return false;
    }

    function isExist() {
      if (empty($this->_data)) {
        return false;
      }

      return true;
    }

    function isValid(){
      return (isset($this->_data['coupons_status']) && ($this->_data['coupons_status']==1));
    }

    function isDateValid(){
      global $osC_Database;

      $Qcoupon = $osC_Database->query('select * from :table_coupons where start_date <= now() and expires_date >= now() and coupons_code = :coupons_code');
      $Qcoupon->bindTable(':table_coupons', TABLE_COUPONS);
      $Qcoupon->bindValue(':coupons_code', $this->_data['coupons_code']);
      $Qcoupon->execute();

      if ($Qcoupon->numberOfRows() === 1) {
        $is_valid = true;
      }else{
        $is_valid = false;
      }
      $Qcoupon->freeResult();

      return $is_valid;
    }

    function isUsesPerCouponValid(){
      global $osC_Database;

      $Qcoupon = $osC_Database->query('select count(c.coupons_id) as number_of_uses from :table_coupons c, :table_coupons_redeem_history crh where c.coupons_id = crh.coupons_id and coupons_code = :coupons_code');
      $Qcoupon->bindTable(':table_coupons', TABLE_COUPONS);
      $Qcoupon->bindTable(':table_coupons_redeem_history', TABLE_COUPONS_REDEEM_HISTORY);
      $Qcoupon->bindValue(':coupons_code', $this->_data['coupons_code']);
      $Qcoupon->execute();

      $number_of_uses = $Qcoupon->valueInt('number_of_uses');
      $Qcoupon->freeResult();

      if($this->_data['uses_per_coupon'] > $number_of_uses )
        return true;
      else
        return false;
    }

    function isUsesPerCustomerValid(){
      global $osC_Database, $osC_Customer;

      $Qcoupon = $osC_Database->query('select count(c.coupons_id) as number_of_uses from :table_coupons c, :table_coupons_redeem_history crh where c.coupons_id = crh.coupons_id and coupons_code = :coupons_code and crh.customers_id = :customers_id');
      $Qcoupon->bindTable(':table_coupons', TABLE_COUPONS);
      $Qcoupon->bindTable(':table_coupons_redeem_history', TABLE_COUPONS_REDEEM_HISTORY);
      $Qcoupon->bindValue(':coupons_code', $this->_data['coupons_code']);
      $Qcoupon->bindValue(':customers_id', $osC_Customer->getID());
      $Qcoupon->execute();

      $number_of_uses = $Qcoupon->valueInt('number_of_uses');
      $Qcoupon->freeResult();

      if($this->_data['uses_per_customer'] > $number_of_uses )
        return true;
      else
        return false;
    }

    function hasRestrictCategories(){
      if(!empty($this->_categories))
        return true;
      else
        return false;
    }

    function hasRestrictProducts(){
      if(!empty($this->_products))
        return true;
      else
        return false;
    }

    function getRestrictProducts(){
      return $this->_products;
    }

    function containRestrictProducts(){
      global $osC_ShoppingCart;

      foreach($osC_ShoppingCart->getProducts() as $product){
        if( in_array($product['id'],$this->_products) )
          return true;
      }

      return false;
    }

    function checkMinimumOrderQuantity(){
      global $osC_ShoppingCart;

      if($this->_data['coupons_minimum_order'] <= $osC_ShoppingCart->getSubTotal()){
        return true;
      }

      return false;
    }

    function getID(){
      return $this->_data['coupons_id'];
    }

    function getCouponCode(){
      return $this->_data['coupons_code'];
    }

    function getCouponAmount(){
      return $this->_data['coupons_amount'];
    }

    function getCouponMimumOrder(){
      return $this->_data['coupons_minimum_order'];
    }
  }
?>
