<?php
/*
  $Id: rss.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Index_Rss extends osC_Template {

/* Private variables */

    var $_module = 'rss',
        $_group = 'index',
        $_page_title,
        $_page_contents = 'rss.php',
        $_page_image = 'table_background_default.gif';

/* Class constructor */
    function osC_Index_Rss() {
      global $osC_Services, $osC_Language, $breadcrumb;

      $this->_page_title = $osC_Language->get('rss_heading');

      if ($osC_Services->isStarted('breadcrumb')) {
        $breadcrumb->add($this->_page_title, osc_href_link(FILENAME_DEFAULT, $this->_module));
      }
    }
    
  }
?>
