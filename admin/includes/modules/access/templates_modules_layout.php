<?php
/*
  $Id: templates_modules_layout.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Templates_modules_layout extends osC_Access {
    var $_module = 'templates_modules_layout',
        $_group = 'templates',
        $_icon = 'windows.png',
        $_title,
        $_sort_order = 300;

    function osC_Access_Templates_modules_layout() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_templates_modules_layout_title');

      $this->_subgroups = array(array('iconCls' => 'icon-templates-modules-layout-boxes-win',
                                      'shortcutIconCls' => 'icon-templates-modules-layout-boxes-shortcut',
                                      'title' => $osC_Language->get('access_templates_modules_layout_boxes_title'),
                                      'identifier' => 'templates_modules_layout-boxes-win',
                                      'params' => array('set' => 'boxes')),
                                array('iconCls' => 'icon-templates-modules-layout-content-win',
                                      'shortcutIconCls' => 'icon-templates-modules-layout-content-shortcut',
                                      'title' => $osC_Language->get('access_templates_modules_layout_content_title'),
                                      'identifier' => 'templates_modules_layout-content-win',
                                      'params' => array('set' => 'content')));
    }
  }
?>
