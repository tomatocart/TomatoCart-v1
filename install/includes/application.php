<?php
/*
  $Id: application.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

// Set the level of error reporting
  if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
    error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE & ~E_DEPRECATED);
  } else {
    error_reporting(E_ALL & ~E_NOTICE);
  }

  define('DEFAULT_LANGUAGE', 'en_US');
  define('HTTP_COOKIE_PATH', '');
  define('HTTPS_COOKIE_PATH', '');
  define('HTTP_COOKIE_DOMAIN', '');
  define('HTTPS_COOKIE_DOMAIN', '');

  require('../includes/functions/compatibility.php');

  require('../includes/functions/general.php');
  require('functions/general.php');
  require('../includes/functions/html_output.php');

  require('../includes/classes/database.php');

  require('../includes/classes/xml.php');

  session_start();

  require('../admin/includes/classes/directory_listing.php');

  require('includes/classes/language.php');
  $osC_Language = new osC_LanguageInstall();

  header('Content-Type: text/html; charset=' . $osC_Language->getCharacterSet());
?>
