<?php
/*
  $Id: summary.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2004 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Summary {

/* Private methods */

    var $_title,
        $_title_link,
        $_data;

/* Public methods */

    function getTitle() {
      return $this->_title;
    }

    function getTitleLink() {
      return $this->_title_link;
    }

    function hasTitleLink() {
      if (isset($this->_title_link) && !empty($this->_title_link)) {
        return true;
      }

      return false;
    }

    function getData() {
      return $this->_data;
    }

    function hasData() {
      if (isset($this->_data) && !empty($this->_data)) {
        return true;
      }

      return false;
    }
  }
?>
