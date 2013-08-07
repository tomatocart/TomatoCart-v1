<?php
/*
  $Id: json.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/application_top.php');
  require('includes/ext_config.php');
  require('includes/classes/json.php');
  
  header('Expires: Thu, 01 Jan 1970 01:00:00 GMT');  
  header('Cache-Control: must-revalidate, cache, public');
  header('Pragma: public');
//  header('Content-Type: application/json, charset=utf-8');
  
  $dir_fs_www_root = dirname(__FILE__);
  
  $toC_Json = new toC_Json();
  $toC_Json->parse();
?>