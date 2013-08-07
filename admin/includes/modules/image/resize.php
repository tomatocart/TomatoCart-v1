<?php
/*
  $Id: resize.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/


  class osC_Image_Admin_resize extends osC_Image_Admin {

// Private variables

    var $_code = 'resize',
        $_has_parameters = true;

// Class constructor

    function osC_Image_Admin_resize() {
      global $osC_Language;

      parent::osC_Image_Admin();

      $osC_Language->loadIniFile('modules/image/resize.php');

      $this->_title = $osC_Language->get('images_resize_title');
    }

// Public methods

    function getParameters() {
      global $osC_Language;

      $groups = array();
      $groups_ids = array();

      foreach ($this->_groups as $group) {
        if ($group['id'] != '1') {
          $groups[] = array('text' => $group['title'],
                            'id' => $group['id']);

          $groups_ids[] = $group['id'];
        }
      }

      return array(array('key' => $osC_Language->get('images_resize_field_groups'),
                         'field' => osc_draw_pull_down_menu('groups[]', $groups, $groups_ids, 'multiple="multiple" size="5"')),
                   array('key' => $osC_Language->get('images_resize_field_overwrite_images'),
                         'field' => osc_draw_checkbox_field('overwrite', '1')));
    }
    
    function getDrawParameters() {
      global $osC_Language;

      $groups = array();

      foreach ($this->_groups as $group) {
        if ($group['id'] != '1') {
          $groups[] = array('text' => $group['title'],
                            'id' => $group['id']);
        }
      }
      
      return $groups;
    }

// Private methods

    function _setHeader() {
      global $osC_Language;

      $this->_header = array($osC_Language->get('images_resize_table_heading_groups'),
                             $osC_Language->get('images_resize_table_heading_total_resized'));
    }

    function _setData() {
      global $osC_Database, $osC_Language;

      $overwrite = false;

      if (isset($_POST['overwrite']) && ($_POST['overwrite'] == '1')) {
        $overwrite = true;
      }

      if (!isset($_POST['groups']) || !is_array($_POST['groups'])) {
        return false;
      }

      $Qoriginals = $osC_Database->query('select image from :table_products_images');
      $Qoriginals->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
      $Qoriginals->execute();

      $counter = array();
      
      if(stripos($_POST['groups'][0], ',')) {
        $_POST['groups'] = explode(',', $_POST['groups'][0]);
      }

      while ($Qoriginals->next()) {
        foreach ($this->_groups as $group) {
          if ( ($group['id'] != '1') && in_array($group['id'], $_POST['groups'])) {
            if (!isset($counter[$group['id']])) {
              $counter[$group['id']] = 0;
            }

            if ( ($overwrite === true) || !file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . 'products/' . $group['code'] . '/' . $Qoriginals->value('image')) ) {
              $this->resize($Qoriginals->value('image'), $group['id']);

              $counter[$group['id']]++;
            }
          }
        }
      }

      foreach ($counter as $key => $value) {
        $this->_data[] = array($this->_groups[$key]['title'],
                               $value);
      }
    }
  }
?>
