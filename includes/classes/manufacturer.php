<?php
/*
  $Id: manufacturer.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Manufacturer {
    var $_data = array();

    function osC_Manufacturer($id) {
      global $osC_Database, $osC_Language;

      $Qmanufacturer = $osC_Database->query('select m.manufacturers_id as id, m.manufacturers_name as name, m.manufacturers_image as image, mi.manufacturers_page_title as page_title, mi.manufacturers_meta_keywords as meta_keywords, mi.manufacturers_meta_description as meta_description from :table_manufacturers m, :table_manufacturers_info mi where m.manufacturers_id = mi.manufacturers_id and m.manufacturers_id = :manufacturers_id and mi.languages_id = :languages_id');
      $Qmanufacturer->bindTable(':table_manufacturers', TABLE_MANUFACTURERS);
      $Qmanufacturer->bindTable(':table_manufacturers_info', TABLE_MANUFACTURERS_INFO);
      $Qmanufacturer->bindInt(':languages_id', $osC_Language->getID());
      $Qmanufacturer->bindInt(':manufacturers_id', $id);
      $Qmanufacturer->execute();

      if ($Qmanufacturer->numberOfRows() === 1) {
        $this->_data = $Qmanufacturer->toArray();
      }
    }

    function getID() {
      if (isset($this->_data['id'])) {
        return $this->_data['id'];
      }

      return false;
    }

    function getTitle() {
      if (isset($this->_data['name'])) {
        return $this->_data['name'];
      }

      return false;
    }

    function getImage() {
      if (isset($this->_data['image'])) {
        return $this->_data['image'];
      }

      return false;
    }
    
    function getPageTitle() {
      return $this->_data['page_title'];    
    }
    
    function getMetaKeywords() {
      return $this->_data['meta_keywords'];
    }
    
    function getMetaDescription() {
      return $this->_data['meta_description'];
    }
  }
?>
