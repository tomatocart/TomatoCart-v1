<?php
/*
  $Id: reports_products.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

require('includes/classes/category_tree.php');
  
  class toC_Json_Reports_products {
    
    function listProductsPurchased() {
      global $osC_Database, $toC_Json;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $QbestPurchased = $osC_Database->query('select SQL_CALC_FOUND_ROWS op.products_id, op.products_name, sum(op.products_quantity) as quantity, op.final_price, sum(op.final_price*op.products_quantity) as total from :table_orders_products op, :table_orders o where op.orders_id = o.orders_id ');
      
      if ( !empty($_REQUEST['categories_id']) ) {
        $osC_CategoryTree = new osC_CategoryTree_Admin();
        $categories_id = end(explode('_', $_REQUEST['categories_id']));
        
        $categories = array();
        array_push($categories, $categories_id);
        $osC_CategoryTree->getChildren($categories_id, $categories);
        
        $QbestPurchased->appendQuery('and op.products_id in (select distinct(products_id) from :table_products_to_categories where categories_id in (' . implode(',',$categories) . ')) ');
      }
      
      if ( !empty($_REQUEST['start_date']) ) {
        $QbestPurchased->appendQuery('and o.date_purchased >= :start_date ');
        $QbestPurchased->bindValue(':start_date', $_REQUEST['start_date']);
      }
      if (!empty($_REQUEST['end_date'])) {
        $QbestPurchased->appendQuery(' and o.date_purchased <= :end_date ');
        $QbestPurchased->bindValue(':end_date', $_REQUEST['end_date']);
      }
      
      $QbestPurchased->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
      $QbestPurchased->bindTable(':table_orders', TABLE_ORDERS);
      $QbestPurchased->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
      $QbestPurchased->appendQuery('group by op.products_id order by total desc');
      $QbestPurchased->setExtBatchLimit($start, $limit);
      $QbestPurchased->execute();
      
      $records = array();
      while ($QbestPurchased->next()) {
      	$records[] = array('products_id' => $QbestPurchased->ValueInt('products_id'),
                        	 'products_name' => $QbestPurchased->Value('products_name'),
                        	 'quantity' => $QbestPurchased->ValueInt('quantity'),
                        	 'final_price' => (float)$QbestPurchased->Value('final_price'),
                        	 'total' => (float)$QbestPurchased->Value('total'),
                        	 'average_price' => (float)($QbestPurchased->Value('total')/$QbestPurchased->Value('quantity')));
      }
      
      $response = array(EXT_JSON_READER_TOTAL => $QbestPurchased->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);
    }
    
    function listProductsViewed() {      
      global $osC_Database, $toC_Json, $osC_Language;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
      
      $QbestViewed = $osC_Database->query('select SQL_CALC_FOUND_ROWS p.products_id, pd.products_name, pd.products_viewed, l.name, l.code from :table_products p, :table_products_description pd, :table_languages l where p.products_id = pd.products_id and l.languages_id = pd.language_id ');
    
      if ( !empty($_REQUEST['categories_id']) ) {
        $osC_CategoryTree = new osC_CategoryTree_Admin();
        $categories_id = end(explode('_', $_REQUEST['categories_id']));
        
        $categories = array();
        array_push($categories, $categories_id);
        $osC_CategoryTree->getChildren($categories_id, $categories);
        
        $QbestViewed->appendQuery('and p.products_id in (select distinct(products_id) from :table_products_to_categories where categories_id in (' . implode(',',$categories) . ')) ');
      }
    
      if ( isset($_REQUEST['language_id']) && !empty($_REQUEST['language_id'])) {
        $QbestViewed->appendQuery(' and l.languages_id=:languages_id');
        $QbestViewed->bindValue(':languages_id', $_REQUEST['language_id']);
      }
    
      $QbestViewed->bindTable(':table_products', TABLE_PRODUCTS);
      $QbestViewed->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $QbestViewed->bindTable(':table_languages', TABLE_LANGUAGES);
      $QbestViewed->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
      $QbestViewed->appendQuery('order by pd.products_viewed desc ');
      $QbestViewed->setExtBatchLimit($start, MAX_DISPLAY_SEARCH_RESULTS);
      $QbestViewed->execute();

      $records = array();
      while ($QbestViewed->next()) {
      	$records[] = array('products_id' => $QbestViewed->ValueInt('products_id'),
                        	 'products_name' => $QbestViewed->Value('products_name'),
                        	 'products_viewed' => intval($QbestViewed->Value('products_viewed')),
                        	 'language' => $osC_Language->showImage($QbestViewed->value('code')));
      }
      
      $response = array(EXT_JSON_READER_TOTAL => $QbestViewed->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);
    }
    
    function listCategoriesPurchased() {      
      global $osC_Database, $toC_Json, $osC_Language;
      
      $osC_CategoryTree = new osC_CategoryTree_Admin();
      
      $categories_id = end(explode('_', (empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id'])));
      
      $Qcategories = $osC_Database->query('select cd.categories_name, c.parent_id, cd.categories_id from :table_categories c, :table_categories_description cd where c.categories_id = cd.categories_id and c.parent_id = :parent_id and cd.language_id = :language_id ');
      $Qcategories->bindValue(':parent_id', $categories_id);
      $Qcategories->bindInt(':language_id', $osC_Language->getID());
      $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
      $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
      $Qcategories->execute();
      
      $records = array();
      while ($Qcategories->next()) {
        $categories = array();
        array_push($categories, $Qcategories->value('categories_id'));
        $osC_CategoryTree->getChildren($Qcategories->value('categories_id'), $categories);
        
        $Qtotal = $osC_Database->query('select sum(op.products_quantity) as quantity, sum(op.final_price*op.products_quantity) as total from :table_orders_products op, :table_orders o where op.products_id in (select DISTINCT(products_id) from :table_products_to_categories where categories_id in (' . implode(",", $categories) . ')) and o.orders_id = op.orders_id ');
    
        if (!empty($_REQUEST['start_date'])) {
          $Qtotal->appendQuery('and o.date_purchased >= :start_date ');
          $Qtotal->bindValue(':start_date', $_REQUEST['start_date']);
        }

        if (!empty($_REQUEST['end_date'])) {
          $Qtotal->appendQuery('and o.date_purchased <= :end_date ');
          $Qtotal->bindValue(':end_date', $_REQUEST['end_date']);
        }
    
        $Qtotal->appendQuery('group by op.products_id order by total desc');
        $Qtotal->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
        $Qtotal->bindTable(':table_orders', TABLE_ORDERS);
        $Qtotal->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
        $Qtotal->execute();
        
        $records[] = array('categories_id' => $Qcategories->valueInt('categories_id'),
                           'total' => (float)$Qtotal->value('total'),
                           'quantity' => intval($Qtotal->valueInt('quantity')),
                           'categories_name' => $Qcategories->value('categories_name'),
                           'path' => $osC_CategoryTree->buildBreadcrumb($Qcategories->valueInt('categories_id')));
      }
      $response = array(EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);
    }
    
    function listLowStock() {
      global $osC_Database, $toC_Json, $osC_Language;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
      
      $QlowStock = $osC_Database->query('select SQL_CALC_FOUND_ROWS p.products_id, pd.products_name, p.products_quantity from :table_products p, :table_products_description pd where p.products_id = pd.products_id and pd.language_id = :language_id and p.products_quantity <= :stock_reorder_level');
    
      if ( !empty($_REQUEST['categories_id']) ) {
        $osC_CategoryTree = new osC_CategoryTree_Admin();
        $categories_id = end(explode('_', $_REQUEST['categories_id']));
        
        $categories = array();
        array_push($categories, $categories_id);
        $osC_CategoryTree->getChildren($categories_id, $categories);
        
        $QlowStock->appendQuery('and p.products_id in (select distinct(products_id) from :table_products_to_categories where categories_id in (' . implode(',',$categories) . ')) ');
      }
      
      $QlowStock->appendQuery(' order by p.products_quantity desc ');
      $QlowStock->bindTable(':table_products', TABLE_PRODUCTS);
      $QlowStock->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $QlowStock->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
      $QlowStock->bindInt(':language_id', $osC_Language->getID());
      $QlowStock->bindInt(':stock_reorder_level', STOCK_REORDER_LEVEL);
      $QlowStock->setExtBatchLimit($start, MAX_DISPLAY_SEARCH_RESULTS);
      $QlowStock->execute();
      
      $records = array();
      while ($QlowStock->next()) {
      	$records[] = array('products_id' => $QlowStock->ValueInt('products_id'),
                        	 'products_name' => $QlowStock->Value('products_name'),
                        	 'products_quantity' => intval($QlowStock->ValueInt('products_quantity')));
      }
      
      $response = array(EXT_JSON_READER_TOTAL => $QlowStock->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);
    }
    
    function getLanguages() {
      global $osC_Language, $toC_Json;
      
      $records = array(array('id' => '', 'text' => $osC_Language->get('none')));
      foreach ( $osC_Language->getAll() as $l ) {
        $records[] = array('id' => $l['id'], 'text' => $l['name']);
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response);
    }
    
    function getCategories() {
      global $osC_Language, $toC_Json;
      
      $osC_CategoryTree = new osC_CategoryTree_Admin();
      
      $records = array(array('id' => '0', 'text' => $osC_Language->get('top_category')));
      foreach ($osC_CategoryTree->getTree() as $value) {
        $records[] = array('id' => $value['id'], 'text' => $value['title']);
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response);
    }
  }
?>