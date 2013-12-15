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
  
// set the level of error reporting to E_ALL except E_NOTICE
  if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
    error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE & ~E_DEPRECATED);
  } else {
    error_reporting(E_ALL & ~E_NOTICE);
  }

// set the local configuration parameters - mainly for developers
  if ( file_exists('includes/local/configure.php') ) {
    include('includes/local/configure.php');
  }

// include server parameters
  require('includes/configure.php');
  
// include tomatocart constants
  require('includes/toc_constants.php');  

// redirect to the installation module if DB_SERVER is empty
  if (strlen(DB_SERVER) < 1) {
    if (is_dir('install')) {
      header('Location: install/index.php');
    }
  }

// define the project version
  define('PROJECT_VERSION', 'TomatoCart v1.1.8.6');

// set the type of request (secure or not)
  $request_type = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on')) ? 'SSL' : 'NONSSL';

  if ($request_type == 'NONSSL') {
    define('DIR_WS_CATALOG', DIR_WS_HTTP_CATALOG);
  } else {
    define('DIR_WS_CATALOG', DIR_WS_HTTPS_CATALOG);
  }

// compatibility work-around logic for PHP4
  require('includes/functions/compatibility.php');

// include the list of project filenames
  require('includes/filenames.php');

// include the list of project database tables
  require('includes/database_tables.php');

// initialize the message stack for output messages
  require('includes/classes/message_stack.php');
  $messageStack = new messageStack();

// initialize the cache class
  require('includes/classes/cache.php');
  $osC_Cache = new osC_Cache();

// include the database class
  require('includes/classes/database.php');

// make a connection to the database... now
  $osC_Database = osC_Database::connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
  $osC_Database->selectDatabase(DB_DATABASE);
  
// set the application parameters

  $Qcfg = $osC_Database->query('select configuration_key as cfgKey, configuration_value as cfgValue from :table_configuration');
  $Qcfg->bindTable(':table_configuration', TABLE_CONFIGURATION);
  $Qcfg->setCache('configuration');
  $Qcfg->execute();

  while ($Qcfg->next()) {
    define($Qcfg->value('cfgKey'), $Qcfg->value('cfgValue'));
  }

  $Qcfg->freeResult();
  
//set the default timezone
if (defined('STORE_TIME_ZONE') && STORE_TIME_ZONE) {
  if (!date_default_timezone_set(STORE_TIME_ZONE)) {
    date_default_timezone_set('UTC');
  }
}

// include functions
  require('includes/functions/general.php');
  require('includes/functions/html_output.php');

// include and start the services
  require('includes/classes/services.php');
  $osC_Services = new osC_Services();

  $osC_Services->startServices();
  
// check database connection
  if (!$osC_Database->isConnected()) {
    $messageStack->add('db_error', $osC_Language->get('db_connection_failed'));
  }

// Maintenance Mode
  if(MAINTENANCE_MODE == 1) {
    //login maintenance mode
    if (isset($_GET['maintenance']) && ($_GET['maintenance'] == 'login')) {
      require('includes/classes/administrators.php');
      
      if (toC_Administrators::login($_POST['user_name'], $_POST['user_password']) === false ) {
        $messageStack->add('maintenance', $osC_Language->get('error_admin_login_no_match'));
      } else {
        osc_redirect(osc_href_link(FILENAME_DEFAULT));
      }
    } 

    //logoff maintenance mode
    if (isset($_GET['maintenance']) && ($_GET['maintenance'] == 'logoff')) {
      unset($_SESSION['admin']);
      
      osc_redirect(osc_href_link(FILENAME_DEFAULT));
    }
  
    if ( !isset($_SESSION['admin']) || empty($_SESSION['admin']) ) {
      require('templates/system/offline.php');
      exit;
    }
  }
?>