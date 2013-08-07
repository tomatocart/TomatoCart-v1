<?php
/*
  $Id: output_compression.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Services_output_compression {
    function start() {
      if (extension_loaded('zlib')) {
        if ((int)ini_get('zlib.output_compression') < 1) {
          ini_set('zlib.output_compression_level', SERVICE_OUTPUT_COMPRESSION_GZIP_LEVEL);
          ob_start('ob_gzhandler');

          return false; // no call to stop() is needed
        }
      }

      return false;
    }

    function stop() {
      return true;
    }
  }
?>
