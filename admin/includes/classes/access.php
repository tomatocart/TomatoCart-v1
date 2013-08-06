<?php
/*
  $Id: access.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access {
    var $_group = 'misc',
        $_icon = 'configure.png',
        $_title,
        $_sort_order = 0,
        $_subgroups;

    function getUserLevels($id) {
      global $osC_Database;

      $modules = array();

      $Qaccess = $osC_Database->query('select module from :table_administrators_access where administrators_id = :administrators_id');
      $Qaccess->bindTable(':table_administrators_access', TABLE_ADMINISTRATORS_ACCESS);
      $Qaccess->bindInt(':administrators_id', $id);
      $Qaccess->execute();

      while ( $Qaccess->next() ) {
        $modules[] = $Qaccess->value('module');
      }

      if ( in_array('*', $modules) ) {
        $modules = array();

        $osC_DirectoryListing = new osC_DirectoryListing('includes/modules/access');
        $osC_DirectoryListing->setIncludeDirectories(false);

        foreach ($osC_DirectoryListing->getFiles() as $file) {
          $modules[] = substr($file['name'], 0, strrpos($file['name'], '.'));
        }
      }

      return $modules;
    }

    function getLevels() {
      global $osC_Language;

      $access = array();

      foreach ( $_SESSION['admin']['access'] as $module ) {
        if ( file_exists('includes/modules/access/' . $module . '.php') ) {
          $module_class = 'osC_Access_' . ucfirst($module);

          if ( !class_exists( $module_class ) ) {
            $osC_Language->loadIniFile('modules/access/' . $module . '.php');
            include('includes/modules/access/' . $module . '.php');
          }

          $module_class = new $module_class();

          $data = array('module' => $module,
                        'icon' => $module_class->getIcon(),
                        'title' => $module_class->getTitle(),
                        'subgroups' => $module_class->getSubGroups());

          if ( !isset( $access[$module_class->getGroup()][$module_class->getSortOrder()] ) ) {
            $access[$module_class->getGroup()][$module_class->getSortOrder()] = $data;
          } else {
            $access[$module_class->getGroup()][] = $data;
          }
        }
      }

      ksort($access);
      foreach ( $access as $group => $links )
        ksort($access[$group]);

      return $access;
    }

    function getModule() {
      return $this->_module;
    }

    function getGroup() {
      return $this->_group;
    }

    /**
     * Get the Group which the Module belongs to.
     *
     * @param $module_name is the module name
     * @return String of the group.
     */
    function getModuleGroup($module_name){
        $group = '';
        foreach ( $_SESSION['admin']['access'] as $module ) {
            if( $module_name == $module ){
                $module_class = 'osC_Access_' . ucfirst($module);
                if ( !class_exists( $module_class ) ) {
                    $osC_Language->loadIniFile('modules/access/' . $module . '.php');
                    include('includes/modules/access/' . $module . '.php');
                }
                $module_class = new $module_class();
                $group = $module_class->getGroup();
            }
        }
        return $group;
    }

    function getGroupTitle($group) {
      global $osC_Language;

      if ( !$osC_Language->isDefined('access_group_' . $group . '_title') ) {
        $osC_Language->loadIniFile( 'modules/access/groups/' . $group . '.php' );
      }

      return $osC_Language->get('access_group_' . $group . '_title');
    }

    function getIcon() {
      return $this->_icon;
    }

    function getTitle() {
      return $this->_title;
    }

    function getSortOrder() {
      return $this->_sort_order;
    }

    function getSubGroups() {
      return $this->_subgroups;
    }

    function hasAccess($module = null) {
      if ( empty($module) ) {
        $module = $this->_module;
      }

      return !file_exists( 'includes/modules/access/' . $module . '.php' ) || in_array( $module, $_SESSION['admin']['access'] );
    }
  }
?>
