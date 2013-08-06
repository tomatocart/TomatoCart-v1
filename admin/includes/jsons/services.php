<?php
/*
  $Id: services.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Json_Services {
  
    function listServices() {
      global $toC_Json, $osC_Language;
      
      $osC_DirectoryListing = new osC_DirectoryListing('includes/modules/services');
      $osC_DirectoryListing->setIncludeDirectories(false);
      $files = $osC_DirectoryListing->getFiles();
      
      $installed = explode(';', MODULE_SERVICES_INSTALLED);
      
  	  $modules = array();
  	  foreach ( $files as $file ) {
  	    include('includes/modules/services/' . $file['name']);
  	
  	    $class = substr($file['name'], 0, strrpos($file['name'], '.'));

  	    $module = 'osC_Services_' . $class . '_Admin';
        $module = new $module();
        
	      $action = array();
        if ( in_array($class, $installed) && !osc_empty($module->keys()) ) {
          $action[] = array('class' => 'icon-edit-record', 'qtip' => $osC_Language->get('icon_edit'));
        } else {
          $action[] = array('class' => 'icon-edit-gray-record', 'qtip' => $osC_Language->get('icon_edit'));
        }
        
  	    if ( !in_array($class, $installed) ) {
          $action[] = array('class' => 'icon-install-record', 'qtip' => $osC_Language->get('icon_install'));
        } else if ($module->uninstallable == false) {
          $action[] = array('class' => 'icon-uninstall-gray-record', 'qtip' => $osC_Language->get('icon_uninstall'));
        } else {
          $action[] = array('class' => 'icon-uninstall-record', 'qtip' => $osC_Language->get('icon_uninstall'));
        }
        
        $modules[] = array(
  	      'code' => $class,
  	      'title' => $module->title,
  	      'action' => $action
  	    );
      }
    
      $response[EXT_JSON_READER_ROOT] = $modules;   
      
      echo $toC_Json->encode($response);
    }
    
    function install() {
      global $toC_Json, $osC_Database, $osC_Language;

      $module_key = $_REQUEST['module_code'];
      include('includes/modules/services/' . $module_key . '.php');
      $class = 'osC_Services_' . $module_key . '_Admin';

      $module = new $class();
      $module->install();

      $installed = explode(';', MODULE_SERVICES_INSTALLED);
      if ( isset($module->depends) ) {
        if ( is_string($module->depends) && ( ( $key = array_search($module->depends, $installed) ) !== false ) ) {
          if ( isset($installed[$key+1]) ) {
            array_splice($installed, $key+1, 0, $module_key);
          } else {
            $installed[] = $module_key;
          }
        } elseif ( is_array($module->depends) ) {
          foreach ( $module->depends as $depends_module ) {
            if ( ( $key = array_search($depends_module, $installed) ) !== false ) {
              if ( !isset($array_position) || ( $key > $array_position ) ) {
                $array_position = $key;
              }
            }
          }

          if ( isset($array_position) ) {
            array_splice($installed, $array_position+1, 0, $module_key);
          } else {
            $installed[] = $module_key;
          }
        }
      } elseif ( isset($module->precedes) ) {
        if ( is_string($module->precedes) ) {
          if ( ( $key = array_search($module->precedes, $installed) ) !== false ) {
            array_splice($installed, $key, 0, $module_key);
          } else {
            $installed[] = $module_key;
          }
        } elseif ( is_array($module->precedes) ) {
          foreach ( $module->precedes as $precedes_module ) {
            if ( ( $key = array_search($precedes_module, $installed) ) !== false ) {
              if ( !isset($array_position) || ( $key < $array_position ) ) {
                $array_position = $key;
              }
            }
          }

          if ( isset($array_position) ) {
            array_splice($installed, $array_position, 0, $module_key);
          } else {
            $installed[] = $module_key;
          }
        }
      } else {
        $installed[] = $module_key;
      }

      $Qsu = $osC_Database->query('update :table_configuration set configuration_value = :configuration_value where configuration_key = :configuration_key');
      $Qsu->bindTable(':table_configuration', TABLE_CONFIGURATION);
      $Qsu->bindValue(':configuration_value', implode(';', $installed));
      $Qsu->bindValue(':configuration_key', 'MODULE_SERVICES_INSTALLED');
      $Qsu->execute();

      if ( !$osC_Database->isError() ) {
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
      global $toC_Json, $osC_Database, $osC_Language;

      $installed = explode(';', MODULE_SERVICES_INSTALLED);

      $module_key = $_REQUEST['module_code'];
      include('includes/modules/services/' . $module_key . '.php');

      $class = 'osC_Services_' . $module_key . '_Admin';

      $module = new $class();
      $module->remove();

      unset($installed[array_search($module_key, $installed)]);

      $Qsu = $osC_Database->query('update :table_configuration set configuration_value = :configuration_value where configuration_key = :configuration_key');
      $Qsu->bindTable(':table_configuration', TABLE_CONFIGURATION);
      $Qsu->bindValue(':configuration_value', implode(';', $installed));
      $Qsu->bindValue(':configuration_key', 'MODULE_SERVICES_INSTALLED');
      $Qsu->execute();

      if ( !$osC_Database->isError() ) {
        osC_Cache::clear('configuration');

        $response['success'] = true;
        $response['feedback'] = $osC_Language->get('ms_success_action_performed');
      } else {
        $response['success'] = false;  
        $response['feedback'] = $osC_Language->get('ms_error_action_not_performed');
      }
      
      echo $toC_Json->encode($response);
    }
  
    function getConfigurationOptions(){
      global $osC_Language, $osC_Database, $toC_Json;
      
      include('includes/modules/services/' . $_REQUEST['code'] . '.php');
    
      $module = 'osC_Services_' . $_REQUEST['code'] . '_Admin';
      $module = new $module();
      
      $keys = array();
      foreach ($module->keys() as $key) {
        $Qkey = $osC_Database->query('select configuration_id, configuration_key, configuration_title, configuration_value, configuration_description, use_function, set_function from :table_configuration where configuration_key = :configuration_key');
        $Qkey->bindTable(':table_configuration', TABLE_CONFIGURATION);
        $Qkey->bindValue(':configuration_key', $key);
        $Qkey->execute();

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
