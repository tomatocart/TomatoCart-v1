<?php
/*
  $Id: rpcs.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require("external/json/json.php");

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
      global $osC_Language;
      
      $module = '';
      $action = '';
      $response['success'] = false;

      if (isset($_SESSION['admin']) || ($_REQUEST['module'] == 'login')) {
        //valid token before all request
        if ($_REQUEST['module'] != 'login') {
          toc_verify_token();
        }
        
        if (isset($_REQUEST['module'])) {
          $module = preg_replace('/[^a-zA-Z_]/iu', '', $_REQUEST['module']);
          
          $_SESSION['module'] = $_REQUEST['module'];
          $osC_Language->loadIniFile($module . '.php');
        } 
        
        if (isset($_REQUEST['action'])) {
          $action = $_REQUEST['action'];
        } 
        
        if (!empty($module) && !empty($action)) {
  
          if (file_exists('includes/jsons/' . $module . '.php')) {
            include('includes/jsons/' . $module . '.php');
            
            //process action
            $words = explode('_', $action);
            $action = $words[0];
            if (sizeof($words) > 1){
              for($i = 1; $i < sizeof($words); $i++)  
                $action .= ucfirst($words[$i]);
            }
  
            call_user_func(array('toC_Json_' .ucfirst($module), $action));
            exit;
          }
        }
      } else {
        $response = array('success' => false, 'error' => 'session_timeout');
      }

      echo $this->encode($response);
    }
  }
?>
