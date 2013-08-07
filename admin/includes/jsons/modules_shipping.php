<?php
/*
  $Id: modules_shipping.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  include('includes/classes/shipping.php');

  class toC_Json_Modules_Shipping {
  
    function listShippingModules() {
      global $toC_Json, $osC_Language;
      
      $osC_DirectoryListing = new osC_DirectoryListing('includes/modules/shipping');
      $osC_DirectoryListing->setIncludeDirectories(false);
      $files = $osC_DirectoryListing->getFiles();
      
  	  $modules = array();
  	
  	  foreach ( $files as $file ) {
  	    include('includes/modules/shipping/' . $file['name']);
  	
  	    $class = substr($file['name'], 0, strrpos($file['name'], '.'));
  	
  	    if ( class_exists('osC_Shipping_' . $class) ) {
  	      $osC_Language->injectDefinitions('modules/shipping/' . $class . '.xml');
  	
  	      $module = 'osC_Shipping_' . $class;
  	      $module = new $module();

  	      $action = array();
          if ($module->isInstalled()) {
            $action[] = array('class' => 'icon-edit-record', 'qtip' => $osC_Language->get('icon_edit'));
            $action[] = array('class' => 'icon-uninstall-record', 'qtip' => $osC_Language->get('icon_uninstall'));
          } else {
            $action[] = array('class' => 'icon-edit-gray-record', 'qtip' => $osC_Language->get('icon_edit'));
            $action[] = array('class' => 'icon-install-record', 'qtip' => $osC_Language->get('icon_install'));
          }
          
  	      $modules[] = array(
  	        'code' => $module->getCode(),
  	        'title' => $module->getTitle(),
  	        'sort_order' => $module->getSortOrder(),
            'action' => $action
  	      );
  	    }
      }
    
      $response[EXT_JSON_READER_ROOT] = $modules;   
      
      echo $toC_Json->encode($response);
    }
    
    function install() {
      global $toC_Json, $osC_Language;

      $code = $_REQUEST['module_code'];
      
      if ( file_exists('includes/modules/shipping/' . $code . '.php') ) {
        $osC_Language->injectDefinitions('modules/shipping/' . $code . '.xml');

        include('includes/modules/shipping/' . $code . '.php');

        $module = 'osC_Shipping_' . $code;
        $module = new $module();

        $module->install();

        osC_Cache::clear('modules-shipping');
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

      $code = $_REQUEST['module_code'];

      if ( file_exists('includes/modules/shipping/' . $code . '.php') ) {
        $osC_Language->injectDefinitions('modules/shipping/' . $code . '.xml');

        include('includes/modules/shipping/' . $code . '.php');

        $module = 'osC_Shipping_' . $code;
        $module = new $module();

        $module->remove();

        osC_Cache::clear('modules-shipping');
        osC_Cache::clear('configuration');

        $response['success'] = true;
        $response['feedback'] = $osC_Language->get('ms_success_action_performed');
      }else{
        $response['success'] = false;  
        $response['feedback'] = $osC_Language->get('ms_error_action_not_performed');
      }
      
      echo $toC_Json->encode($response);
    }
  
    function getConfigurationOptions(){
      global $osC_Language, $osC_Database, $toC_Json;
      
      include('includes/modules/shipping/' . $_REQUEST['code'] . '.php');
    
      $osC_Language->injectDefinitions('modules/shipping/' . $_REQUEST['code'] . '.xml');
    
      $module = 'osC_Shipping_' . $_REQUEST['code'];
      $module = new $module();
    
      $keys = array();
      foreach ($module->getKeys() as $key) {
        $Qkey = $osC_Database->query('select configuration_id, configuration_key, configuration_title, configuration_value, configuration_description, use_function, set_function from :table_configuration where configuration_key = :configuration_key');
        $Qkey->bindTable(':table_configuration', TABLE_CONFIGURATION);
        $Qkey->bindValue(':configuration_key', $key);
        $Qkey->execute();

        $cfgValue = $Qkey->value('configuration_value');
        if ( !osc_empty($Qkey->value('use_function')) ) {
          $cfgValue = osc_call_user_func($Qkey->value('use_function'), $Qkey->value('configuration_value'));
        }        
        
        $control = array();
        if ( !osc_empty($Qkey->value('set_function')) ) {
          $control = osc_call_user_func($Qkey->value('set_function'), $Qkey->value('configuration_value'), $key);
          $field['title'] = $Qkey->value('configuration_title');
        } else {
          $control['type'] = 'textfield';
          $control['name'] = 'configuration[' . $key . ']';
        }
        $control['id'] = $Qkey->value('configuration_id');
        $control['title'] = $Qkey->value('configuration_title');
        $control['value'] = $Qkey->value('configuration_value');
        $control['description'] = $Qkey->value('configuration_description');
        
        $keys[] = $control;
      }
      
      echo $toC_Json->encode($keys);
    }
      
    function save() {
      global $toC_Json, $osC_Database, $osC_Language;

      $error = false;

      $osC_Database->startTransaction();

      $data = array('configuration' => $_POST['configuration']);
      foreach ( $data['configuration'] as $key => $value ) {
        $Qupdate = $osC_Database->query('update :table_configuration set configuration_value = :configuration_value where configuration_key = :configuration_key');
        $Qupdate->bindTable(':table_configuration', TABLE_CONFIGURATION);
        $Qupdate->bindValue(':configuration_value', is_array($data['configuration'][$key]) ? implode(',', $data['configuration'][$key]) : $value);
        $Qupdate->bindValue(':configuration_key', $key);
        $Qupdate->setLogging($_SESSION['module']);
        $Qupdate->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
          break;
        }
      }

      if ( $error === false ) {
        $osC_Database->commitTransaction();
        
        osC_Cache::clear('configuration');
        
        $response['success'] = true;
        $response['feedback'] = $osC_Language->get('ms_success_action_performed');
      } else {
        $osC_Database->rollbackTransaction();

        $response['success'] = false;  
        $response['feedback'] = $osC_Language->get('ms_error_action_not_performed');
      }

      echo $toC_Json->encode($response);
    }    
  }
?>
