<?php
/*
  $Id: compare_products_add.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Actions_compare_products_add {
    function execute() {
      global $osC_Session, $toC_Compare_Products;
      
      $link = osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), $params = osc_get_all_get_params(array('cid', 'action')));
      
      //Get the compare products id string
      if (isset($_GET['cid'])) {
        $cid = $_GET['cid'];
        
        if (strpos($cid, '_') !== false) {
          $cid = str_replace('_', '#', $cid);
        }
        
        //if the products have any variants, the string should be formated as 1#1:1;2:1
        if (preg_match('/^[0-9]+(#?([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)*$/', $cid) || preg_match('/^[a-zA-Z0-9 -_]*$/', $cid)) {
          $toC_Compare_Products->addProduct($cid);
          
          $link = preg_replace('/^products.php?[a-zA-Z0-9 -_]*$/i', 'products.php?' . $cid, $link);
        }
      }
      
      osc_redirect($link);
    }
  }
?>