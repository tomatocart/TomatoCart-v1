<?php
/*
  $Id: configuration.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Configuration extends osC_Access {
    var $_module = 'configuration',
        $_group = 'configuration',
        $_icon = 'configure.png',
        $_title,
        $_sort_order = 300;

    function osC_Access_Configuration() {
      global $osC_Database, $osC_Language;

      $this->_title = $osC_Language->get('access_configuration_title');

      $this->_subgroups = array();

      $Qgroups = $osC_Database->query('select configuration_group_id, configuration_group_title from :table_configuration_group where visible = 1 order by sort_order, configuration_group_title');
      $Qgroups->bindTable(':table_configuration_group', TABLE_CONFIGURATION_GROUP);
      $Qgroups->execute();

      while ($Qgroups->next()) {
        $title = str_replace(' ', '_', strtolower($Qgroups->value('configuration_group_title')));
        $title = str_replace('/', '_', $title);
        $title = 'configuration_' . $title . '_title'; 
      
        $this->_subgroups[] = array('iconCls' => 'icon-configuration-win',
                                    'shortcutIconCls' => 'icon-configuration-shortcut',
                                    'title' => $osC_Language->get($title),
                                    'identifier' => 'configuration-' . $Qgroups->valueInt('configuration_group_id') . '-win',
                                    'params' => array('gID' => $Qgroups->valueInt('configuration_group_id')));
      }
    }
  }
?>
