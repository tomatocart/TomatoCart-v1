 
<?php
/*
  $Id: administrators_log.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/ 

  class toC_Json_Administrators_Log {
  
    function listAdministratorsLog() {
      global $toC_Json, $osC_Database;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];  
      
      $Qlog = $osC_Database->query('select SQL_CALC_FOUND_ROWS count(al.id) as total, al.id, al.module, al.module_action, al.module_id, al.action, a.user_name, unix_timestamp(al.datestamp) as datestamp from :table_administrators_log al, :table_administrators a where');
      
      if ( !empty($_REQUEST['fm']) && in_array($_REQUEST['fm'], $_SESSION['admin']['access']) ) {
        $Qlog->appendQuery('al.module = :module');
        $Qlog->bindValue(':module', $_REQUEST['fm']);
      } else {
        $Qlog->appendQuery('al.module in (":modules")');
        $Qlog->bindRaw(':modules', implode('", "', $_SESSION['admin']['access']));
      }
      
      $Qlog->appendQuery('and');
    
      if ( is_numeric($_REQUEST['fu']) && !empty($_REQUEST['fu']) ) {
        $Qlog->appendQuery('al.administrators_id = :administrators_id and');
        $Qlog->bindInt(':administrators_id', $_REQUEST['fu']);
      }
      
      $Qlog->appendQuery('al.administrators_id = a.id group by al.id order by al.id desc');
      $Qlog->bindTable(':table_administrators_log', TABLE_ADMINISTRATORS_LOG);
      $Qlog->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
      $Qlog->setExtBatchLimit($start, $limit);
      $Qlog->execute();
       
      $records = array();     
      while ($Qlog->next()){          
        $records[] = array('administrators_log_id' => $Qlog->valueInt('id'),
                           'administrators_id' => $Qlog->valueInt('administrators_id'),
                           'module' => $Qlog->value('module'). ' (' . $Qlog->valueInt('total') . ')',
                           'module_id'=> $Qlog->valueInt('module_id'),
                           'module_action' => $Qlog->valueProtected('module_action'), 
                           'user_name' => $Qlog->valueProtected('user_name'),
                           'date' => osC_DateTime::getShort(osC_DateTime::fromUnixTimestamp($Qlog->value('datestamp')), true),
                           'logo_info_title' => osc_icon('info.png') . ' ' . $Qlog->valueProtected('user_name') . ' &raquo; ' . $Qlog->valueProtected('module_action') . ' &raquo; ' . $Qlog->value('module') . ' &raquo; ' . $Qlog->valueInt('module_id'));
      }
      $Qlog->freeResult();

      $response = array(EXT_JSON_READER_TOTAL => $Qlog->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);

      echo $toC_Json->encode($response);
    }
     
    function getModules() {
      global $toC_Json, $osC_Language;
        
      $records = array(array('id' => '-1', 'text' => $osC_Language->get('filter_all')));
      foreach ( $_SESSION['admin']['access'] as $module ) {
        $records[] = array('id' => $module, 'text' => $module);
      }

      $response = array(EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json -> encode($response);
    }
 
    function getUsers(){
      global $toC_Json, $osC_Language, $osC_Database;
        
      $Qadmins = $osC_Database->query('select id, user_name from :table_administrators order by user_name');
      $Qadmins->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
      $Qadmins->execute();
      
      $records = array(array('id' => '', 'text' => $osC_Language->get('filter_all')));
      while ( $Qadmins->next() ) {
        $records[] = array('id' => $Qadmins->valueInt('id'), 'text' => $Qadmins->value('user_name'));
      }
      $Qadmins->freeResult();
       
      $response = array(EXT_JSON_READER_ROOT => $records);

      echo $toC_Json->encode($response);
    }

    function getAdministratorsLogInfo(){
      global $toC_Json, $osC_Database;
        
      $Qlog = $osC_Database->query('select field_key, old_value, new_value from :table_administrators_log where id = :id');
      $Qlog->bindTable(':table_administrators_log', TABLE_ADMINISTRATORS_LOG);
      $Qlog->bindValue(':id', $_REQUEST['administrators_log_id']);
      $Qlog->execute();
      
      $records = array();
      while($Qlog->next()){
        $records[] = array('fields' => $Qlog->valueProtected('field_key'),
                           'old_value' => $Qlog->valueProtected('old_value'),
                           'new_value' => $Qlog->valueProtected('new_value'));
      }
      $Qlog->freeResult();
      
      $response = array(EXT_JSON_READER_TOTAL => sizeof($records),
                        EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
  }  
      
    function deleteAdministratorsLog() {
      global $toC_Json, $osC_Language;
      
      if (osC_AdministratorsLog::delete($_REQUEST['administrators_log_id'])) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }

    function deleteAdministratorsLogs() {
      global $toC_Json, $osC_Language;
      
      $error = false;
      
      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if ( !osC_AdministratorsLog::delete($id) ) {
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