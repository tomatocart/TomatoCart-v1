<?php
/*
  $Id: departments.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  include('includes/classes/departments.php');

  class toC_Json_Departments {
  
    function listDepartments() {
      global $osC_Database, $toC_Json, $osC_Language;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qdepartments = $osC_Database->query('select d.departments_id, departments_title, departments_email_address from :table_departments d inner join :table_departments_description pd on d.departments_id = pd.departments_id and pd.languages_id = :language_id');
      $Qdepartments->bindTable(':table_departments', TABLE_DEPARTMENTS);
      $Qdepartments->bindTable(':table_departments_description', TABLE_DEPARTMENTS_DESCRIPTION);
      $Qdepartments->bindInt(':language_id', $osC_Language->getID());
      $Qdepartments->setExtBatchLimit($start, $limit);
      $Qdepartments->execute();
      
      $records = array();
      while($Qdepartments->next()) {
        $records[] = array('id'    => $Qdepartments->valueInt('departments_id'),
                           'title' => $Qdepartments->value('departments_title'),
                           'email_address' => $Qdepartments->value('departments_email_address'));
      
      }
              
      $response = array(EXT_JSON_READER_TOTAL => $Qdepartments->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records); 
                        
      echo $toC_Json->encode($response);
    }
    
    function saveDepartment() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $data = array('title' => $_REQUEST['departments_title'],
                    'email_address' => $_REQUEST['departments_email_address'],
                    'description'   => $_REQUEST['departments_description']);
      
      if (toC_Departments::save(isset($_REQUEST['id']) ? $_REQUEST['id']: null, $data)) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function loadDepartment() {
      global $toC_Json;
      
      $data = toC_Departments::getData($_REQUEST['id']);
      
      $response = array('success' => true, 'data' => $data);
     
      echo $toC_Json->encode($response);   
    }
    
    function deleteDepartment() {
      global $toC_Json, $osC_Language;
      
      if (toC_Departments::delete($_REQUEST['id'])) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
     
      echo $toC_Json->encode($response);                            
    }
    
    function deleteDepartments() {
      global $toC_Json, $osC_Language;
      
      $error = false;
      
      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if ( !toC_Departments::delete($id) ) {
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
  }
?>