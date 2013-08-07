<?php
/*
  $Id: watermark.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  include("includes/classes/watermark.php");
  require('includes/classes/image.php');

  class toC_Json_Watermark {
    
    function loadImageGroups() {
      global $toC_Json;
      
      $osC_Image = new osC_Image_Admin();
      
      foreach($osC_Image->getGroups() as $group) {
        if ($group['id'] != 1) {
          $modules[] = array('id' => $group['id'],
                             'text' => $group['code']);
        }
      }
      
      $response = array(EXT_JSON_READER_ROOT => $modules);
      
      echo $toC_Json->encode($response);
    }
  
    function loadUploadedImage() {
      global $toC_Json;
      
      $image = false;
      if (defined('WATERMARK_FILE_NAME')) {
        $image = WATERMARK_FILE_NAME;
      }
      
      $response = array('success' => true, 'image' => $image);
      
      echo $toC_Json->encode($response);
    }
    
    function uploadWatermark() {
      global $toC_Json;
      
      $response = toC_Watermark_Admin::save($_FILES['watermark_image']);
      
      header('Content-Type: text/html');
      
      echo $toC_Json->encode($response);
    }
    
    function processWatermark() {
      global $toC_Json, $osC_Language;
      
      if (toC_Watermark_Admin::processWatermark($_REQUEST['type'], $_REQUEST['image_group'], $_REQUEST['watermark_position'], $_REQUEST['watermark_opacity'])) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));      
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      echo $toC_Json->encode($response);
    }
    
    function deleteWatermark() {
      global $toC_Json, $osC_Language;

      if ( toC_Watermark_Admin::delete($_REQUEST['type'], $_REQUEST['image_group'], $_REQUEST['watermark_position'], $_REQUEST['watermark_opacity']) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteWatermarkImage() {
      global $toC_Json, $osC_Language;
      
      if (isset($_REQUEST['image_name']) && toC_Watermark_Admin::deleteWatermarkImage($_REQUEST['image_name'])) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
           
      echo $toC_Json->encode($response);
    }
  }
?>
