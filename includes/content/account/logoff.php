<?php
/*
  $Id: logoff.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Account_Logoff extends osC_Template {

/* Private variables */

    var $_module = 'logoff',
        $_group = 'account',
        $_page_title,
        $_page_contents = 'logoff.php';

/* Class constructor */

    function osC_Account_Logoff() {
      global $osC_Language, $osC_Services, $breadcrumb;

      $this->_page_title = $osC_Language->get('sign_out_heading');

      if ($osC_Services->isStarted('breadcrumb')) {
        $breadcrumb->add($osC_Language->get('breadcrumb_sign_out'));
      }

      $this->_process();
    }

/* Private methods */

    function _process() {
      global $osC_ShoppingCart, $osC_Customer, $toC_Wishlist, $osC_Session;

      $osC_ShoppingCart->reset();

      $osC_Customer->reset();
      
      $toC_Wishlist->reset();
      
      if (SERVICE_SESSION_REGENERATE_ID == '1') {
        $osC_Session->recreate();
      }
    }
  }
?>
