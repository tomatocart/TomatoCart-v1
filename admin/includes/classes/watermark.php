<?php
/*
  $Id: watermark.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Watermark_Admin {
    function _process($type, $image_group, $position, $opacity, $watermark = true) {
      global $osC_Database;
      
      if ($type == 'products') {
        $Qimages = $osC_Database->query('select image from :table_products_images');
        $Qimages->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
      } else {
        $Qimages = $osC_Database->query('select articles_image as image from :table_articles');
        $Qimages->bindTable(':table_articles', TABLE_ARTICLES);
      }
      
      $Qimages->execute();
      
      $osC_Image = new osC_Image_Admin();
      while ($Qimages->next()) {
        $image = $Qimages->value('image');

        if ( $watermark === true ) {
          $original_image = DIR_FS_CATALOG . DIR_WS_IMAGES . $type . '/originals/' . $image;
          $original_watermarked_image = DIR_FS_CATALOG . DIR_WS_IMAGES . $type . '/originals/' . 'watermark_' . $image;
        
          toc_draw_watermark($original_image, $original_watermarked_image, DIR_FS_CATALOG . DIR_WS_IMAGES . WATERMARK_FILE_NAME, $position, $opacity);
        }
        
        foreach ($osC_Image->getGroups() as $group) {
          if ($group['id'] == $image_group) {
            $osC_Image->resize($image, $group['id'], $type, $watermark);
          }
        }
      }
      
      return true;
    }  
  
    function processWatermark($type, $image_group, $position, $opacity) {
      global $osC_Database;
      
      $error = false;
    
      $data = array('watermark_' . $type . '_' . $image_group . '_' . 'POSITION' => $position,
                    'watermark_' . $type . '_' . $image_group . '_' . 'OPACITY'  => $opacity);
      
      foreach ( $data as $key => $value) {
        $key = strtoupper($key);
        
        if( defined($key) ) {
          $Qconfiguration = $osC_Database->query('update :table_configuration set configuration_value = :configuration_value, last_modified = now() where configuration_key = :configuration_key');
        } else {
          $Qconfiguration = $osC_Database->query('insert into :table_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values (:configuration_title, :configuration_key, :configuration_value, :configuration_description, 6, 0, now())');
          $Qconfiguration->bindValue(':configuration_title', implode(' ', array_map('strtolower', explode('_', $key))));
          $Qconfiguration->bindValue(':configuration_description', implode(' ', array_map('strtolower', explode('_', $key))));
          
          define($key, $value);
        }
        
        $Qconfiguration->bindTable(':table_configuration', TABLE_CONFIGURATION);
        $Qconfiguration->bindValue(':configuration_value', $value);
        $Qconfiguration->bindValue(':configuration_key', $key);
        $Qconfiguration->execute();
        
        if ($osC_Database->isError()) {
          $error = true;
          break;
        } 
      }
      
      if ($error === false) {
        osC_Cache::clear('configuration');
        
        self::_process($type, $image_group, $position, $opacity);
        
        return true;
      }
    }
    
    function delete($type, $group, $position, $opacity) {
      global $osC_Database;
      
      $error = false;
    
      $data = array('watermark_' . $type . '_' . $image_group . '_' . 'POSITION' => $position,
                    'watermark_' . $type . '_' . $image_group . '_' . 'OPACITY'  => $opacity);
      
      foreach($data as $key => $value) {
        $key = strtoupper($key);
        
        if ( defined($key) ) {
          $Qdelete = $osC_Database->query('delete from :table_configuration where configuration_key = :configuration_key');
          $Qdelete->bindTable(':table_configuration', TABLE_CONFIGURATION);
          $Qdelete->bindValue(':configuration_key', $key);
          $Qdelete->execute();
          
          if ($osC_Database->isError()) {
            $error = true;
            
            break;
          }  
        }
      }

      if ($error === false) {
        osC_Cache::clear('configuration');
      
        self::_process($type, $group, null, null, false);
        
        return true;
      }

      return false;
    }
    
    function save($image_name) {
      global $osC_Database;
    
      $image = new upload($image_name, realpath('../' . DIR_WS_IMAGES));
        
      if ( $image->exists() && $image->parse() ) {
        if ($image->save()) {
          if (defined('WATERMARK_FILE_NAME')) {
            @unlink('../' . DIR_WS_IMAGES . WATERMARK_FILE_NAME);
            
            $Qupdate = $osC_Database->query('update :table_configuration set configuration_value = :configuration_value, last_modified = now() where configuration_key = \'WATERMARK_FILE_NAME\'');
            $Qupdate->bindTable(':table_configuration', TABLE_CONFIGURATION);
            $Qupdate->bindValue(':configuration_value', $image->filename);
            $Qupdate->execute();
          } else {
            $Qinsert = $osC_Database->query("insert into :table_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('watermark file name', 'WATERMARK_FILE_NAME', :configuration_value, 'The name of the watermark file', 6, 0, now());");
            $Qinsert->bindTable(':table_configuration', TABLE_CONFIGURATION);
            $Qinsert->bindValue(':configuration_value', $image->filename);
            $Qinsert->execute();
          }
          
          if (!$osC_Database->isError()) {
            $response = array('success' => true, 'image' => '../' . DIR_WS_IMAGES . $_FILES['watermark_image']['name']);
            
            osC_Cache::clear('configuration');
          } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
          }
          
          return $response;
        }
      }
    }
    
    function deleteWatermarkImage($image_name) {
      global $osC_Database;
      
      $error = false;
      
      if (!empty($image_name)) {
        if (file_exists('../' . DIR_WS_IMAGES . $image_name)) {
          @unlink('../' . DIR_WS_IMAGES . $image_name);
        } 
        
        $Qupdate = $osC_Database->query('update :table_configuration set configuration_value = :configuration_value, last_modified = now() where configuration_key = :configuration_key');
        $Qupdate->bindTable(':table_configuration', TABLE_CONFIGURATION);
        $Qupdate->bindValue(':configuration_value', '');
        $Qupdate->bindValue(':configuration_key', 'WATERMARK_FILE_NAME');
        $Qupdate->execute();
      }
      
      if (!$osC_Database->isError()) {
        osC_Cache::clear('configuration');
        
        $error = true;
      }
      
      return $error;
    }
  }
?>