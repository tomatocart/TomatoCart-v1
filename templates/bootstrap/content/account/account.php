<?php
/**
 * TomatoCart Open Source Shopping Cart Solution
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License v3 (2007)
 * as published by the Free Software Foundation.
 *
 * @package      TomatoCart
 * @author       TomatoCart Dev Team
 * @copyright    Copyright (c) 2009 - 2012, TomatoCart. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html
 * @link         http://tomatocart.com
 * @since        Version 1.1.8
 * @filesource
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
        <div class="row-fluid">
            <div class="span2"><?php echo osc_image('templates/' . $osC_Template->getCode() . '/img/my_account.png', $osC_Language->get('my_account_title')); ?></div>
            
            <ul class="span10">
                <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'edit', 'SSL'), $osC_Language->get('my_account_information')); ?></li>
                <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'address_book', 'SSL'), $osC_Language->get('my_account_address_book')); ?></li>
                <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'password', 'SSL'), $osC_Language->get('my_account_password')); ?></li>
            </ul>
        </div>
    </div>
</div>

<div class="moduleBox">
    <h6><?php echo $osC_Language->get('my_orders_title'); ?></h6>
    
    <div class="content">
        <div class="row-fluid">
            <div class="span2"><?php echo osc_image('templates/' . $osC_Template->getCode() . '/img/my_orders.png', $osC_Language->get('my_orders_title')); ?></div>
            
            <ul class="span10">
                <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'orders', 'SSL'), $osC_Language->get('my_orders_view')); ?></li>
                <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'orders=list_return_requests', 'SSL'), $osC_Language->get('my_orders_return_view')); ?></li>
                <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'orders=list_credit_slips', 'SSL'), $osC_Language->get('my_credit_slips_view')); ?></li>
            </ul>
        </div>
    </div>
</div>

<div class="moduleBox">
    <h6><?php echo $osC_Language->get('my_wishlist_title'); ?></h6>
    
    <div class="content">
        <div class="row-fluid">
            <div class="span2"><?php echo osc_image('templates/' . $osC_Template->getCode() . '/img/my_wishlist.png', $osC_Language->get('my_wishlist_title')); ?></div>
            
            <ul class="span10">
            	<li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'wishlist', 'SSL'), $osC_Language->get('my_wishlist_view')); ?></li>
            </ul>
        </div>
    </div>
</div>

<div class="moduleBox">
    <h6><?php echo $osC_Language->get('my_notifications_title'); ?></h6>
    
    <div class="content">
        <div class="row-fluid">
            <div class="span2"><?php echo osc_image('templates/' . $osC_Template->getCode() . '/img/my_notifications.png', $osC_Language->get('my_notifications_title')); ?></div>
            
            <ul class="span10">
                <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'newsletters', 'SSL'), $osC_Language->get('my_notifications_newsletters')); ?></li>
                <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'notifications', 'SSL'), $osC_Language->get('my_notifications_products')); ?></li>
            </ul>
        </div>
    </div>
</div>