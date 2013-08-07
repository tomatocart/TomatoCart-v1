<?php
/*
  $Id: feature_products_manager.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
  include('includes/classes/category_tree.php');
  include("includes/classes/feature_products_manager.php");
  
  class toC_Json_Feature_Products_Manager {
  
    function listProducts() {
      global $osC_Database, $toC_Json, $osC_Language;
      $current_category_id = end(explode( '_' ,(empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id'])));
      
      if ( $current_category_id > 0 ) {
        $osC_CategoryTree = new osC_CategoryTree_Admin();
        $osC_CategoryTree->setBreadcrumbUsage(false);
    
        $in_categories = array($current_category_id);
    
        foreach($osC_CategoryTree->getTree($current_category_id) as $category) {
          $in_categories[] = $category['id'];
        }

        $Qproducts = $osC_Database->query("select pd.products_id as products_id, pd.products_name as products_name, pf.sort_order as sort_order from :table_products_frontpage pf, :table_products_description pd, :table_products_to_categories p2c where pf.products_id = pd.products_id and p2c.products_id = pf.products_id and p2c.categories_id in (:categories_id)");
        $Qproducts->bindRaw(':categories_id', implode(',', $in_categories));
        $Qproducts->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES); 
      } else {
      	$Qproducts = $osC_Database->query("select pd.products_id as products_id, pd.products_name as products_name, pf.sort_order as sort_order from :table_products_frontpage pf, :table_products_description pd where pf.products_id = pd.products_id");
      }
      $Qproducts->appendQuery("order by pf.sort_order");
      $Qproducts->bindTable(':table_products_frontpage', TABLE_PRODUCTS_FRONTPAGE);
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->execute();
      
      $records = array();
      while($Qproducts->next()) {
        $records[] = array('products_id'   => $Qproducts->value("products_id"),
                           'products_name' => $Qproducts->value("products_name"),
                           'sort_order'    => $Qproducts->value("sort_order"));
      }

      $response = array(EXT_JSON_READER_TOTAL => sizeof($records), EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);   
    }
    
    function deleteProduct() {
      global $toC_Json, $osC_Language;
      
      if (toC_Feature_Products_Manager_Admin::delete($_REQUEST['products_id'])) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }   
      
      echo $toC_Json->encode($response);
    }
    
    function deleteProducts() {
      global $toC_Json, $osC_Language;
      
      $error = false;
      
      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if ( !toC_Feature_Products_Manager_Admin::delete($id) ) {
          $error = true;
          break;
        }
      }

      if ($error === false) {      
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function updateSortOrder() {
      global $toC_Json, $osC_Language;
      
      if (toC_Feature_Products_Manager_Admin::save($_REQUEST['products_id'], $_REQUEST['sort_value'])) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }   
      
      echo $toC_Json->encode($response);
    }
    
    function getCategories() {
      global $toC_Json, $osC_Language;
      
      $osC_CategoryTree = new osC_CategoryTree_Admin();
      
      $categories_array = array();
      if (isset($_REQUEST['top']) && ($_REQUEST['top'] == '1')) {
        $categories_array = array(array('id' => '', 'text' => $osC_Language->get('top_category')));
      }
      
      foreach ($osC_CategoryTree->getTree() as $value) {
        $categories_array[] = array('id' => $value['id'],
                                    'text' => $value['title']);
      }

      $response = array(EXT_JSON_READER_ROOT => $categories_array);    
                          
      echo $toC_Json->encode($response);
    }
  }
?>