<?php
/*
  $Id: cart_remove.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Actions_cart_remove {
    function execute() {
      global $osC_Session, $osC_ShoppingCart;

      $id = false;

      foreach ($_GET as $key => $value) {
        if ( (ereg('^[0-9]+(#?([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)*$', $key) || ereg('^[a-zA-Z0-9 -_]*$', $key)) && ($key != $osC_Session->getName()) ) {
          $id = $key;
        }

        break;
      }

      if (($id !== false) && osC_Product::checkEntry($id)) {
        $osC_Product = new osC_Product($id);

        $product_id = $osC_Product->getID();
        
        //gift certificate use timestamp as variant
        if($osC_Product->isGiftCertificate()) {
          $product_id .= '#' . $_GET['variants'];
        } else {
          if (isset($_GET['variants']) && ereg('^([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*$', $_GET['variants'])) {
            $product_id .= '#' . $_GET['variants'];
          }
        }

        $osC_ShoppingCart->remove($product_id);
      }

      osc_redirect(osc_href_link(FILENAME_CHECKOUT));
    }
  }
?>
