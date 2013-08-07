<?php
  /*
    $Id: gadget.php $
    TomatoCart Open Source Shopping Cart Solutions
    http://www.tomatocart.com
  
    Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd
  
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License v2 (1991)
    as published by the Free Software Foundation.
  */
  
  class toC_Gadget {
    var $_code,
        $_title,
        $_type,
        $_icon,
        $_height,
        $_autorun = false,
        $_interval = 30000,
        $_path,
        
        $_file = '',
        $_description;
            
    function getGadgets() {
      global $osC_Language;
      
      $osC_DirectoryListing = new osC_DirectoryListing('includes/modules/gadgets');
      $osC_DirectoryListing->setIncludeDirectories(false);
    
      $gadgets = array();
      
      foreach ($osC_DirectoryListing->getFiles() as $file) {
        require_once('includes/modules/gadgets/'.$file['name']);
        $class = substr($file['name'], 0, strrpos($file['name'], '.'));
        
        $osC_Language->loadIniFile('modules/gadgets/' . $file['name']);
        
        if ( class_exists('toC_Gadget_' . $class ) ) {
          $module_class = 'toC_Gadget_' . $class;
          $module = new $module_class();
        
          $gadgets[] = array('code' => $module->getCode(),
                             'title' => $module->getTitle());
        }
      }
      
      return $gadgets;    
    }
    
    function getCode() {
      return $this->_code;    
    }
    
    function getTitle() {
      return $this->_title;    
    }
    
    function getType() {
      return $this->_type;    
    }
    
    function getIcon() {
      return $this->_icon;
    }
    
    function getHeight() {
      return $this->_height;
    }
    
    function getAutorun() {
      return $this->_autorun;
    }
    
    function getInterval() {
      return $this->_interval;
    }
    
    function getPath() {
      return $this->_path;
    }
    
    function getDescription() {
      return $this->_description;
    }
    
    function getFile() {
      return $this->_file;
    }
    
    function renderView() {
        
    }
    
    function renderData() {
          
    }
    
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
    
    function getDateOrTime($date) {
      $day_start = mktime(0, 0, 0);
      $day_end = mktime(0, 0, 0, date('m'), date('d') + 1);
      
      if ($date > $day_start && $date < $day_end) {
        $date = date('H:i', $date);
      } else {
        $date = osC_DateTime::getShort(osC_DateTime::fromUnixTimestamp($date));
      }
      
      return $date;
    }
  }
?>

  