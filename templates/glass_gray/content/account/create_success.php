<?php
/*
  $Id: create_success.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  if ($osC_NavigationHistory->hasSnapshot()) {
    $origin_href = $osC_NavigationHistory->getSnapshotURL();
    $osC_NavigationHistory->resetSnapshot();
  } else {
    $origin_href = osc_href_link(FILENAME_DEFAULT);
  }
?>

<div class="moduleBox">
  <h1><?php echo $osC_Template->getPageTitle(); ?></h1>
  
  <div class="content">
    <div style="float: left;"><?php echo osc_image('templates/' . $osC_Template->getCode() . '/images/account_successs.png', $osC_Template->getPageTitle()); ?></div>
  
    <div style="padding-top: 30px;">
      <p><?php echo sprintf($osC_Language->get('success_account_created'), osc_href_link(FILENAME_INFO, 'contact')); ?></p>
    </div>

    <div style="clear: both"></div>
  </div>
</div>

<div class="submitFormButtons" style="text-align: right;">
  <?php echo osc_link_object($origin_href, osc_draw_image_button('button_continue.gif', $osC_Language->get('button_continue'))); ?>
</div>