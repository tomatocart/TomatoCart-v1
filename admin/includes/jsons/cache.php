<?php
/*
  $Id: cache.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Json_Cache {

    function listCache() {
      global $toC_Json;
      
      $osC_DirectoryListing = new osC_DirectoryListing(DIR_FS_WORK);
      $osC_DirectoryListing->setIncludeDirectories(false);
      $osC_DirectoryListing->setCheckExtension('cache');
    
      $response = array();
      
      foreach ( $osC_DirectoryListing->getFiles() as $file ) {
        $last_modified = filemtime(DIR_FS_WORK . '/' . $file['name']);
    
        if ( strpos($file['name'], '-') !== false ) {
          $code = substr($file['name'], 0, strpos($file['name'], '-'));
        } else {
          $code = substr($file['name'], 0, strpos($file['name'], '.'));
        }
        
        if ( isset($cached_files[$code]) ) {
          $cached_files[$code]['total']++;
    
          if ( $last_modified > $cached_files[$code]['last_modified'] ) {
            $cached_files[$code]['last_modified'] = $last_modified;
          }
        } else {
          $cached_files[$code] = array('total' => 1,
                                       'last_modified' => $last_modified);
        }
         $response[] = array('code' => $code, 'total' => $cached_files[$code]['total'], 'last_modified' => osC_DateTime::getShort(osC_DateTime::fromUnixTimestamp($cached_files[$code]['last_modified']), true));
      }  
          
      $response = array(EXT_JSON_READER_ROOT => $response); 
      
      echo $toC_Json->encode($response);
    }
    
    function deleteCache() {
      global $toC_Json, $osC_Language;
      
      osC_Cache::clear($_REQUEST['block']);

      $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));

      echo $toC_Json->encode($response);               
    }
      
    function deleteCaches() {
      global $toC_Json, $osC_Language;
        
      $ids = explode(',', $_REQUEST['batch']);
    
      foreach ($ids as $id) {
        osC_Cache::clear($id);
      }
     
      $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
        
      echo $toC_Json->encode($response);               
    }  
    
  }
?>
