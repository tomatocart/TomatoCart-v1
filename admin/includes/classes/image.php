<?php
/*
  $Id: image.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('../includes/classes/image.php');

  class osC_Image_Admin extends osC_Image {

// Private variables

    var $_title, $_header, $_data = array();

    var $_has_parameters = false;

// Class constructor

    function osC_Image_Admin() {
      parent::osC_Image();
    }

// Public methods

    function &getGroups() {
      return $this->_groups;
    }

    function resize($image, $group_id, $type = 'products', $watermark = true) {
      return $this->resizeWithGD($image, $group_id, $type, $watermark);
    }

    function hasGDSupport() {
      if ( imagetypes() & ( IMG_JPG || IMG_GIF || IMG_PNG ) ) {
        return true;
      }

      return false;
    }

    function resizeWithGD($image, $group_id, $type, $watermark = true) {
      if (!file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . $type . '/' . $this->_groups[$group_id]['code'])) {
        mkdir(DIR_FS_CATALOG . DIR_WS_IMAGES . $type . '/' . $this->_groups[$group_id]['code'], 0777);
      }
      
      $original_image = DIR_FS_CATALOG . DIR_WS_IMAGES . $type . '/' . $this->_groups[1]['code'] . '/' . $image;
      $dest_image = DIR_FS_CATALOG . DIR_WS_IMAGES . $type . '/' . $this->_groups[$group_id]['code'] . '/' . $image;
      
      if (file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . $type . '/' . $this->_groups[1]['code'] . '/' . $image)) {
        //watermark
        if ($watermark == true) {
          if ( defined('WATERMARK_FILE_NAME') && @file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . WATERMARK_FILE_NAME) ) {
            $opacity_name = strtoupper('WATERMARK_' . $type . '_' . $group_id . '_OPACITY');
            $position_name = strtoupper('WATERMARK_' . $type . '_' . $group_id . '_POSITION');
            
            if ( defined($opacity_name) && defined($position_name) ) {
              $original_watermarked_image = DIR_FS_CATALOG . DIR_WS_IMAGES . $type . '/' . $this->_groups[1]['code'] . '/' . 'watermark_' . $image;
              
              if (!file_exists($original_watermarked_image)) {
                toc_draw_watermark($original_image, $original_watermarked_image, DIR_FS_CATALOG . DIR_WS_IMAGES . WATERMARK_FILE_NAME, constant($position_name), constant($opacity_name));
              } 
              
              $original_image = $original_watermarked_image;
            } 
          }
        }
      
        return osc_gd_resize($original_image, $dest_image, $this->_groups[$group_id]['size_width'], $this->_groups[$group_id]['size_height'], $this->_groups[$group_id]['force_size'] == '1');
      }
    }

    function getModuleCode() {
      return $this->_code;
    }

    function &getTitle() {
      return $this->_title;
    }

    function &getHeader() {
      return $this->_header;
    }

    function &getData() {
      return $this->_data;
    }

    function activate() {
      $this->_setHeader();
      $this->_setData();
    }

    function hasParameters() {
      return $this->_has_parameters;
    }

    function existsInGroup($id, $group_id) {
      global $osC_Database;

      $Qimage = $osC_Database->query('select image from :table_products_images where id = :id');
      $Qimage->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
      $Qimage->bindInt(':id', $id);
      $Qimage->execute();

      return file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . 'products/' . $this->_groups[$group_id]['code'] . '/' . $Qimage->value('image'));
    }

    function delete($id) {
      global $osC_Database;

      $Qimage = $osC_Database->query('select image from :table_products_images where id = :id');
      $Qimage->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
      $Qimage->bindInt(':id', $id);
      $Qimage->execute();

      foreach ($this->_groups as $group) {
        @unlink(DIR_FS_CATALOG . DIR_WS_IMAGES . 'products/' . $group['code'] . '/' . $Qimage->value('image'));
      }
      
      //remove watermark file
      if (file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . 'products/' . $this->_groups[1]['code'] . '/watermark_' . $Qimage->value('image'))) {
        @unlink(DIR_FS_CATALOG . DIR_WS_IMAGES . 'products/' . $this->_groups[1]['code'] . '/watermark_' . $Qimage->value('image'));
      }

      $Qdel = $osC_Database->query('delete from :table_products_images where id = :id');
      $Qdel->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
      $Qdel->bindInt(':id', $id);
      $Qdel->execute();

      return ($Qdel->affectedRows() === 1);
    }

    function setAsDefault($id) {
      global $osC_Database;

      $Qimage = $osC_Database->query('select products_id from :table_products_images where id = :id');
      $Qimage->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
      $Qimage->bindInt(':id', $id);
      $Qimage->execute();

      if ($Qimage->numberOfRows() === 1) {
        $Qupdate = $osC_Database->query('update :table_products_images set default_flag = :default_flag where products_id = :products_id and default_flag = :default_flag');
        $Qupdate->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
        $Qupdate->bindInt(':default_flag', 0);
        $Qupdate->bindInt(':products_id', $Qimage->valueInt('products_id'));
        $Qupdate->bindInt(':default_flag', 1);
        $Qupdate->execute();

        $Qupdate = $osC_Database->query('update :table_products_images set default_flag = :default_flag where id = :id');
        $Qupdate->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
        $Qupdate->bindInt(':default_flag', 1);
        $Qupdate->bindInt(':id', $id);
        $Qupdate->execute();

        return ($Qupdate->affectedRows() === 1);
      }
    }

    function reorderImages($images_array) {
      global $osC_Database;

      $counter = 0;

      foreach ($images_array as $id) {
        $counter++;

        $Qupdate = $osC_Database->query('update :table_products_images set sort_order = :sort_order where id = :id');
        $Qupdate->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
        $Qupdate->bindInt(':sort_order', $counter);
        $Qupdate->bindInt(':id', $id);
        $Qupdate->execute();
      }

      return ($counter > 0);
    }

    function show($image, $title, $parameters = '', $group = '', $type = 'products') {
      if (empty($group) || !$this->exists($group)) {
        $group = $this->getCode(DEFAULT_IMAGE_GROUP_ID);
      }

      $group_id = $this->getID($group);

      $width = $height = '';

      if ( ($this->_groups[$group_id]['force_size'] == '1') || empty($image) ) {
        $width = $this->_groups[$group_id]['size_width'];
        $height = $this->_groups[$group_id]['size_height'];
      }

      if (empty($image)) {
        $image = 'pixel_trans.gif';
      } else {
        $image = $type . '/' . $this->_groups[$group_id]['code'] . '/' . $image;
      }

      return osc_image('../' . DIR_WS_IMAGES . $image, $title, $width, $height, $parameters);
    }

    function deleteArticlesImage($id) {
      global $osC_Database;

      $Qimage = $osC_Database->query('select articles_image from :table_articles where articles_id = :articles_id');
      $Qimage->bindTable(':table_articles', TABLE_ARTICLES);
      $Qimage->bindInt(':articles_id', $id);
      $Qimage->execute();

      foreach ($this->_groups as $group) {
        @unlink(DIR_FS_CATALOG . DIR_WS_IMAGES . 'articles/' . $group['code'] . '/' . $Qimage->value('articles_image'));
      }
    }
  }
?>
