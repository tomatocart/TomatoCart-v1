<?php
/*
  $Id: slide_images.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Slide_Images extends osC_Access {
    var $_module = 'slide_images',
        $_group = 'articles',
        $_icon = 'image.png',
        $_title,
        $_sort_order = 400;

    function osC_Access_Slide_Images() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_slide_images_title');
    }
  }
?>
