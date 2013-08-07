<?php
/*
  $Id: core.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Services_core {
    function start() {
      global $osC_Customer, $osC_Tax, $osC_Weight, $osC_ShoppingCart, $osC_NavigationHistory, $osC_Image, $toC_Wishlist, $toC_Compare_Products, $toC_Customization_Fields, $toC_Json;

      include('includes/classes/template.php');
      include('includes/classes/modules.php');
      include('includes/classes/category.php');
      include('includes/classes/product.php');
      include('includes/classes/datetime.php');
      include('includes/classes/xml.php');
      include('includes/classes/mail.php');
      include('includes/classes/address.php');

      include('includes/classes/customer.php');
      $osC_Customer = new osC_Customer();

      include('includes/classes/tax.php');
      $osC_Tax = new osC_Tax();

      include('includes/classes/weight.php');
      $osC_Weight = new osC_Weight();

      include('includes/classes/shopping_cart.php');
      $osC_ShoppingCart = new osC_ShoppingCart();
      $osC_ShoppingCart->update();
      
      include('includes/classes/wishlist.php');
      $toC_Wishlist = new toC_Wishlist();
      
      include('includes/classes/compare_products.php');
      $toC_Compare_Products = new toC_Compare_Products();
      
      include('includes/classes/customization_fields.php');
      $toC_Customization_Fields = new toC_Customization_Fields();

      include('includes/classes/navigation_history.php');
      $osC_NavigationHistory = new osC_NavigationHistory(true);

      include('includes/classes/image.php');
      $osC_Image = new osC_Image();
      
      include('includes/classes/json.php');
      $toC_Json = new toC_Json();

      return true;
    }

    function stop() {
      return true;
    }
  }
?>