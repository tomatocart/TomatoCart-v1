<?php
/*
  $Id: json.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
 require("ext/json/json.php");

  class toC_Json {
  
    var $json = null;
    
    function toC_Json() {
      $this->json = new Services_JSON();
    }

    function encode($value) {
      return $this->json->encode($value);
    }
    
    function decode($value) {
      return $this->json->decode($value);
    }
      
    function parse() {
      $error = false;
      
      if (isset($_REQUEST['module'])) {
        $module = preg_replace('/[^a-zA-Z_]/iu', '', $_REQUEST['module']);
      } 
      
      if (isset($_REQUEST['action'])) {
        $action = $_REQUEST['action'];
        
        //process action
        $words = explode('_', $action);
        $action = $words[0];
        if (sizeof($words) > 1) {
          for($i = 1; $i < sizeof($words); $i++)  
            $action .= ucfirst($words[$i]);
        }
      }
      
      if (isset($_REQUEST['template']) && !empty($_REQUEST['template'])) {
        if (!empty($module) && !empty($action)) {
          if (file_exists('templates/' . $_REQUEST['template'] . '/modules/jsons/' . $module . '.php')) {
            require('templates/' . $_REQUEST['template'] . '/modules/jsons/' . $module . '.php');
            
            call_user_func(array('toC_Json_' . ucfirst($module), $action));
            exit;
          }else if (file_exists('includes/modules/jsons/' . $module . '.php')) {
            require('includes/modules/jsons/' . $module . '.php');
            
            call_user_func(array('toC_Json_' . ucfirst($module), $action));
            exit;
          }
        }
      } else {
        if (!empty($module) && !empty($action)) {
          if (file_exists('includes/modules/jsons/' . $module . '.php')) {
            require('includes/modules/jsons/' . $module . '.php');
            
            call_user_func(array('toC_Json_' . ucfirst($module), $action));
            exit;
          }
        }
      }      

      $response = array('success' => false, 'error' => 'noActionError');
      echo $this->encode($response);
    }
  }
?>