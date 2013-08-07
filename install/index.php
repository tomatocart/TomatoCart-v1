<?php
/*
  $Id: index.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2004 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/application.php');

  $page_contents = 'step_1.php';
  
  if (isset($_GET['step']) && is_numeric($_GET['step'])) {
    switch ($_GET['step']) {
      case '2':
        $page_contents = 'step_2.php';
        break;
            
      case '3':
        $page_contents = 'step_3.php';
        break;    
        
      case '4':
        $page_contents = 'step_4.php';
        break;

      case '5':
        $page_contents = 'step_5.php';
        break;

      case '6':
        $page_contents = 'step_6.php';
        break;
    }
  }

  require('templates/main_page.php');
?>
