<?php
/*
  $Id: account.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<h1><?php echo$osC_Template->getPageTitle(); ?></h1>

<?php
  if ($messageStack->size('account') > 0) {
    echo $messageStack->output('account');
  }
?>

<div class="moduleBox">
  <h6><?php echo $osC_Language->get('my_account_title'); ?></h6>

  <div class="content">
    <div style="float:left"><?php echo osc_image('templates/' . $osC_Template->getCode() . '/images/my_account.png', $osC_Language->get('my_account_title')); ?></div>

    <ul style="float:left;padding-left:40px;list-style-image: url(<?php echo osc_href_link('templates/' . $osC_Template->getCode() . '/images/arrow_gray.png', null, 'SSL'); ?>);">
      <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'edit', 'SSL'), $osC_Language->get('my_account_information')); ?></li>
      <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'address_book', 'SSL'), $osC_Language->get('my_account_address_book')); ?></li>
      <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'password', 'SSL'), $osC_Language->get('my_account_password')); ?></li>
    </ul>

    <div style="clear: both;"></div>
  </div>
</div>

<div class="moduleBox">
  <h6><?php echo $osC_Language->get('my_orders_title'); ?></h6>

  <div class="content">
    <div style="float:left"><?php echo osc_image('templates/' . $osC_Template->getCode() . '/images/my_orders.png', $osC_Language->get('my_orders_title')); ?></div>

    <ul style="float:left;padding-left:40px; list-style-image: url(<?php echo osc_href_link('templates/' . $osC_Template->getCode() . '/images/arrow_gray.png', null, 'SSL'); ?>);">
      <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'orders', 'SSL'), $osC_Language->get('my_orders_view')); ?></li>
      <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'orders=list_return_requests', 'SSL'), $osC_Language->get('my_orders_return_view')); ?></li>
      <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'orders=list_credit_slips', 'SSL'), $osC_Language->get('my_credit_slips_view')); ?></li>
    </ul>

    <div style="clear: both;"></div>
  </div>
</div>

<div class="moduleBox">
  <h6><?php echo $osC_Language->get('my_wishlist_title'); ?></h6>

  <div class="content">
    <div style="float:left"><?php echo osc_image('templates/' . $osC_Template->getCode() . '/images/my_wishlist.png', $osC_Language->get('my_wishlist_title')); ?></div>

    <ul style="float:left;padding-left:40px; list-style-image: url(<?php echo osc_href_link('templates/' . $osC_Template->getCode() . '/images/arrow_gray.png', null, 'SSL'); ?>);">
      <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'wishlist', 'SSL'), $osC_Language->get('my_wishlist_view')); ?></li>
    </ul>

    <div style="clear: both;"></div>
  </div>
</div>

<div class="moduleBox">
  <h6><?php echo $osC_Language->get('my_notifications_title'); ?></h6>

  <div class="content">
    <div style="float:left"><?php echo osc_image('templates/' . $osC_Template->getCode() . '/images/my_notifications.png', $osC_Language->get('my_notifications_title')); ?></div>

    <ul style="float:left;padding-left:40px; list-style-image: url(<?php echo osc_href_link('templates/' . $osC_Template->getCode() . '/images/arrow_gray.png', null, 'SSL'); ?>);">
      <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'newsletters', 'SSL'), $osC_Language->get('my_notifications_newsletters')); ?></li>
      <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'notifications', 'SSL'), $osC_Language->get('my_notifications_products')); ?></li>
    </ul>

    <div style="clear: both;"></div>
  </div>
</div>
