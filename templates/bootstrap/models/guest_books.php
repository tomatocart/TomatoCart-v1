<?php
/**
 * TomatoCart Open Source Shopping Cart Solution
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License v3 (2007)
 * as published by the Free Software Foundation.
 *
 * @package      TomatoCart
 * @author       TomatoCart Dev Team
 * @copyright    Copyright (c) 2009 - 2012, TomatoCart. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html
 * @link         http://tomatocart.com
 * @since        Version 1.1.8
 * @filesource
*/

/**
 * Get guest book
 * 
 * @access public
 * @return mixed
 */
function get_guest_book() {
    global $osC_Database, $osC_Language;
    
    $QguestBook = $osC_Database->query('select guest_books_id, title, url, content, date_added from :table_guest_books where guest_books_status = 1 and languages_id = :languages_id order by guest_books_id desc limit :guest_book_list');
    $QguestBook->bindTable(':table_guest_books', TABLE_GUEST_BOOKS);
    $QguestBook->bindInt(':languages_id', $osC_Language->getID());
    $QguestBook->bindInt(':guest_book_list', BOX_GUEST_BOOK_LIST);
    $QguestBook->execute();
    
    if ($QguestBook->numberOfRows() > 0) {
        $data = array();
        
        while ($QguestBook->next()) {
            $data[] = array('title' => $QguestBook->value('title'), 'content' => $QguestBook->value('content'));
        }
        
        $QguestBook->freeResult();
                
        return $data;
    }
                              
    $QguestBook->freeResult();
    
    return NULL;
}