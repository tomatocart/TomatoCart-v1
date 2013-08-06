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

  class toC_Json_Polls {
  
    function _process($polls_id, $votes) {
      global $osC_Database, $osC_Language, $osC_Customer;
      
      $error = false;
      
      $customers_id = $osC_Customer->getID();
      $remote_addr = $_SERVER['REMOTE_ADDR'];
      
      $osC_Database->startTransaction();
  
      foreach ($votes as $polls_ansers_id) {
        $Qvote = $osC_Database->query('insert into :table_polls_votes (polls_id, polls_answers_id, customers_id, date_voted, customers_ip_address) values (:polls_id, :polls_answers_id, :customers_id, now(), :customers_ip_address)');
        $Qvote->bindTable(':table_polls_votes', TABLE_POLLS_VOTES);
        $Qvote->bindInt(':polls_id', $polls_id);
        $Qvote->bindInt(':polls_answers_id', $polls_ansers_id);
        $Qvote->bindInt(':customers_id', $customers_id);
        $Qvote->bindValue(':customers_ip_address', $remote_addr);
        $Qvote->execute();
        
        if ( $osC_Database->isError() ) {
          $error = true;
          break;
        }

        if ($error === false) {
          $Qanswer = $osC_Database->query('update :table_polls_answers set votes_count = (votes_count + 1) where polls_answers_id = :polls_ansers_id');
          $Qanswer->bindTable(':table_polls_answers', TABLE_POLLS_ANSWERS);
          $Qanswer->bindInt(':polls_ansers_id', $polls_ansers_id);
          $Qanswer->execute();
          
          if ( $osC_Database->isError() ) {
            $error = true;
          }
        }
        
        if ($error === false) {
          $Qpoll = $osC_Database->query('update :table_polls set votes_count = (votes_count + 1) where polls_id = :polls_id');
          $Qpoll->bindTable(':table_polls', TABLE_POLLS);
          $Qpoll->bindInt(':polls_id', $polls_id);
          $Qpoll->execute();
          
          if ( $osC_Database->isError() ) {
            $error = true;
          }
        }
      }
      
      if ( $error === false ) {
        $osC_Database->commitTransaction();
        
        return true;
      } 
        
      $osC_Database->rollbackTransaction();
      
      return false;
    }
    
    function _result($polls_id) {
      global $osC_Database, $osC_Language;
      
      $Qpolls = $osC_Database->query('select p.polls_id, p.votes_count, p.polls_type, pd.polls_title from :table_polls p inner join :table_polls_description pd on p.polls_id = pd.polls_id where p.polls_status = 1 and pd.languages_id = :languages_id and p.polls_id = :polls_id');
      $Qpolls->bindTable(':table_polls', TABLE_POLLS);
      $Qpolls->bindTable(':table_polls_description', TABLE_POLLS_DESCRIPTION);
      $Qpolls->bindInt(':languages_id', $osC_Language->getID());
      $Qpolls->bindInt(':polls_id', $polls_id);
      $Qpolls->execute();
      
      $content = '<div>';
      $content .= '<h6>' . $Qpolls->value('polls_title') . '</h6>';
      
      $Qanswers = $osC_Database->query('select pa.votes_count, pad.answers_title from :table_polls_answers pa inner join :table_polls_answers_description pad on pa.polls_answers_id = pad.polls_answers_id where pa.polls_id = :polls_id and pad.languages_id = :languages_id order by pa.sort_order desc');
      $Qanswers->bindTable(':table_polls_answers', TABLE_POLLS_ANSWERS);
      $Qanswers->bindTable(':table_polls_answers_description', TABLE_POLLS_ANSWERS_DESCRIPTION);
      $Qanswers->bindInt(':polls_id', $polls_id);
      $Qanswers->bindInt(':languages_id', $osC_Language->getID());
      $Qanswers->execute();
    
      $count = $Qpolls->valueInt('votes_count');
      while ($Qanswers->next()) {
        $votes_count = $Qanswers->valueInt('votes_count');
        $answers_title = $Qanswers->value('answers_title');
        
        if ($count != 0 ) {
          $status = (float) $votes_count / (float) $count;
        }else {
          $status = 0;
        }
        
        if($status < 0.3){
          $bar_bg_color = 'red';
        } else if(($status >= 0.3) && ($status < 0.6)) {
          $bar_bg_color = 'blue';
        } else {
          $bar_bg_color = 'green';
        }
        
        $bar_length = intval($status * 100 * 18 / 10);
        
        $content .= '<div class="voteBar">' . 
                      '<div style="width: ' . $bar_length . 'px ;height:10px;background:' . $bar_bg_color . ';"></div>' . 
                    '</div>';
         
        $content .= '<p>' . $Qanswers->value('answers_title') . ' - <b>' . $Qanswers->valueInt('votes_count') . ' </b>' . $osC_Language->get('box_polls_votes') . '&nbsp;&nbsp;(' . floor($status * 100) . '%)</p>';
        
      }
      $content .= '</div>';
      
      return $content;
    }
    
    function vote() {
      global $osC_Database, $osC_Language, $toC_Json, $osC_Customer;

      $polls_id = $_GET['polls_id'];
      $votes = $_GET['vote'];
      $remote_addr = $_SERVER['REMOTE_ADDR'];
      $content = '';
      
      if (DISALLOW_MORE_THAN_ONE_VOTE_FROM_ONE_IP == 1) {
        $Qcheck = $osC_Database->query('select polls_votes_id from :table_polls_votes where polls_id = :polls_id and customers_ip_address = :customers_ip_address');
        $Qcheck->bindTable(':table_polls_votes', TABLE_POLLS_VOTES);
        $Qcheck->bindInt(':polls_id', $polls_id);
        $Qcheck->bindValue(':customers_ip_address', $remote_addr);
        $Qcheck->execute();
        
        if ($Qcheck->numberOfRows() > 0) {
          $content = '<span class="error">' . $osC_Language->get('box_polls_one_vote_per_ip_error') . "</span>";
        } else {
          self::_process($polls_id, $votes);
        }
      } else {
        self::_process($polls_id, $votes);
      }
      
      $content .= self::_result($polls_id);
      
      $response = array('success' => true, 'content' => $content);
      
      echo $toC_Json->encode($response);
    }
  
    function pollResult() {
      global $osC_Database, $osC_Language, $toC_Json;
      
      $content = self::_result($_GET['polls_id']);
      
      $response = array('success' => true, 'content' => $content);
      
      echo $toC_Json->encode($response);
    }
  }
  