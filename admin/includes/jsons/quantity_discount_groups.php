<?php
/*
  $Id: quantity_discount_groups.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/classes/quantity_discount_groups.php');
  
  class toC_Json_Quantity_Discount_Groups {
    
    function listQuantityDiscountGroups() {
      global $osC_Database, $toC_Json;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qgroups = $osC_Database->query('select quantity_discount_groups_id, quantity_discount_groups_name from :table_quantity_discount_groups order by quantity_discount_groups_id');
      $Qgroups->bindTable(':table_quantity_discount_groups', TABLE_QUANTITY_DISCOUNT_GROUPS);
      $Qgroups->setExtBatchLimit($start, $limit);
      $Qgroups->execute();
      
      $records = array();
      while ( $Qgroups->next() ) {
        $Qentries = $osC_Database->query('select count(*) as total_entries from :table_quantity_discount_groups_values where quantity_discount_groups_id = :quantity_discount_groups_id');
        $Qentries->bindTable(':table_quantity_discount_groups_values', TABLE_QUANTITY_DISCOUNT_GROUPS_VALUES);
        $Qentries->bindInt(':quantity_discount_groups_id', $Qgroups->valueInt('quantity_discount_groups_id'));
        $Qentries->execute();
        
        $records[] = array('quantity_discount_groups_id' => $Qgroups->ValueInt('quantity_discount_groups_id'),
                           'quantity_discount_groups_name' => $Qgroups->Value('quantity_discount_groups_name'),
                           'total_entries' => $Qentries->Value('total_entries'));
      }
      
      $response = array(EXT_JSON_READER_TOTAL => $Qgroups->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);
    }
      
    function saveQuantityDiscountGroup() {
      global $toC_Json, $osC_Language;
      
      $data = array('quantity_discount_groups_name' => $_REQUEST['quantity_discount_groups_name']);
      
      if (toC_Quantity_Discount_Groups_Admin::save((isset($_REQUEST['quantity_discount_groups_id']) && is_numeric($_REQUEST['quantity_discount_groups_id']) ? $_REQUEST['quantity_discount_groups_id'] : null), $data) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
      
      echo $toC_Json->encode($response);
    }
    
    function listQuantityDiscountEntries() {
      global $osC_Database, $toC_Json, $osC_Language;
      
      $Qentries = $osC_Database->query('select qdgv.quantity_discount_groups_values_id, qdgv.quantity_discount_groups_id, qdgv.customers_groups_id, qdgv.quantity, qdgv.discount, gd.customers_groups_name from :table_quantity_discount_groups_values qdgv left join :table_customers_groups_description gd on (qdgv.customers_groups_id = gd.customers_groups_id and gd.language_id = :language_id)  where quantity_discount_groups_id = :quantity_discount_groups_id  order by qdgv.customers_groups_id, qdgv.quantity');
      $Qentries->bindTable(':table_quantity_discount_groups_values', TABLE_QUANTITY_DISCOUNT_GROUPS_VALUES);
      $Qentries->bindTable(':table_customers_groups_description', TABLE_CUSTOMERS_GROUPS_DESCRIPTION);
      $Qentries->bindInt(':quantity_discount_groups_id', $_REQUEST['quantity_discount_groups_id']);
      $Qentries->bindInt(':language_id', $osC_Language->getID());
      $Qentries->execute();
      
      $records = array();
      while ( $Qentries->next() ) {
        $records[] = array('quantity_discount_groups_values_id' => $Qentries->ValueInt('quantity_discount_groups_values_id'),
                           'quantity_discount_groups_id' => $Qentries->ValueInt('quantity_discount_groups_id'),
                           'customers_groups_id' => $Qentries->ValueInt('customers_groups_id'),
                           'quantity' => $Qentries->Value('quantity'),
                           'discount' => $Qentries->Value('discount') . '%',
                           'customers_groups_name' => ($Qentries->Value('customers_groups_name') == null) ? $osC_Language->get('none') : $Qentries->Value('customers_groups_name'));
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);
    }
    
    function loadQuantityDiscountGroup() {
      global $toC_Json;
      
      $data = toC_Quantity_Discount_Groups_Admin::getData($_REQUEST['quantity_discount_groups_id']);
      
      $response = array('success' => true, 'data' => $data);
      
      echo $toC_Json->encode($response);
    }
      
    function loadQuantityDiscountGroupsValue(){
      global $toC_Json;

      $data = toC_Quantity_Discount_Groups_Admin::getEntryData($_REQUEST['quantity_discount_groups_values_id'], $_REQUEST['quantity_discount_groups_id']);
    
      $response = array('success' => true, 'data' => $data);
      
      echo $toC_Json->encode($response);
    }
      
    function saveQuantityDiscountGroupsValue(){
      global $toC_Json, $osC_Language;
      
      $response = array();
      $data = array('quantity_discount_groups_id' => $_REQUEST['quantity_discount_groups_id'],
                    'customers_groups_id' => $_REQUEST['customers_groups_id'],
                    'quantity' => $_REQUEST['quantity'],
                    'discount' => $_REQUEST['discount']);   
                     
      if (toC_Quantity_Discount_Groups_Admin::saveEntry((isset($_REQUEST['quantity_discount_groups_values_id']) && is_numeric($_REQUEST['quantity_discount_groups_values_id']) ? $_REQUEST['quantity_discount_groups_values_id'] : null), $data) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
      
      echo $toC_Json->encode($response);
    }
      
    function getCustomerGroups(){
      global $osC_Language, $toC_Json, $osC_Database;
      
      $records = array();
      $response = array();
      
      $Qgroups = $osC_Database->query('select customers_groups_id,  customers_groups_name from :table_customers_groups_description where language_id = :language_id order by customers_groups_id');
      $Qgroups->bindTable(':table_customers_groups_description', TABLE_CUSTOMERS_GROUPS_DESCRIPTION);
      $Qgroups->bindInt(':language_id', $osC_Language->getID());
      $Qgroups->execute();
    
      
      $records = array(array('id' => '0', 'text' => $osC_Language->get('none')));
      while ($Qgroups->next()) {
        $records[] = array('id' => $Qgroups->valueInt('customers_groups_id'),
                           'text' => $Qgroups->value('customers_groups_name'));
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);
    }
    
    function deleteQuantityDiscountGroup(){
      global $osC_Language, $toC_Json;
      
      $error = false;
      $feedback = array();
      
      $osC_ObjectInfo = new osC_ObjectInfo(toC_Quantity_Discount_Groups_Admin::getData($_REQUEST['quantity_discount_groups_id']));
      if ( $osC_ObjectInfo->get('total_products') > 0 ) {
        $error = true;
        $feedback[] = sprintf($osC_Language->get('delete_error_quantity_discount_group_in_use'), $osC_ObjectInfo->get('total_products'));
      }
      
      if ($error === false) {    
        if (toC_Quantity_Discount_Groups_Admin::delete($_REQUEST['quantity_discount_groups_id']) ) {
          $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }
      
      echo $toC_Json->encode($response);
    }
      
    function deleteQuantityDiscountGroupsValue(){
      global $osC_Language, $toC_Json;
      
      if ( toC_Quantity_Discount_Groups_Admin::deleteEntry($_REQUEST['quantity_discount_groups_values_id'], $_REQUEST['quantity_discount_groups_id']) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteQuantityDiscountGroupsValues(){
      global $osC_Language, $toC_Json;
      
      $error = false;
      $batch = explode(',', $_REQUEST['batch']);
      
      foreach ( $batch as $id ) {
        if ( !toC_Quantity_Discount_Groups_Admin::deleteEntry($id, $_REQUEST['quantity_discount_groups_id']) ) {
          $error = true;
          break;
        }
      }

      if ( $error === false ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
      
      echo $toC_Json->encode($response);
    }
  }
?>