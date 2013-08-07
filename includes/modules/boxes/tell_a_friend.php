<?php
/*
  $Id: tell_a_friend.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Boxes_tell_a_friend extends osC_Modules {
    var $_title,
        $_code = 'tell_a_friend',
        $_author_name = 'osCommerce',
        $_author_www = 'http://www.oscommerce.com',
        $_group = 'boxes';

    function osC_Boxes_tell_a_friend() {
      global $osC_Language;

      $this->_title = $osC_Language->get('box_tell_a_friend_heading');
    }

    function initialize() {
      global $osC_Language, $osC_Template, $osC_Product;

      if (isset($osC_Product) && is_a($osC_Product, 'osC_Product') && ($osC_Template->getModule() != 'tell_a_friend')) {
        $this->_content = '<form name="tell_a_friend" action="' . osc_href_link(FILENAME_PRODUCTS, 'tell_a_friend&' . $osC_Product->getID()) . '" method="post">' . "\n" .
                          osc_draw_input_field('to_email_address', null, 'style="width: 80%;"') . '&nbsp;' . osc_draw_image_submit_button('button_tell_a_friend.gif', $osC_Language->get('box_tell_a_friend_text')) . '<br />' . $osC_Language->get('box_tell_a_friend_text') . "\n" .
                          '</form>' . "\n";
      }
    }
  }
?>
