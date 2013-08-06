<?php
/*
  $Id: geoip.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_GeoIP_Admin {
    var $_group = 'geoip',
        $_status = false,
        $_active = false;

// class methods
    function load() {
      global $osC_Language;

      if (defined('MODULE_DEFAULT_GEOIP') && !osc_empty(MODULE_DEFAULT_GEOIP) && file_exists('includes/modules/geoip/' . MODULE_DEFAULT_GEOIP . '.php')) {
        $osC_Language->loadIniFile('modules/geoip/' . MODULE_DEFAULT_GEOIP . '.php');
        include('includes/modules/geoip/' . MODULE_DEFAULT_GEOIP . '.php');
        $module = 'osC_GeoIP_' . MODULE_DEFAULT_GEOIP;
        return new $module();
      } else {
        return new osC_GeoIP_Admin();
      }
    }

    function activate() {
    }

    function deactivate() {
    }

    function getCountryISOCode2() {
    }

    function getCountryName() {
    }

    function getData() {
      return array();
    }

    function isValid() {
      return false;
    }

    function isActive() {
      return $this->_active;
    }

    function getCode() {
      return $this->_code;
    }

    function getTitle() {
      return $this->_title;
    }

    function getDescription() {
      return $this->_description;
    }

    function getAuthorName() {
      return $this->_author_name;
    }

    function getAuthorAddress() {
      return $this->_author_www;
    }

    function hasKeys() {
      static $has_keys;

      if (isset($has_keys) === false) {
        $has_keys = (sizeof($this->getKeys()) > 0) ? true : false;
      }

      return $has_keys;
    }

    function getKeys() {
      return array();
    }

    function isInstalled() {
      return $this->_status;
    }

    function install() {
      global $osC_Database, $osC_Language;

      if (defined('MODULE_DEFAULT_GEOIP')) {
        include('includes/modules/geoip/' . MODULE_DEFAULT_GEOIP . '.php');
        $module = 'osC_GeoIP_' . MODULE_DEFAULT_GEOIP;
        $module = new module();
        $module->remove();
      }

      $Qcfg = $osC_Database->query('insert into :table_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, date_added) values (:configuration_title, :configuration_key, :configuration_value, :configuration_description, :configuration_group_id, :date_added)');
      $Qcfg->bindTable(':table_configuration', TABLE_CONFIGURATION);
      $Qcfg->bindValue(':configuration_title', 'Default GeoIP Module');
      $Qcfg->bindValue(':configuration_key', 'MODULE_DEFAULT_GEOIP');
      $Qcfg->bindValue(':configuration_value', $this->_code);
      $Qcfg->bindvalue(':configuration_description', 'Default GeoIP module.');
      $Qcfg->bindInt(':configuration_group_id', 6);
      $Qcfg->bindRaw(':date_added', 'now()');
      $Qcfg->execute();

      $Qinstall = $osC_Database->query('insert into :table_templates_boxes (title, code, author_name, author_www, modules_group) values (:title, :code, :author_name, :author_www, :modules_group)');
      $Qinstall->bindTable(':table_templates_boxes', TABLE_TEMPLATES_BOXES);
      $Qinstall->bindValue(':title', $this->_title);
      $Qinstall->bindValue(':code', $this->_code);
      $Qinstall->bindValue(':author_name', $this->_author_name);
      $Qinstall->bindValue(':author_www', $this->_author_www);
      $Qinstall->bindValue(':modules_group', $this->_group);
      $Qinstall->execute();

/*
      foreach ($osC_Language->getAll() as $key => $value) {
        if (file_exists('../includes/languages/' . $key . '/modules/' . $this->_group . '/' . $this->_code . '.xml')) {
          foreach ($osC_Language->extractDefinitions($key . '/modules/' . $this->_group . '/' . $this->_code . '.xml') as $def) {
            $Qcheck = $osC_Database->query('select id from :table_languages_definitions where definition_key = :definition_key and content_group = :content_group and languages_id = :languages_id limit 1');
            $Qcheck->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);
            $Qcheck->bindValue(':definition_key', $def['key']);
            $Qcheck->bindValue(':content_group', $def['group']);
            $Qcheck->bindInt(':languages_id', $value['id']);
            $Qcheck->execute();

            if ($Qcheck->numberOfRows() === 1) {
              $Qdef = $osC_Database->query('update :table_languages_definitions set definition_value = :definition_value where definition_key = :definition_key and content_group = :content_group and languages_id = :languages_id');
            } else {
              $Qdef = $osC_Database->query('insert into :table_languages_definitions (languages_id, content_group, definition_key, definition_value) values (:languages_id, :content_group, :definition_key, :definition_value)');
            }
            $Qdef->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);
            $Qdef->bindInt(':languages_id', $value['id']);
            $Qdef->bindValue(':content_group', $def['group']);
            $Qdef->bindValue(':definition_key', $def['key']);
            $Qdef->bindValue(':definition_value', $def['value']);
            $Qdef->execute();
          }
        }
      }

      osC_Cache::clear('languages');
*/

      osC_Cache::clear('configuration');
    }

    function remove() {
      global $osC_Database, $osC_Language;

      $Qdel = $osC_Database->query('delete from :table_configuration where configuration_key = :configuration_key');
      $Qdel->bindTable(':table_configuration', TABLE_CONFIGURATION);
      $Qdel->bindValue(':configuration_key', 'MODULE_DEFAULT_GEOIP');
      $Qdel->execute();

      $Qdel = $osC_Database->query('delete from :table_templates_boxes where code = :code and modules_group = :modules_group');
      $Qdel->bindTable(':table_templates_boxes', TABLE_TEMPLATES_BOXES);
      $Qdel->bindValue(':code', $this->_code);
      $Qdel->bindValue(':modules_group', $this->_group);
      $Qdel->execute();

      if ($this->hasKeys()) {
        $Qdel = $osC_Database->query('delete from :table_configuration where configuration_key in (":configuration_key")');
        $Qdel->bindTable(':table_configuration', TABLE_CONFIGURATION);
        $Qdel->bindRaw(':configuration_key', implode('", "', $this->getKeys()));
        $Qdel->execute();
      }

/*
      if (file_exists('../includes/languages/' . $osC_Language->getCode() . '/modules/' . $this->_group . '/' . $this->_code . '.xml')) {
        foreach ($osC_Language->extractDefinitions($osC_Language->getCode() . '/modules/' . $this->_group . '/' . $this->_code . '.xml') as $def) {
          $Qdel = $osC_Database->query('delete from :table_languages_definitions where definition_key = :definition_key and content_group = :content_group');
          $Qdel->bindTable(':table_languages_definitions', TABLE_LANGUAGES_DEFINITIONS);
          $Qdel->bindValue(':definition_key', $def['key']);
          $Qdel->bindValue(':content_group', $def['group']);
          $Qdel->execute();
        }

        osC_Cache::clear('languages');
      }
*/

      osC_Cache::clear('configuration');
    }
  }
?>
