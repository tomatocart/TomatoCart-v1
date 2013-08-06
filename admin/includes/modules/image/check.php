<?php
/*
  $Id: check.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  include('includes/classes/image.php');
  
  class osC_Image_Admin_check extends osC_Image_Admin {

// Private variables

    var $_code = 'check';

// Class constructor

    function osC_Image_Admin_check() {
      global $osC_Language;

      parent::osC_Image_Admin();

      $osC_Language->loadIniFile('modules/image/check.php');

      $this->_title = $osC_Language->get('images_check_title');
    }

// Private methods

    function _setHeader() {
      global $osC_Language;

      $this->_header = array($osC_Language->get('images_check_table_heading_groups'),
                             $osC_Language->get('images_check_table_heading_results'));
    }

    function _setData() {
      global $osC_Database;

      $counter = array();

      $Qimages = $osC_Database->query('select image from :table_products_images');
      $Qimages->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
      $Qimages->execute();

      while ($Qimages->next()) {
        foreach ($this->_groups as $group) {
          if (!isset($counter[$group['id']]['records'])) {
            $counter[$group['id']]['records'] = 0;
          }

          $counter[$group['id']]['records']++;

          if (file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . 'products/' . $group['code'] . '/' . $Qimages->value('image'))) {
            if (!isset($counter[$group['id']]['existing'])) {
              $counter[$group['id']]['existing'] = 0;
            }

            $counter[$group['id']]['existing']++;
          }
        }
      }

      foreach ($counter as $key => $value) {
        $this->_data[] = array($this->_groups[$key]['title'],
                               $value['existing'] . ' / ' . $value['records']);
      }
    }
  }
?>
