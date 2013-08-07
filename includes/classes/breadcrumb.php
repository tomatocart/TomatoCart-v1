<?php
/*
  $Id: breadcrumb.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class breadcrumb {
    var $_trail;

    function breadcrumb() {
      $this->reset();
    }

    function reset() {
      $this->_trail = array();
    }

    function add($title, $link = '') {
      $this->_trail[] = array('title' => $title, 'link' => $link);
    }

    function trail($separator = ' - ') {
      $trail_string = '';

      for ($i=0, $n=sizeof($this->_trail); $i<$n; $i++) {
        if (isset($this->_trail[$i]['link']) && !empty($this->_trail[$i]['link'])) {
          $trail_string .= osc_link_object($this->_trail[$i]['link'], $this->_trail[$i]['title']);
        } else {
          $trail_string .= $this->_trail[$i]['title'];
        }

        if (($i+1) < $n) $trail_string .= $separator;
      }

      return $trail_string;
    }
  }
?>
