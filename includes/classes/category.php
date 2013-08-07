<?php
/*
  $Id: category.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Category {
    var $_data = array();

    function osC_Category($id) {
      global $osC_CategoryTree;

      if ($osC_CategoryTree->exists($id)) {
        $this->_data = $osC_CategoryTree->getData($id);
      }
    }

    function getID() {
      return $this->_data['id'];
    }

    function getTitle() {
      return $this->_data['name'];
    }

    function getImage() {
      return $this->_data['image'];
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

    function hasParent() {
      if ($this->_data['parent_id'] > 0) {
        return true;
      }

      return false;
    }

    function getParent() {
      return $this->_data['parent_id'];
    }

    function getPath() {
      global $osC_CategoryTree;

      return $osC_CategoryTree->buildBreadcrumb($this->_data['id']);
    }
  }
?>
