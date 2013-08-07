<?php
/*
  $Id: modules_geoip.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  include('includes/classes/geoip.php');

  class toC_Json_Modules_Geoip {

    function listGeoipModules() {
      global $toC_Json, $osC_Language;
      
      $osC_DirectoryListing = new osC_DirectoryListing('includes/modules/geoip');
      $osC_DirectoryListing->setIncludeDirectories(false);
      $files = $osC_DirectoryListing->getFiles();
      
  	  $modules = array();
  	
  	  foreach ( $files as $file ) {
        include('includes/modules/geoip/' . $file['name']);
    
        $class = substr($file['name'], 0, strrpos($file['name'], '.'));
    
        if (class_exists('osC_GeoIP_' . $class)) {
          $osC_Language->loadIniFile('modules/geoip/' . $class . '.php');
    
          $module = 'osC_GeoIP_' . $class;
          $module = new $module();

          $action = array();
          if ($module->isInstalled()) {
            $action[] = array('class' => 'icon-uninstall-record', 'qtip' => $osC_Language->get('icon_uninstall'));
          } else {
            $action[] = array('class' => 'icon-install-record', 'qtip' => $osC_Language->get('icon_install'));
          }
          
  	      $modules[] = array(
  	        'code' => $class,
  	        'title' => $module->getTitle(),
    	      'description' => $module->getDescription(),
    	      'author' => $module->getAuthorName(),
  	        'action' => $action
  	      );
  	    }
      }
    
      $response[EXT_JSON_READER_ROOT] = $modules;   
      
      echo $toC_Json->encode($response);
    }
    
    function install() {
      global $toC_Json, $osC_Language;

      $key = $_REQUEST['module_code'];
      
      if ( file_exists('includes/modules/geoip/' . $key . '.php') ) {
        $osC_Language->loadIniFile('modules/geoip/' . $key . '.php');

        include('includes/modules/geoip/' . $key . '.php');

        $module = 'osC_GeoIP_' . $key;
        $module = new $module();

        $module->install();

        osC_Cache::clear('modules-geoip');
        osC_Cache::clear('configuration');
      
        $response['success'] = true;
        $response['feedback'] = $osC_Language->get('ms_success_action_performed');
      } else {
        $response['success'] = false;  
        $response['feedback'] = $osC_Language->get('ms_error_action_not_performed');
      }
      
      echo $toC_Json->encode($response);
    }
    
    function uninstall() {
      global $toC_Json, $osC_Language;

      $key = $_REQUEST['module_code'];

      if ( file_exists('includes/modules/geoip/' . $key . '.php') ) {
        include('includes/modules/geoip/' . $key . '.php');

        $module = 'osC_GeoIP_' . $key;
        $module = new $module();

        $module->remove();

        osC_Cache::clear('modules-geoip');
        osC_Cache::clear('configuration');

        $response['success'] = true;
        $response['feedback'] = $osC_Language->get('ms_success_action_performed');
      }else{
        $response['success'] = false;  
        $response['feedback'] = $osC_Language->get('ms_error_action_not_performed');
      }
      
      echo $toC_Json->encode($response);
    }
  }
?>
