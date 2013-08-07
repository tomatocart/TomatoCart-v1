<?php
/*
  $Id: dashboard.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  include ('includes/classes/portlet.php');
  include ('includes/classes/desktop_settings.php');
  
  class toC_Json_Dashboard {
    
    function getPortletNodes() {
      global $toC_Json;
      
      $portlets = toC_Portlet::getPortlets();
      
      $nodes = array();
      foreach($portlets as $portlet) {
        $nodes[] = array('id' => $portlet['code'],
                         'text' => $portlet['title'],
                         'leaf' => true);
      }

      echo $toC_Json->encode($nodes);
    }
    
    function getPortletView() {
      global $toC_Json, $osC_Language;
      
      $portlet = isset($_REQUEST['portlet']) ? $_REQUEST['portlet'] : '';
      $class_path = 'includes/modules/portlets/' . $portlet . '.php';

      $response = array();
      if ( !empty($portlet) && file_exists($class_path) ) {
        include($class_path);
        $osC_Language->loadIniFile('modules/portlets/' . $portlet . '.php');
        
        if ( class_exists('toC_Portlet_' . $portlet ) ) {
          $module_class = 'toC_Portlet_' . $portlet;
          $module = new $module_class();

          echo $module->renderView();
        }
      }else {
        $response = array('success' => false);
        
        echo $toC_Json->encode($response);
      }
      
    }
    
    function renderData() {
      global $toC_Json, $osC_Language;
      
      $portlet = isset($_REQUEST['portlet']) ? $_REQUEST['portlet'] : '';
      $class_path = 'includes/modules/portlets/' . $portlet . '.php';
      
      $response = array();
      if ( !empty($portlet) && file_exists($class_path) ) {
        include($class_path);
        $osC_Language->loadIniFile('modules/portlets/' . $portlet . '.php');
        
        if ( class_exists('toC_Portlet_' . $portlet) ) {
          $module_class = 'toC_Portlet_' . $portlet;
          $module = new $module_class();
          
          echo $module->renderData();
        }
      }
    }
    
    function saveDashboardLayout() {
      global $toC_Json, $osC_Language;
      
      $portlets = isset($_REQUEST['portlets']) ? $_REQUEST['portlets'] : null;
      $desktop_settings = new toC_Desktop_Settings();
      
      if ( $desktop_settings->saveDashBoards($portlets) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      }else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      } 
      
      echo $toC_Json->encode($response);
    }
    
    function loadPortlets() {
      global $toC_Json, $osC_Language;
      
      $desktop_settings = new toC_Desktop_Settings();
      
      $portlets = $desktop_settings->getDashBoards();
      
      if ( !empty($portlets) ) {
        $response = array('success' => true, 'portlets' => $portlets, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      }
      else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
  }
?>
