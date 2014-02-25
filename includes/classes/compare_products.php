<?php
/*
  $Id: compare_products.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

	class toC_Compare_Products {
	  var $_contents = array();
	      
	  function toC_Compare_Products() {
	    if (!isset($_SESSION['toC_Compare_Products_data'])) {
	      $_SESSION['toC_Compare_Products_data'] = array();
	    }
	    
	    $this->_contents =& $_SESSION['toC_Compare_Products_data'];
	  }
	    
    function exists($products_id) {
      return isset($this->_contents[$products_id]);   
    }
    
    function hasContents() {
      return !empty($this->_contents);
    }
    
	  function reset() {
      $this->_contents = array();
    }
    
	  function addProduct($products_id) {
	    if (!$this->exists($products_id)) {
        $this->_contents[$products_id] = $products_id;
	    }
	  }
	  
	  function deleteProduct($products_id) {
	    if (isset($this->_contents[$products_id])) {
        unset($this->_contents[$products_id]);
      }
	  }
	  
	  function getProducts() {
	    $products = array_keys($this->_contents);
	    
	    return $products;
	  }
	  
	  function outputCompareProductsTable() {
      global $osC_Language, $osC_Image, $osC_Weight, $osC_Currencies, $osC_Services;
      
      $content = '';
      
      $products_images = array();
      $products_titles = array();
      $products_price = array();
      $products_weight = array();
      $products_sku = array();
      $products_manufacturers = array();
      $products_desciptions = array();
	    $products_attributes = array();
	    $products_variants = array();
	    
	    $cols = array('<col width="20%">');
	    $col_width = round(80 / count($this->getProducts()));
	    
      if ($this->hasContents()) {
        foreach ($this->getProducts() as $products_id_string) {
          $cols[] = '<col width="' . $col_width . '%">';
          
          $osC_Product = new osC_Product($products_id_string);
          
          $products_id = osc_get_product_id($products_id_string);
          
          $image = $osC_Product->getImages();
          $product_title = $osC_Product->getTitle();
          $product_price = $osC_Product->getPriceFormated(true);
          $product_weight = $osC_Product->getWeight();
          $product_sku = $osC_Product->getSKU();
          
         //if the product have any variants, it means that the $products_id should be a product string such as 1#1:1;2:2
          $variants = array();
          if ($osC_Product->hasVariants()) {
            $product_variants = $osC_Product->getVariants();
            if (preg_match('/^[0-9]+(?:#?(?:[0-9]+:?[0-9]+)+(?:;?([0-9]+:?[0-9]+)+)*)+$/', $products_id_string)) {
              $products_variant = $product_variants[$products_id_string];
              
              $variants = osc_parse_variants_from_id_string($products_id_string);
            }else {
              $products_variant = $osC_Product->getDefaultVariant();
              
              $variants = $products_variant['groups_id'];
            }
            
            //if the product have any variants, get the group_name:value_name string
            if (isset($products_variant) && isset($products_variant['groups_name']) && is_array($products_variant['groups_name']) && !empty($products_variant['groups_name'])) {
              $products_variants[$products_id]['variants'] = array();
              
              foreach($products_variant['groups_name'] as $groups_name => $value_name) {
                $products_variants[$products_id]['variants'][] = array('name' => $groups_name, 'value' => $value_name);
              }
            }
            
            $product_price = $osC_Currencies->displayPrice($osC_Product->getPrice($variants), $osC_Product->getTaxClassID());
            $product_weight = $products_variant['weight'];
            $product_sku = $products_variant['sku'];
            $image = $products_variant['image'];
          }
          
          $image = (is_array($image) ? $image[0]['image'] : $image);
          
          $products_titles[] = $product_title;
          
          if (!osc_empty($product_price)) {
            $products_price[] = $product_price;
          }
          
          if (!osc_empty($product_weight)) {
            $products_weight[] = $osC_Weight->display($product_weight, $osC_Product->getWeightClass());
          }
          
          if (!osc_empty($product_sku)) {
            $products_sku[] = $product_sku;
          }
          
          if (!osc_empty($osC_Product->getManufacturer()))  {
            $products_manufacturers[] = $osC_Product->getManufacturer();
          }
          
          if (!osc_empty($osC_Product->getDescription()))  {
            $products_desciptions[] = $osC_Product->getDescription();
          }
          
          if ( $osC_Product->hasAttributes() ) {
            foreach ( $osC_Product->getAttributes() as $attribute) {
              $products_attributes[$products_id]['attributes'][] = array('name' => $attribute['name'], 'value' => $attribute['value']);
            }
          }
          
          $products_id_string = str_replace('#', '_', $products_id_string);
          
          //used to fix bug [#209 - Compare / wishlist variant problem]
          if (isset($osC_Services) && $osC_Services->isStarted('sefu') && count($variants) > 0) {
          	$products_images[] = '<div class="image">' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, $products_id), $osC_Image->show($image, $osC_Product->getTitle())) . '</div>' .
          											 '<div class="button">' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, $products_id . '&pid=' . $products_id_string . '&action=cart_add'), osc_draw_image_button('button_in_cart.gif', $osC_Language->get('button_add_to_cart'))) . '</div>';
          }else {
          	$products_images[] = '<div class="image">' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, $products_id), $osC_Image->show($image, $osC_Product->getTitle())) . '</div>' .
          											 '<div class="button">' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, $products_id_string . '&action=cart_add'), osc_draw_image_button('button_in_cart.gif', $osC_Language->get('button_add_to_cart'))) . '</div>';
          }
        }
        
        $content .= '<table id="compareProducts" cellspacing="0" cellpadding="2" border="0">';
        
        //add col groups
        $content .= '<colgroup>';
        foreach($cols as $col) {
          $content .= $col;
        }
        $content .= '</colgroup>';
        
        //add product header
        $content .= '<tbody>';
        $content .= '<tr class="first">';
        $content .= '<th>&nbsp;</th>';
        
        if (!osc_empty($products_images)) {
          foreach($products_images as $k => $product_image) {
            $content .= '<td' . ($k == (count($products_images) - 1) ? ' class="last"' : '') . '>' . $product_image . '</td>';
          }
        }
        $content .= '</tr>';
        $content .= '</tbody>';
        
        //add compare details
        $content .= '<tbody>';
        
        $row_class='even';
        
        //add product name
        if (!osc_empty($products_titles)) {
          $content .= '<tr class="' . $row_class . '">' .
                        '<th>' . $osC_Language->get('field_products_name') . '</th>';
          
          foreach($products_titles as $k => $product_title) {
            $content .= '<td' . ($k == (count($products_titles) - 1) ? ' class="last"' : '') . '>' . $product_title . '</td>';
          }
          
          $content .= '</tr>';
          
          $row_class = ($row_class == 'even' ? 'odd' : 'even');
        }
        
        //add product price
        if (!osc_empty($products_price)) {
          $content .= '<tr class="' . $row_class . '">' .
                        '<th>' . $osC_Language->get('field_products_price') . '</th>';
          
          foreach($products_price as $k => $product_price) {
            $content .= '<td' . ($k == (count($products_price) - 1) ? ' class="last"' : '') . '>' . $product_price . '</td>';
          }
          
          $content .= '</tr>';
          
          $row_class = ($row_class == 'even' ? 'odd' : 'even');
        }
        
        //add product weight
        if (!osc_empty($products_weight)) {
          $content .= '<tr class="' . $row_class . '">' .
                        '<th>' . $osC_Language->get('field_products_weight') . '</th>';
          
          foreach($products_weight as $k => $product_weight) {
            $content .= '<td' . ($k == (count($products_weight) - 1) ? ' class="last"' : '') . '>' . $product_weight . '</td>';
          }
          
          $content .= '</tr>';
          
          $row_class = ($row_class == 'even' ? 'odd' : 'even');
        }
        
        //add product sku
        if (!osc_empty($products_sku)) {
          $content .= '<tr class="' . $row_class . '">' .
                        '<th>' . $osC_Language->get('field_products_sku') . '</th>';
          
          foreach($products_sku as $k => $product_sku) {
            $content .= '<td' . ($k == (count($products_sku) - 1) ? ' class="last"' : '') . '>' . $product_sku . '</td>';
          }
          
          $content .= '</tr>';
          
          $row_class = ($row_class == 'even' ? 'odd' : 'even');
        }
        
        //add product manufacturers
        if (!osc_empty($products_manufacturers)) {
          $content .= '<tr class="' . $row_class . '">' .
                        '<th>' . $osC_Language->get('field_products_manufacturer') . '</th>';
          
          foreach($products_manufacturers as $k => $product_manufacturer) {
            $content .= '<td' . ($k == (count($products_manufacturers) - 1) ? ' class="last"' : '') . '>' . $product_manufacturer . '</td>';
          }
          
          $content .= '</tr>';
          
          $row_class = ($row_class == 'even' ? 'odd' : 'even');
        }
        
       //add product variants
        if (!osc_empty($products_variants)) {
          $content .= '<tr class="' . $row_class . '">' .
                      '<th>' . $osC_Language->get('field_products_variants') . '</th>';
          
          foreach($this->getProducts() as $k => $products_id) {
            if (isset($products_variants[$products_id]['variants']) && !osc_empty($products_variants[$products_id]['variants'])) {
              
              $content .= '<td' . ($k == (count($this->getProducts()) - 1) ? ' class="last"' : '') . '>';
              foreach($products_variants[$products_id]['variants'] as $variant) {
                $content .= '<span class="variant">' . $variant['name'] . ': ' . $variant['value'] . '</span>';
              }
              $content .= '</td>';
            }
          }
          
          $content .= '</tr>';
          
          $row_class = ($row_class == 'even' ? 'odd' : 'even');
        }
        
        //add product attributes
        if (!osc_empty($products_attributes)) {
          $content .= '<tr class="' . $row_class . '">' .
                      '<th>' . $osC_Language->get('field_products_attributes') . '</th>';
          
          foreach($this->getProducts() as $k => $products_id) {
            if (isset($products_attributes[$products_id]['attributes']) && !osc_empty($products_attributes[$products_id]['attributes'])) {
              
              $content .= '<td' . ($k == (count($this->getProducts()) - 1) ? ' class="last"' : '') . '>';
              foreach($products_attributes[$products_id]['attributes'] as $attribute) {
                $content .= '<span class="attribute">' . $attribute['name'] . ': ' . $attribute['value'] . '</span>';
              }
              $content .= '</td>';
            }
          }
          
          $content .= '</tr>';
          
          $row_class = ($row_class == 'even' ? 'odd' : 'even');
        }
        
        //add product description
        if (!osc_empty($products_desciptions)) {
          $content .= '<tr class="' . $row_class . ' last">' .
                        '<th>' . $osC_Language->get('field_products_description') . '</th>';
          
          foreach($products_desciptions as $k => $product_description) {
            $content .= '<td' . ($k == (count($products_desciptions) - 1) ? ' class="last"' : '') . '>' . $product_description . '</td>';
          }
          
          $content .= '</tr>';
          
          $row_class = ($row_class == 'even' ? 'odd' : 'even');
        }
                
        $content .= '</tbody>';
        $content .= '</table>';
      }
      
      return $content;
	  }
	}
?>