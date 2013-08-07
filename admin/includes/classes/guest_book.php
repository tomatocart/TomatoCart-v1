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

  class toC_Guest_Book_Admin {
    function getData($id) {
      global $osC_Database, $osC_Language;
  
      $QguestBook = $osC_Database->query('select * from :table_guest_books where guest_books_id = :guest_books_id');
      $QguestBook->bindTable(':table_guest_books', TABLE_GUEST_BOOKS);
      $QguestBook->bindInt(':guest_books_id', $id);
      $QguestBook->execute();
  
      $data = $QguestBook->toArray();
  
      $QguestBook->freeResult();
  
      return $data;
    }
  
    function setStatus($id, $flag){
      global $osC_Database;
  
      $QguestBook = $osC_Database->query('update :table_guest_books set guest_books_status = :guest_books_status where guest_books_id = :guest_books_id');
      $QguestBook->bindTable(':table_guest_books', TABLE_GUEST_BOOKS);
      $QguestBook->bindInt(':guest_books_status', $flag);
      $QguestBook->bindInt(':guest_books_id', $id);
      $QguestBook->setLogging($_SESSION['module'], $id);
  
      $QguestBook->execute();
      
      if ( !$osC_Database->isError() ) {
        osC_Cache::clear('box-guest-book');
        
        return true;
      }
  
      return false;
    }
    
    function save($data) {
      global $osC_Database, $osC_Language;
  
      if( isset($data['guest_books_id']) && !empty($data['guest_books_id']) ) {
  	    $QguestBook = $osC_Database->query('update :table_guest_books set title = :title, email = :email, url = :url, guest_books_status = :guest_books_status,  content = :content where guest_books_id = :guest_books_id');
  	    $QguestBook->bindInt(':guest_books_id', $data['guest_books_id']);
      } else {
        $QguestBook = $osC_Database->query('insert into :table_guest_books (title, email, url, guest_books_status, languages_id, content, date_added) values(:title, :email, :url, :guest_books_status, :languages_id, :content, now())');
      }
      
      $QguestBook->bindTable(':table_guest_books', TABLE_GUEST_BOOKS);
      $QguestBook->bindValue(':title', $data['title']);
      $QguestBook->bindValue(':email', $data['email']);
      $QguestBook->bindValue(':url', $data['url']);
      $QguestBook->bindInt(':guest_books_status', $data['guest_books_status']);
      $QguestBook->bindInt(':languages_id', $data['languages_id']);
      $QguestBook->bindValue(':content', $data['content']);
      $QguestBook->setLogging($_SESSION['module'], $data['guest_books_id']);
      $QguestBook->execute();
      
      if ( !$osC_Database->isError() ) {
        osC_Cache::clear('box-guest-book');
        
        return true;
      }
      
      return false;
    }
    
    function delete($id) {
      global $osC_Database;
     
      $QguestBook = $osC_Database->query('delete from :table_guest_books where guest_books_id = :guest_books_id');
      $QguestBook->bindTable(':table_guest_books', TABLE_GUEST_BOOKS);
      $QguestBook->bindInt(':guest_books_id',$id);
      $QguestBook->setLogging($_SESSION['module'], $id);
      $QguestBook->execute();     
  
      if (!$osC_Database->isError()) {
        osC_Cache::clear('box-guest-book');
        
        return true;
      }
  
      return false;
    }  
  }
?>
