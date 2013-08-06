<?php
/*
  $Id: logo_upload.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/logo_upload.php');

  class toC_Json_Logo_Upload {
   
    function saveLogo() {
      global $toC_Json, $osC_Language;
      
      if ( toC_Logo_Upload::upload() ) {
        $image = toC_Logo_Upload::getOriginalLogo();
        
        list($orig_width, $orig_height) = getimagesize($image);
        $width = intval(120 * $orig_width / $orig_height);
        
        $response = array('success' => true, 'image' => $image, 'height' => 120, 'width' => $width);
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));      
      }

      header('Content-Type: text/html');
      
      echo $toC_Json->encode($response);
    }
    
    function getLogo() {
      global $toC_Json;

      $image = toC_Logo_Upload::getOriginalLogo();
      
      if (!empty($image)) {
        list($orig_width, $orig_height) = getimagesize($image);
        $width = intval(120 * $orig_width / $orig_height);
    
        $image = '<img src="' . $image . '" width="' . $width . '" height="120" style="padding: 10px" />'; 
        
        $response = array('success' => true, 'image' => $image);
      } else {
        $response = array('success' => false);      
      }
      
      echo $toC_Json->encode($response);
    }
  }
?>