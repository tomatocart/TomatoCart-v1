<?php
/*
  $Id: compare_products.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Products_Compare_Products extends osC_Template {
  
    var $_module = 'compare_products',
        $_group = 'products',
        $_page_title,
        $_page_contents = 'compare_products.php',
        $_page_image,
        $_has_header = false,
        $_has_footer = false,
        $_has_box_modules = false,
        $_has_content_modules = false,
        $_show_debug_messages = false;

/* Class constructor */

    function osC_Products_Compare_Products() {
      global $osC_Language, $osC_Image, $attributes, $toC_Compare_Products, $osC_NavigationHistory, $osC_Weight;

      $this->_title = $osC_Language->get('compare_products_title');
      
      $osC_NavigationHistory->removeCurrentPage();
    }
  }
?>
