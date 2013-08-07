<?php
/*
  $Id: compare_products_remove.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Actions_compare_products_remove {
    function execute() {
      global $osC_Session, $toC_Compare_Products;
      
      //the cid must be a numeric or the string as 1#1:1;2:2
      if (isset($_GET['cid'])) {
        if (preg_match('/^[0-9]+(_?([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)*$/', $_GET['cid']) || preg_match('^[a-zA-Z0-9 -_]*$', $_GET['cid'])) {
          $cid = $_GET['cid'];
          
          if (strpos($cid, '_') !== false) {
            $cid = str_replace('_', '#', $cid);
          }
        }
        
        $toC_Compare_Products->deleteProduct($cid);
        
        osc_redirect(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), osc_get_all_get_params(array('cid', 'action'))));
      }
    }
  }
?>