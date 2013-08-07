<?php
/*
  $Id: faqs.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/classes/faqs.php');
  
  class toC_Json_Faqs {
    
    function listFaqs() {
      global $osC_Database, $toC_Json, $osC_Language;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qfaqs = $osC_Database->query('select f.faqs_id, f.faqs_status, f.faqs_order, fd.faqs_question from :table_faqs f, :table_faqs_description fd where f.faqs_id = fd.faqs_id and fd.language_id = :language_id ');
      
      if ( !empty($_REQUEST['search']) ) {
        $Qfaqs->appendQuery('and fd.faqs_question like :faqs_question');
        $Qfaqs->bindValue(':faqs_question', '%' . $_REQUEST['search'] . '%');
      }
    
      $Qfaqs->appendQuery('order by f.faqs_order, f.faqs_id');
      $Qfaqs->bindTable(':table_faqs', TABLE_FAQS);
      $Qfaqs->bindTable(':table_faqs_description', TABLE_FAQS_DESCRIPTION);
      $Qfaqs->bindInt(':language_id', $osC_Language->getID());
      $Qfaqs->setExtBatchLimit($start, $limit);
      $Qfaqs->execute();
      
      $records = array();
      while ($Qfaqs->next()) {
      	$records[] = array('faqs_id' => $Qfaqs->Value('faqs_id'),
      	                   'faqs_status' => $Qfaqs->Value('faqs_status'),
      	                   'faqs_order' => $Qfaqs->Value('faqs_order'),
      	                   'faqs_question' => $Qfaqs->Value('faqs_question'));
      }
      
      $response = array(EXT_JSON_READER_TOTAL => $Qfaqs->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response);
    }
    
    function loadFaq() {
      global $osC_Database, $toC_Json;
      
      $data = toC_Faqs_Admin::getData($_REQUEST['faqs_id']);
  
      $Qad = $osC_Database->query('select faqs_question, faqs_url, faqs_answer, language_id from :table_faqs_description where faqs_id = :faqs_id');
      $Qad->bindTable(':table_faqs_description', TABLE_FAQS_DESCRIPTION);
      $Qad->bindInt(':faqs_id', $_REQUEST['faqs_id']);
      $Qad->execute();
      
      while ($Qad->next()) {
        $data['faqs_question[' . $Qad->valueInt('language_id') . ']'] = $Qad->value('faqs_question');
        $data['faqs_url[' . $Qad->valueInt('language_id') . ']'] = $Qad->value('faqs_url');
        $data['faqs_answer[' . $Qad->valueInt('language_id') . ']'] = $Qad->value('faqs_answer');
      }

      $response = array('success' => true, 'data' => $data);
      
      echo $toC_Json->encode($response);
    }
    
    function saveFaq() {
      global $osC_Language, $toC_Json;
      
      //search engine friendly urls
      $formatted_urls = array();
      $urls = $_REQUEST['faqs_url'];
      if (is_array($urls) && !empty($urls)) {
        foreach($urls as $languages_id => $url) {
          $url = toc_format_friendly_url($url);
          if (empty($url)) {
            $url = toc_format_friendly_url($_REQUEST['faqs_question'][$languages_id]);
          }

          $formatted_urls[$languages_id] = $url;
        }
      }
      
      $data = array('faqs_question' => $_REQUEST['faqs_question'],
                    'faqs_url' => $formatted_urls,
                    'faqs_answer' => $_REQUEST['faqs_answer'],
                    'faqs_order' => $_REQUEST['faqs_order'],
                    'faqs_status' => $_REQUEST['faqs_status']);

      if ( toC_Faqs_Admin::save((isset($_REQUEST['faqs_id']) && is_numeric($_REQUEST['faqs_id']) ? $_REQUEST['faqs_id'] : null), $data) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteFaq() {
      global $toC_Json, $osC_Language;
      
      if ( toC_Faqs_Admin::delete($_REQUEST['faqs_id']) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteFaqs() {
      global $toC_Json, $osC_Language;
      
      $error = false;
      
      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if ( !toC_Faqs_Admin::delete($id) ) {
          $error = true;
          break;
        }
      }

      if ( $error === false ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
  
    function setStatus() {
      global $toC_Json, $osC_Language;
    
      if ( isset($_REQUEST['faqs_id']) && toC_Faqs_Admin::setStatus($_REQUEST['faqs_id'], (isset($_REQUEST['flag']) ? $_REQUEST['flag'] : null)) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed') );
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
  }
?>