<?php
/*
  $Id: administrators.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/administrators.php');

  class toC_Json_Administrators {
        
    function listAdministrators() {
      global $toC_Json, $osC_Database;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];       
      
      $Qadmin = $osC_Database->query('select id, user_name, email_address from :table_administrators order by user_name');
      $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
      $Qadmin->setExtBatchLimit($start, $limit);
      $Qadmin->execute();
            
      $records = array();     
      while ($Qadmin->next()) {          
        $records[] = array(
          'id' => $Qadmin->valueInt('id'),
          'user_name' => $Qadmin->value('user_name'),
          'email_address' => $Qadmin->value('email_address')
        );           
      }
      $Qadmin->freeResult();
      
      $response = array(EXT_JSON_READER_TOTAL => $Qadmin->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }
    
    function getAccesses() {
      global $toC_Json, $osC_Language;
      
      $osC_DirectoryListing = new osC_DirectoryListing('includes/modules/access');
      $osC_DirectoryListing->setIncludeDirectories(false);
    
      $access_modules_array = array();
    
      foreach ($osC_DirectoryListing->getFiles() as $file) {
        $module = substr($file['name'], 0, strrpos($file['name'], '.'));
    
        if (!class_exists('osC_Access_' . ucfirst($module))) {
          $osC_Language->loadIniFile('modules/access/' . $file['name']);
          include($osC_DirectoryListing->getDirectory() . '/' . $file['name']);
        }
    
        $module = 'osC_Access_' . ucfirst($module);
        $module = new $module();
        $title = osC_Access::getGroupTitle($module->getGroup());
        
        $access_modules_array[$title][] = array('id' => $module->getModule(),
                                                'text' => $module->getTitle(),
                                                'leaf' => true);
      }
    
      ksort($access_modules_array);
      
      $access_options = array(); 
      $count = 1;
      foreach ( $access_modules_array as $group => $modules ) {
        $access_option['id'] = $count;
        $access_option['text'] = $group;
        
        $mod_arrs = array();
        foreach($modules as $module) {
          $mod_arrs[] = $module;
        }

        $access_option['children'] = $mod_arrs;
        
        $access_options[] = $access_option;
        $count++;
      }

      echo $toC_Json->encode($access_options);
    }
    
    function loadAdministrator() {
      global $toC_Json;
     
      $data = osC_Administrators_Admin::getData($_REQUEST['aID']);
      
      if (is_array($data['access_modules']) && !empty($data['access_modules'])) {
        if($data['access_modules'][0] == '*') 
          $data['access_globaladmin'] = '1';
      }
      
      $response = array('success' => true, 'data' => $data);
     
      echo $toC_Json->encode($response);   
    }
   
    function saveAdministrator() {
      global $toC_Json, $osC_Language;

      $data = array('username' => $_REQUEST['user_name'],
                    'password' => $_REQUEST['user_password'],
                    'email_address' => $_REQUEST['email_address']);
      
      $modules = null;
      if (isset($_REQUEST['modules']) && !empty($_REQUEST['modules'])) {
        $modules = explode(",", $_REQUEST['modules']);
      }
      
      if(isset($_REQUEST['access_globaladmin']) && ($_REQUEST['access_globaladmin'] == 'on')) {
        $modules = array('*');
      }      
      
      switch ( osC_Administrators_Admin::save((isset($_REQUEST['aID']) && is_numeric($_REQUEST['aID']) ? $_REQUEST['aID'] : null), $data, $modules) ) {
        case 1:
          if ( isset($_REQUEST['aID']) && is_numeric($_REQUEST['aID']) && ($_REQUEST['aID'] == $_SESSION['admin']['id']) ) {
            $_SESSION['admin']['access'] = osC_Access::getUserLevels($_REQUEST['aID']);
          }

          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
          break;

        case -1:
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
          break;

        case -2:
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_username_already_exists'));
          break;
          
        case -3:
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_email_format'));
          break;
          
        case -4:
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_email_already_exists'));                    
          break;       
      }
      
      echo $toC_Json->encode($response);
    }

    function deleteAdministrator() {
      global $toC_Json, $osC_Language;
      
      if (osC_Administrators_Admin::delete($_REQUEST['adminId'])) {
        $response['success'] = true;
        $response['feedback'] = $osC_Language->get('ms_success_action_performed');
      }
      else {
        $response['success'] = false;
        $response['feedback'] = $osC_Language->get('ms_error_action_not_performed'); 
      }
     
      echo $toC_Json->encode($response);
    }
    
    function deleteAdministrators() {
      global $toC_Json, $osC_Language;
     
      $error = false;
      
      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if ( !osC_Administrators_Admin::delete($id) ) {
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
