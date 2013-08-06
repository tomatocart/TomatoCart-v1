<?php
/*
  $Id: checkout.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $_SERVER['SCRIPT_FILENAME'] = __FILE__;

  require('includes/application_top.php');

  $osC_NavigationHistory->setSnapshot();
  
  $osC_Language->load('checkout');

  if ($osC_Services->isStarted('breadcrumb')) {
    $breadcrumb->add($osC_Language->get('breadcrumb_checkout'), osc_href_link(FILENAME_CHECKOUT, null, 'SSL'));
  }

  $osC_Template = osC_Template::setup('cart');

  require('templates/' . $osC_Template->getCode() . '/index.php');

  require('includes/application_bottom.php');
?>
