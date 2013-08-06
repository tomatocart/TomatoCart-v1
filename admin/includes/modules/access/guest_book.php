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

  class osC_Access_Guest_book extends osC_Access {
    var $_module = 'guest_book',
        $_group = 'articles',
        $_icon = 'database_save.png',
        $_title,
        $_sort_order = 500;

    function osC_Access_Guest_book() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_guest_book_title');
    }
  }
?>
