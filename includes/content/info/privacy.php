<?php
/*
  $Id: privacy.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Info_Privacy extends osC_Template {

/* Private variables */

    var $_module = 'privacy',
        $_group = 'info',
        $_page_title,
        $_page_contents = 'info_privacy.php',
        $_page_image = 'table_background_specials.gif';

/* Class constructor */

    function osC_Info_Privacy() {
      global $osC_Services, $osC_Language, $breadcrumb;

      $this->_page_title = $osC_Language->get('info_privacy_heading');

      if ($osC_Services->isStarted('breadcrumb')) {
        $breadcrumb->add($osC_Language->get('breadcrumb_privacy'), osc_href_link(FILENAME_INFO, $this->_module));
      }
    }
  }
?>
