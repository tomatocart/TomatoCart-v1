<?php
/*
  $Id: templates.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Json_Templates {
  
    function listTemplates() {
      global $toC_Json, $osC_Language;
      
      $osC_DirectoryListing = new osC_DirectoryListing('../templates');
      $osC_DirectoryListing->setIncludeDirectories(true);
      $osC_DirectoryListing->setIncludeFiles(false);
      $osC_DirectoryListing->setExcludeEntries('system');
      $files = $osC_DirectoryListing->getFiles(true);
      
  	  foreach ( $files as $file ) {
  	    if ( file_exists('../templates/' . $file['name'] . '/template.php') ) {
          include('../templates/' . $file['name'] . '/template.php');
      
          $code = $file['name'];
          $class = 'osC_Template_' . $code;
      
          if ( class_exists($class) ) {
            $module = new $class();
            
            $module_title = $module->getTitle();
            
            $action = array();
            
            if ( $module->isInstalled() ) {
              if ( $module->getCode() == DEFAULT_TEMPLATE ) {
                $module_title .= '&nbsp;(' . $osC_Language->get('default_entry') . ')';
                
                $action[] = array('class' => 'icon-default-record', 'qtip' => $osC_Language->get('field_set_as_default'));
              } else {
                $action[] = array('class' => 'icon-default-gray-record', 'qtip' => $osC_Language->get('field_set_as_default'));
              }
              
              $action[] = array('class' => 'icon-uninstall-record', 'qtip' => $osC_Language->get('icon_uninstall'));
            } else {
              $action[] = array('class' => 'icon-empty-record', 'qtip' => $osC_Language->get('field_set_as_default'));
              $action[] = array('class' => 'icon-install-record', 'qtip' => $osC_Language->get('icon_install'));
            }
            
            $modules[] = array('code' => $module->getCode(),
                               'title' => $module_title,
                               'author' => $module->getAuthorName(),
                               'url' => $module->getAuthorAddress(),
                               'action' => $action);
          }
  	    }
      }
    
      $response = array(EXT_JSON_READER_ROOT => $modules);
      
      echo $toC_Json->encode($response);
    }
        
    function install() {
      global $toC_Json, $osC_Language;

      $module_name = $_REQUEST['module_code'];

      if ( file_exists('../templates/' . $module_name . '/template.php') ) {
        include('../templates/' . $module_name . '/template.php');

        $class = 'osC_Template_' . $module_name;

        $module = new $class();
        $module->install();
        
        osC_Cache::clear('configuration');
        osC_Cache::clear('templates');

        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function uninstall() {
      global $toC_Json, $osC_Language;
      
      $module_name = $_REQUEST['module_code'];
      
      $error = false;
      $feedback = array();
      if ( $module_name == DEFAULT_TEMPLATE ) {
        $error = true;
        $feedback[] = $osC_Language->get('uninstall_error_template_prohibited');
      }
      
      if($error === false) {
        if ( file_exists('../templates/' . $module_name . '/template.php') ) {
          include('../templates/' . $module_name . '/template.php');
  
          $class = 'osC_Template_' . $module_name;
  
          $module = new $class();
          $module->remove();
  
          osC_Cache::clear('configuration');
          osC_Cache::clear('templates');
  
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function setDefault() {
      global $toC_Json, $osC_Language;
      
      $module_name = $_REQUEST['template'];
      $response = array();
      
      if( isset($_REQUEST['configuration']) )
        $data = array('configuration' => $_REQUEST['configuration']);
      else
        $data = array('configuration' => '');

      if ( self::_save($module_name, $data, true ) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
        
    function uploadTemplate() {
      global $toC_Json, $osC_Language;
       
      $feedback = array();
      
      if( self::_upload_new_template($feedback) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => implode('<br />', $feedback));
      }
      
      header('Content-Type: text/html');
      
      $a = $toC_Json->encode($response);
      echo $toC_Json->encode($response);
    }

    function _upload_new_template(&$result){
      global $osC_Language, $osC_Database;

      $template_file = new upload('template_file', realpath('../templates') , '777' , 'zip');

      if ( $template_file->exists() ) {
        if( $template_file->parse() && $template_file->save() ){

          $module_name = substr($template_file->filename,0,strpos($template_file->filename,'.'));
          $directory = realpath('../templates') . '/' . $module_name;

          if( is_dir($directory) ){
              $result[] = $osC_Language->get('ms_error_template_directory_exist');
              osc_remove(realpath('../templates') . '/' . $template_file->filename);
              return false;
          }

          require_once('../ext/zip/pclzip.lib.php');
          
          $archive = new PclZip(realpath('../templates') . '/' . $template_file->filename);
        	if ($archive->extract(PCLZIP_OPT_PATH, realpath('../templates')) == 0) {
          	return false;
          }
          
          osc_remove(realpath('../templates') . '/' . $template_file->filename);

          if ( file_exists('../templates/' . $module_name . '/template.php') ) {
            include('../templates/' . $module_name . '/template.php');

            $class  = 'osC_Template_' . $module_name;

            if(!class_exists($class)){
              $result[] = $osC_Language->get('ms_error_template_class_not_exist');
              osc_remove(realpath('../templates') . '/' . $module_name);
              return false;
            }
            $module = new $class();

            $Qtemplate = $osC_Database->query('select id from :table_templates where code = :code');
            $Qtemplate->bindTable(':table_templates', TABLE_TEMPLATES);
            $Qtemplate->bindvalue(':code', $module->_code);
            $Qtemplate->execute();

            if( $Qtemplate->numberOfRows() > 0 ){
              $result[] = $osC_Language->get('ms_error_template_code_exist');
              osc_remove(realpath('../templates') . '/' . $module_name);
              return false;
            }

            return true;
          }else{
             $result[] = $osC_Language->get('ms_error_template_file_not_exist');
             osc_remove(realpath('../templates') . '/' . $module_name);
             return false;
          }
        }else{
          $result[] = $osC_Language->get('ms_error_wrong_zip_file');
          osc_remove(realpath('../templates') . '/' . $template_file->filename);
        }
      }
      
      return false;
    }
        
    function _save($module_name, $data, $default = false) {
      global $osC_Database;

      $error = false;

      $osC_Database->startTransaction();

      if ( !empty($data['configuration']) ) {
        if ( $default === true ) {
          $data['configuration']['DEFAULT_TEMPLATE'] = $module_name;
        }

        foreach ( $data['configuration'] as $key => $value ) {
          $Qupdate = $osC_Database->query('update :table_configuration set configuration_value = :configuration_value where configuration_key = :configuration_key');
          $Qupdate->bindTable(':table_configuration', TABLE_CONFIGURATION);
          $Qupdate->bindValue(':configuration_value', $value);
          $Qupdate->bindValue(':configuration_key', $key);
          $Qupdate->setLogging($_SESSION['module']);
          $Qupdate->execute();

          if ( $osC_Database->isError() ) {
            $error = true;
            break;
          }
        }
      } elseif ( $default === true ) {
        $Qupdate = $osC_Database->query('update :table_configuration set configuration_value = :configuration_value where configuration_key = :configuration_key');
        $Qupdate->bindTable(':table_configuration', TABLE_CONFIGURATION);
        $Qupdate->bindValue(':configuration_value', $module_name);
        $Qupdate->bindValue(':configuration_key', 'DEFAULT_TEMPLATE');
        $Qupdate->setLogging($_SESSION['module']);
        $Qupdate->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
        }
      }

      if ( $error === false ) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('configuration');

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }
  
  }
?>
