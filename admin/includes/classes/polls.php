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

  class toC_Polls_Admin {
    function getData($id) {
      global $osC_Database;

      $Qpoll = $osC_Database->query('select * from :table_polls where polls_id = :polls_id');
      $Qpoll->bindTable(':table_polls', TABLE_POLLS);
      $Qpoll->bindInt(':polls_id', $id);
      $Qpoll->execute();

      $data = $Qpoll->toArray();

      $Qpoll->freeResult();

      return $data;
    }

    function save($id = null, $data) {
      global $osC_Database, $osC_Language;

      $error = false;

      $osC_Database->startTransaction();

      if ( is_numeric($id) ) {
        $Qpoll = $osC_Database->query('update :table_polls set polls_type = :polls_type, polls_status = :polls_status where polls_id = :polls_id');
        $Qpoll->bindInt(':polls_id', $id);
      } else {
        $Qpoll = $osC_Database->query('insert into :table_polls (polls_type, polls_status, votes_count, date_added) values (:polls_type, :polls_status, 0, :date_added)');
        $Qpoll->bindRaw(':date_added', 'now()');
      }

      $Qpoll->bindTable(':table_polls', TABLE_POLLS);
      $Qpoll->bindValue(':polls_type', $data['polls_type']);
      $Qpoll->bindValue(':polls_status', $data['polls_status']);
      $Qpoll->setLogging($_SESSION['module'], $id);
      $Qpoll->execute();
      
      if ( $osC_Database->isError() ) {
        $error = true;
      }

      if ($error === false) {
        $polls_id = ( !empty($id) ) ? $id : $osC_Database->nextID();
        
        foreach ($osC_Language->getAll() as $l) {
          if (is_numeric($id)) {
            $Qdescription = $osC_Database->query('update :table_polls_description set polls_title = :polls_title where polls_id = :polls_id and languages_id = :languages_id');
          } else {
            $Qdescription = $osC_Database->query('insert into :table_polls_description (polls_id, polls_title, languages_id) values (:polls_id, :polls_title, :languages_id)');
          }

          $Qdescription->bindTable(':table_polls_description', TABLE_POLLS_DESCRIPTION);
          $Qdescription->bindInt(':polls_id', $polls_id);
          $Qdescription->bindValue(':polls_title', $data['polls_question_title'][$l['id']]);
          $Qdescription->bindInt(':languages_id', $l['id']);
          $Qdescription->setLogging($_SESSION['module'], $polls_id);
          $Qdescription->execute();

          if ($osC_Database->isError()) {
            $error = true;
            break;
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

    function delete($id) {
      global $osC_Database;

      $error = false;

      $osC_Database->startTransaction();

      if ( $error === false ) {
        $Qpolls = $osC_Database->query('delete from :table_polls_description where polls_id = :polls_id');
        $Qpolls->bindTable(':table_polls_description', TABLE_POLLS_DESCRIPTION);
        $Qpolls->bindInt(':polls_id', $id);
        $Qpolls->setLogging($_SESSION['module'], $id);
        $Qpolls->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
        }
      }
      
      if ( $error === false ) {
        $Qpolls = $osC_Database->query('delete from :table_polls_votes where polls_id = :polls_id');
        $Qpolls->bindTable(':table_polls_votes', TABLE_POLLS_VOTES);
        $Qpolls->bindInt(':polls_id', $id);
        $Qpolls->setLogging($_SESSION['module'], $id);
        $Qpolls->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
        }
      }
      
      if ( $error === false ) {
        $Qanswers = $osC_Database->query('select polls_answers_id from :table_polls_answers where polls_id = :polls_id');
        $Qanswers->bindTable(':table_polls_answers', TABLE_POLLS_ANSWERS);
        $Qanswers->bindInt(':polls_id', $id);
        $Qanswers->execute();
        
        if ( $Qanswers->numberOfRows() > 0 ) {
          while ($Qanswers->next()) {
            $Qdescription = $osC_Database->query('delete from :table_polls_answers_description where polls_answers_id = :polls_answers_id');
            $Qdescription->bindTable(':table_polls_answers_description', TABLE_POLLS_ANSWERS_DESCRIPTION);
            $Qdescription->bindInt(':polls_answers_id', $Qanswers->valueInt('polls_answers_id'));
            $Qdescription->setLogging($_SESSION['module'], $Qanswers->valueInt('polls_answers_id'));
            $Qdescription->execute();
  
            if ( $osC_Database->isError() ) {
              $error = true;
              
              break;
            }
            
          }
        }
      }
      
      if ( $error === false ) {
        $Qanswer = $osC_Database->query('delete from :table_polls_answers where polls_id = :polls_id');
        $Qanswer->bindTable(':table_polls_answers', TABLE_POLLS_ANSWERS);
        $Qanswer->bindInt(':polls_id', $id);
        $Qanswer->setLogging($_SESSION['module'], $id);
        $Qanswer->execute();
        
        if ( $osC_Database->isError() ) {
          $error = true;
        }
      }

      if ( $error === false ) {
        $Qpolls = $osC_Database->query('delete from :table_polls where polls_id = :polls_id');
        $Qpolls->bindTable(':table_polls', TABLE_POLLS);
        $Qpolls->bindInt(':polls_id', $id);
        $Qpolls->setLogging($_SESSION['module'], $id);
        $Qpolls->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
        }
      }
      
      if ( $error === false ) {
        $osC_Database->commitTransaction();

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }
    
    function getPollAnswerData($id) {
      global $osC_Database;

      $Qanswer = $osC_Database->query('select * from :table_polls_answers where polls_answers_id = :polls_answers_id');
      $Qanswer->bindTable(':table_polls_answers', TABLE_POLLS_ANSWERS);
      $Qanswer->bindInt(':polls_answers_id', $id);
      $Qanswer->execute();

      $data = $Qanswer->toArray();

      $Qanswer->freeResult();

      return $data;
    }

    function savePollAnswer($id = null, $data) {
      global $osC_Database, $osC_Language;

      $error = false;

      $osC_Database->startTransaction();

      if ( is_numeric($id) ) {
        $Qpoll = $osC_Database->query('update :table_polls_answers set polls_id = :polls_id, votes_count = :votes_count, sort_order = :sort_order where polls_answers_id = :polls_answers_id');
        $Qpoll->bindInt(':polls_answers_id', $id);
      } else {
        $Qpoll = $osC_Database->query('insert into :table_polls_answers (polls_id, votes_count, sort_order) values (:polls_id, :votes_count, :sort_order)');
      }

      $Qpoll->bindTable(':table_polls_answers', TABLE_POLLS_ANSWERS);
      $Qpoll->bindInt(':polls_id', $data['polls_id']);
      $Qpoll->bindInt(':votes_count', $data['votes_count']);
      $Qpoll->bindInt(':sort_order', $data['sort_order']);
      $Qpoll->setLogging($_SESSION['module'], $id);
      $Qpoll->execute();
      
      if ( $osC_Database->isError() ) {
        $error = true;
      }

      if ($error === false) {
        $polls_answers_id = ( !empty($id) ) ? $id : $osC_Database->nextID();
              
        foreach ($osC_Language->getAll() as $l) {
          if (is_numeric($id)) {
            $Qdescription = $osC_Database->query('update :table_polls_answers_description set answers_title = :answers_title where polls_answers_id = :polls_answers_id and languages_id = :languages_id');
          } else {
            $Qdescription = $osC_Database->query('insert into :table_polls_answers_description (polls_answers_id, languages_id, answers_title) values (:polls_answers_id, :languages_id, :answers_title)');
          }

          $Qdescription->bindTable(':table_polls_answers_description', TABLE_POLLS_ANSWERS_DESCRIPTION);
          $Qdescription->bindInt(':polls_answers_id', $polls_answers_id);
          $Qdescription->bindInt(':languages_id', $l['id']);
          $Qdescription->bindValue(':answers_title', $data['answers_title'][$l['id']]);
          $Qdescription->setLogging($_SESSION['module'], $polls_answers_id);
          $Qdescription->execute();

          if ($osC_Database->isError()) {
            $error = true;
            break;
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

    function deletePollAnswer($id) {
      global $osC_Database;

      $error = false;
      
      $osC_Database->startTransaction();

      if ( is_numeric($id) ) {
        $Qvote = $osC_Database->query('select votes_count, polls_id from :table_polls_answers where polls_answers_id = :polls_answers_id');
        $Qvote->bindTable(':table_polls_answers', TABLE_POLLS_ANSWERS);
        $Qvote->bindInt(':polls_answers_id', $id);
        $Qvote->execute();
        
        $votes_count = $Qvote->valueInt('votes_count');
        $polls_id = $Qvote->valueInt('polls_id');
        
        $Qvote = $osC_Database->query('update :table_polls set votes_count = (votes_count - :votes_count) where polls_id = :polls_id');
        $Qvote->bindTable(':table_polls', TABLE_POLLS);
        $Qvote->bindInt(':votes_count', $votes_count);
        $Qvote->bindInt(':polls_id', $polls_id);
        $Qvote->execute();
        
        if ( $osC_Database->isError() ) {
          $error = true;
        }
      }
      
      if ($error === false) {
        $Qdelete = $osC_Database->query('delete from :table_polls_answers where polls_answers_id = :polls_answers_id');
        $Qdelete->bindTable(':table_polls_answers', TABLE_POLLS_ANSWERS);
        $Qdelete->bindInt(':polls_answers_id', $id);
        $Qdelete->setLogging($_SESSION['module'], $id);
        $Qdelete->execute();
        
        if ( $osC_Database->isError() ) {
          $error = true;
        }
      }
      
      if ($error === false) {
        $Qdelete = $osC_Database->query('delete from :table_polls_answers_description where polls_answers_id = :polls_answers_id');
        $Qdelete->bindTable(':table_polls_answers_description', TABLE_POLLS_ANSWERS_DESCRIPTION);
        $Qdelete->bindInt(':polls_answers_id', $id);
        $Qdelete->setLogging($_SESSION['module'], $id);
        $Qdelete->execute();
        
        if ( $osC_Database->isError() ) {
          $error = true;
        }
      }

      if ($error === false) {
        $Qdelete = $osC_Database->query('delete from :table_polls_votes where polls_answers_id = :polls_answers_id');
        $Qdelete->bindTable(':table_polls_votes', TABLE_POLLS_VOTES);
        $Qdelete->bindInt(':polls_answers_id', $id);
        $Qdelete->setLogging($_SESSION['module'], $id);
        $Qdelete->execute();
        
        if ( $osC_Database->isError() ) {
          $error = true;
        }
      }
      
      if ( $error === false ) {
        $osC_Database->commitTransaction();

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }
    
    function setStatus($polls_id, $status) {
      global $osC_Database;
      
      $Qstatus = $osC_Database->query('update :table_polls set polls_status = :polls_status where polls_id = :polls_id');
      $Qstatus->bindTable(':table_polls', TABLE_POLLS);
      $Qstatus->bindInt(':polls_id', $polls_id);
      $Qstatus->bindInt(':polls_status', $status);
      $Qstatus->setLogging($_SESSION['module'], $polls_id);
      $Qstatus->execute();
  
      if ( !$osC_Database->isError() ) {
        return true;
      }
                  
      return false;
    }
  }
?>