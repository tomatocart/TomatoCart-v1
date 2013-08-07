<?php
/*
  $Id: homepage_info.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Homepage_Info_Admin {
  
    function getData() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $data = array();
      $clear_cache = false;
      
      foreach ($osC_Language->getAll() as $l) {
        $name = $l['name'];
        $code = strtoupper($l['code']);
        
        //check page title for language
        if (!defined('HOME_PAGE_TITLE_' . $code)) {
          $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Homepage Page Title For $name', 'HOME_PAGE_TITLE_$code', '','the page title for the front page', '6', '0', now())");

          define('HOME_PAGE_TITLE_' . $code, '');
          
          $clear_cache = true;
        }
        
        //check meta keywords for language
        if (!defined('HOME_META_KEYWORD_' . $code)) {
          $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Homepage Meta Keywords For $name', 'HOME_META_KEYWORD_$code', '','the meta keywords for the front page', '6', '0', now())");

          define('HOME_META_KEYWORD_' . $code, '');
          
          $clear_cache = true;
        }
        
        //check meta description for language
        if (!defined('HOME_META_DESCRIPTION_' . $code)) {
          $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Homepage Meta Description For $name', 'HOME_META_DESCRIPTION_$code', '','the meta description for the front page', '6', '0', now())");
          
          define('HOME_META_DESCRIPTION_' . $code, '');
          
          $clear_cache = true;        
        }
        
	      $Qhomepage = $osC_Database->query('select * from :table_languages_definitions where definition_key ="index_text" and languages_id = :languages_id');
	      $Qhomepage->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);
	      $Qhomepage->bindInt(':languages_id', $l['id']);
	      $Qhomepage->execute();  

	      if($Qhomepage->next()) {
	        $data['index_text[' . $l['id'] . ']'] = $Qhomepage->value('definition_value');
	      }
        
	      $data['HOME_PAGE_TITLE[' . $code . ']'] = constant('HOME_PAGE_TITLE_' . $code);
        $data['HOME_META_KEYWORD[' . $code . ']'] = constant('HOME_META_KEYWORD_' . $code);
        $data['HOME_META_DESCRIPTION[' . $code . ']'] = constant('HOME_META_DESCRIPTION_' . $code);
      }
      
      if ($clear_cache == true) {
        osC_Cache::clear('configuration');
        
      }
      
      return $data;
    }
      
    function saveData($data) {
      global $osC_Database, $osC_Language;
      $error = false;
      
      foreach($data['page_title'] as $key => $value) {
        $Qconfiguration = $osC_Database->query("update :table_configuration set configuration_value = :configuration_value where configuration_key = :configuration_key");
        $Qconfiguration->bindValue(":configuration_key", 'HOME_PAGE_TITLE_' . $key);
        $Qconfiguration->bindValue(":configuration_value", $value);
        $Qconfiguration->bindTable(":table_configuration", TABLE_CONFIGURATION);
        $Qconfiguration->execute();
        
        if($osC_Database->isError()) {
          $error = true;
          break;
        }
      }
      
      if ($error === false) {
        foreach($data['keywords'] as $key => $value) {
          $Qconfiguration = $osC_Database->query("update :table_configuration set configuration_value = :configuration_value where configuration_key = :configuration_key");
          $Qconfiguration->bindValue(":configuration_key", 'HOME_META_KEYWORD_' . $key);
          $Qconfiguration->bindValue(":configuration_value", $value);
          $Qconfiguration->bindTable(":table_configuration", TABLE_CONFIGURATION);
          $Qconfiguration->execute();
          
          if($osC_Database->isError()) {
            $error = true;
            break;
          }
        }
      }
      
      if ($error === false) {
        foreach($data['descriptions'] as $key => $value) {
          $Qconfiguration = $osC_Database->query("update :table_configuration set configuration_value = :configuration_value where configuration_key = :configuration_key");
          $Qconfiguration->bindValue(":configuration_key", 'HOME_META_DESCRIPTION_' . $key);
          $Qconfiguration->bindValue(":configuration_value", $value);
          $Qconfiguration->bindTable(":table_configuration", TABLE_CONFIGURATION);
          $Qconfiguration->execute();
          
          if($osC_Database->isError()) {
            $error = true;
            break;
          }
        }
      }
      
      if ($error === false) {
        foreach($data['index_text'] as $languages_id => $value) {
          $Qupdate = $osC_Database->query('update :table_languages_definitions set definition_value = :definition_value where definition_key ="index_text" and content_group = "index" and languages_id = :languages_id ');
          $Qupdate->bindTable(":table_languages_definitions", TABLE_LANGUAGES_DEFINITIONS);
          $Qupdate->bindValue(":definition_value", $value);
          $Qupdate->bindInt(':languages_id', $languages_id);
          $Qupdate->execute();
          
          if($osC_Database->isError()) {
            $error = true;
            break;
          }
        }
      }
            
      if ($error === false) {
        osC_Cache::clear('configuration');
        
        foreach ($osC_Language->getAll() as $l) {
          osC_Cache::clear('languages-' . $l['code'] . '-index');
        }
        
        return true;
      }
      
      return false;
    }
  }
?>
