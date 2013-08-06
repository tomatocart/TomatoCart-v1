<?php
/*
  $Id: slide_images.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/slide_images.php');
  require('includes/classes/image.php');

  class toC_Json_Slide_Images {

    function listSlideImages() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qimages = $osC_Database->query('select *  from :table_slide_images where language_id =:language_id ');
      $Qimages->appendQuery('order by sort_order');
      $Qimages->bindTable(':table_slide_images', TABLE_SLIDE_IMAGES);
      $Qimages->bindInt(':language_id', $osC_Language->getID());
      $Qimages->setExtBatchLimit($start, $limit);
      $Qimages->execute();
        
      $records = array();     
      while ( $Qimages->next() ) {
        $image = '';
        if (file_exists('../images/' . $Qimages->value('image'))) {
          list($orig_width, $orig_height) = getimagesize('../images/' . $Qimages->value('image'));
          $width = intval($orig_width * 80 / $orig_height);
          
          $image = '<img src="../images/' . $Qimages->value('image') . '" width="' . $width . '" height="80" />';
        }
       
        $records[] = array('image_id' => $Qimages->valueInt('image_id'),
                           'image' =>  $image,
                           'image_url' => $Qimages->value('image_url'),
                           'sort_order' => $Qimages->value('sort_order'),
                           'status' => $Qimages->value('status'));
      }
      $Qimages->freeResult();         
       
      $response = array(EXT_JSON_READER_TOTAL => $Qimages->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }          
    
    function setStatus() {
      global $toC_Json, $osC_Language;
        
      if ( toC_Slide_Images_Admin::setStatus($_REQUEST['image_id'], ( isset($_REQUEST['flag']) ? $_REQUEST['flag'] : null) ) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed') );
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteSlideImage() {
      global $toC_Json, $osC_Language;
      
      if ( toC_Slide_Images_Admin::delete($_REQUEST['image_id']) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteSlideImages() {
      global $toC_Json, $osC_Language;
     
      $error = false;
      
      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if (!toC_Slide_Images_Admin::delete($id)) {
          $error = true;
          break;
        }
      }
      
      if ($error === false) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));               
      }
       
      echo $toC_Json->encode($response);               
    }       
    
    function loadSlideImages() {
      global $toC_Json, $osC_Database;
     
      $data = toC_Slide_Images_Admin::getData($_REQUEST['image_id']);
      
      $Qimage = $osC_Database->query('select * from :table_slide_images where image_id = :image_id');
      $Qimage->bindTable(':table_slide_images', TABLE_SLIDE_IMAGES);
      $Qimage->bindInt(':image_id', $_REQUEST['image_id']);
      $Qimage->execute();
      
      while ($Qimage->next()) {
        list($orig_width, $orig_height) = getimagesize('../images/' . $Qimage->value('image'));
        $width = intval($orig_width * 80 / $orig_height);
        
        $image = '<img src="../images/' . $Qimage->value('image') . '" width="' . $width . '" height="80" style="margin-left: 112px" />'; 
        
        $data['description['.$Qimage->valueInt('language_id').']'] = $Qimage->value('description');
        $data['image_url['.$Qimage->valueInt('language_id').']'] = $Qimage->value('image_url');
        $data['slide_image'.$Qimage->valueInt('language_id')] = $image;
      }
        
      $response = array('success' => true, 'data' => $data);
       
      echo $toC_Json->encode($response);    
    }
   
    function saveSlideImages() {
      global $toC_Json, $osC_Language;
      
      header('Content-Type: text/html');
      
      $data = array('status' => $_REQUEST['status'],
                    'image_url' => $_REQUEST['image_url'],
                    'description' => $_REQUEST['description'],
                    'sort_order' => $_REQUEST['sort_order']
                   );
      
      $error = false;
      $feedback = array();
      if ( !isset($_REQUEST['image_id']) ) {
        foreach ( $osC_Language->getAll() as $l ) {
          if ( empty($_FILES['image'.$l['id']]['name']) ) {
            $error = true;  
            $feedback[] = sprintf($osC_Language->get('ms_error_image_empty'), $l['name']);
          }
        }
      }
      
      if ( $error === false ) {
        if ( toC_Slide_Images_Admin::save( ( isset($_REQUEST['image_id']) ? $_REQUEST['image_id'] : null ), $data) ) {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        } } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br>' . implode('<br>', $feedback));
      }
     
      echo $toC_Json->encode($response);
    }
  }
?>
