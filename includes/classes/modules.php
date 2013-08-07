<?php
/*
  $Id: modules.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Modules {
    var $_modules,
        $_code,
        $_title,
        $_title_link,
        $_content,
        $_author_name,
        $_author_www,
        $_keys,
        $_group;

    function osC_Modules($group) {
      global $osC_Database, $osC_Template, $osC_Cache;

      $this->_group = $group;

      if ($osC_Cache->read('templates_' . $this->_group . '_layout-' . $osC_Template->getCode() . '-' . $osC_Template->getGroup() . '-' . $osC_Template->getPageContentsFilename())) {
        $data = $osC_Cache->getCache();
      } else {
        $data = array();

        $Qspecific = $osC_Database->query('select b2p.boxes_group, b.code from :table_templates_boxes_to_pages b2p, :table_templates_boxes b, :table_templates t where b2p.templates_id = :templates_id and b2p.page_specific = 1 and b2p.content_page in (:content_page) and b2p.templates_boxes_id = b.id and b.modules_group = :modules_group and b2p.templates_id = t.id order by b2p.boxes_group, b2p.sort_order');
        $Qspecific->bindTable(':table_templates_boxes_to_pages', TABLE_TEMPLATES_BOXES_TO_PAGES);
        $Qspecific->bindTable(':table_templates_boxes', TABLE_TEMPLATES_BOXES);
        $Qspecific->bindTable(':table_templates', TABLE_TEMPLATES);
        $Qspecific->bindInt(':templates_id', $osC_Template->getID());
        $Qspecific->bindRaw(':content_page', '"*", "' . $osC_Template->getGroup() . '/*", "' . $osC_Template->getGroup() . '/' . substr($osC_Template->getPageContentsFilename(), 0, strrpos($osC_Template->getPageContentsFilename(), '.')) . '"');
        $Qspecific->bindValue(':modules_group', $this->_group);
        $Qspecific->execute();

        if ($Qspecific->numberOfRows()) {
          while ($Qspecific->next()) {
            $data[$Qspecific->value('boxes_group')][] = $Qspecific->value('code');
          }
        } else {
          $_data = array();

          $Qmodules = $osC_Database->query('select b2p.boxes_group, b2p.content_page, b.code from :table_templates_boxes_to_pages b2p, :table_templates_boxes b, :table_templates t where b2p.templates_id = :templates_id and b2p.content_page in (:content_page) and b2p.templates_boxes_id = b.id and b.modules_group = :modules_group and b2p.templates_id = t.id order by b2p.boxes_group, b2p.sort_order');
          $Qmodules->bindTable(':table_templates_boxes_to_pages', TABLE_TEMPLATES_BOXES_TO_PAGES);
          $Qmodules->bindTable(':table_templates_boxes', TABLE_TEMPLATES_BOXES);
          $Qmodules->bindTable(':table_templates', TABLE_TEMPLATES);
          $Qmodules->bindInt(':templates_id', $osC_Template->getID());
          $Qmodules->bindRaw(':content_page', '"*", "' . $osC_Template->getGroup() . '/*", "' . $osC_Template->getGroup() . '/' . substr($osC_Template->getPageContentsFilename(), 0, strrpos($osC_Template->getPageContentsFilename(), '.')) . '"');
          $Qmodules->bindValue(':modules_group', $this->_group);
          $Qmodules->execute();

          while ($Qmodules->next()) {
            $_data[$Qmodules->value('boxes_group')][] = array('code' => $Qmodules->value('code'),
                                                              'page' => $Qmodules->value('content_page'));
          }

          foreach ($_data as $groups => $modules) {
            $clean = array();

            foreach ($modules as $module) {
              if (isset($clean[$module['code']])) {
                if (substr_count($module['page'], '/') > substr_count($clean[$module['code']]['page'], '/')) {
                  unset($clean[$module['code']]);
                }
              }

              $clean[$module['code']] = $module;
            }

            $_data[$groups] = $clean;
          }

          foreach ($_data as $groups => $modules) {
            foreach ($modules as $module) {
              $data[$groups][] = $module['code'];
            }
          }
        }

        $osC_Cache->writeBuffer($data);
      }

      $this->_modules = $data;
    }

    function getCode() {
      return $this->_code;
    }

    function getTitle() {
      return $this->_title;
    }

    function getTitleLink() {
      return $this->_title_link;
    }

    function hasTitleLink() {
      return !empty($this->_title_link);
    }

    function getContent() {
      return $this->_content;
    }

    function hasContent() {
      return !empty($this->_content);
    }

    function getAuthorName() {
      return $this->_author_name;
    }

    function getAuthorAddress() {
      return $this->_author_www;
    }

    function getGroup($group) {
      global $osC_Template;
      
      $modules = array();

      if (isset($this->_modules[$group])) {
        foreach ($this->_modules[$group] as $module) {
          
          if (file_exists('templates/' . $osC_Template->getCode() . '/includes/modules/' . $this->_group . '/' . $module . '.php')) {
            $class = 'osC_' . ucfirst($this->_group) . '_' . $module;
            
            if (class_exists($class) === false) {
              include_once('templates/' . $osC_Template->getCode() . '/includes/modules/' . $this->_group . '/' . $module . '.php');
            }
            
            $modules[] = $class;
          }else if (file_exists('includes/modules/' . $this->_group . '/' . $module . '.php')) {
            $class = 'osC_' . ucfirst($this->_group) . '_' . $module;

            if (class_exists($class) === false) {
              include_once('includes/modules/' . $this->_group . '/' . $module . '.php');
            }
            
            $modules[] = $class;
          }
        }
      }

      return $modules;
    }

    function isInstalled($code = '', $group = '') {
      global $osC_Database;

      if (empty($code) && empty($group)) {
        static $is_installed;

        $code = $this->_code;
        $group = $this->_group;
      }

      if (isset($is_installed) === false) {
        $Qcheck = $osC_Database->query('select id from :table_templates_boxes where code = :code and modules_group = :modules_group');
        $Qcheck->bindTable(':table_templates_boxes', TABLE_TEMPLATES_BOXES);
        $Qcheck->bindValue(':code', $code);
        $Qcheck->bindValue(':modules_group', $group);
        $Qcheck->execute();

        $is_installed = ($Qcheck->numberOfRows()) ? true : false;
      }

      return $is_installed;
    }

    function hasKeys() {
      static $has_keys;

      if (isset($has_keys) === false) {
        $has_keys = (sizeof($this->getKeys()) > 0) ? true : false;
      }

      return $has_keys;
    }

    function getKeys() {
      if (isset($this->_keys) === false) {
        $this->_keys = array();
      }

      return $this->_keys;
    }

    function isActive() {
      return true;
    }

    function install() {
      global $osC_Database, $osC_Language;

      $Qinstall = $osC_Database->query('insert into :table_templates_boxes (title, code, author_name, author_www, modules_group) values (:title, :code, :author_name, :author_www, :modules_group)');
      $Qinstall->bindTable(':table_templates_boxes', TABLE_TEMPLATES_BOXES);
      $Qinstall->bindValue(':title', $this->_title);
      $Qinstall->bindValue(':code', $this->_code);
      $Qinstall->bindValue(':author_name', $this->_author_name);
      $Qinstall->bindValue(':author_www', $this->_author_www);
      $Qinstall->bindValue(':modules_group', $this->_group);
      $Qinstall->execute();

      foreach ($osC_Language->getAll() as $key => $value) {
        if (file_exists(dirname(__FILE__) . '/../languages/' . $key . '/modules/' . $this->_group . '/' . $this->_code . '.xml')) {
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
    }

    function remove() {
      global $osC_Database, $osC_Language;

      $Qmodule = $osC_Database->query('select id from :table_templates_boxes where code = :code and modules_group = :modules_group');
      $Qmodule->bindTable(':table_templates_boxes', TABLE_TEMPLATES_BOXES);
      $Qmodule->bindValue(':code', $this->_code);
      $Qmodule->bindValue(':modules_group', $this->_group);
      $Qmodule->execute();

      $Qdel = $osC_Database->query('delete from :table_templates_boxes_to_pages where templates_boxes_id = :templates_boxes_id');
      $Qdel->bindTable(':table_templates_boxes_to_pages', TABLE_TEMPLATES_BOXES_TO_PAGES);
      $Qdel->bindValue(':templates_boxes_id', $Qmodule->valueInt('id'));
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
    }
  }
?>
