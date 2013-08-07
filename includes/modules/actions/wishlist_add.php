<?php
/*
  $Id: wishlist_add.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Actions_wishlist_add {
    function execute() {
      global $osC_Session, $toC_Wishlist, $osC_Product;
      
      $id = false;

      foreach ($_GET as $key => $value) {
        if ( (ereg('^[0-9]+(_?([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)*$', $key) || ereg('^[a-zA-Z0-9 -_]*$', $key)) && ($key != $osC_Session->getName()) ) {
          $id = $key;
        }

        break;
      }
      
      //change the variants in the product info page, then attach the wid param to represent the variant product
      if (isset($_GET['wid']) && preg_match('/^[0-9]+(_?([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)*$/', $_GET['wid'])) {
        $id = $_GET['wid'];
      }
      
      if (strpos( $id, '_') !== false) {
        $id = str_replace('_', '#', $id);
      }

      if (($id !== false) && osC_Product::checkEntry($id)) {
        $osC_Product = new osC_Product($id);
      }
      
      if (isset($osC_Product)) {
        $variants = null;
        
        if (isset($_POST['variants']) && is_array($_POST['variants'])) {
          $variants = $_POST['variants'];
        } else if (isset($_GET['variants']) && !empty($_GET['variants'])) {
          $variants = osc_parse_variants_string($_GET['variants']);
        }else if (strpos($id, '#') !== false) {
          $variants = osc_parse_variants_from_id_string($id);
        }
        
        if (!osc_empty($variants)) {
          $toC_Wishlist->add($osC_Product->getID(), $variants);  
        }else {
          $toC_Wishlist->add($osC_Product->getID());      
        }
      }

      osc_redirect(osc_href_link(FILENAME_ACCOUNT, 'wishlist'));
    }
  }
?>
