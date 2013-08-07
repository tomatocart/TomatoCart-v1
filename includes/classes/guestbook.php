<?php
/*
  $Id: guestbook.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Guestbook {
  
    function saveEntry($data) {
      global $osC_Database, $osC_Language;
      
      $QguestBook = $osC_Database->query('insert into :table_guest_books (title, email, url, guest_books_status, languages_id, content, date_added) values(:title, :email, :url, 0, :languages_id, :content, now())');
      $QguestBook->bindTable(':table_guest_books', TABLE_GUEST_BOOKS);
      $QguestBook->bindValue(':title', $data['title']);
      $QguestBook->bindValue(':email', $data['email']);
      $QguestBook->bindValue(':url', $data['url']);
      $QguestBook->bindInt(':languages_id', $osC_Language->getID());
      $QguestBook->bindValue(':content', $data['content']);
      $QguestBook->execute();
      
      if ($QguestBook->affectedRows() === 1) {
        return true;
      }
      
      return false;
    }
    
    function &getListing() {
      global $osC_Database, $osC_Language;

      $Qlisting = $osC_Database->query('select guest_books_id, title, email, url, guest_books_status, languages_id, content, date_added from :table_guest_books where guest_books_status = 1 and languages_id = :languages_id order by guest_books_id desc');
      $Qlisting->bindTable(':table_guest_books', TABLE_GUEST_BOOKS);
      $Qlisting->bindInt(':languages_id', $osC_Language->getID());
      $Qlisting->execute();

      return $Qlisting;
    }
  }
?>
