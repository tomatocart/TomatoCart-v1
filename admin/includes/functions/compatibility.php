<?php
/*
  $Id: compatibility.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

/*
 * posix_getpwuid() not implemented on Microsoft Windows platforms
 */

//  if (!function_exists('posix_getpwuid')) {
//    function posix_getpwuid($id) {
//      return '-?-';
//    }
//  }

/*
 * posix_getgrgid() not implemented on Microsoft Windows platforms
 */

//  if (!function_exists('posix_getgrgid')) {
//    function posix_getgrgid($id) {
//      return '-?-';
//    }
//  }
?>
