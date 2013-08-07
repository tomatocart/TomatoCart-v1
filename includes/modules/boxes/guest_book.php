<?php
/*
  $Id: guest_book.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Boxes_guest_book extends osC_Modules {
    var $_title,
        $_code = 'guest_book',
        $_author_name = 'TomaotCart',
        $_author_www = 'http://www.tomatocart.com',
        $_group = 'boxes';

    function osC_Boxes_guest_book() {
      global $osC_Language;

      $this->_title = $osC_Language->get('box_guest_book_heading');
    }

    function initialize() {
      global $osC_Database, $osC_Language;
      
      $this->_title_link = osc_href_link(FILENAME_INFO, 'guestbook');
      
      $QguestBook = $osC_Database->query('select guest_books_id, title, url, content, date_added from :table_guest_books where guest_books_status = 1 and languages_id = :languages_id order by guest_books_id desc limit :guest_book_list');
      $QguestBook->bindTable(':table_guest_books', TABLE_GUEST_BOOKS);
      $QguestBook->bindInt(':languages_id', $osC_Language->getID());
      $QguestBook->bindInt(':guest_book_list', BOX_GUEST_BOOK_LIST);
      $QguestBook->setCache('box-guest-book-' . $osC_Language->getCode());
      $QguestBook->execute();
      
      if ($QguestBook->numberOfRows() > 0) {
        $this->_content = '<dl>';
        
        while ($QguestBook->next()) {
          $this->_content .= '<dt>' . $QguestBook->value('title') . '</dt>';
          $this->_content .= '<dd>' . $QguestBook->value('content') . '</dd>';
        }
                
        $this->_content .= '</dl>';
      }
      
      $this->_content .= '<p align="right">' . osc_link_object(osc_href_link(FILENAME_INFO, 'guestbook'), osc_draw_image_button('small_read_more.png', $osC_Language->get('button_read_more'))) . '</p>';
                                  
      $QguestBook->freeResult();
    }
  
    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Amount of Entries', 'BOX_GUEST_BOOK_LIST', '3', 'Amount of entries displayed in the guestbook box', '6', '0', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('BOX_GUEST_BOOK_LIST');
      }

      return $this->_keys;
    }
  }
?>
