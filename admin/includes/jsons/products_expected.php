<?php
/*
  $Id: products_expected.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/products.php');

  class toC_Json_Products_Expected {
  
    function listProductsExpected() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qproducts = $osC_Database->query('select p.products_id, p.products_date_available, pd.products_name from :table_products p, :table_products_description pd where p.products_date_available is not null and p.products_id = pd.products_id and pd.language_id = :language_id order by p.products_date_available');
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindInt(':language_id', $osC_Language->getID());
      $Qproducts->setExtBatchLimit($start, $limit);
      $Qproducts->execute();

      $record = array();
    
      while ( $Qproducts->next() ) {
        $record[] = array('products_id' => $Qproducts->valueInt('products_id'),
                           'products_name' => $Qproducts->value('products_name'),
                           'products_date_available' =>  osC_DateTime::getShort($Qproducts->value('products_date_available')));         
      }
        
      $response = array(EXT_JSON_READER_TOTAL => $Qproducts->getBatchSize(),
                        EXT_JSON_READER_ROOT => $record); 
                          
      echo $toC_Json->encode($response);
    }
    
    function loadProductsExpected() {
      global $toC_Json;
      
      $data = osC_Products_Admin::getData($_REQUEST['products_id']);
      
      $data['products_date_available'] = osC_DateTime::getDate($data['products_date_available']);
      
      $response = array( 'success' => true, 'data' => $data ); 
                   
      echo $toC_Json->encode($response);  
    }
    
    function saveProductsExpected() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $data = array('date_available' => $_REQUEST['products_date_available']);

      if ( osC_Products_Admin::setDateAvailable($_REQUEST['products_id'], $data) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed')); 
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);   
    }

  }
?>
