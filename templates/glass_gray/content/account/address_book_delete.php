<?php
/*
  $Id: address_book_delete.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $Qentry = osC_AddressBook::getEntry($_GET['address_book']);
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<div class="moduleBox">
  <h6><?php echo $osC_Language->get('address_book_delete_address_title'); ?></h6>

  <div class="content">
    <div style="float: right; padding: 0px 0px 10px 20px;">
      <?php echo osC_Address::format($_GET['address_book'], '<br />'); ?>
    </div>

    <div style="float: right; padding: 0px 0px 10px 20px; text-align: center;">
      <?php echo '<b>' . $osC_Language->get('selected_address_title') . '</b><br />' . osc_image(DIR_WS_IMAGES . 'arrow_south_east.gif'); ?>
    </div>

    <?php echo $osC_Language->get('address_book_delete_address_description'); ?>

    <div style="clear: both;"></div>
  </div>
</div>

<div class="submitFormButtons">
  <span style="float: right;"><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'address_book=' . $_GET['address_book'] . '&delete=confirm', 'SSL'), osc_draw_image_button('button_delete.gif', $osC_Language->get('button_delete'))); ?></span>

  <?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'address_book', 'SSL'), osc_draw_image_button('button_back.gif', $osC_Language->get('button_back'))); ?>
</div>
