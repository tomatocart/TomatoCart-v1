<?php
/*
  $Id: main_page.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $template = 'main_page';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $osC_Language->getCharacterSet(); ?>" />

<link rel="shortcut icon" href="images/tomatocart.ico" type="image/x-icon" />

<title>TomatoCart, Open Source Shopping Cart Solutions</title>

<meta name="robots" content="noindex,nofollow">

<link rel="stylesheet" type="text/css" href="templates/main_page/stylesheet.css">

<link rel="stylesheet" type="text/css" href="ext/niftycorners/niftyCorners.css">
<script type="text/javascript" src="ext/niftycorners/nifty.js"></script>

</head>

<body>

<div id="wapper">
  <div id="pageHeader">
    <div style="float: right; padding-top: 40px; padding-right: 15px; color: #000000; font-weight: bold;"><a href="http://www.tomatocart.com" target="_blank"><?php echo $osC_Language->get('head_tomatocart_support_title'); ?></a> &nbsp;|&nbsp;<a href="http://www.tomatocart.com/index.php/community/forum" target="_blank"><?php echo $osC_Language->get('head_tomatocart_support_forum_title'); ?></a> &nbsp;|&nbsp; <a href="http://www.oscommerce.com" target="_blank"><?php echo $osC_Language->get('head_oscommerce_support_title'); ?></a></div>
    <a href="index.php"><img src="images/logo.png" border="0" title="TomatoCart, Open Source Shopping Cart Solutions" style="margin: 10px 10px 0px 10px;" /></a>
  </div>

  <div id="pageContent">
    <div id="pageNav">
      
      <?php $step = (isset($_REQUEST['step']) && is_numeric($_REQUEST['step'])) ? $_REQUEST['step'] : 1;?>
      
      <ul>
        <li class="title"><?php echo $osC_Language->get('nav_menu_title'); ?></li>
        <li class="<?php echo ($step == 1) ? 'current' : ''; ?>"><?php echo $osC_Language->get('nav_menu_step_1_text'); ?></li>
        <li class="<?php echo ($step == 2) ? 'current' : ''; ?>"><?php echo $osC_Language->get('nav_menu_step_2_text'); ?></li>
        <li class="<?php echo ($step == 3) ? 'current' : ''; ?>"><?php echo $osC_Language->get('nav_menu_step_3_text'); ?></li>
        <li class="<?php echo ($step == 4) ? 'current' : ''; ?>"><?php echo $osC_Language->get('nav_menu_step_4_text'); ?></li>
        <li class="<?php echo ($step == 5) ? 'current' : ''; ?>"><?php echo $osC_Language->get('nav_menu_step_5_text'); ?></li>
        <li class="<?php echo ($step == 6) ? 'last' : ''; ?>"><?php echo $osC_Language->get('nav_menu_step_6_text'); ?></li>
      </ul>
  	  <div id="mBox">
  	    <div id="mBoxContents"></div>
  	  </div>    
    </div>
    
  	<div id="mainBlock">
  	  <?php require('templates/pages/' . $page_contents); ?>
  	</div>
  	<div class="clear"></div>
  </div>

  <div id="pageFooter">
    <?php echo $osC_Language->get('foot_copy_text'); ?>
  </div>
</div>
</body>
</html>
