<?php
/*
  $Id: image_groups.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com
  
  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd
  
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/image_groups.php');
  
  class toC_Json_Image_Groups {
    
    function listImageGroups() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qgroups = $osC_Database->query('select id, title from :table_products_images_groups where language_id = :language_id order by title');
      $Qgroups->bindTable(':table_products_images_groups', TABLE_PRODUCTS_IMAGES_GROUPS);
      $Qgroups->bindInt(':language_id', $osC_Language->getID());
      $Qgroups->setExtBatchLimit($start, $limit);
      $Qgroups->execute();
      
      $records = array();
      while ($Qgroups->next()) {
      	$title = $Qgroups->Value('title');
      	
      	if ($Qgroups->ValueInt('id') == DEFAULT_IMAGE_GROUP_ID) {
      	  $title .= ' (' . $osC_Language->get('default_entry') . ')';
      	}
      	
      	$records[] = array('id' => $Qgroups->ValueInt('id'), 'title' => $title);
      }
      
      $response = array(EXT_JSON_READER_TOTAL => $Qgroups->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);
    }
    
    function loadImageGroup() {
      global $toC_Json, $osC_Database;
      
      $data = osC_ImageGroups_Admin::getData($_REQUEST['image_groups_id']);
      
      $Qgroup = $osC_Database->query('select * from :table_products_images_groups where id = :id');
      $Qgroup->bindTable(':table_products_images_groups', TABLE_PRODUCTS_IMAGES_GROUPS);
      $Qgroup->bindInt(':id', $_REQUEST['image_groups_id']);
      $Qgroup->execute();
      
      while ($Qgroup->next()) {
        $data['title[' . $Qgroup->ValueInt('language_id') . ']'] = $Qgroup->Value('title');
        $data['code'] = $Qgroup->Value('code');
        $data['size_width'] = $Qgroup->Value('size_width');
        $data['size_height'] = $Qgroup->Value('size_height');
      }
      
      if ($_REQUEST['image_groups_id'] == DEFAULT_IMAGE_GROUP_ID) {
        $data['is_default'] = '1';
      }
      
      $response = array('success' => true, 'data' => $data);
      
      echo $toC_Json->encode($response);
    }
      
    function saveImageGroup() {
      global $toC_Json, $osC_Language;
      
      $data = array('title' => $_REQUEST['title'],
                    'code' => $_REQUEST['code'],
                    'width' => $_REQUEST['size_width'],
                    'height' => $_REQUEST['size_height'],
                    'force_size' => isset($_REQUEST['force_size']) && ( $_REQUEST['force_size'] == 'on' ) ? true : false);

      if ( osC_ImageGroups_Admin::save( (isset($_REQUEST['image_groups_id']) ? $_REQUEST['image_groups_id'] : null), $data, ( isset($_REQUEST['is_default']) && ( $_REQUEST['is_default'] == 'on' ) ? true : false )) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }    
    
    function deleteImageGroup() {
      global $toC_Json, $osC_Language;
      
      $error = false;
      $feedback = array();
      
      if ( $_REQUEST['image_groups_id'] == DEFAULT_IMAGE_GROUP_ID ) {
        $error = true;
        $feedback[] = $osC_Language->get('delete_error_image_group_prohibited');
      } 
      
      if ( $error === false ) {   
        if (osC_ImageGroups_Admin::delete($_REQUEST['image_groups_id'])) {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }  
      
      echo $toC_Json->encode($response);
    }
    
    function deleteImageGroups() {
      global $toC_Json, $osC_Language;
      
      $error = false;
      $feedback = array();
          
      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if ( $id == DEFAULT_IMAGE_GROUP_ID ) {
          $error = true;
          $feedback[] = $osC_Language->get('batch_delete_error_image_group_prohibited');
          break;
        }
      }
      
      if ($error === false) {
        foreach ($batch as $id) {
          if ( !osC_ImageGroups_Admin::delete($id) ) {
            $error = true;
            break;
          }
        }
      
        if ($error === false) {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }  
      
      echo $toC_Json->encode($response);
    }
  }
?>