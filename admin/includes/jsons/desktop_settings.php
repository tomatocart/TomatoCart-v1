<?php
/*
  $Id: change_shipping_method.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/desktop_settings.php');
  require('includes/classes/gadget.php');

  class toC_Json_Desktop_Settings {

    function listWallpapers() {
      global $toC_Json;
    
      $wallpapers = toC_Desktop_Settings::getWallpapers();
      echo $success = '{"wallpapers" : ' . $toC_Json->encode($wallpapers) . '}';
    }
    
    function listThemes() {
      global $toC_Json;
    
      $themes = toC_Desktop_Settings::getThemes();
      echo $success = '{"themes" : ' . $toC_Json->encode($themes) . '}';
    }
    
    function loadModules() {
      global $toC_Json;
      
      $toC_Desktop_Settings = new toC_Desktop_Settings();
      $desktop_Settings = $toC_Desktop_Settings->_settings;
       
      $modules = toC_Desktop_Settings::listModules($desktop_Settings);
      
      echo $toC_Json->encode($modules);
    }
    
    function saveSettings() {
      global $toC_Json, $osC_Language;
      
      $data = array();
      
      $data['autorun'] = $_REQUEST['autorun'];
      $data['quickstart'] = $_REQUEST['quickstart'];
      $data['contextmenu'] = $_REQUEST['contextmenu'];
      $data['shortcut'] = $_REQUEST['shortcut'];
      
      $data['theme'] = $_REQUEST['theme'];
      $data['wallpaper'] = $_REQUEST['wallpaper'];
      $data['transparency'] = $_REQUEST['transparency'];
      $data['backgroundcolor'] = $_REQUEST['backgroundcolor'];
      $data['fontcolor'] = $_REQUEST['fontcolor'];
      $data['wallpaperposition'] = $_REQUEST['wallpaperposition'];
      
      $data['sidebaropen'] = $_REQUEST['sidebaropen'];
      $data['sidebartransparency'] = $_REQUEST['sidebartransparency'];
      $data['sidebarbackgroundcolor'] = $_REQUEST['sidebarbackgroundcolor'];
      $data['sidebargadgets'] = $_REQUEST['sidebargadgets'];

      $toC_Desktop_Settings = new toC_Desktop_Settings();
      
      if ($toC_Desktop_Settings->saveDesktop($data)) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function saveGadgets() {
    	$data = $_REQUEST['gadgets'];
    	
    	$toC_Desktop_Settings = new toC_Desktop_Settings();
    	$toC_Desktop_Settings->saveGadgets($data);
    	
    	echo '{success: true}';
    }
    
    function getGadgets() {
      global $toC_Json, $osC_Language;
      
      $record = array();
      $osC_DirectoryListing = new osC_DirectoryListing('includes/modules/gadgets');
      if ($osC_DirectoryListing->getSize()) {
        foreach($osC_DirectoryListing->getFiles() as $file) {
          $gadget = substr($file['name'], 0, strrpos($file['name'], '.'));
          $class_path = 'includes/modules/gadgets/' . $gadget . '.php';
          
          if ( !empty($gadget) && file_exists($class_path) ) {
            include($class_path);
            $osC_Language->loadIniFile('modules/gadgets/' . $gadget . '.php');
            
            if ( class_exists('toC_Gadget_' . $gadget) ) {
              $module_class = 'toC_Gadget_' . $gadget;
              $module = new $module_class();
              
              $records[] = array('code' => $module->getCode(), 
                                 'type' => $module->getType(),
                                 'icon' => $module->getIcon(), 
                                 'title' => $module->getTitle(),
                                 'file' => $module->getFile(),
                                 'description' => $module->getDescription());
            }
          }
        }
        
        $response = array('success' => true, 'records' => $records);
      }else {      
        $response = array('success' => false);
      }  
         
      echo $toC_Json->encode($response);
    }
    
  function getGadget() {
      global $toC_Json, $osC_Language;
      
      $gadget = $_REQUEST['gadget'];
      $class_path = 'includes/modules/gadgets/' . $gadget . '.php';
      
      $data = false;
      if ( !empty($gadget) && file_exists($class_path) ) {
        include($class_path);
        $osC_Language->loadIniFile('modules/gadgets/' . $gadget . '.php');
        
        if ( class_exists('toC_Gadget_' . $gadget) ) {
          $module_class = 'toC_Gadget_' . $gadget;
          $module = new $module_class();
          
          $data = array('title' => $module->getTitle(),
                        'code' => $module->getCode(), 
                        'type' => $module->getType(),
                        'icon' => $module->getIcon(), 
                        'autorun' => $module->getAutorun(),
                        'interval' => $module->getInterval(),
                        'file' => $module->getFile(),
                        'description' => $module->getDescription());
        }
      }
      
      if ($data !== false) {
        $response = array('success' => true, 'data' => $data);
      } else {
        $response = array('success' => false);
      }
      
      echo $toC_Json->encode($response);
    }
    
  function getGadgetView() {
      global $toC_Json, $osC_Language;
      
      $gadget = isset($_REQUEST['gadget']) ? $_REQUEST['gadget'] : '';
      $class_path = 'includes/modules/gadgets/' . $gadget . '.php';

      $response = array();
      if ( !empty($gadget) && file_exists($class_path) ) {
        include($class_path);
        $osC_Language->loadIniFile('modules/gadgets/' . $gadget . '.php');
        
        if ( class_exists('toC_Gadget_' . $gadget ) ) {
          $module_class = 'toC_Gadget_' . $gadget;
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
      
      $gadget = isset($_REQUEST['gadget']) ? $_REQUEST['gadget'] : '';
      $class_path = 'includes/modules/gadgets/' . $gadget . '.php';
      
      if ( !empty($gadget) && file_exists($class_path) ) {
        include($class_path);
        $osC_Language->loadIniFile('modules/gadgets/' . $gadget . '.php');
        
        if ( class_exists('toC_Gadget_' . $gadget) ) {
          $module_class = 'toC_Gadget_' . $gadget;
          $module = new $module_class();
          
          echo $module->renderData();
        }
      }
    }
  }
?>
