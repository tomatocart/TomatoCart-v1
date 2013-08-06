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

  class osC_Image {
    var $_groups;

    function osC_Image() {
      global $osC_Database, $osC_Language;

      $this->_groups = array();

      $Qgroups = $osC_Database->query('select * from :table_products_images_groups where language_id = :language_id');
      $Qgroups->bindTable(':table_products_images_groups', TABLE_PRODUCTS_IMAGES_GROUPS);
      $Qgroups->bindInt(':language_id', $osC_Language->getID());
      $Qgroups->setCache('images_groups-' . $osC_Language->getID());
      $Qgroups->execute();

      while ($Qgroups->next()) {
        $this->_groups[$Qgroups->valueInt('id')] = $Qgroups->toArray();
      }

      $Qgroups->freeResult();
    }

    function getID($code) {
      foreach ($this->_groups as $group) {
        if ($group['code'] == $code) {
          return $group['id'];
        }
      }

      return 0;
    }

    function getCode($id) {
      return $this->_groups[$id]['code'];
    }

    function getWidth($code) {
      return $this->_groups[$this->getID($code)]['size_width'];
    }

    function getHeight($code) {
      return $this->_groups[$this->getID($code)]['size_height'];
    }

    function exists($code) {
      return isset($this->_groups[$this->getID($code)]);
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
        $image = 'no_image.png';
      } else {
        $image = $type . '/' . $this->_groups[$group_id]['code'] . '/' . $image;
      }

      if($type == 'products'){
        $parameters .= 'class="productImage"';
      }

      return osc_image(DIR_WS_IMAGES . $image, $title, $width, $height, $parameters);
    }

    function getImageUrl($image, $group = '', $type = 'products') {
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
        $image = 'no_image.png';
      } else {
        $image = $type . '/' . $this->_groups[$group_id]['code'] . '/' . $image;
      }

      return HTTP_SERVER . DIR_WS_HTTP_CATALOG . DIR_WS_IMAGES . $image;
    }

    function getAddress($image, $group = 'default') {
      $group_id = $this->getID($group);

      return DIR_WS_IMAGES . 'products/' . $this->_groups[$group_id]['code'] . '/' . $image;
    }
  }
?>
