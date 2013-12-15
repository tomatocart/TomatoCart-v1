<?php
/*
  $Id: application_top.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

// start the timer for the page parse time log
  define('PAGE_PARSE_START_TIME', microtime());

  define('TOC_IN_ADMIN', true);

// set the level of error reporting to E_ALL except E_NOTICE
  if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
    error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE & ~E_DEPRECATED);
  } else {
    error_reporting(E_ALL & ~E_NOTICE);
  }

// set the local configuration parameters - mainly for developers
  if ( file_exists('../includes/local/configure.php') ) {
    include('../includes/local/configure.php');
  }

// include server parameters
  require('../includes/configure.php');
  
// include tomatocart constants
  require('../includes/toc_constants.php');  
    
// Define the project version
  define('PROJECT_VERSION', 'TomatoCart v1.1.8.6');

// set the type of request (secure or not)
  $request_type = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on')) ? 'SSL' : 'NONSSL';

  if ($request_type == 'NONSSL') {
    define('DIR_WS_CATALOG', DIR_WS_HTTP_CATALOG);
  } else {
    define('DIR_WS_CATALOG', DIR_WS_HTTPS_CATALOG);
  }

  if ( ($request_type == 'NONSSL') && (ENABLE_SSL == true) ) {
    header('Location: ' . HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . DIR_FS_ADMIN);
  }

// compatibility work-around logic for PHP4
  require('../includes/functions/compatibility.php');
  require('includes/functions/compatibility.php');

// include the list of project filenames
  require('includes/filenames.php');

// include the list of project database tables
  require('../includes/database_tables.php');

// initialize the cache class
  require('../includes/classes/cache.php');
  $osC_Cache = new osC_Cache();

// include the administrators log class
  require('includes/classes/administrators_log.php');

// include the database class
  require('../includes/classes/database.php');

  $osC_Database = osC_Database::connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
  $osC_Database->selectDatabase(DB_DATABASE);

// set application wide parameters
  $Qcfg = $osC_Database->query('select configuration_key as cfgKey, configuration_value as cfgValue from :table_configuration');
  $Qcfg->bindTable(':table_configuration', TABLE_CONFIGURATION);
  $Qcfg->setCache('configuration');
  $Qcfg->execute();

  while ($Qcfg->next()) {
    define($Qcfg->value('cfgKey'), $Qcfg->value('cfgValue'));
  }

  $Qcfg->freeResult();

// define our general functions used application-wide
  require('../includes/functions/general.php');
  require('includes/functions/general.php');

  require('../includes/functions/html_output.php');
  require('includes/functions/html_output.php');

// include session class
  include('includes/classes/session.php');
  $osC_Session = new osC_Session_Admin('toCAdminID');
  $osC_Session->start();
  
  require('includes/classes/directory_listing.php');
  require('includes/classes/access.php');

  require('../includes/classes/address.php');
  require('../includes/classes/weight.php');
  require('../includes/classes/xml.php');
  require('../includes/classes/datetime.php');
  
  //check http host
  if ($_SERVER['HTTP_HOST'] != HTTP_COOKIE_DOMAIN) {
    if ($_SERVER['HTTPS'] == 'on') {
      osc_redirect_admin(HTTPS_SERVER . DIR_WS_HTTP_CATALOG . DIR_FS_ADMIN);
    } else {
      osc_redirect_admin(HTTP_SERVER . DIR_WS_HTTP_CATALOG . DIR_FS_ADMIN);
    }
  }

// set the language
  require('includes/classes/language.php');
  $osC_Language = new osC_Language_Admin();

  if (isset($_GET['admin_language']) && !empty($_GET['admin_language'])) {
    $osC_Language->set($_GET['admin_language']);
  }

  $osC_Language->loadIniFile();

  header('Content-Type: text/html; charset=' . $osC_Language->getCharacterSet());

  osc_setlocale(LC_TIME, explode(',', $osC_Language->getLocale()));

// define our localization functions
  require('includes/functions/localization.php');

// initialize the message stack for output messages
  require('../includes/classes/message_stack.php');
  $osC_MessageStack = new messageStack();
  $osC_MessageStack->loadFromSession();

// entry/item info classes
  require('includes/classes/object_info.php');

// email class
  require('../includes/classes/mail.php');

// file uploading class
  require('includes/classes/upload.php');

  // check if a default currency is set
  if (!defined('DEFAULT_CURRENCY')) {
    $osC_MessageStack->add('header', $osC_Language->get('ms_error_no_default_currency'), 'error');
  }

// check if a default language is set
  if (!defined('DEFAULT_LANGUAGE')) {
    $osC_MessageStack->add('header', ERROR_NO_DEFAULT_LANGUAGE_DEFINED, 'error');
  }

  if (function_exists('ini_get') && ((bool)ini_get('file_uploads') == false) ) {
    $osC_MessageStack->add('header', $osC_Language->get('ms_warning_uploads_disabled'), 'warning');
  }
?>