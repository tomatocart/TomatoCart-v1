<?php
/*
  $Id: unit_classes.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/ 
  require('includes/classes/unit_classes.php');
  
  class toC_Json_Unit_Classes {
    
    function listUnitClasses() {
      global  $toC_Json, $osC_Database, $osC_Language;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qclasses = $osC_Database->query('select quantity_unit_class_id,  quantity_unit_class_title from :table_quantity_unit_classes where language_id = :language_id');
      $Qclasses->bindTable(':table_quantity_unit_classes', TABLE_QUANTITY_UNIT_CLASSES);
      $Qclasses->bindInt(':language_id', $osC_Language->getID());
      $Qclasses->setExtBatchLimit($start, $limit);
      $Qclasses->execute();

      $records = array();
      
      while ($Qclasses->next()) {
        $unit_class_title = $Qclasses->value('quantity_unit_class_title');
        
        if ( $Qclasses->value('quantity_unit_class_id') == DEFAULT_UNIT_CLASSES ){
          $unit_class_title  .=  ' (' . $osC_Language->get('default_entry') . ')';
        }
      
        $records[] = array('unit_class_id' => $Qclasses->value('quantity_unit_class_id'),
                           'unit_class_title' => $unit_class_title,
                           'languange_id'=>$osC_Language->getID());
      }
      
      $response = array(EXT_JSON_READER_TOTAL => $Qclasses->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records); 
                        
      echo $toC_Json->encode($response);
    
    }
    
    function loadUnitClass() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $unit_class_id = empty($_REQUEST['unit_class_id']) ? 0 : $_REQUEST['unit_class_id'];
      
      if ( $unit_class_id == DEFAULT_UNIT_CLASSES ) {
        $data['is_default'] = 1; 
      }

      $Qclasses = $osC_Database->query('select language_id, quantity_unit_class_title from :table_quantity_unit_classes where quantity_unit_class_id = :quantity_unit_class_id');
      $Qclasses->bindTable(':table_quantity_unit_classes', TABLE_QUANTITY_UNIT_CLASSES);
      $Qclasses->bindInt(':quantity_unit_class_id', $unit_class_id);
      $Qclasses->execute();
      
      while ( $Qclasses->next() ) {
        $data['unit_class_title[' . $Qclasses->valueInt('language_id') . ']'] =  $Qclasses->value('quantity_unit_class_title');
      }
      
      $response = array('success' => true, 'data' => $data);
         
      echo $toC_Json->encode($response);
    }
    
    function saveUnitClass(){
      global $toC_Json, $osC_Language;
      
      $data = array('unit_class_title' => $_REQUEST['unit_class_title']);
      
      if ( toC_Unit_Class_Admin::save( (isset( $_REQUEST['unit_class_id'] ) && is_numeric($_REQUEST['unit_class_id'] ) ? $_REQUEST['unit_class_id'] : null), $data, ( isset($_REQUEST['default']) && ( $_REQUEST['default'] == 'on' ) ? true : false ) ) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }

      echo $toC_Json->encode($response);
    }
    
    function deleteUnitClass() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $error = false;
      $feedback = array();
      
      if ($_REQUEST['unit_class_id'] == DEFAULT_UNIT_CLASSES) {
        $error = true;
        $feedback[] = $osC_Language->get('delete_error_unit_class_prohibited');
      } else {
        $Qcheck = $osC_Database->query('select count(*) as total from :table_products where quantity_unit_class = :quantity_unit_class');
        $Qcheck->bindTable(':table_products', TABLE_PRODUCTS);
        $Qcheck->bindInt(':quantity_unit_class', $_REQUEST['unit_class_id']);
        $Qcheck->execute();
            
        if ( $Qcheck->valueInt('total') > 0 ) {
          $error = true;
          $feedback[] = sprintf($osC_Language->get('delete_error_unit_class_in_use'), $Qcheck->valueInt('total'));
        }
      }
      
      if  ($error == false ) {
        if (toC_Unit_Class_Admin::delete($_REQUEST['unit_class_id'])) {
          $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }
      } else {
        $response = array('success' => false, 'feedback' => implode('<br />', $feedback));
      }
      
      echo $toC_Json->encode($response);
    } 
    
    function deleteUnitClasses() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $error = false;
      $feedback = array();
      
      $batch = explode(',', $_REQUEST['batch']);
        
      foreach ($batch as $id) {
        if ($id == DEFAULT_UNIT_CLASSES) {
          $error = true;
          $feedback[] = $osC_Language->get('batch_delete_error_unit_class_prohibited');
        } else {
          $Qcheck = $osC_Database->query('select count(*) as total from :table_products where quantity_unit_class = :quantity_unit_class');
          $Qcheck->bindTable(':table_products', TABLE_PRODUCTS);
          $Qcheck->bindInt(':quantity_unit_class', $id);
          $Qcheck->execute();
              
          if ( $Qcheck->valueInt('total') > 0 ) {
            $error = true;
            $feedback[] = $osC_Language->get('batch_delete_error_unit_class_in_use');
            break;
          }
        }
      }
      if ($error === false) {
        foreach ($batch as $id) {

          if ( !toC_Unit_Class_Admin::delete($id) ) {
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
        $response = array('success' => false, 'feedback' => implode('<br />', $feedback));
      }
      
      echo $toC_Json->encode($response);  
    }
  }
      
        