<?php
/*
  $Id: products.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Products_Products extends osC_Template {

/* Private variables */

    var $_module = 'products',
        $_group = 'products',
        $_page_title,
        $_page_contents = 'info.php',
        $_page_image = 'table_background_list.gif';

/* Class constructor */

    function osC_Products_Products() {
      global $osC_Database, $osC_Services, $osC_Session, $osC_Language, $breadcrumb, $cPath, $cPath_array, $osC_Manufacturer, $osC_Product;

      if (empty($_GET) === false) {
        $id = false;

// PHP < 5.0.2; array_slice() does not preserve keys and will not work with numerical key values, so foreach() is used
        foreach ($_GET as $key => $value) {
          if ( (ereg('^[0-9]+(_?([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)*$', $key) || ereg('^[a-zA-Z0-9 -_]*$', $key)) && ($key != $osC_Session->getName()) ) {
            $id = $key;
          }

          break;
        }
        
        if (strpos( $id, '_') !== false) {
          $id = str_replace('_', '#', $id);
        }

        if (($id !== false) && osC_Product::checkEntry($id)) {
          $osC_Product = new osC_Product($id);
          $osC_Product->incrementCounter();
          
          $this->_page_title = $osC_Product->getTitle();
          
          $this->addPageTags('keywords', $osC_Product->getTitle());
          $this->addPageTags('keywords', $osC_Product->getSKU());

          if ($osC_Product->hasPageTitle()) {
            $this->setMetaPageTitle($osC_Product->getPageTitle());
          }
          
          if ($osC_Product->hasTags()) {
            $this->addPageTags('keywords', $osC_Product->getTags());
          }
          
          if ($osC_Product->hasMetaKeywords()) {
            $this->addPageTags('keywords', $osC_Product->getMetaKeywords());
          }

          if ($osC_Product->hasMetaDescription()) {
            $this->addPageTags('description', $osC_Product->getMetaDescription());
          }
          
          osC_Services_category_path::process($osC_Product->getCategoryID());

          if (isset($_GET['manufacturers']) && (empty($_GET['manufacturers']) === false)) {
            require_once('includes/classes/manufacturer.php');
            $osC_Manufacturer = new osC_Manufacturer($_GET['manufacturers']);
            
            if ($osC_Services->isStarted('breadcrumb')) {
              $breadcrumb->add($osC_Manufacturer->getTitle(), osc_href_link(FILENAME_DEFAULT, 'manufacturers=' . $_GET['manufacturers'])); 
              $breadcrumb->add($osC_Product->getTitle(), osc_href_link(FILENAME_PRODUCTS, $osC_Product->getID()));  
            }
            
            //Using rel=”canonical” links to remove the duplication - same product info page
            //To fix the bug - [#123] Two Different SEO link for one product
            if (isset($osC_Services) && $osC_Services->isStarted('sefu')) {
              $this->_add_canonical($osC_Product->getID());
            }
          } else { 
            if ($osC_Services->isStarted('breadcrumb')) {
              $Qcategories = $osC_Database->query('select categories_id, categories_name from :table_categories_description where categories_id in (:categories_id) and language_id = :language_id');
              $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
              $Qcategories->bindRaw(':categories_id', implode(',', $cPath_array));
              $Qcategories->bindInt(':language_id', $osC_Language->getID());
              $Qcategories->execute();
    
              $categories = array();
              while ($Qcategories->next()) {
                $categories[$Qcategories->value('categories_id')] = $Qcategories->valueProtected('categories_name');
              }
  
              $Qcategories->freeResult();
    
              for ($i=0, $n=sizeof($cPath_array); $i<$n; $i++) {
                $breadcrumb->add($categories[$cPath_array[$i]], osc_href_link(FILENAME_DEFAULT, 'cPath=' . implode('_', array_slice($cPath_array, 0, ($i+1)))));
              }
  
          		$breadcrumb->add($osC_Product->getTitle(), osc_href_link(FILENAME_PRODUCTS, $osC_Product->getID()));
            }
          }

          $this->addStyleSheet('ext/mojozoom/mojozoom.css');
          $this->addStyleSheet('templates/' . $this->getCode() . '/javascript/milkbox/milkbox.css');
        } else {
          $this->_page_title = $osC_Language->get('product_not_found_heading');
          $this->_page_contents = 'info_not_found.php';
        }
      } else {
        $this->_page_title = $osC_Language->get('product_not_found_heading');
        $this->_page_contents = 'info_not_found.php';
      }
    }
    
    /**
     * Using rel=”canonical” links to remove the duplication - same product info page
     * To fix the bug - [#123] Two Different SEO link for one product
     * 
     * @access private
     * @param int $products_id
     * @return void
     */
    function _add_canonical($products_id) {
      global $toC_Sefu, $request_type;
      
      //get the product link to be stored by the search engine
      $product_link = $toC_Sefu->getProductCategoryLink($products_id);
      
      //get the link prefix
      if ( ($request_type == 'SSL') && (ENABLE_SSL === true) ) {
        $link_prefix = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
      } else {
        $link_prefix = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
      }
      
      $this->rel_canonical = '<link rel="canonical" href="' . $link_prefix . $product_link . '"/>';
    }
  }
?>
