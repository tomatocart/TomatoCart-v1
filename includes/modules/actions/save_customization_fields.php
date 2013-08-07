<?php
/*
  $Id: save_customization_fields.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require_once('includes/classes/upload.php');

  class osC_Actions_save_customization_fields {
    function execute() {
      global $osC_Session, $osC_Product, $toC_Customization_Fields, $osC_Language, $messageStack;
    
      if (!isset($osC_Product)) {
        $id = false;

        foreach ($_GET as $key => $value) {
          if ( (ereg('^[0-9]+(#?([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)*$', $key) || ereg('^[a-zA-Z0-9 -_]*$', $key)) && ($key != $osC_Session->getName()) ) {
            $id = $key;
          }

          break;
        }

        if (($id !== false) && osC_Product::checkEntry($id)) {
          $osC_Product = new osC_Product($id);
        }
      }

      if (isset($osC_Product)) {
        $errors = array();
        $data = array();
        
        $customizations = $osC_Product->getCustomizations();
        foreach ($customizations as $field) {
          $fields_id = $field['customization_fields_id'];
          
          if ($field['type'] == CUSTOMIZATION_FIELD_TYPE_INPUT_TEXT) {
            $value = isset($_POST['customizations'][$fields_id]) ? $_POST['customizations'][$fields_id] : null;
            
            if ( $field['is_required'] && ($value == null) ) {
              $messageStack->add_session('products_customizations', sprintf($osC_Language->get('error_customization_field_must_be_specified'), $field['name']), 'error');
            } else if ( $value != null ) {
              $data[$fields_id] = array('customization_fields_id' => $field['customization_fields_id'],
                                        'customization_fields_name' => $field['name'],
                                        'customization_type' => CUSTOMIZATION_FIELD_TYPE_INPUT_TEXT,
                                        'customization_value' => $value);
            }
          } else {
            $file = new upload('customizations_' . $fields_id, DIR_FS_CACHE . '/products_customizations/');
            
            if ($field['is_required'] && !$file->exists() && (!$toC_Customization_Fields->hasCustomizationField($osC_Product->getID(), $fields_id))) {
              $messageStack->add_session('products', sprintf($osC_Language->get('error_customization_field_must_be_specified'), $field['name']), 'error');
            } else if ( $file->exists() ) {
              if ($file->parse() && $file->save()) {
                $filename = $file->filename;
                $cache_filename = md5($filename . time());
                rename(DIR_FS_CACHE . '/products_customizations/' . $filename, DIR_FS_CACHE . '/products_customizations/' . $cache_filename);
              
                $data[$fields_id] = array('customization_fields_id' => $field['customization_fields_id'],
                                          'customization_fields_name' => $field['name'],
                                          'customization_type' => CUSTOMIZATION_FIELD_TYPE_INPUT_FILE,
                                          'customization_value' => $filename,
                                          'cache_filename' => $cache_filename);
              } else {
                $messageStack->add_session('products_customizations', $file->getLastError(), 'error');
              }
            }
          }
        }
        
        //var_dump($data);exit;
        if ($messageStack->size('products_customizations') === 0) {
          $toC_Customization_Fields->set($osC_Product->getID(), $data);
        }
      }

      osc_redirect(osc_href_link(FILENAME_PRODUCTS, $osC_Product->getID()));  
    }
  }
?>