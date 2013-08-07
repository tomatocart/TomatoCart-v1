<?php
/*
  $Id: product_variants.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/product_variants.php');

  class toC_Json_Product_Variants {

    function listProductVariants() {
      global $toC_Json, $osC_Database, $osC_Language;
      
            $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
    
      $Qgroup = $osC_Database->query('select products_variants_groups_id, products_variants_groups_name, sort_order from :table_products_variants_groups where language_id = :language_id order by sort_order, products_variants_groups_name');
      $Qgroup->bindTable(':table_products_variants_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
      $Qgroup->bindInt(':language_id', $osC_Language->getID());
      $Qgroup->setExtBatchLimit($start, $limit);
      $Qgroup->execute();
      
      $records = array();
      while ($Qgroup->next()) {
        $Qentries = $osC_Database->query('select count(*) as total_entries from :table_products_variants_values_to_products_variants_groups where products_variants_groups_id = :products_variants_groups_id');
        $Qentries->bindTable(':table_products_variants_values_to_products_variants_groups', TABLE_PRODUCTS_VARIANTS_VALUES_TO_PRODUCTS_VARIANTS_GROUPS);
        $Qentries->bindInt(':products_variants_groups_id', $Qgroup->valueInt('products_variants_groups_id'));
        $Qentries->execute();
        
        $records[] = array( 'products_variants_groups_id' => $Qgroup->value('products_variants_groups_id'),
                            'products_variants_groups_name' => $Qgroup->value('products_variants_groups_name'),
                            'total_entries' => $Qentries->value('total_entries'), 
                            'sort_order' => $Qgroup->valueInt('sort_order'));
      }
      
        $response = array(EXT_JSON_READER_TOTAL => $Qgroup->getBatchSize(),
                          EXT_JSON_READER_ROOT => $records); 
      
      echo $toC_Json->encode($response);
    }
    
    function loadProductVariant() {
      global $toC_Json, $osC_Database;
      
      $data = osC_ProductVariants_Admin::getData($_REQUEST['products_variants_groups_id']);
      
      $Qgroup = $osC_Database->query('select * from :table_products_variants_groups where products_variants_groups_id = :products_variants_groups_id');
      $Qgroup->bindTable(':table_products_variants_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
      $Qgroup->bindInt(':products_variants_groups_id', $_REQUEST['products_variants_groups_id']);
      $Qgroup->execute();
      
      while ($Qgroup->next()) {
        $data['products_variants_groups_name[' . $Qgroup->ValueInt('language_id') . ']'] = $Qgroup->Value('products_variants_groups_name');
        
        if (!isset($data['sort_order'])) {
          $data['sort_order'] = $Qgroup->valueInt('sort_order');
        }
      }
      
      $response = array('success' => true, 'data' => $data); 
      
      echo $toC_Json->encode($response);
    }
    
    function saveProductVariant() {
      global $toC_Json, $osC_Language;
      
      $data = array('name' => $_POST['products_variants_groups_name'], 'sort_order' => $_POST['sort_order']);
      
      if ( osC_ProductVariants_Admin::save((isset($_POST['products_variants_groups_id']) && is_numeric($_POST['products_variants_groups_id']) ? $_POST['products_variants_groups_id'] : null), $data) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
      
      echo $toC_Json->encode($response);
    }
   
    function deleteProductVariant() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $error = false;
      $feedback = array();
      
      $Qproducts = $osC_Database->query('select count(*) as total_products from :table_products_variants_entries where products_variants_groups_id = :products_variants_groups_id');
      $Qproducts->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
      $Qproducts->bindInt(':products_variants_groups_id', $_REQUEST['products_variants_groups_id']);
      $Qproducts->execute();
          
      if ( $Qproducts->value('total_products') > 0 ) {
        $error = true;
        $feedback[] = sprintf($osC_Language->get('delete_error_variant_group_in_use'), $Qproducts->value('total_products'));
      }
      
      if ($error === false) {
        if (osC_ProductVariants_Admin::delete($_REQUEST['products_variants_groups_id'])) {
          $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }       

      echo $toC_Json->encode($response);
    }
    
    function listProductVariantsEntries() {
      global $toC_Json, $osC_Database, $osC_Language;
          
      $Qentries = $osC_Database->query('select pvv.products_variants_values_id, pvv.products_variants_values_name, pvv.sort_order from :table_products_variants_values pvv, :table_products_variants_values_to_products_variants_groups pvv2pvg where pvv2pvg.products_variants_groups_id = :products_variants_groups_id and pvv2pvg.products_variants_values_id = pvv.products_variants_values_id and pvv.language_id = :language_id order by pvv.sort_order, pvv.products_variants_values_name');
      $Qentries->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
      $Qentries->bindTable(':table_products_variants_values_to_products_variants_groups', TABLE_PRODUCTS_VARIANTS_VALUES_TO_PRODUCTS_VARIANTS_GROUPS);
      $Qentries->bindInt(':products_variants_groups_id', $_REQUEST['products_variants_groups_id']);
      $Qentries->bindInt(':language_id', $osC_Language->getID());
      $Qentries->execute();
      
      $records = array();
      while ($Qentries->next()) {       
         $records[] = array('products_variants_values_id' => $Qentries->value('products_variants_values_id'),
                            'products_variants_values_name' => $Qentries->value('products_variants_values_name'), 
                            'sort_order' => $Qentries->valueInt('sort_order'));
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records); 
      
      echo $toC_Json->encode($response);
    }
    
    function saveProductVariantsEntry() {
      global $toC_Json, $osC_Language;
      
      $data = array('name' => $_REQUEST['products_variants_values_name'],
                    'products_variants_groups_id' => $_REQUEST['products_variants_groups_id'], 
                    'sort_order' => $_POST['sort_order']);
      
      if ( osC_ProductVariants_Admin::saveEntry((isset($_REQUEST['products_variants_values_id']) && is_numeric($_REQUEST['products_variants_values_id']) ? $_REQUEST['products_variants_values_id'] : null), $data) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteProductVariantsEntry() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $error = false;
      $feedback = array();

      $osC_ObjectInfo = new osC_ObjectInfo(osC_ProductVariants_Admin::getEntryData($_REQUEST['products_variants_values_id']));
      if ( $osC_ObjectInfo->get('total_products') > 0 ) {
        $error = true;
        $feedback[] = sprintf($osC_Language->get('delete_error_group_entry_in_use'), $osC_ObjectInfo->get('total_products'));
      }
      
      if ($error === false) {
        if (osC_ProductVariants_Admin::deleteEntry($_REQUEST['products_variants_values_id'], $_REQUEST['products_variants_groups_id'])) {
          $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }      

      echo $toC_Json->encode($response);             
    }  
    
    function deleteProductVariantsEntries() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $error = false;
      $feedback = array();
      $check_products_array = array();
      
      $batch = explode(',', $_REQUEST['batch']);
      
      $Qentries = $osC_Database->query('select products_variants_values_id, products_variants_values_name from :table_products_variants_values where products_variants_values_id in (":products_variants_values_id") and language_id = :language_id order by products_variants_values_name');
      $Qentries->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
      $Qentries->bindRaw(':products_variants_values_id', implode('", "', array_unique(array_filter(array_slice($batch, 0, MAX_DISPLAY_SEARCH_RESULTS), 'is_numeric'))));
      $Qentries->bindInt(':language_id', $osC_Language->getID());
      $Qentries->execute();
      
      while ( $Qentries->next() ) {
        $Qproducts = $osC_Database->query('select count(*) as total_products from :table_products_variants_entries where products_variants_values_id = :products_variants_values_id');
        $Qproducts->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
        $Qproducts->bindInt(':products_variants_values_id', $Qentries->valueInt('products_variants_values_id'));
        $Qproducts->execute();
        
        if ( $Qproducts->valueInt('total_products') > 0 ) {
          $check_products_array[] = $Qentries->value('products_variants_values_name');
        }        
      }
      
      if ( !empty($check_products_array) ) {
        $error = true;
        $feedback[] = $osC_Language->get('batch_delete_error_group_entries_in_use') . '<p>' . implode(', ', $check_products_array) . '</p>';
      }
      
      if ($error === false) {
        foreach ($batch as $id) {
          if (!osC_ProductVariants_Admin::deleteEntry($id, $_REQUEST['products_variants_groups_id'])) {
            $error = true;
            break;
          }
        }
              
        if ($error === false) {
          $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false ,'feedback' => $osC_Language->get('ms_error_action_not_performed'));               
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      } 
        
      echo $toC_Json->encode($response);               
    } 
    
    function loadProductVariantsEntry() {
      global $toC_Json, $osC_Database;
      
      $data = osC_ProductVariants_Admin::getEntryData($_REQUEST['products_variants_values_id']);
      
      $Qentry = $osC_Database->query('select * from :table_products_variants_values where products_variants_values_id = :products_variants_values_id');
      $Qentry->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
      $Qentry->bindInt(':products_variants_values_id', $_REQUEST['products_variants_values_id']);
      $Qentry->execute();
      
      while ($Qentry->next()) {
        $data['products_variants_values_name[' . $Qentry->ValueInt('language_id') . ']'] = $Qentry->Value('products_variants_values_name');
        
        if (!isset($data['sort_order'])) {
          $data['sort_order'] = $Qentry->ValueInt('sort_order');
        }
      }

      $response = array('success' => true, 'data' => $data); 
      
      echo $toC_Json->encode($response);
    }
    
  }
?>
