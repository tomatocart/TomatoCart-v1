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

  class osC_Boxes_Compare_Products extends osC_Modules {
    var $_title,
        $_code = 'compare_products',
        $_author_name = 'TomatoCart',
        $_author_www = 'http://www.tomatocart.com',
        $_group = 'boxes';

    function osC_Boxes_Compare_Products() {
      global $osC_Language;

      $this->_title = $osC_Language->get('box_compare_products_heading');
    }

    function initialize() {
      global $osC_Language, $osC_Template, $toC_Compare_Products;
      
      if ($toC_Compare_Products->hasContents()) {
        $osC_Template->addStyleSheet('ext/multibox/multibox.css');
        $osC_Template->addJavascriptFilename('ext/multibox/Overlay.js');
        $osC_Template->addJavascriptFilename('ext/multibox/MultiBox.js');
        
        $this->_content = '<ul>';
        
        foreach ($toC_Compare_Products->getProducts() as $products_id) {
          $osC_Product = new osC_Product($products_id);
          
          $cid = $products_id;
          $str_variants = '';
          //if the product have any variants, it means that the $products_id should be a product string such as 1#1:1;2:2
          if ($osC_Product->hasVariants()) {
            $variants = $osC_Product->getVariants();
            if (preg_match('/^[0-9]+(#?([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)+$/', $products_id)) {
              $products_variant = $variants[$products_id];
            }else {
              $products_variant = $osC_Product->getDefaultVariant();
            }
            
            //if the product have any variants, get the group_name:value_name string
            if (isset($products_variant) && isset($products_variant['groups_name']) && is_array($products_variant['groups_name']) && !empty($products_variant['groups_name'])) {
              $str_variants .= ' -- ';
              
              foreach($products_variant['groups_name'] as $groups_name => $value_name) {
                $str_variants .= '<strong>' . $groups_name . ': ' . $value_name . '</strong>;';
              }
              
              //clean the last ';'
              if (($pos = strrpos($str_variants, ';')) !== false) {
                $str_variants = substr($str_variants, 0, -1);
              }
            }
            
            //build the product string that could be used
            if (strpos($products_id, '#') !== false) {
              $cid = str_replace('#', '_', $products_id);
            }
          }
            
          $this->_content .= '<li>' . osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), 'cid=' . $cid . '&' . osc_get_all_get_params(array('cid', 'action')) . '&action=compare_products_remove'), osc_draw_image_button('button_delete_icon.png', $osC_Language->get('button_delete')), 'style="float: right; margin: 0 3px 1px 3px"') . osc_link_object(osc_href_link(FILENAME_PRODUCTS, $products_id), $osC_Product->getTitle() . $str_variants) . '</li>';
        }
        
        $this->_content .= '</ul>';
        $this->_content .= 
          '<p>' .
            '<span style="float: right">' . osc_link_object(osc_href_link(FILENAME_JSON, 'module=products&action=compare_products'), osc_draw_image_button('small_compare_now.png', $osC_Language->get('button_compare_now')), 'class="multibox" rel="ajax:true"') . '</span>' .
            osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), osc_get_all_get_params(array('action')) . '&action=compare_products_clear'), osc_draw_image_button('small_clear.png', $osC_Language->get('button_clear'))) . '&nbsp;&nbsp;' .
          '</p>';
      
        $js .= '<script type="text/javascript">
                  window.addEvent("domready",function() {
                    
                    var box = new MultiBox(\'multibox\', { 
                        movieWidth: 820,
                        movieHeight: 600
                    });
                  });
                </script>';
        
        $this->_content .= "\n" . $js;
      }
    }
  }
?>
