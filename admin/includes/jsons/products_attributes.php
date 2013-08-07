<?php
/*
  $Id: products_attributes.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/classes/products_attributes.php');
  
  class toC_Json_Products_Attributes {
    
    function listProductsAttributes() {
      global $osC_Database, $toC_Json, $osC_Language;

      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qgroups = $osC_Database->query('select products_attributes_groups_id, products_attributes_groups_name from :table_products_attributes_groups ');
      $Qgroups->bindTable(':table_products_attributes_groups', TABLE_PRODUCTS_ATTRIBUTES_GROUPS);
      $Qgroups->setExtBatchLimit($start, $limit);
      $Qgroups->execute();
      
      $records = array();
      while ( $Qgroups->next() ) {
        $Qentries = $osC_Database->query('select count(*) as total_entries from :table_products_attributes_values where products_attributes_groups_id = :products_attributes_groups_id and language_id = :language_id ');
        $Qentries->bindTable(':table_products_attributes_values', TABLE_PRODUCTS_ATTRIBUTES_VALUES);
        $Qentries->bindInt(':products_attributes_groups_id', $Qgroups->valueInt('products_attributes_groups_id'));
        $Qentries->bindInt(':language_id', $osC_Language->getID());
        $Qentries->execute();
        
        $total_entries = $Qentries->Value('total_entries');
        $records[] = array('products_attributes_groups_id' => $Qgroups->ValueInt('products_attributes_groups_id'),
                           'products_attributes_groups_name' => $Qgroups->Value('products_attributes_groups_name'),
                           'total_entries' => $total_entries);
      }
      $Qgroups->freeResult();
      
      $response = array(EXT_JSON_READER_TOTAL => $Qgroups->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);
    }
      
    function saveProductsAttributes() {
      global $toC_Json, $osC_Language;
      
      $data = array('name' => $_REQUEST['products_attributes_groups_name']);

      if ( osC_Products_Attributes_Admin::save((isset($_REQUEST['products_attributes_groups_id']) && is_numeric($_REQUEST['products_attributes_groups_id']) ? $_REQUEST['products_attributes_groups_id'] : null), $data) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false ,'feedback' => $osC_Language->get('ms_error_action_not_performed'));               
      }
      
      echo $toC_Json->encode($response);
    }
          
    function loadProductsAttributes() {
      global $toC_Json;

      $data = osC_Products_Attributes_Admin::getData($_REQUEST['products_attributes_groups_id']);
      
      $response = array('success' => true, 'data' => $data);
      
      echo $toC_Json->encode($response);
    }
      
    function deleteProductsAttributes() {
      global $osC_Language, $toC_Json;
      
      $error = false;
      $feedback = array();
      
      $osC_ObjectInfo = new osC_ObjectInfo(osC_Products_Attributes_Admin::getData($_REQUEST['products_attributes_groups_id']));
      if ( $osC_ObjectInfo->get('total_products') > 0 ) {
        $error = true;
        $feedback[] = sprintf($osC_Language->get('delete_error_attributes_group_in_use'), $osC_ObjectInfo->get('total_products'));
      }
      
      if ($error === false) {    
        if ( osC_Products_Attributes_Admin::delete($_REQUEST['products_attributes_groups_id']) ) {
          $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false ,'feedback' => $osC_Language->get('ms_error_action_not_performed'));               
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }
      
      echo $toC_Json->encode($response);
    }    
          
    function listProductsAttributesEntries() {
      global $osC_Database, $toC_Json, $osC_Language;
      
      $Qentries = $osC_Database->query('select * from :table_products_attributes_values where products_attributes_groups_id = :products_attributes_groups_id  and language_id = :language_id order by sort_order ');
      $Qentries->bindTable(':table_products_attributes_values', TABLE_PRODUCTS_ATTRIBUTES_VALUES);
      $Qentries->bindInt(':products_attributes_groups_id', $_REQUEST['products_attributes_groups_id']);
      $Qentries->bindInt(':language_id', $osC_Language->getID());
      $Qentries->execute();
      
      $records = array();
      while ($Qentries->next()) {
        $records[] = array('products_attributes_values_id' => $Qentries->ValueInt('products_attributes_values_id'),
                           'products_attributes_groups_id' => $Qentries->ValueInt('products_attributes_groups_id'),
                           'language_id' => $Qentries->ValueInt('language_id'),
                           'status' => $Qentries->Value('status'),
                           'module' => $Qentries->Value('module'),
                           'name' => $Qentries->Value('name'),
                           'value' => $Qentries->Value('value'),
                           'sort_order' => $Qentries->ValueInt('sort_order'));
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);
    }
      
    function loadProductsAttributesEntries() {
      global $toC_Json, $osC_Database;

      $Qentries = $osC_Database->query('select * from :table_products_attributes_values where products_attributes_values_id = :products_attributes_values_id');
      $Qentries->bindTable(':table_products_attributes_values', TABLE_PRODUCTS_ATTRIBUTES_VALUES);
      $Qentries->bindInt(':products_attributes_values_id', $_REQUEST['products_attributes_values_id']);
      $Qentries->execute();
      
      $data = array();
      while ($Qentries->next()) {
        $data['attribute_module'] = $Qentries->Value('module');
        $data['status'] = $Qentries->ValueInt('status');
        $data['name[' . $Qentries->valueInt('language_id') . ']'] = $Qentries->value('name');
        $data['value[' . $Qentries->valueInt('language_id') . ']'] = $Qentries->value('value');
        $data['sort_order'] = $Qentries->ValueInt('sort_order');
      }
      
      $response = array('success' => true, 'data' => $data);
      
      echo $toC_Json->encode($response);
    }
      
    function saveProductsAttributesEntries() {
      global $toC_Json, $osC_Language;
      
      $data = array('products_attributes_groups_id' => $_REQUEST['products_attributes_groups_id'],
                    'name' => $_REQUEST['name'],
                    'module' => $_REQUEST['attribute_module'],
                    'value' => isset($_REQUEST['value']) ? $_REQUEST['value'] : null,
                    'status' => $_REQUEST['status'],
                    'sort_order' => $_REQUEST['sort_order']);
      
      if ( osC_Products_Attributes_Admin::saveEntry((isset($_REQUEST['products_attributes_values_id']) && is_numeric($_REQUEST['products_attributes_values_id']) ? $_REQUEST['products_attributes_values_id'] : null), $data) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false ,'feedback' => $osC_Language->get('ms_error_action_not_performed'));               
      }

      echo $toC_Json->encode($response);
    }
      
    function deleteProductsAttributesEntry() {
      global $osC_Language, $toC_Json;
      
      $error = false;
      $feedback = array();

      $osC_ObjectInfo = new osC_ObjectInfo(osC_Products_Attributes_Admin::getEntryData($_REQUEST['products_attributes_values_id']));
      if ( $osC_ObjectInfo->get('total_products') > 0 ) {
        $error = true;
        $feedback[] = sprintf($osC_Language->get('delete_error_group_entry_in_use'), $osC_ObjectInfo->get('total_products'));
      }
      
      if ($error === false) {    
        if ( osC_Products_Attributes_Admin::deleteEntry($_REQUEST['products_attributes_values_id'], $_REQUEST['products_attributes_groups_id']) ) {
          $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false ,'feedback' => $osC_Language->get('ms_error_action_not_performed'));               
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteProductsAttributesEntries() {
      global $osC_Language, $toC_Json, $osC_Database;
      
      $error = false;
      $feedback = array();
      $batch = explode(',', $_REQUEST['batch']);
      
      $check_products_array = array();
    
      $Qentries = $osC_Database->query('select * from :table_products_attributes_values where products_attributes_values_id in (":products_attributes_values_id") and language_id = :language_id');
      $Qentries->bindTable(':table_products_attributes_values', TABLE_PRODUCTS_ATTRIBUTES_VALUES);
      $Qentries->bindRaw(':products_attributes_values_id', implode('", "', array_unique(array_filter(array_slice($batch, 0, MAX_DISPLAY_SEARCH_RESULTS), 'is_numeric'))));
      $Qentries->bindInt(':language_id', $osC_Language->getID());
      $Qentries->execute();
      
      while ( $Qentries->next() ) {
        $Qproducts = $osC_Database->query('select count(*) as total_products from :table_products_attributes where products_attributes_values_id = :products_attributes_values_id');
        $Qproducts->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
        $Qproducts->bindInt(':products_attributes_values_id', $Qentries->valueInt('products_attributes_values_id'));
        $Qproducts->execute();
    
        if ( $Qproducts->valueInt('total_products') > 0 ) {
          $check_products_array[] = $Qentries->value('name');
        }
      }
      if ( !empty($check_products_array) ) {
        $error = true;
        $feedback[] = $osC_Language->get('batch_delete_error_group_entries_in_use') . '<br />' . implode(', ', $check_products_array);
      }
      
      if ($error === false) {  
        foreach ( $batch as $id ) {
          if ( !osC_Products_Attributes_Admin::deleteEntry($id, $_REQUEST['products_attributes_groups_id']) ) {
            $error = true;
            break;
          }
        }
  
        if ( $error === false ) {
          $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false ,'feedback' => $osC_Language->get('ms_error_action_not_performed'));               
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }
      
      echo $toC_Json->encode($response);
    }
      
    function setEntryStatus() {
      global $toC_Json, $osC_Language;
      
      if ( isset($_REQUEST['products_attributes_values_id']) && osC_Products_Attributes_Admin::setEntryStatus($_REQUEST['products_attributes_values_id'], (isset($_REQUEST['flag']) ? $_REQUEST['flag'] : null)) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
  }
?>