<?php
/*
  $Id: products.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Products extends osC_Access {
    var $_module = 'products',
        $_group = 'content',
        $_icon = 'products.png',
        $_title,
        $_sort_order = 200;

    function osC_Access_Products() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_products_title');

      $this->_subgroups = array(array('iconCls' => 'icon-products-win',
                                      'shortcutIconCls' => 'icon-products-shortcut',
                                      'title' => $osC_Language->get('access_products_title'),
                                      'identifier' => 'products-win'), 
                                array('iconCls' => 'icon-new-products-win',
                                      'shortcutIconCls' => 'icon-new-products-shortcut',
                                      'title' => $osC_Language->get('access_products_new_title'),
                                      'identifier' => 'products-dialog-win'),
                                array('iconCls' => 'icon-products_attachments-win',
                                      'shortcutIconCls' => 'icon-products_attachments-shortcut',
                                      'title' => $osC_Language->get('access_products_attachments_title'),
                                      'identifier' => 'products_attachments-win'));
    }
  }
?>
