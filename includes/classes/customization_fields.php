<?php
/*
  $Id: customization_fields.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

	class toC_Customization_Fields {
	  var $_contents = array();
	      
	  function toC_Customization_Fields() {
	    if (!isset($_SESSION['toC_Customization_Fields_data'])) {
	      $_SESSION['toC_Customization_Fields_data'] = array('contents' => array());
	    }
	    
	    $this->_contents =& $_SESSION['toC_Customization_Fields_data']['contents'];
	  }
	  
	  function exists($products_id) {
	    return (isset($this->_contents[$products_id]) && !empty($this->_contents[$products_id]));
	  }
	  
	  function hasContents() {
	    return !empty($this->_contents);
	  }

	  function reset() {
	    $this->_contents = array();
    }
    
    function get($products_id) {
      if ( $this->exists($products_id) ) {
        return $this->_contents[$products_id];
      }
      
      return false;
    }

	  function set($products_id, $data) {
      if ( $this->exists($products_id) ) {
        foreach ($this->get($products_id) as $customization_fields_id => $field) {
          if ($field['customization_type'] == CUSTOMIZATION_FIELD_TYPE_INPUT_FILE) {
            //if user upload a new file, then overwrite the old one
            if ( isset($data[$customization_fields_id]) ) {
              if ( file_exists(DIR_FS_CACHE . '/products_customizations/' . $field['cache_filename']) ) {
                @unlink(DIR_FS_CACHE . '/products_customizations/' . $field['cache_filename']);
              }
              
              $this->_contents[$products_id][$customization_fields_id] = $data[$customization_fields_id];
            }
          } else {
            $this->_contents[$products_id][$customization_fields_id] = $data[$customization_fields_id];
          }
        }
      } else {
        $this->_contents[$products_id] = $data;
      }
    }
    
    function remove($products_id) {
      if ( $this->exists($products_id) ) {
        unset($this->_contents[$products_id]);
      }
    }
    
    function hasCustomizationField ($products_id, $customization_fields_id) {
      if ( $this->exists($products_id) ) {
        return (isset($this->_contents[$products_id][$customization_fields_id]) && !empty($this->_contents[$products_id][$customization_fields_id]));
      }
      
      return false;
    }
    
    function getCustomizationField($products_id, $customization_fields_id) {
      if ($this->hasCustomizationField($products_id, $customization_fields_id)) {
        return $this->_contents[$products_id][$customization_fields_id];
      }
      
      return false;
    }
  }
?>