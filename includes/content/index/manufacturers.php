<?php
/*
  $Id: manufacturers.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Index_Manufacturers extends osC_Template {

/* Private variables */

    var $_module = 'manufacturers',
        $_group = 'index',
        $_page_title,
        $_page_contents = 'product_listing.php',
        $_page_image = 'table_background_list.gif';

/* Class constructor */

    function osC_Index_Manufacturers() {
      global $osC_Services, $osC_Language, $breadcrumb, $osC_Manufacturer;

      $this->_page_title = sprintf($osC_Language->get('index_heading'), STORE_NAME);

      if (is_numeric($_GET[$this->_module])) {
        include('includes/classes/manufacturer.php');
        $osC_Manufacturer = new osC_Manufacturer($_GET[$this->_module]);

        if ($osC_Services->isStarted('breadcrumb')) {
          $breadcrumb->add($osC_Manufacturer->getTitle(), osc_href_link(FILENAME_DEFAULT, $this->_module . '=' . $_GET[$this->_module]));
        }
        
        $this->_page_title = $osC_Manufacturer->getTitle();
        $this->_page_image = 'manufacturers/' . $osC_Manufacturer->getImage();

        $page_title = $osC_Manufacturer->getPageTitle();
        if (!empty($page_title)) {
          $this->setMetaPageTitle($page_title);        
        }
        
        $meta_keywords = $osC_Manufacturer->getMetaKeywords();        
        if (!empty($meta_keywords)) {
          $this->addPageTags('keywords', $meta_keywords);
        }
        
        $meta_description = $osC_Manufacturer->getMetaDescription();        
        if (!empty($meta_description)) {
          $this->addPageTags('description', $meta_description);
        }
        
        $this->_process();
      } else {
        $this->_page_contents = 'index.php';
      }
    }

/* Private methods */

    function _process() {
      global $osC_Manufacturer, $osC_Products;

      include('includes/classes/products.php');
      $osC_Products = new osC_Products();
      $osC_Products->setManufacturer($osC_Manufacturer->getID());

      if (isset($_GET['filter']) && is_numeric($_GET['filter']) && ($_GET['filter'] > 0)) {
        $osC_Products->setCategory($_GET['filter']);
      }
      
      if ( isset($_GET['products_attributes']) && is_array($_GET['products_attributes']) ) {
        $osC_Products->setProductAttributesFilter($_GET['products_attributes']);
      }

      if (isset($_GET['sort']) && !empty($_GET['sort'])) {
        if (strpos($_GET['sort'], '|d') !== false) {
          $osC_Products->setSortBy(substr($_GET['sort'], 0, -2), '-');
        } else {
          $osC_Products->setSortBy($_GET['sort']);
        }
      }
    }
  }
?>
