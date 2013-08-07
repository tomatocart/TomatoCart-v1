<?php
/*
  $Id: compatibility.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

/**
 * Register Globals -- This feature has been DEPRECATED as of PHP 5.3.0. Relying on this feature is highly discouraged.
 * 
 * Forcefully disable register_globals if enabled
 *
 * Based from work by Richard Heyes (http://www.phpguru.org)
 */

  if ((int)ini_get('register_globals') > 0) {
    if (isset($_REQUEST['GLOBALS'])) {
      die('GLOBALS overwrite attempt detected');
    }

    $noUnset = array('GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');

    $input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES, isset($_SESSION) ? (array)$_SESSION : array());

    foreach ($input as $k => $v) {
      if (!in_array($k, $noUnset) && isset($GLOBALS[$k])) {
        unset($GLOBALS[$k]);
      }
    }

    unset($noUnset);
    unset($input);
    unset($k);
    unset($v);
  }

/**
 * Magic Quotes -- This feature has been DEPRECATED as of PHP 5.3.0. Relying on this feature is highly discouraged.
 * 
 * Forcefully disable magic_quotes_gpc if enabled
 *
 * Based from work by Ilia Alshanetsky (Advanced PHP Security)
 */
  
  if ((int)get_magic_quotes_gpc() > 0) {
    $in = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);

    while (list($k, $v) = each($in)) {
      foreach ($v as $key => $val) {
        if (!is_array($val)) {
          $in[$k][$key] = stripslashes($val);

          continue;
        }

        $in[] =& $in[$k][$key];
      }
    }

    unset($in);
    unset($k);
    unset($v);
    unset($key);
    unset($val);
  }

/**
 * checkdnsrr() natively supported from PHP 5.3.0.
 */

  if (!function_exists('checkdnsrr')) {
    function checkdnsrr($host, $type) {
      if(!empty($host) && !empty($type)) {
        @exec('nslookup -type=' . escapeshellarg($type) . ' ' . escapeshellarg($host), $output);

        foreach ($output as $k => $line) {
          if(eregi('^' . $host, $line)) {
            return true;
          }
        }
      }

      return false;
    }
  }


  function osc_strrpos_string($haystack, $needle, $offset = 0) {
    if ( !empty($haystack) && !empty($needle) && ( $offset <= strlen($haystack) ) ) {
      $last_pos = $offset;
      $found = false;

      while ( ( $curr_pos = strpos($haystack, $needle, $last_pos) ) !== false ) {
        $found = true;
        $last_pos = $curr_pos + 1;
      }

      if ( $found === true ) {
        return $last_pos - 1;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }
?>
