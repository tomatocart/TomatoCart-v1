<?php
/*
  $Id: debug.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Services_debug {
    function start() {
      global $messageStack, $osC_Language;

      if (SERVICE_DEBUG_SHOW_DEVELOPMENT_WARNING == '1') {
        $messageStack->add('debug', 'This is a development version of TomatoCart (' . PROJECT_VERSION . ') - please use it for testing purposes only! [' . __CLASS__ . ']');
      }

      if (SERVICE_DEBUG_CHECK_LOCALE == '1') {
        $setlocale = osc_setlocale(LC_TIME, explode(',', $osC_Language->getLocale()));

        if (($setlocale === false) || ($setlocale === null)) {
          $messageStack->add('debug', 'Error: Locale does not exist: ' . $osC_Language->getLocale() . ' [' . __CLASS__ . ']', 'error');
        }
      }

      if ((SERVICE_DEBUG_CHECK_INSTALLATION_MODULE == '1') && file_exists(dirname($_SERVER['SCRIPT_FILENAME']) . '/install')) {
        $messageStack->add('debug', sprintf($osC_Language->get('warning_install_directory_exists'), dirname($_SERVER['SCRIPT_FILENAME']) . '/install') . ' [' . __CLASS__ . ']', 'warning');
      }

      if ((SERVICE_DEBUG_CHECK_CONFIGURATION == '1') && file_exists(dirname($_SERVER['SCRIPT_FILENAME']) . '/includes/configure.php') && is_writeable(dirname($_SERVER['SCRIPT_FILENAME']) . '/includes/configure.php')) {
        $messageStack->add('debug', sprintf($osC_Language->get('warning_config_file_writeable'), dirname($_SERVER['SCRIPT_FILENAME']) . '/includes/configure.php') . ' [' . __CLASS__ . ']', 'warning');
      }

      if ((SERVICE_DEBUG_CHECK_SESSION_DIRECTORY == '1') && (STORE_SESSIONS == '')) {
        if (!is_dir($osC_Session->getSavePath())) {
          $messageStack->add('debug', sprintf($osC_Language->get('warning_session_directory_non_existent'), $osC_Session->getSavePath()) . ' [' . __CLASS__ . ']', 'warning');
        } elseif (!is_writeable($osC_Session->getSavePath())) {
          $messageStack->add('debug', sprintf($osC_Language->get('warning_session_directory_not_writeable'), $osC_Session->getSavePath()) . ' [' . __CLASS__ . ']', 'warning');
        }
      }

      if ((SERVICE_DEBUG_CHECK_SESSION_AUTOSTART == '1') && (bool)ini_get('session.auto_start')) {
        $messageStack->add('debug', $osC_Language->get('warning_session_auto_start') . ' [' . __CLASS__ . ']', 'warning');
      }

      if ((SERVICE_DEBUG_CHECK_DOWNLOAD_DIRECTORY == '1') && (DOWNLOAD_ENABLED == '1')) {
        if (!is_dir(DIR_FS_DOWNLOAD)) {
          $messageStack->add('debug', sprintf($osC_Language->get('warning_download_directory_non_existent'), DIR_FS_DOWNLOAD) . ' [' . __CLASS__ . ']', 'warning');
        }
      }

      return true;
    }

    function stop() {
      global $messageStack, $osC_Template;

      $time_start = explode(' ', PAGE_PARSE_START_TIME);
      $time_end = explode(' ', microtime());
      $parse_time = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);

      if (!osc_empty(SERVICE_DEBUG_EXECUTION_TIME_LOG)) {
        if (!@error_log(strftime('%c') . ' - ' . $_SERVER['REQUEST_URI'] . ' (' . $parse_time . 's)' . "\n", 3, SERVICE_DEBUG_EXECUTION_TIME_LOG)) {
          if (!file_exists(SERVICE_DEBUG_EXECUTION_TIME_LOG) || !is_writable(SERVICE_DEBUG_EXECUTION_TIME_LOG)) {
            $messageStack->add('debug', 'Error: Execution time log file not writeable: ' . SERVICE_DEBUG_EXECUTION_TIME_LOG . ' [' . __CLASS__ . ']', 'error');
          }
        }
      }

      if (SERVICE_DEBUG_EXECUTION_DISPLAY == '1') {
        $messageStack->add('debug', 'Execution Time: ' . $parse_time . 's [' . __CLASS__ . ']', 'warning');
      }

      if ( $osC_Template->showDebugMessages() && ($messageStack->size('debug') > 0) ) {
        echo $messageStack->output('debug');
      }

      return true;
    }
  }
?>
