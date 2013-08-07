<?php
/*
  $Id: products.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Json_Products {
  
    function compareProducts() {
      global $osC_Language, $toC_Compare_Products;
      
      $osC_Language->load('products');
      
      $content = '<div class="compareContainer">';
      $content .=   '<div class="compareHeader clearfix">';
      $content .=     '<h1>' . $osC_Language->get('compare_products_heading') . '</h1>';
      $content .=   '</div>';
      
      $content .= $toC_Compare_Products->outputCompareProductsTable();
      $content .= '</div>';
      
      echo $content;
    }
    
    function getVariantsFormattedPrice() {
      global $toC_Json;
      
      $response = array();
      
      if (isset($_POST['products_id_string']) && preg_match('/^[0-9]+(#([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)$/', $_POST['products_id_string'])) {
        $response['success'] = true;
        
        $variants = osc_parse_variants_from_id_string($_POST['products_id_string']);
        $osC_Product = new osC_Product($_POST['products_id_string']);
        $formatted_price = $osC_Product->getPriceFormated(true, $variants);
        
        $response['formatted_price'] = $formatted_price;
      }else {
        $response['success'] = false;
        $response['feedback'] = 'The products id string is not valid';
      }
      
      echo $toC_Json->encode($response);
    }
  }
  