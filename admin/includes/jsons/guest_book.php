 
<?php
/*
  $Id: guest_book.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/ 
  require('includes/classes/guest_book.php');
  
  class toC_Json_Guest_book {
  
    function listGuestBook() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];  
      
      $QguestBook = $osC_Database->query('select guest_books_id, title, email, url, guest_books_status, languages_id, content, date_added from :table_guest_books order by guest_books_id desc');
      $QguestBook->bindTable(':table_guest_books', TABLE_GUEST_BOOKS);
      $QguestBook->setExtBatchLimit($start, $limit);
      $QguestBook->execute();
       
      $records = array();     
      while ($QguestBook->next()){       
        $records[] = array('guest_books_id' => $QguestBook->valueInt('guest_books_id'),
                           'title' => $QguestBook->value('title'),
                           'email'=> $QguestBook->value('email'),
                           'url' => $QguestBook->value('url'), 
                           'guest_books_status' => $QguestBook->value('guest_books_status'),
                           'languages' => $osC_Language->showImage(osC_Language_Admin::getData($QguestBook->valueInt('languages_id'), 'code')),
                           'content' => $QguestBook->value('content'),
                           'date_added' => osC_DateTime::getDate($QguestBook->value('date_added')));
      }
      
      $QguestBook->freeResult();

      $response = array(EXT_JSON_READER_TOTAL => $QguestBook->getBatchSize(),
                        EXT_JSON_READER_ROOT  => $records);

      echo $toC_Json->encode($response);
    }

    function loadGuestBook() {
      global $toC_Json, $osC_Language;
     
      $data = toC_Guest_Book_Admin::getData($_REQUEST['guest_books_id']);
      
      $response = array('success' => true, 'data' => $data);

      echo $toC_Json->encode($response);   
    }
          
    function setStatus() {
      global $toC_Json, $osC_Language;
        
      if ( toC_Guest_Book_Admin::setStatus($_REQUEST['guest_books_id'], isset($_REQUEST['flag']) ? $_REQUEST['flag'] : null ) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed') );
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }   

    function getLanguages() {
      global $toC_Json, $osC_Language;
      
      $languages = array();
      foreach ($osC_Language->getAll() as $l) {
        $languages[] = array('id' => $l['id'], 'text' => $l['name']);
      }
      
      $response = array(EXT_JSON_READER_ROOT => $languages);   
      
      echo $toC_Json->encode($response);
    }
    
    function saveGuestBook() {
      global $toC_Json, $osC_Language;

      $data = array('guest_books_id' => $_REQUEST['guest_books_id'] ? $_REQUEST['guest_books_id'] : null,
                    'title' => $_REQUEST['title'],
                    'email' => $_REQUEST['email'],
                    'url' => $_REQUEST['url'],
                    'content' => $_REQUEST['content'],
                    'languages_id' => $_REQUEST['languages_id'],
                    'guest_books_status' => $_REQUEST['guest_books_status']);
      
      if ( toC_Guest_Book_Admin::save($data) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteGuestBook() {
      global $toC_Json, $osC_Language;
      
      if ( toC_Guest_Book_Admin::delete($_REQUEST['guest_books_id']) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteGuestBooks() {
      global $toC_Json, $osC_Language;
      
      $error = false;
      
      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if ( !toC_Guest_Book_Admin::delete($id) ) {
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
  }
?>