<?php
/*
  $Id: pdf.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  header('Cache-Control: no-cache, must-revalidate');
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
  header("Last-Modified: " . gmdate("D,d M Y H:i:s") . " GMT");
  header("Pragma: no-cache");
  header("Content-Type: application/pdf");
  
  require('includes/application_top.php');
  require('includes/classes/toc_pdf.php');
  
  toc_verify_token();
  
  $dir_fs_www_root = dirname(__FILE__);
  
  if (isset($_SESSION['admin'])) {
    if (isset($_REQUEST['module'])) {
      $module = $_REQUEST['module'];
      $osC_Language->loadIniFile($module . '.php');
    } 
    
    if (isset($_REQUEST['pdf'])) {
      $pdf = preg_replace('/[^a-zA-Z_]/iu', '', $_REQUEST['pdf']);
    } 
    
    if (!empty($module) && !empty($pdf)) {
  
      if (file_exists('includes/modules/pdf/' . $pdf . '.php')) {
        include('includes/modules/pdf/' . $pdf . '.php');
        
        $pdf_class = 'toC_' .ucfirst($pdf) . '_PDF';
        $object = new $pdf_class();
        $object ->render();
        
        exit;
      }
    }
  } else {
    echo 'Please login to generate the pdf document.';
  }
?>