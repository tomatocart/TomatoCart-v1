<?php
/*
  $Id: templates_modules_layout.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com
  
  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd
  
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
  class toC_Json_Templates_Modules_Layout {
    
    function getTemplates() {
      global $toC_Json, $osC_Database;
      
      $Qtemplates = $osC_Database->query('select id, title, code from :table_templates order by title');
      $Qtemplates->bindTable(':table_templates', TABLE_TEMPLATES);
      $Qtemplates->execute();
      
      $records = array();
      while ($Qtemplates->next()) {
      	$records[] = array('id' => $Qtemplates->ValueInt('id'),
      	                   'title' => $Qtemplates->Value('title'),
      	                   'default' => (($Qtemplates->Value('code') == DEFAULT_TEMPLATE) ? 1 : 0)
      	);
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);
    }
    
    function listTemplatesModulesLayout() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $id = empty($_REQUEST['id']) ? 0 : $_REQUEST['id'];
      
      $Qlayout = $osC_Database->query('select b2p.*, b.title as box_title from :table_templates_boxes_to_pages b2p, :table_templates_boxes b where b2p.templates_id = :templates_id and b2p.templates_boxes_id = b.id and b.modules_group = :modules_group order by b2p.page_specific desc, b2p.boxes_group, b2p.sort_order, b.title');
      $Qlayout->bindTable(':table_templates_boxes_to_pages', TABLE_TEMPLATES_BOXES_TO_PAGES);
      $Qlayout->bindTable(':table_templates_boxes', TABLE_TEMPLATES_BOXES);
      $Qlayout->bindInt(':templates_id', $id);
      $Qlayout->bindValue(':modules_group', $_REQUEST['set']);
      $Qlayout->execute();

      $Qcode = $osC_Database->query('select * from :table_templates where id= :id');
      $Qcode->bindTable(':table_templates', TABLE_TEMPLATES);
      $Qcode->bindValue(':id', $id);
      $Qcode->execute();
      
      $records = array();
      while ($Qlayout->next()) {
        $records[] = array('id' => $Qlayout->ValueInt('id'),
                           'content_page' => $Qlayout->Value('content_page'),
                           'boxes_group' => $Qlayout->Value('boxes_group'),
                           'sort_order' => $Qlayout->Value('sort_order'),
                           'page_specific' => $Qlayout->Value('page_specific'),
                           'templates_boxes_id' => $Qlayout->ValueInt('templates_boxes_id'),
                           'box_title' => $Qlayout->Value('box_title'),
                           'code' => $Qcode->Value('code'));
      }
      
      $response = array(EXT_JSON_READER_TOTAL => $Qlayout->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response);
    }    

    function getModules() {
      global $toC_Json, $osC_Database, $osC_Language;
    
      $Qboxes = $osC_Database->query('select id, title from :table_templates_boxes where modules_group = :modules_group order by title');
      $Qboxes->bindTable(':table_templates_boxes', TABLE_TEMPLATES_BOXES);
      $Qboxes->bindValue(':modules_group', $_REQUEST['set']);
      $Qboxes->execute();
    
      $boxes = array();
      while ( $Qboxes->next() ) {
        $boxes[] = array('id' => $Qboxes->valueInt('id'),
                         'text' => $Qboxes->value('title'));
      }
      
      $response = array(EXT_JSON_READER_ROOT => $boxes);

      echo $toC_Json->encode($response);
    }
    
    function getPages() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $Qtemplate = $osC_Database->query('select code from :table_templates where id = :id');
      $Qtemplate->bindTable(':table_templates', TABLE_TEMPLATES);
      $Qtemplate->bindValue(':id', $_REQUEST['filter']);
      $Qtemplate->execute();

      $filter_id = $_REQUEST['filter'];
      $_REQUEST['filter'] = $Qtemplate->Value('code');
      
      $pages_array = array(array('id' => $filter_id . '/*',
                                 'text' => '*'));
    
      $d_boxes = new osC_DirectoryListing('../templates/' . $_REQUEST['filter'] . '/content');
      $d_boxes->setRecursive(true);
      $d_boxes->setAddDirectoryToFilename(true);
      $d_boxes->setCheckExtension('php');
      $d_boxes->setExcludeEntries('.svn');
    
      foreach ( $d_boxes->getFiles(false) as $box ) {
        if ( $box['is_directory'] === true ) {
          $entry = array('id' => $filter_id . '/' . $box['name'] . '/*',
                         'text' => $box['name'] . '/*');
        } else {
          $page_filename = substr($box['name'], 0, strrpos($box['name'], '.'));
    
          $entry = array('id' => $filter_id . '/' . $page_filename,
                         'text' => $page_filename);
        }
    
        if ( ( $_REQUEST['filter'] != DEFAULT_TEMPLATE ) && ( $d_boxes->getSize() > 0 ) ) {
          $entry['group'] = '-- ' . $_REQUEST['filter'] . ' --';
        }
    
        $pages_array[] = $entry;
      }
    
      if ( $_REQUEST['filter'] != DEFAULT_TEMPLATE ) {
        $d_boxes = new osC_DirectoryListing('../templates/' . DEFAULT_TEMPLATE . '/content');
        $d_boxes->setRecursive(true);
        $d_boxes->setAddDirectoryToFilename(true);
        $d_boxes->setCheckExtension('php');
        $d_boxes->setExcludeEntries('.svn');
    
        foreach ( $d_boxes->getFiles(false) as $box ) {
          if ( $box['is_directory'] === true ) {
            $entry = array('id' => $filter_id . '/' . $box['name'] . '/*',
                           'text' => $box['name'] . '/*');
          } else {
            $page_filename = substr($box['name'], 0, strrpos($box['name'], '.'));
    
            $entry = array('id' => $filter_id . '/' . $page_filename,
                           'text' => $page_filename);
          }
    
          $check_entry = $entry;
          $check_entry['group'] = '-- ' . $_REQUEST['filter'] . ' --';
    
          if ( !in_array($check_entry, $pages_array) ) {
            $entry['group'] = '-- ' . DEFAULT_TEMPLATE . ' --';
    
            $pages_array[] = $entry;
          }
        }
      }
      
      $response = array(EXT_JSON_READER_ROOT => $pages_array);

      echo $toC_Json->encode($response);
    }
    
    function getGroups() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $Qtemplate = $osC_Database->query('select code from :table_templates where id = :id');
      $Qtemplate->bindTable(':table_templates', TABLE_TEMPLATES);
      $Qtemplate->bindValue(':id', $_REQUEST['filter']);
      $Qtemplate->execute();

      $filter_id = $_REQUEST['filter'];
      $_REQUEST['filter'] = $Qtemplate->Value('code');
    
      require('../templates/' . $_REQUEST['filter'] . '/template.php');
    
      $class = 'osC_Template_' . $_REQUEST['filter'];
      $filter_template = new $class();
      
      $groups_array = array();
      
      foreach ( $filter_template->getGroups($_REQUEST['set']) as $group ) {
        $groups_array[] = array('id' => $group,
                                'text' => $group);
      }
      
      $Qgroups = $osC_Database->query('select distinct b2p.boxes_group from :table_templates_boxes_to_pages b2p, :table_templates_boxes b where b2p.templates_id = :templates_id and b2p.templates_boxes_id = b.id and b.modules_group = :modules_group and b2p.boxes_group not in (:boxes_group) order by b2p.boxes_group');
      $Qgroups->bindTable(':table_templates_boxes_to_pages', TABLE_TEMPLATES_BOXES_TO_PAGES);
      $Qgroups->bindTable(':table_templates_boxes', TABLE_TEMPLATES_BOXES);
      $Qgroups->bindInt(':templates_id', $filter_id);
      $Qgroups->bindValue(':modules_group', $_REQUEST['set']);
      $Qgroups->bindRaw(':boxes_group', '"' . implode('", "', $filter_template->getGroups($_REQUEST['set'])) . '"');
      $Qgroups->execute();
    
      while ($Qgroups->next()) {
        $groups_array[] = array('id' => $Qgroups->value('boxes_group'),
                                'text' => $Qgroups->value('boxes_group'));
      }
      
      if ( !empty($groups_array) ) {
        array_unshift($groups_array, array('id' => null, 'text' => $osC_Language->get('please_select')));
      }  
      
      $response = array(EXT_JSON_READER_ROOT => $groups_array);
      
      echo $toC_Json->encode($response);
    }
      
    function saveBoxLayout(){
      global $osC_Database, $toC_Json, $osC_Language;

      $data = array('box' => isset($_REQUEST['box'])? $_REQUEST['box'] : '',
                    'content_page' => $_REQUEST['content_page'],
                    'page_specific' => (isset($_REQUEST['page_specific']) && ($_REQUEST['page_specific'] == 'on') ? true : false),
                    'group' => (isset($_REQUEST['group']) && !empty($_REQUEST['group']) ? $_REQUEST['group'] : $_REQUEST['group_new']),
                    'sort_order' => $_REQUEST['sort_order']);
                    
      if ( ( isset($_REQUEST['box_page_id']) && is_numeric($_REQUEST['box_page_id']) ) ) {
        $box_page_id = $_REQUEST['box_page_id'];
      }
      
      if ( toC_Json_Templates_Modules_Layout::_save($box_page_id, $data, $_REQUEST['set']) ) {
        $response['success'] = true;
        $response['feedback'] = $osC_Language->get('ms_success_action_performed');
      } else {
        $response['success'] = false;
        $response['feedback'] = $osC_Language->get('ms_error_action_not_performed');
      }
      
      echo $toC_Json->encode($response);
    }

    function loadBoxLayout() {
      global $osC_Database, $toC_Json;
      
      $Qlayout = $osC_Database->query('select b2p.*, b.title as box_title from :table_templates_boxes_to_pages b2p, :table_templates_boxes b where b2p.id = :id and b2p.templates_boxes_id = b.id');
      $Qlayout->bindTable(':table_templates_boxes_to_pages', TABLE_TEMPLATES_BOXES_TO_PAGES);
      $Qlayout->bindTable(':table_templates_boxes', TABLE_TEMPLATES_BOXES);
      $Qlayout->bindInt(':id', $_REQUEST['box_page_id']);
      $Qlayout->execute();
      
      $records = array();
      while ($Qlayout->next()) {
        $data['box'] = $Qlayout->Value('templates_boxes_id');
        $data['box_title'] = $Qlayout->Value('box_title');
        $data['content_page'] = $Qlayout->ValueInt('templates_id') . '/' . $Qlayout->Value('content_page');
        $data['page_specific'] = $Qlayout->ValueInt('page_specific');
        $data['group'] = $Qlayout->Value('boxes_group');
        $data['sort_order'] = $Qlayout->ValueInt('sort_order');
      }
      
      $response = array('success' => true, 'data' => $data);
      
      echo $toC_Json->encode($response);
    }
    
    function deleteBoxLayout() {
      global $toC_Json, $osC_Language;
      
      if ( toC_Json_Templates_Modules_Layout::_delete($_REQUEST['box_layout_id'], $_REQUEST['set']) ) {
        $response['success'] = true;
        $response['feedback'] = $osC_Language->get('ms_success_action_performed');
      } else {
        $response['success'] = true;
        $response['feedback'] = $osC_Language->get('ms_error_action_not_performed');
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteBoxLayouts() {
      global $osC_Database, $toC_Json, $osC_Language;
      
      $error = false;
      $batch = explode(',', $_REQUEST['batch']);
      
      foreach ($batch as $id) {
        if ( !toC_Json_Templates_Modules_Layout::_delete($id, $_REQUEST['set']) ) {
          $error = true;
          break;
        }
      }
      if ($error === false) {
        $response['success'] = true;
        $response['feedback'] = $osC_Language->get('ms_success_action_performed');
      } else {
        $response['success'] = false;
        $response['feedback'] = $osC_Language->get('ms_error_action_not_performed');
      }
      
      echo $toC_Json->encode($response);
    }

    function _save($id = null, $data, $set) {
      global $osC_Database;
      
      $link = explode('/', $data['content_page'], 2);

      if ( is_numeric($id) ) {
        $Qlayout = $osC_Database->query('update :table_templates_boxes_to_pages set content_page = :content_page, boxes_group = :boxes_group, sort_order = :sort_order, page_specific = :page_specific where id = :id');
        $Qlayout->bindInt(':id', $id);
      } else {
        $Qlayout = $osC_Database->query('insert into :table_templates_boxes_to_pages (templates_boxes_id, templates_id, content_page, boxes_group, sort_order, page_specific) values (:templates_boxes_id, :templates_id, :content_page, :boxes_group, :sort_order, :page_specific)');
        $Qlayout->bindInt(':templates_boxes_id', $data['box']);
        $Qlayout->bindInt(':templates_id', $link[0]);
      }

      $Qlayout->bindTable(':table_templates_boxes_to_pages', TABLE_TEMPLATES_BOXES_TO_PAGES);
      $Qlayout->bindValue(':content_page', $link[1]);
      $Qlayout->bindValue(':boxes_group', $data['group']);
      $Qlayout->bindInt(':sort_order', $data['sort_order']);
      $Qlayout->bindInt(':page_specific', ($data['page_specific'] === true) ? '1' : '0');
      $Qlayout->setLogging($_SESSION['module'], $id);
      $Qlayout->execute();

      if ( !$osC_Database->isError() ) {
        osC_Cache::clear('templates_' . $set . '_layout');

        return true;
      }

      return false;
    }
    
    function _delete($id, $set) {
      global $osC_Database;

      $Qdel = $osC_Database->query('delete from :table_templates_boxes_to_pages where id = :id');
      $Qdel->bindTable(':table_templates_boxes_to_pages', TABLE_TEMPLATES_BOXES_TO_PAGES);
      $Qdel->bindInt(':id', $id);
      $Qdel->setLogging($_SESSION['module'], $id);
      $Qdel->execute();

      if ( !$osC_Database->isError() ) {
        osC_Cache::clear('templates_' . $set . '_layout');

        return true;
      }

      return false;
    }
  }
  
?>