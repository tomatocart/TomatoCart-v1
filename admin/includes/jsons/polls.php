<?php
/*
  $Id: polls.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/polls.php');
  
  class toC_Json_Polls {
        
    function listPolls() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qpolls = $osC_Database->query('select p.polls_id, p.polls_type, p.polls_status, p.votes_count, p.date_added, pd.polls_title from :table_polls p left join :table_polls_description pd on (p.polls_id = pd.polls_id and pd.languages_id = :languages_id)');
      $Qpolls->bindTable(':table_polls', TABLE_POLLS);
      $Qpolls->bindTable(':table_polls_description', TABLE_POLLS_DESCRIPTION);
      $Qpolls->bindInt(':languages_id', $osC_Language->getID());
      
      if (isset($_REQUEST['search']) && !empty($_REQUEST['search'])) {
        $Qpolls->appendQuery('where pd.polls_title like :polls_title');
        $Qpolls->bindValue(':polls_title', '%' . $_REQUEST['search'] . '%');
      }
      
      $Qpolls->appendQuery('order by pd.polls_title');
      $Qpolls->setExtBatchLimit($start, $limit);
      $Qpolls->execute();

      $records = array();     
      while ( $Qpolls->next() ) {           
        $polls_info = 
          '<table width="100%" CELLSPACING="5" style="margin-left: 50px">' .
            '<tbody>' . 
              '<tr>
                <td width="120">' . $osC_Language->get('field_polls_type') . '</td>
                <td>' . ($Qpolls->valueInt('polls_type') == '0' ? $osC_Language->get('field_polls_single_choice') : $osC_Language->get('field_polls_multiple_choice')) . '</td>
              </tr>' .
              '<tr>
                <td>' . $osC_Language->get('field_polls_number_of_responses') . '</td>
                <td>' . $Qpolls->value('votes_count') . '</td>
              </tr>' . 
              '<tr>
                <td>' . $osC_Language->get('field_polls_date_created') . '</td>
                <td>' . osC_DateTime::getShort($Qpolls->value('date_added')) . '</td>
              </tr>' .
            '</tbody>' .
          '</table>';
        
        $records[] = array(
          'polls_id' => $Qpolls->valueInt('polls_id'),
          'polls_title' => $Qpolls->value('polls_title'),
          'polls_status' => $Qpolls->valueInt('polls_status'),
          'polls_info' => $polls_info
        );           
      }
      $Qpolls->freeResult();
      
      $response = array(EXT_JSON_READER_TOTAL => $Qpolls->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }
    
    function savePoll() {
      global $toC_Json, $osC_Language;
      
      $data = array('polls_type' => $_REQUEST['polls_type'],
                    'polls_status' => $_REQUEST['polls_status'],
                    'polls_question_title' => $_REQUEST['question_title']);
      
      if ( toC_Polls_Admin::save( ( isset($_REQUEST['polls_id'] ) && is_numeric( $_REQUEST['polls_id'] ) ? $_REQUEST['polls_id'] : null ), $data) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function listPollAnswers() {
      global $toC_Json, $osC_Database, $osC_Language;

      $Qanswers = $osC_Database->query('select pa.polls_answers_id, pa.votes_count, pad.answers_title from :table_polls_answers pa, :table_polls_answers_description pad where pa.polls_answers_id = pad.polls_answers_id and pa.polls_id = :polls_id and pad.languages_id = :languages_id');
      $Qanswers->bindTable(':table_polls_answers', TABLE_POLLS_ANSWERS);
      $Qanswers->bindTable(':table_polls_answers_description', TABLE_POLLS_ANSWERS_DESCRIPTION);
      $Qanswers->bindInt(':polls_id', $_REQUEST['polls_id']);
      $Qanswers->bindInt(':languages_id', $osC_Language->getID());
      $Qanswers->execute();
      
      $records = array();
      while ($Qanswers->next()) {
        $records[] = array('polls_answers_id' => $Qanswers->valueInt('polls_answers_id'),
                           'polls_id' => $_REQUEST['polls_id'],
                           'votes_count' => $Qanswers->value('votes_count'),
                           'answers_title' => $Qanswers->value('answers_title'));       
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
                                                 
      echo $toC_Json->encode($response);     
    }
    
    function  savePollAnswer() {
      global $toC_Json, $osC_Language;
      
      $data = array('polls_id' => $_REQUEST['polls_id'],
                    'votes_count' => $_REQUEST['votes_count'],
                    'sort_order' => $_REQUEST['sort_order'],
                    'answers_title' => $_REQUEST['answers_title']);
      
      if ( toC_Polls_Admin::savePollAnswer( ( isset($_REQUEST['polls_answers_id'] ) && is_numeric( $_REQUEST['polls_answers_id'] ) ? $_REQUEST['polls_answers_id'] : null ), $data) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function loadPoll() {
      global $toC_Json, $osC_Database;
      
      $data = toC_Polls_Admin::getData($_REQUEST['polls_id']);
      
      $data['date_added'] = osC_DateTime::getDate($data['date_added']);
      
      $Qdescription = $osC_Database->query('select polls_title, languages_id from :table_polls_description where polls_id = :polls_id');
      $Qdescription->bindTable(':table_polls_description', TABLE_POLLS_DESCRIPTION);
      $Qdescription->bindInt(':polls_id', $_REQUEST['polls_id']);
      $Qdescription->execute();
      
      while ($Qdescription->next()) {
        $data['question_title[' . $Qdescription->valueInt('languages_id') .']'] = $Qdescription->value('polls_title');
      }
      $Qdescription->freeResult();
        
      $response = array('success' => true, 'data' => $data);
     
      echo $toC_Json->encode($response);   
    }
   
    function loadPollAnswer() {
      global $toC_Json, $osC_Database;
     
      $data = toC_Polls_Admin::getPollAnswerData($_REQUEST['polls_answers_id']);
      
      $Qdescription = $osC_Database->query('select answers_title, languages_id from :table_polls_answers_description where polls_answers_id = :polls_answers_id');
      $Qdescription->bindTable(':table_polls_answers_description', TABLE_POLLS_ANSWERS_DESCRIPTION);
      $Qdescription->bindInt(':polls_answers_id', $_REQUEST['polls_answers_id']);
      $Qdescription->execute();
      
      while ($Qdescription->next()) {
        $data['answers_title[' . $Qdescription->valueInt('languages_id') .']'] = $Qdescription->value('answers_title');
      }
      $Qdescription->freeResult();
      
      $response = array('success' => true, 'data' => $data);

      echo $toC_Json->encode($response);
    }
    
    function deletePoll() {
      global $toC_Json, $osC_Language;
      
      if (toC_Polls_Admin::delete($_REQUEST['polls_id'])) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
     
      echo $toC_Json->encode($response);                            
    }
    
    function deletePolls() {
      global $toC_Json, $osC_Language;
     
      $error = false;
      
      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if ( !toC_Polls_Admin::delete($id) ) {
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
    
    function deletePollAnswer() {
      global $toC_Json, $osC_Language;
      
      $polls_answers_id = isset($_REQUEST['polls_answers_id']) ? $_REQUEST['polls_answers_id'] : null;
      
      if (is_numeric($polls_answers_id)) {
        if ( toC_Polls_Admin::deletePollAnswer($_REQUEST['polls_answers_id']) ) {      
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deletePollAnswers() {
      global $toC_Json, $osC_Language;
      
      $batch = explode(',', $_REQUEST['batch']);
      
      $error = false;
      
      foreach($batch as $id) {
        if (!toC_Polls_Admin::deletePollAnswer($id)) {
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
    
    function setStatus(){
      global $toC_Json, $osC_Language; 
    
      $flag = $_REQUEST['flag'];
      $polls_id = $_REQUEST['polls_id'];
      
      if (toC_Polls_Admin::setStatus($polls_id, $flag)) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
  }
?>
