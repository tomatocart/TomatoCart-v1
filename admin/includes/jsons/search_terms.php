<?php
/*
  $Id: search_terms.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/search_terms.php');
  
  class toC_Json_Search_Terms {

    function listSearchTerms() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qterms = $osC_Database->query('select * from :table_search_terms');
      $Qterms->bindTable(':table_search_terms', TABLE_SEARCH_TERMS);
      
      if (isset($_REQUEST['search']) && !empty($_REQUEST['search'])) {
        $Qterms->appendQuery('where text like :text');
        $Qterms->bindValue(':text', '%' . $_REQUEST['search'] . '%');
      }
      
      $Qterms->appendQuery('order by search_count desc');
      $Qterms->setExtBatchLimit($start, $limit);
      $Qterms->execute();
      
      $records = array();
      while ($Qterms->next()) {
        $records[] = array('search_terms_id' => $Qterms->valueInt('search_terms_id'),
                           'text' => $Qterms->value('text'),
                           'products_count' => $Qterms->valueInt('products_count'),
                           'search_count' => $Qterms->valueInt('search_count'),
                           'synonym' => $Qterms->value('synonym'),
                           'show_in_terms' => $Qterms->valueInt('show_in_terms'));
      }

      $response = array(EXT_JSON_READER_TOTAL => $Qterms->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);

      echo $toC_Json->encode($response);    
    }
    
    function loadSearchTerm() {
      global $toC_Json;

      $data = toC_Search_Terms_Admin::getData($_REQUEST['search_terms_id']);
      
      $response = array('success' => true, 'data' => $data);
      
      echo $toC_Json->encode($response);
    }
    
    function save() {
      global $toC_Json, $osC_Language;
      
      $data = array('text' => $_REQUEST['text'],
                    'products_count' => $_REQUEST['products_count'],
                    'search_count' => $_REQUEST['search_count'],
                    'synonym' => $_REQUEST['synonym'],
                    'show_in_terms' => $_REQUEST['show_in_terms']);
      
      if (toC_Search_Terms_Admin::save($_REQUEST['search_terms_id'], $data)) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }

      echo $toC_Json->encode($response);
    }
  
    function setStatus(){
      global $toC_Json, $osC_Language; 
    
      if (toC_Search_Terms_Admin::setStatus($_REQUEST['search_terms_id'], $_REQUEST['flag'])) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
  }
?>
