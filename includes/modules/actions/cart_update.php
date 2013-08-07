<?php
/*
  $Id: cart_update.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Actions_cart_update {
    function execute() {
      global $osC_ShoppingCart;


      if (isset($_POST['products']) && is_array($_POST['products']) && !empty($_POST['products'])) {
        foreach ($_POST['products'] as $product => $quantity) {
          $customizations_qty = null;
          
          if ( is_array($quantity) ) {
            $customizations_qty = $quantity;
            $qty = 0;
            
            foreach ($quantity as $key => $value) {
              $qty += $value;
            }
            
            $quantity = $qty;
          } else if ( !is_numeric($quantity) ) {
            return false;
          }

          $product = explode('#', $product, 2);
          $variants_array = array();

          if (isset($product[1])) {
            $variants = explode(';', $product[1]);

            foreach ($variants as $set) {
              $variant = explode(':', $set);

              if (is_numeric($variant[0]) && is_numeric($variant[1])) {
                $variants_array[$variant[0]] = $variant[1];
              }
            }
          }

          $osC_Product = new osC_Product($product[0]);
          
          if ($osC_Product->isGiftCertificate()) {
            $variants_array = $product[1];
          }
          
          $osC_ShoppingCart->add($product[0], $variants_array, $quantity, null, $customizations_qty, 'update');
        }
      }

      osc_redirect(osc_href_link(FILENAME_CHECKOUT));
    }
  }
?>
