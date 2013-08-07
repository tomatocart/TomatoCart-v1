<?php
/*
  $Id: customers_groups.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/customers_groups.php');

  class toC_Json_Customers_Groups {
        
    function listCustomersGroups() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];      
      
      $Qgroups = $osC_Database->query('select c.customers_groups_id, cg.language_id, cg.customers_groups_name,  c.customers_groups_discount, c.is_default from :table_customers_groups c, :table_customers_groups_description cg where c.customers_groups_id = cg.customers_groups_id and cg.language_id = :language_id order by cg.customers_groups_name');
      $Qgroups->bindTable(':table_customers_groups', TABLE_CUSTOMERS_GROUPS);
      $Qgroups->bindTable(':table_customers_groups_description', TABLE_CUSTOMERS_GROUPS_DESCRIPTION);
      $Qgroups->bindInt(':language_id', $osC_Language->getID());
      $Qgroups->setExtBatchLimit($start, $limit);
      $Qgroups->execute();
        
      $records = array();     
      while ( $Qgroups->next() ) {
        $group_name = $Qgroups->value('customers_groups_name');
        if ( $Qgroups->valueInt('is_default') ) {
          $group_name .= '(' . $osC_Language->get('default_entry') . ')';
        }
        
        $records[] = array(
          'language_id' => $Qgroups->valueInt('language_id'),
          'customers_groups_id' => $Qgroups->valueInt('customers_groups_id'),
          'customers_groups_name' => $group_name,
          'customers_groups_discount' => sprintf("%d%%", $Qgroups->valueInt('customers_groups_discount'))
        );           
      }
      $Qgroups->freeResult();
       
      $response = array(EXT_JSON_READER_TOTAL => $Qgroups->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }          
    
    function loadCustomersGroups() {
      global $toC_Json, $osC_Database;
     
      $data = toC_Customers_Groups_Admin::getData($_REQUEST['groups_id']);
      
      $Qcgd = $osC_Database->query('select language_id, customers_groups_name from :table_customers_groups_description where customers_groups_id = :customers_groups_id');
      $Qcgd->bindTable(':table_customers_groups_description', TABLE_CUSTOMERS_GROUPS_DESCRIPTION);
      $Qcgd->bindInt(':customers_groups_id', $_REQUEST['groups_id']);
      $Qcgd->execute();
      
      while ($Qcgd->next()) {
        $data['customers_groups_name[' . $Qcgd->ValueInt('language_id') . ']'] = $Qcgd->Value('customers_groups_name');
      }
      
      $response = array('success' => true, 'data' => $data);
      
      echo $toC_Json->encode($response);
    }
   
    function saveCustomersGroups() {
      global $toC_Json, $osC_Language;
      
      $data = array('customers_groups_id' => $_REQUEST['groups_id'],
                    'customers_groups_discount' => $_REQUEST['customers_groups_discount'],
                    'customers_groups_name' => $_REQUEST['customers_groups_name'],
                    'is_default' => (isset($_REQUEST['is_default']) ? $_REQUEST['is_default'] : ''));
      
      if ( toC_Customers_Groups_Admin::save((isset($_REQUEST['groups_id']) && is_numeric($_REQUEST['groups_id'] ) ? $_REQUEST['groups_id'] : null), $data) ) {
        $response = array('success' => true , 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false , 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
     
      echo $toC_Json->encode($response);
    }

    function deleteCustomersGroup() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $error = false;
      
      $osC_ObjectInfo = new osC_ObjectInfo(toC_Customers_Groups_Admin::getData($_REQUEST['customer_groups_id']));
      
      $Qcheck = $osC_Database->query('select count(*) as total from :table_customers where customers_groups_id = :customers_groups_id');
      $Qcheck->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qcheck->bindInt(':customers_groups_id', $_REQUEST['customer_groups_id']);
      $Qcheck->execute();
      
      $feedback = array();
      if ($osC_ObjectInfo->get('is_default') == 1) {
        $error = true;
        $feedback[] = $osC_Language->get('delete_error_customer_group_prohibited');
      }
      
      if ($Qcheck->valueInt('total') > 0) {
        $error = true;
        $feedback[] = sprintf($osC_Language->get('delete_error_customer_group_in_use'), $Qcheck->valueInt('total'));
      }
      
      if ($error === false) {
        if ( !toC_Customers_Groups_Admin::delete($_REQUEST['customer_groups_id']) ) {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        } else {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        }
      } else {
        $feedback = implode('<br />', $feedback);
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . $feedback);
      }
            
      echo $toC_Json->encode($response);
    }
    
    function deleteCustomersGroups() {
      global $toC_Json, $osC_Database, $osC_Language;
     
      $error = false;
      
      $batch = explode(',', $_REQUEST['batch']);
    
      $Qgroups = $osC_Database->query('select cg.customers_groups_id, cgd.customers_groups_name, is_default from :table_customers_groups cg, :table_customers_groups_description cgd where cg.customers_groups_id = cgd.customers_groups_id and cg.customers_groups_id in (":customers_groups_id") and cgd.language_id = :language_id order by customers_groups_name');
      $Qgroups->bindTable(':table_customers_groups', TABLE_CUSTOMERS_GROUPS);
      $Qgroups->bindTable(':table_customers_groups_description', TABLE_CUSTOMERS_GROUPS_DESCRIPTION);
      $Qgroups->bindRaw(':customers_groups_id', implode('", "', array_unique(array_filter(array_slice($batch, 0, MAX_DISPLAY_SEARCH_RESULTS), 'is_numeric'))));
      $Qgroups->bindInt(':language_id', $osC_Language->getID());
      $Qgroups->execute();
      
      $feedback = array();
      $check_customers_flag = array();
      while ($Qgroups->next()) {
        if ($Qgroups->value('is_default') == 1) {
          $error = true;
          $feedback[] = $osC_Language->get('delete_error_customer_group_prohibited');
        }
    
        $Qcheck = $osC_Database->query('select count(*) as total from :table_customers where customers_groups_id = :customers_groups_id');
        $Qcheck->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcheck->bindInt(':customers_groups_id', $Qgroups->value('customers_groups_id'));
        $Qcheck->execute();
    
        if ($Qcheck->valueInt('total') > 0) {
          $error = true;
          $check_customers_flag[] = $Qgroups->value('customers_groups_name');
        }
      }

      if ($error === false) {
        foreach ($batch as $id) {
          if (!toC_Customers_Groups_Admin::delete($id)) {
            $error = true;
            break;
          }
        }

        if ($error === false) {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }
      } else {
        if (!empty($check_customers_flag)) {
          $feedback[] = $osC_Language->get('batch_delete_error_customer_group_in_use') . '<br />' . implode(', ', $check_customers_flag);
        }
        
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }
       
      echo $toC_Json->encode($response);               
    }       
  }
?>