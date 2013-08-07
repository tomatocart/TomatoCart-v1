<?php
/*
  $Id: weight_classes.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/weight_classes.php');

  class toC_Json_Weight_Classes {
  
    function listWeightClasses() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qclasses = $osC_Database->query('select weight_class_id, weight_class_key, weight_class_title from :table_weight_classes where language_id = :language_id order by weight_class_title');
      $Qclasses->bindTable(':table_weight_classes', TABLE_WEIGHT_CLASS);
      $Qclasses->bindInt(':language_id', $osC_Language->getID());
      $Qclasses->setExtBatchLimit($start, $limit);
      $Qclasses->execute();

      
      $record = array();
      while ( $Qclasses->next() ) {
        $class_name = $Qclasses->value('weight_class_title');
    
        if ( $Qclasses->valueInt('weight_class_id') == SHIPPING_WEIGHT_UNIT ) {
          $class_name .= ' (' . $osC_Language->get('default_entry') . ')';
        }
        $record[] = array('weight_class_title' => $class_name,
                          'weight_class_id' => $Qclasses->value('weight_class_id'),
                          'weight_class_key' => $Qclasses->value('weight_class_key'));         
      }
      
      $response = array(EXT_JSON_READER_TOTAL => $Qclasses->getBatchSize(),
                        EXT_JSON_READER_ROOT => $record); 
                        
      echo $toC_Json->encode($response);
      
    }
    
    function loadWeightClasses() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $data = osC_WeightClasses_Admin::getData($_REQUEST['weight_class_id']);
      
      if ( $data['weight_class_id'] == SHIPPING_WEIGHT_UNIT ) {
        $data['is_default'] = 1; 
      }
      
      $Qwc = $osC_Database->query('select language_id, weight_class_key, weight_class_title from :table_weight_classes where weight_class_id = :weight_class_id');
      $Qwc->bindTable(':table_weight_classes', TABLE_WEIGHT_CLASS);
      $Qwc->bindInt(':weight_class_id', $_REQUEST['weight_class_id']);
      $Qwc->execute();
      
      while ( $Qwc->next() ) {
        $data['name[' . $Qwc->ValueInt('language_id') . ']'] =  $Qwc->value('weight_class_title');
        $data['key[' . $Qwc->ValueInt('language_id') . ']'] = $Qwc->value('weight_class_key');
      }
      $Qwc->freeResult();
      
      $Qrules = $osC_Database->query('select r.weight_class_to_id, r.weight_class_rule, c.weight_class_title, c.weight_class_key from :table_weight_classes_rules r, :table_weight_classes c where r.weight_class_from_id = :weight_class_from_id and r.weight_class_to_id != :weight_class_to_id and r.weight_class_to_id = c.weight_class_id and c.language_id = :language_id order by c.weight_class_title');
      $Qrules->bindTable(':table_weight_classes_rules', TABLE_WEIGHT_CLASS_RULES);
      $Qrules->bindTable(':table_weight_classes', TABLE_WEIGHT_CLASS);
      $Qrules->bindInt(':weight_class_from_id', $_REQUEST['weight_class_id']);
      $Qrules->bindInt(':weight_class_to_id', $_REQUEST['weight_class_id']);
      $Qrules->bindInt(':language_id', $osC_Language->getID());
      $Qrules->execute();
        
      $rules = array();
      while ( $Qrules->next() ) {
        $rules[] = array('weight_class_id' => $Qrules->value('weight_class_to_id'),
                         'weight_class_rule' => $Qrules->value('weight_class_rule'),
                         'weight_class_title' => $Qrules->value('weight_class_title'));         
      }
      $Qrules->freeResult();
      
      $data['rules'] = $rules;
      
      $response = array('success' => true, 'data' => $data); 
      
      echo $toC_Json->encode($response);  
    }
    
    function getWeightClassesRules() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $Qrules = $osC_Database->query('select weight_class_id, weight_class_title from :table_weight_classes where language_id = :language_id order by weight_class_title');
      $Qrules->bindTable(':table_weight_classes', TABLE_WEIGHT_CLASS);
      $Qrules->bindInt(':language_id', $osC_Language->getID());
      $Qrules->execute();
        
      $rules = array();
      while ( $Qrules->next() ) {
        $rules[] = array( 'weight_class_id' => $Qrules->value('weight_class_id'),
                          'weight_class_title' => $Qrules->value('weight_class_title'));         
      }
      
      $response = array('rules' => $rules); 
      
      echo $toC_Json->encode($response);  
    }
    
    function saveWeightClasses() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $data = array('name' => $_REQUEST['name'],
                    'key' => $_REQUEST['key'],
                    'rules' => $_REQUEST['rules']);
      
      if ( osC_WeightClasses_Admin::save(($_REQUEST['weight_class_id'] > 0 ? $_REQUEST['weight_class_id'] : null), $data, ( isset($_REQUEST['is_default']) && ( $_REQUEST['is_default'] == 'on' ) ? true : false )) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
      
      echo $toC_Json->encode($response);
    }
  
    function deleteWeightClass() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $error = false;
      $feedback = array();
      
      if ( $_REQUEST['weight_classes_id'] == SHIPPING_WEIGHT_UNIT ) {
        $error = true;
        $feedback[] = $osC_Language->get('delete_error_weight_class_prohibited');
      } else {
      $Qcheck = $osC_Database->query('select count(*) as total from :table_products where products_weight_class = :products_weight_class');
      $Qcheck->bindTable(':table_products', TABLE_PRODUCTS);
      $Qcheck->bindInt(':products_weight_class', $_REQUEST['weight_classes_id']);
      $Qcheck->execute();
            
        if ( $Qcheck->valueInt('total') > 0 ) {
          $error = true;
          $feedback[] = sprintf($osC_Language->get('delete_error_weight_class_in_use'), $Qcheck->valueInt('total'));
        }
      }
      
      if ($error === false) {
        if (osC_WeightClasses_Admin::delete( $_REQUEST['weight_classes_id'])) {
          $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteWeightClasses() {
      global $toC_Json, $osC_Database, $osC_Language;
    
      $error = false;
      $feedback = array();
      
      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if ( $id == SHIPPING_WEIGHT_UNIT ) {
          $error = true;
          $feedback[] = $osC_Language->get('delete_error_weight_class_prohibited');
        } else {
          $Qcheck = $osC_Database->query('select count(*) as total from :table_products where products_weight_class = :products_weight_class');
          $Qcheck->bindTable(':table_products', TABLE_PRODUCTS);
          $Qcheck->bindInt(':products_weight_class', $id);
          $Qcheck->execute();
              
          if ( $Qcheck->valueInt('total') > 0 ) {
            $error = true;
            $feedback[] = $osC_Language->get('batch_delete_error_weight_class_in_use');
            break;
          }
        }
      }
      
      if ($error === false) {
        foreach ($batch as $id) {
          if ( !osC_WeightClasses_Admin::delete($id) ) {
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
