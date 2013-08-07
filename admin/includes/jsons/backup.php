<?php
/*
  $Id: backup.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/classes/backup.php');

  class toC_Json_Backup {

    function listBackup() {
      global $toC_Json;
      
      $osC_DirectoryListing = new osC_DirectoryListing(DIR_FS_BACKUP);
      $osC_DirectoryListing->setIncludeDirectories(false);
      $osC_DirectoryListing->setExcludeEntries('.htaccess');
      
      $response = array();
      foreach ( $osC_DirectoryListing->getFiles() as $file ) {
         $response[] = array('file' => $file['name'], 
                             'date' => osC_DateTime::getDate(osC_DateTime::fromUnixTimestamp(filemtime(DIR_FS_BACKUP . $file['name'])), true), 
                             'size' => number_format(filesize(DIR_FS_BACKUP . $file['name'])));
      }  
          
      $response = array(EXT_JSON_READER_ROOT => $response); 
      
      echo $toC_Json->encode($response);
    }
    
    function deleteBackup() {
      global $toC_Json, $osC_Language;
      
      if( isset($_REQUEST['file']) ) {
        if ( osC_Backup_Admin::delete($_REQUEST['file']) ) {
           $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
           $response = array('success' => false ,'feedback' => $osC_Language->get('ms_error_backup_directory_not_writable'));
        }
      }
  
      echo $toC_Json->encode($response);                 
    }
    
    function deleteBackups() {
      global $toC_Json, $osC_Language;
      
      $error = false;
      $batch = explode(',', $_REQUEST['batch']);
    
      foreach ($batch as $file) {
        if (!osC_Backup_Admin::delete($file)) {
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
    
    function backBackup(){
      global $toC_Json, $osC_Language;
     
      if ( osC_Backup_Admin::backup($_REQUEST['compression'], (isset($_REQUEST['download_only']) && ($_REQUEST['download_only'] == 'yes') ? true : false)) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false ,'feedback' => $osC_Language->get('ms_error_backup_directory_not_writable'));
      }
      
      echo $toC_Json->encode($response);               
    }
    
    function downloadBackup() {
      $filename = basename($_REQUEST['file']);

      $extension = substr($filename, -3);

      if ( ( $extension == 'zip' ) || ( $extension == '.gz' ) || ( $extension == 'sql' ) ) {
        if ( file_exists(DIR_FS_BACKUP . $filename) ) {
          header('Content-Description: File Transfer');
          header('Content-Type: application/octet-stream');
          header('Content-Transfer-Encoding: binary');
          header('Content-Disposition: attachment; filename=' . $filename);
          header('Content-Length: ' . filesize(DIR_FS_BACKUP . $filename));
          header('Pragma: public');
          header('Expires: 0');
          header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
          
          ob_clean();
          flush();
          readfile(DIR_FS_BACKUP . $filename);            
          exit;
        }
      }
    }
    
    function restoreBackup() {
      global $toC_Json, $osC_Language;
      
      if ( osC_Backup_Admin::restore($_REQUEST['file']) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_database_restore'));
      } else {
        $response = array('success' => false ,'feedback' => $osC_Language->get('ms_error_backup_directory_not_writable'));
      }
      
      echo $toC_Json->encode($response);  
    }
    
    function restoreLocal() {
      global $toC_Json, $osC_Language;
      
      
      if ( osC_Backup_Admin::restore() ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_database_restore'));
      } else {
        $response = array('success' => false ,'feedback' => $osC_Language->get('ms_error_backup_directory_not_writable'));
      }
      
      header('Content-Type: text/html');
      
      echo $toC_Json->encode($response);    
    }
  }
?>
