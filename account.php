<?php
/*
  $Id: account.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $_SERVER['SCRIPT_FILENAME'] = __FILE__;

  require('includes/application_top.php');

  if ($osC_Customer->isLoggedOn() === false) {
    if (!empty($_GET)) {
      $first_array = array_slice($_GET, 0, 1);
    }

    if (empty($_GET) || (!empty($_GET) && !in_array(osc_sanitize_string(basename(key($first_array))), array('login', 'create', 'password_forgotten', 'wishlist')))) {
      $osC_NavigationHistory->setSnapshot();

      osc_redirect(osc_href_link(FILENAME_ACCOUNT, 'login', 'SSL'));
    }
  }

  $osC_Language->load('account');

  if ($osC_Services->isStarted('breadcrumb')) {
    $breadcrumb->add($osC_Language->get('breadcrumb_my_account'), osc_href_link(FILENAME_ACCOUNT, null, 'SSL'));
  }

  $osC_Template = osC_Template::setup('account');

  require('templates/' . $osC_Template->getCode() . '/index.php');

  require('includes/application_bottom.php');
?>
