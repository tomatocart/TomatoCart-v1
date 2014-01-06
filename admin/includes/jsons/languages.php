<?php
/*
  $Id: languages.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('../includes/classes/currencies.php');

  class toC_Json_Languages {
        
    function listLanguages() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];      
      
      $Qlanguages = $osC_Database->query('select * from :table_languages order by sort_order, name');
      $Qlanguages->bindTable(':table_languages', TABLE_LANGUAGES);
      $Qlanguages->setExtBatchLimit($start, $limit);
      $Qlanguages->execute();
        
      $records = array();     
      while ( $Qlanguages->next() ) {
        $Qdef = $osC_Database->query('select count(*) as total_definitions from :table_languages_definitions where languages_id = :languages_id');
        $Qdef->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);
        $Qdef->bindInt(':languages_id', $Qlanguages->valueInt('languages_id'));
        $Qdef->execute();
        
        $languages_name = $Qlanguages->value('name');

        if ($Qlanguages->value('code') == DEFAULT_LANGUAGE) {
          $languages_name .= ' (' . $osC_Language->get('default_entry') . ')';
        }
        
        $action = array();
        $action[] = array('class' => 'icon-edit-record', 'qtip' => $osC_Language->get('icon_edit'));
        $action[] = array('class' => 'icon-export-record', 'qtip' => $osC_Language->get('icon_export'));
        $action[] = array('class' => 'icon-delete-record', 'qtip' => $osC_Language->get('icon_trash'));
        $action[] = array('class' => 'icon-languages', 'qtip' => $osC_Language->get('icon-languages'));
        
        $records[] = array(
          'languages_id' => $Qlanguages->valueInt('languages_id'),
          'code' =>  $Qlanguages->value('code'),
          'total_definitions' => $Qdef->value('total_definitions'),
          'languages_name' => $languages_name,
          'languages_flag' => $osC_Language->showImage($Qlanguages->value('code')),
          'action' => $action
        );           
      }
      $Qlanguages->freeResult();
      $Qdef->freeResult();         
       
      $response = array(EXT_JSON_READER_TOTAL => $Qlanguages->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }          
    
   function getLanguages() {
      global $toC_Json;

      $osC_DirectoryListing = new osC_DirectoryListing('../includes/languages');
      $osC_DirectoryListing->setIncludeDirectories(false);
      $osC_DirectoryListing->setCheckExtension('xml');
        
      $records = array();
      foreach ($osC_DirectoryListing->getFiles() as $file) {
        $records[] = array('id' => substr($file['name'], 0, strrpos($file['name'], '.')), 
                           'text' => substr($file['name'], 0, strrpos($file['name'], '.')));
      }

      $response = array(EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response);
    }
    
    function importLanguage() {
      global $toC_Json, $osC_Language, $osC_Currencies;

      $osC_Currencies = new osC_Currencies();

      if (osC_Language_Admin::import($_REQUEST['languages_id'], $_REQUEST['import_type'])) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteLanguage() {
      global $toC_Json, $osC_Language;
      
      $error = false;
      $feedback = array();
      
      $del_files = isset($_POST['delFiles']) && $_POST['delFiles'] == 1 ? true : false;
      
      if ( $_POST['code'] == DEFAULT_LANGUAGE ) {
        $error = true;
        $feedback[] = $osC_Language->get('introduction_delete_language_invalid');
      }
      
      if ($error === false) {
        if ( osC_Language_Admin::remove($_POST['languages_id'], $del_files) ) {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteLanguages() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $error = false;
      $feedback = array();
      
      $del_files = isset($_POST['delFiles']) && $_POST['delFiles'] == 1 ? true : false;
     
      $batch = explode(',', $_POST['batch']);
      $Qcheck = $osC_Database->query('select code from :table_languages where languages_id in (":languages_id")');
      $Qcheck->bindTable(':table_languages', TABLE_LANGUAGES);
      $Qcheck->bindRaw(':languages_id', implode('", "', array_unique(array_filter(array_slice($batch, 0, MAX_DISPLAY_SEARCH_RESULTS), 'is_numeric'))));
      $Qcheck->execute();
      
      while( $Qcheck->next() ) {
        if ( $Qcheck->value('code') == DEFAULT_LANGUAGE ) {
          $error = true;
          $feedback[] = $osC_Language->get('introduction_delete_language_invalid');
          break;
        }
      }

      if ($error === false) {
        foreach ($batch as $id) {
          if ( !osC_Language_Admin::remove($id, $del_files) ) {
            $error = true;
            break;
          } 
        }
          
        if ($error === false) {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }          
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback)); 
      }  
      
      echo $toC_Json->encode($response);               
    }       
    
    function getCurrencies() {
      global $toC_Json;

      $osC_Currencies = new osC_Currencies();
      
      $records = array();
      foreach ($osC_Currencies->getData() as $currency) {
        $records[] = array('currencies_id' => $currency['id'],
                           'text' => $currency['title']);
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response);
    }
    
    function getParentLanguage() {
      global $toC_Json, $osC_Language;

      $records =  array(array('parent_id' => '0', 'text' => $osC_Language->get('none')));                           
      foreach ( $osC_Language->getAll() as $l ) {
        if ( $l['id'] != $_REQUEST['languages_id'] ) {
          $records[] = array('parent_id' => $l['id'], 'text' => $l['name'] . ' (' . $l['code'] . ')');
        }
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response);
    }
    
    function getGroups() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $Qgroups = $osC_Database->query('select distinct content_group from :table_languages_definitions where languages_id = :languages_id order by content_group');
      $Qgroups->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);
      $Qgroups->bindInt(':languages_id', $_REQUEST['languages_id']);
      $Qgroups->execute();

      $records =  array();
      while ( $Qgroups->next() ) {
        $records[] = array('id' => $Qgroups->value('content_group'),
                           'text' => $Qgroups->value('content_group'));
      }
      $Qgroups->freeResult();
      
      $response = array(EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response);
    }
    
    function loadLanguage() {
      global $toC_Json;
     
      $data = osC_Language_Admin::getData($_REQUEST['languages_id']);
      
      $data['default'] = ($data['code'] == DEFAULT_LANGUAGE) ? true : false;
      
      $response = array('success' => true, 'data' => $data);
       
      echo $toC_Json->encode($response);    
    }
   
    function saveLanguage() {
      global $toC_Json, $osC_Language;
      
      $languages_id = ( isset($_REQUEST['languages_id']) && is_numeric($_REQUEST['languages_id']) ) ? $_REQUEST['languages_id'] : null;
      
      $data = array('name' => $_REQUEST['name'],
                    'code' => $_REQUEST['code'],
                    'locale' => $_REQUEST['locale'],
                    'charset' => $_REQUEST['charset'],
                    'date_format_short' => $_REQUEST['date_format_short'],
                    'date_format_long' => $_REQUEST['date_format_long'],
                    'time_format' => $_REQUEST['time_format'],
                    'text_direction' => $_REQUEST['text_id'],
                    'currencies_id' => $_REQUEST['currencies_id'],
                    'numeric_separator_decimal' => $_REQUEST['numeric_separator_decimal'],
                    'numeric_separator_thousands' => $_REQUEST['numeric_separator_thousands'],
                    'parent_id' => $_REQUEST['parent_id'],
                    'sort_order' => $_REQUEST['sort_order']);

      if ( osC_Language_Admin::update($languages_id, $data, (isset($_REQUEST['default']) && ($_REQUEST['default'] == 'on'))) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }

      echo $toC_Json->encode($response);
    }
    
    function export() {
      global $toC_Json, $osC_Currencies;
      
      $osC_Currencies = new osC_Currencies();
      
      $groups = explode(',', $_REQUEST['export']);
      
      osC_Language_Admin::export($_REQUEST['languages_id'], $groups, (isset($_REQUEST['include_data']) && ($_REQUEST['include_data'] == 'on')));
    }
    
    function uploadLanguage() {
      global $toC_Json, $osC_Language, $osC_Currencies;
      
      $osC_Currencies = new osC_Currencies();
      
      $error = false;
      $feedback = array();
      
      $language = $_FILES['upload_file'];
      $tmp_path = DIR_FS_CACHE . 'languages/' . time();    
      
      if ( !is_dir(DIR_FS_CACHE . 'languages') ) {
      	$old = umask(0);
        if ( !mkdir(DIR_FS_CACHE . 'languages', 0777) ) {
          $error = true;
        }
        umask($old);
      }

      $old = umask(0);
      if ( ($error === false) && (mkdir($tmp_path, 0777)) ) {
      	umask($old);
      	
        $temp_file = new upload($language, $tmp_path);
        
        if ( $temp_file->exists() && $temp_file->parse() && $temp_file->save() ) {
          require_once('../ext/zip/pclzip.lib.php');
          
          $archive = new PclZip($tmp_path . '/' . $temp_file->filename);
          
          if ($archive->extract(PCLZIP_OPT_PATH, $tmp_path) == 0) {
          	$error = true;
          	$feedback[] = $osC_Language->get('ms_error_wrong_zip_file_format');
          } 
        } else {
          $error = true;
          $feedback[] = $osC_Language->get('ms_error_save_file_failed');
        }
      } else {
        $error = true;
        $feedback[] = sprintf($osC_Language->get('ms_error_creating_directory_failed'), DIR_FS_CACHE);
      }
      
      if ($error === false) {
        $osC_DirectoryListing = new osC_DirectoryListing($tmp_path);
        $osC_DirectoryListing->setIncludeDirectories(true);
        $osC_DirectoryListing->setIncludeFiles(false);
        $files = $osC_DirectoryListing->getFiles();
        
        $code = null;
        foreach ( $files as $file ) {
          if( is_dir($tmp_path . '/' . $file['name'] . '/includes') && is_dir($tmp_path . '/' . $file['name'] . '/'. DIR_FS_ADMIN) && is_dir($tmp_path . '/' . $file['name'] . '/install') ) {
            $code = $file['name'];
            
            break;
          }
        }
        
        if ( $code != null ) {
          toc_dircopy($tmp_path . '/' . $code . "/includes/languages", DIR_FS_CATALOG . 'includes/languages');
          toc_dircopy($tmp_path . '/' . $code . "/". DIR_FS_ADMIN. "includes/languages", DIR_FS_CATALOG . DIR_FS_ADMIN. 'includes/languages');
          toc_dircopy($tmp_path . '/' . $code . "/install/includes/languages", DIR_FS_CATALOG . 'install/includes/languages');
          toc_dircopy($tmp_path . '/' . $code . "/install/templates", DIR_FS_CATALOG . 'install/templates');
          
          osc_remove($tmp_path);
        } else {
          $error = true;
          $feedback[] = $osC_Language->get('ms_error_wrong_language_package');
        }
      }
      
      if ( $error === false ) {
        if ( osC_Language_Admin::import($code, 'replace') ) {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . ' ' . implode(' ', $feedback));
      }
      
      header('Content-Type: text/html');
      echo $toC_Json->encode($response);
    }
    
  	function listTranslationGroups() {
      global $toC_Json, $osC_Database;

      $Qgroups = $osC_Database->query('select distinct content_group from :table_languages_definitions where languages_id = :languages_id order by content_group');
      $Qgroups->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);
      $Qgroups->bindInt(':languages_id', $_REQUEST['languages_id']);
      $Qgroups->execute();
      
      $records = array();
      while($Qgroups->next()){
        $records[] = array('id' => $Qgroups->value('content_group'), 
                          'text' => $Qgroups->value('content_group'),
                          'cls' => 'x-tree-node-collapsed',
                          'leaf' => true);
      }
      
      echo $toC_Json->encode($records);
    }
    
  	function listTranslations() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $group = isset($_REQUEST['group'])? $_REQUEST['group']: 'general';
      
      $QdefinitionValue = $osC_Database->query("select * from :table_languages_definitions where languages_id = :languages_id and content_group = :content_group");
      
      if (isset($_REQUEST['search']) && !empty($_REQUEST['search'])) {
        $QdefinitionValue->appendQuery('and definition_key like :definition_key or definition_value like :definition_value');
        $QdefinitionValue->bindValue(':definition_key', '%' . $_REQUEST['search'] . '%');
        $QdefinitionValue->bindValue(':definition_value', '%' . $_REQUEST['search'] . '%');
      }
      
      $QdefinitionValue->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);
      $QdefinitionValue->bindInt(':languages_id', $_REQUEST['languages_id']);
      $QdefinitionValue->bindvalue(':content_group', $group);
      $QdefinitionValue->execute();
      
      $records = array();
      
      $action = array();
      $action[] = array('class' => 'icon-edit-record', 'qtip' => $osC_Language->get('icon_edit'));
      while($QdefinitionValue->next()){
        $records[] = array('languages_definitions_id' => $QdefinitionValue->valueInt('id'),
        									 'languages_id' => $_REQUEST['languages_id'],
                           'definition_key' => $QdefinitionValue->value('definition_key'),
                           'definition_value' => $QdefinitionValue->value('definition_value'), 
                           'content_group' => $group,
        									 'action' => $action);
      }
      $QdefinitionValue->freeResult();  

      $response = array('total' => sizeof($records), 'records' => $records);
      
      echo $toC_Json->encode($response);
    }
    
    function updateTranslation() {
      global $toC_Json, $osC_Language;

      $value = rtrim($_REQUEST['definition_value']);
      
      if ( osC_Language_Admin::saveDefinition($_REQUEST['languages_id'], $_REQUEST['group'], $_REQUEST['definition_key'] , $value) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }

      echo $toC_Json->encode($response);
    }
    
    function deleteTranslation() {
      global $toC_Json, $osC_Language;

      $languages_definitions_id = $_REQUEST['languages_definitions_id'];
      
      if ( osC_Language_Admin::deleteDefinition($languages_definitions_id)) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }

      echo $toC_Json->encode($response);
    }
    
    function addTranslation() {
      global $toC_Json, $osC_Language;
      
      $data = array('languages_id' => $_REQUEST['languages_id'],
                    'definition_group' => $_REQUEST['definition_group'],
                    'definition_key' => $_REQUEST['definition_key'],
                    'definition_value' => rtrim($_REQUEST['definition_value']));
      
      if ( osC_Language_Admin::addDefinition($data) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }

      echo $toC_Json->encode($response);
    }
  }
?>
