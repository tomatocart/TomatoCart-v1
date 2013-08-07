<?php
/*
  $Id: templates_modules.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Templates_modules extends osC_Access {
    var $_module = 'templates_modules',
        $_group = 'templates',
        $_icon = 'modules.png',
        $_title,
        $_sort_order = 200;

    function osC_Access_Templates_modules() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_templates_modules_title');

      $this->_subgroups = array(array('iconCls' => 'icon-templates-modules-boxes-win',
                                      'shortcutIconCls' => 'icon-templates-modules-boxes-shortcut',
                                      'title' => $osC_Language->get('access_templates_modules_boxes_title'),
                                      'identifier' => 'templates_modules-boxes-win',
                                      'params' => array('set' => 'boxes')),
                                array('iconCls' => 'icon-templates-modules-content-win',
                                      'shortcutIconCls' => 'icon-templates-modules-content-shortcut',
                                      'title' => $osC_Language->get('access_templates_modules_content_title'),
                                      'identifier' => 'templates_modules-content-win',
                                      'params' => array('set' => 'content')));
    }
  }
?>
