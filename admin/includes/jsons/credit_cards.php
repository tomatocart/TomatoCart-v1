<?php
/*
  $Id: credit_cards.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/credit_cards.php');

  class toC_Json_Credit_Cards {
        
    function listCreditCards() {
      global $toC_Json, $osC_Database;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qcc = $osC_Database->query('select id, credit_card_name, pattern, credit_card_status, sort_order from :table_credit_cards order by sort_order, credit_card_name');
      $Qcc->bindTable(':table_credit_cards', TABLE_CREDIT_CARDS);
      $Qcc->setExtBatchLimit($start, $limit);
      $Qcc->execute();
            
      $records = array();     
      while ( $Qcc->next() ) {          
        $records[] = array(
          'credit_cards_id' => $Qcc->valueInt('id'),
          'credit_cards_name' => $Qcc->value('credit_card_name'),
          'sort_order' => $Qcc->valueInt('sort_order')
        );           
      }
      $Qcc->freeResult();
      
      $response = array(EXT_JSON_READER_TOTAL => $Qcc->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }          
              
    function loadCreditCard() {
      global $toC_Json;
     
      $data = osC_CreditCards_Admin::getData($_REQUEST['credit_cards_id']);       
      
      $response = array('success' => true, 'data' => $data);
     
      echo $toC_Json->encode($response);   
    }
   
    function saveCreditCard() {
      global $toC_Json, $osC_Language;

      $data = array('credit_card_name' => $_REQUEST['credit_card_name'],
                    'pattern' => $_REQUEST['pattern'],
                    'credit_card_status' => (isset($_REQUEST['credit_card_status']) && ($_REQUEST['credit_card_status'] == 'on') ? 1 : 0),
                    'sort_order' => $_REQUEST['sort_order']);
     
      if ( osC_CreditCards_Admin::save(isset($_REQUEST['credit_cards_id']) ? $_REQUEST['credit_cards_id'] : null, $data) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));      
      }
     
      echo $toC_Json->encode($response);
    }

    function deleteCreditCard() {
      global $toC_Json, $osC_Language;
      
      if ( osC_CreditCards_Admin::delete($_REQUEST['credit_cards_id']) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      }
      else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteCreditCards() {
      global $toC_Json, $osC_Language;
     
      $error = false;

      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if ( !osC_CreditCards_Admin::delete($id) ) {
          $error = true;
          break;
        }
      }
       
      if ($error === false) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));               
      }
       
      echo $toC_Json->encode($response);               
    }       
  }
?>
