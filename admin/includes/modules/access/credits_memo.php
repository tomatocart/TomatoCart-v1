<?php
/*
  $Id: credits_memo.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Credits_Memo extends osC_Access {
    var $_module = 'credits_memo',
        $_group = 'customers',
        $_icon = 'orders.png',
        $_title,
        $_sort_order = 900;

    function osC_Access_Credits_Memo() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_credits_memo_title');
    }
  }
?>
