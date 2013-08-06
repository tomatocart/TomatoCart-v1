<?php
/*
  $Id: search.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Boxes_search extends osC_Modules {
    var $_title,
        $_code = 'search',
        $_author_name = 'osCommerce',
        $_author_www = 'http://www.oscommerce.com',
        $_group = 'boxes';

    function osC_Boxes_search() {
      global $osC_Language;

      $this->_title = $osC_Language->get('box_search_heading');
    }

    function initialize() {
      global $osC_Language;

      $this->_title_link = osc_href_link(FILENAME_SEARCH);

      $this->_content = '<form name="search" action="' . osc_href_link(FILENAME_SEARCH, null, 'NONSSL', false) . '" method="get">' .
                        osc_draw_input_field('keywords', null, 'style="width: 80%;" maxlength="30"') . '&nbsp;' . osc_draw_hidden_session_id_field() . osc_draw_image_submit_button('button_quick_find.gif', $osC_Language->get('box_search_heading')) . '<br />' . sprintf($osC_Language->get('box_search_text'), osc_href_link(FILENAME_SEARCH)) .
                        '</form>';
    }
  }
?>
