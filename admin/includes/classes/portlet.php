<?php
  /*
    $Id: portlet.php $
    TomatoCart Open Source Shopping Cart Solutions
    http://www.tomatocart.com
  
    Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License v2 (1991)
    as published by the Free Software Foundation.
  */
  
  class toC_Portlet {
    
    var $_code,
        $_title,
        $_view;

    function encodeArray($config) {
      if (is_array($config)) {
        $options = array();
        foreach($config as $key => $value) {
          if (is_array($value)) {
            $options[] = '"' . $key . '": ' . $this->encodeArray($value);
          } else if (gettype($value) == 'boolean') {
            $options[] = '"' . $key . '": ' . (($value == true) ? 'true' : 'false');
          } else {
            $options[] = '"' . $key . '": ' . $value;
          }
        }
        
        return '{' . implode(', ', $options) . '}';
      } else {
        return '{}';
      }
    }

    function getPortlets() {
      global $osC_Language;
      
      $osC_DirectoryListing = new osC_DirectoryListing('includes/modules/portlets');
      $osC_DirectoryListing->setIncludeDirectories(false);
    
      $portlets = array();
      
      foreach ($osC_DirectoryListing->getFiles() as $file) {
        require_once('includes/modules/portlets/'.$file['name']);
        $class = substr($file['name'], 0, strrpos($file['name'], '.'));
        
        $osC_Language->loadIniFile('modules/portlets/' . $file['name']);
        
        if ( class_exists('toC_Portlet_' . $class ) ) {
          $module_class = 'toC_Portlet_' . $class;
          $module = new $module_class();
        
          $portlets[] = array('code' => $module->getCode(),
                              'title' => $module->getTitle());
        }
      }
      
      return $portlets;
    }

    function getCode() {
      return $this->_code;
    }

    function getTitle() {
      return $this->_title;
    }
    
    function renderView() {
    
    }

    function renderData() {
    
    }
  }
?>