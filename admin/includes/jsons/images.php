<?php
/*
  $Id: images.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
 require('includes/modules/image/check.php');
 require('includes/modules/image/resize.php');

  class toC_Json_Images {

    function listImages() {
      global $toC_Json;
      
      $osC_DirectoryListing = new osC_DirectoryListing('includes/modules/image');
      $osC_DirectoryListing->setIncludeDirectories(false);
      $osC_DirectoryListing->setCheckExtension('php');
     
      $record = array();
      foreach ( $osC_DirectoryListing->getFiles() as $file ) {
        $class = 'osC_Image_Admin_' . substr($file['name'], 0, strrpos($file['name'], '.'));
  
        if ( class_exists($class) ) {
          $module = new $class();
          $record[] = array('module' => $module->getTitle(), 
                            'run' => substr($file['name'], 0, strrpos($file['name'], '.')));
        }
      }
      
      $response = array(EXT_JSON_READER_ROOT => $record); 
      
      echo $toC_Json->encode($response);
    }
    
    function CheckImages() {
      global $toC_Json;
      
      $osC_Images = new osC_Image_Admin_check();
      $osC_Images->activate();
      
      $record = array();
      foreach ( $osC_Images->getData() as $data ) {
        $record[] = array('group' => $data[0], 'count' => $data[1]);
      }
      
      $response = array(EXT_JSON_READER_ROOT => $record); 
      
      echo $toC_Json->encode($response);
    }  
    
    function getImageGroups(){
      global $toC_Json;
       
      $osC_Images = new osC_Image_Admin_resize();

      $record = $osC_Images->getDrawParameters();
      
      $response = array(EXT_JSON_READER_ROOT => $record);
      
      echo $toC_Json->encode($response);
    }
    
    function listImagesResizeResult(){
      global $toC_Json;
      
      ini_set('max_execution_time', 1800);
       
      $osC_Images = new osC_Image_Admin_resize();

      $osC_Images->_setData();
      
      $record = array();
      foreach ( $osC_Images->getData() as $data ) {
        $record[] = array('group' => $data[0], 'count' => $data[1]);
      }
      
      $response = array('success' => true, EXT_JSON_READER_ROOT => $record); 
      
      echo $toC_Json->encode($response);               
    }
  }
?>
