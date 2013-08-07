<?php
/*
  $Id: cache.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2004 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.

  Class usage examples:

  - Caching HTML:
    if ($osC_Cache->read('key', 60) === false) {
      $osC_Cache->startBuffer();
      ------ PHP/HTML LOGIC HERE ------
      $osC_Cache->stopBuffer();
    }

    echo $osC_Cache->getCache();

  - Caching data (in memory):
    if ($osC_Cache->read('key', 60) {
      $variable = $osC_Cache->getCache();
    } else {
      $variable = array('some', 'data');

      $osC_Cache->writeBuffer($variable);
    }
*/

  class osC_Cache {
    var $cached_data,
        $cache_key;

    function write($key, &$data) {
      $filename = DIR_FS_WORK . $key . '.cache';

      if ($fp = @fopen($filename, 'w')) {
        flock($fp, 2); // LOCK_EX
        fputs($fp, serialize($data));
        flock($fp, 3); // LOCK_UN
        fclose($fp);

        return true;
      }

      return false;
    }

    function read($key, $expire = 0) {
      $this->cache_key = $key;

      $filename = DIR_FS_WORK . $key . '.cache';

      if (file_exists($filename)) {
        $difference = floor((time() - filemtime($filename)) / 60);

        if ( ($expire == '0') || ($difference < $expire) ) {
          if ($fp = @fopen($filename, 'r')) {
            $this->cached_data = unserialize(fread($fp, filesize($filename)));

            fclose($fp);

            return true;
          }
        }
      }

      return false;
    }

    function &getCache() {
      return $this->cached_data;
    }

    function startBuffer() {
      ob_start();
    }

    function stopBuffer() {
      $this->cached_data = ob_get_contents();

      ob_end_clean();

      $this->write($this->cache_key, $this->cached_data);
    }

    function writeBuffer(&$data) {
      $this->cached_data = $data;

      $this->write($this->cache_key, $this->cached_data);
    }

    function clear($key) {      
      $key_length = strlen($key);

      $d = opendir(DIR_FS_WORK);

      while (($entry = readdir($d)) !== false) {
        if ((strlen($entry) >= $key_length) && (substr($entry, 0, $key_length) == $key)) {
          @unlink(DIR_FS_WORK . $entry);
        }
      }

      closedir($d);     
    }
  }
?>
