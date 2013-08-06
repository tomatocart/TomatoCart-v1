<?php
/*
  $Id: file_manager.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/classes/file_manager.php');
  
  define('OSC_ADMIN_FILE_MANAGER_ROOT_PATH', realpath('../') . '/');

  class toC_Json_File_Manager {

    function listNodes() {
      global $toC_Json;
    
      $directory = '/';
      if ( isset($_REQUEST['directory']) && !empty($_REQUEST['directory']) ) { 
        $directory = urldecode($_REQUEST['directory']);
      }
       
      $osC_DirectoryListing = new osC_DirectoryListing(OSC_ADMIN_FILE_MANAGER_ROOT_PATH . '/' . $directory);
      $osC_DirectoryListing->setIncludeFiles(false);
      $nodes = array();
    
      foreach (($osC_DirectoryListing->getFiles()) as $file) {
        $path = $directory . '/' . $file['name'];
        $path = str_replace('//', '/', $path);
        
        if ($file['is_directory'] === true) {
          $nodes[] = array(id => $path, text => $file['name'], path => $path);
        }
       }
       $response = $nodes;

      echo $toC_Json->encode($response);
    }

    function listDirectory() {
      global $osC_Language , $toC_Json , $osC_MessageStack;

      $directory = OSC_ADMIN_FILE_MANAGER_ROOT_PATH;

      if ( isset($_REQUEST['directory']) && !empty($_REQUEST['directory']) ) {
        $directory .= '/' . urldecode($_REQUEST['directory']);
      } elseif ( isset($_REQUEST['goto']) && !empty($_REQUEST['goto']) ) {
        $directory .= '/' . urldecode($_REQUEST['goto']);
      }

      $osC_DirectoryListing = new osC_DirectoryListing($directory);
      $osC_DirectoryListing->setStats(true);

      $records = array();
      foreach ( ($osC_DirectoryListing->getFiles()) as $file ) {
        $file_owner = function_exists('posix_getpwuid') ? posix_getpwuid($file['user_id']) : '-?-';
        $group_owner = function_exists('posix_getgrgid') ? posix_getgrgid($file['group_id']) : '-?-';

        if ( $file['is_directory'] === true ) {
          $entry_icon = osc_icon('folder_red.png');

          $action = array(array('class' => 'icon-empty-record','qtip' => ''),
                          array('class' => 'icon-empty-record','qtip' => ''),
                          array('class' => 'icon-delete-record','qtip' => $osC_Language->get('icon_trash')));
        } else {
          $entry_icon = osc_icon('file.png');

          $action = array(array('class' => 'icon-edit-record','qtip' => $osC_Language->get('icon_edit')),
                          array('class' => 'icon-download-record','qtip' => $osC_Language->get('icon_download')),
                          array('class' => 'icon-delete-record','qtip' => $osC_Language->get('icon_trash')));
        }

        $records[] = array (
          'icon' => $entry_icon,
          'file_name' => $file['name'],
          'is_directory' => $file['is_directory'],
          'size' => number_format($file['size']),
          'permission' => osc_get_file_permissions($file['permissions']),
          'file_owner' => $file_owner,
          'group_owner' => $group_owner,
          'writeable' => osc_icon(is_writable($osC_DirectoryListing->getDirectory() . '/' . $file['name']) ? 'checkbox_ticked.gif' : 'checkbox_crossed.gif'),
          'last_modified_date' => osC_DateTime::getShort(osC_DateTime::fromUnixTimestamp($file['last_modified']), true),
          'action' => $action);
      }

      $response = array(EXT_JSON_READER_ROOT => $records);

      echo $toC_Json->encode($response);
    }

    function loadFile() {
      global $toC_Json;

      $directory = OSC_ADMIN_FILE_MANAGER_ROOT_PATH . $_REQUEST['directory'];
      $file = $directory . '/' . $_REQUEST['file_name'];

      $data['directory'] = $directory;
      $data['content'] = file_get_contents($file);

      $response = array('success' => true, 'data' => $data);

      echo $toC_Json->encode($response);
    }

    function saveFile() {
      global $osC_Language , $toC_Json;

      $directory = OSC_ADMIN_FILE_MANAGER_ROOT_PATH . $_REQUEST['directory'];

      if ( osC_FileManager_Admin::saveFile($_REQUEST['file_name'], $_REQUEST['content'], $directory) ){
       $response = array('success' => true,'feedback'=>$osC_Language->get('ms_success_action_performed'));
      } else{
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }

      echo $toC_Json->encode($response);
    }

    function saveDirectory() {
      global $osC_Language , $toC_Json;

      $directory = OSC_ADMIN_FILE_MANAGER_ROOT_PATH . $_REQUEST['directory'];

      if ( osC_FileManager_Admin::createDirectory($_REQUEST['directory_name'], $directory) ){
        $response = array('success' => true,'feedback'=>$osC_Language->get('ms_success_action_performed'));
      } else{
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }

      echo $toC_Json->encode($response);
    }
  
    function renameDirectory() {
      global $osC_Language , $toC_Json;

      $directory = OSC_ADMIN_FILE_MANAGER_ROOT_PATH . $_REQUEST['directory'];
      $new_directory = OSC_ADMIN_FILE_MANAGER_ROOT_PATH . dirname($_REQUEST['directory']) . '/' . $_REQUEST['new_directory'];

      if ( @rename($directory, $new_directory) ){
        $response = array('success' => true,'feedback'=>$osC_Language->get('ms_success_action_performed'));
      } else{
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }

      echo $toC_Json->encode($response);
    }
    
    function uploadFile() {
      global $toC_Json , $osC_Language;

      $error = false;

      $directory = OSC_ADMIN_FILE_MANAGER_ROOT_PATH . $_REQUEST['directory'];

      for ( $i = 1;$i < 6; $i++ ) {
       $file = 'file_upload' . $i;

        if ( is_uploaded_file($_FILES[$file]['tmp_name']) ) {
          if ( !osC_FileManager_Admin::storeFileUpload($file, $directory) ) {
            $error = true;
            break;
          }
        }
      }

      header('Content-Type: text/html');

      if ($error == false) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }

      echo $toC_Json->encode($response);
    }

    function delete() {
      global $toC_Json, $osC_Language;

      $file_name = $_REQUEST['file_name'];
      $directory = OSC_ADMIN_FILE_MANAGER_ROOT_PATH . $_REQUEST['directory'];

      if ( osC_FileManager_Admin::delete($file_name, $directory) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }

      echo $toC_Json->encode($response);
    }

    function batchDelete() {
      global $toC_Json, $osC_Language;

      $error = false;
      $batch = explode(',', $_REQUEST['batch']);
      $directory = OSC_ADMIN_FILE_MANAGER_ROOT_PATH . $_REQUEST['directory'];

      foreach ($batch as $file) {
        if ( !osC_FileManager_Admin::delete($file, $directory) ) {
          $error = true;
          break;
        }
      }

      if ($error === false) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }

      echo $toC_Json->encode($response);
    }

    function download() {
      $file_name = str_replace('&nbsp;', '', strip_tags($_REQUEST['file_name']));
      $directory = OSC_ADMIN_FILE_MANAGER_ROOT_PATH . $_REQUEST['directory'];

      if ( file_exists($directory . '/' .$file_name) ) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename=' . $file_name);
        header('Content-Length: ' . filesize($directory . '/' . $file_name));
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        readfile($directory . '/' .$file_name);
        exit;
      }
    }
  
}
?>
