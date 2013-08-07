<?php
/*
  $Id: info_sitemap.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $osC_CategoryTree->reset();
  $osC_CategoryTree->setShowCategoryProductCount(false);
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<div class="moduleBox" id="sitemap">

  <div class="content">
    <div style="float: right; width: 49%;">
      <ul>
        <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, null, 'SSL'), $osC_Language->get('sitemap_account')); ?>
          <ul>
            <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'edit', 'SSL'), $osC_Language->get('sitemap_account_edit')); ?></li>
            <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'address_book', 'SSL'), $osC_Language->get('sitemap_address_book')); ?></li>
            <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'orders', 'SSL'), $osC_Language->get('sitemap_account_history')); ?></li>
            <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'newsletters', 'SSL'), $osC_Language->get('sitemap_account_notifications')); ?></li>
          </ul>
        </li>
        <li><?php echo osc_link_object(osc_href_link(FILENAME_CHECKOUT, null, 'SSL'), $osC_Language->get('sitemap_shopping_cart')); ?></li>
        <li><?php echo osc_link_object(osc_href_link(FILENAME_CHECKOUT, 'shipping', 'SSL'), $osC_Language->get('sitemap_checkout_shipping')); ?></li>
        <li><?php echo osc_link_object(osc_href_link(FILENAME_SEARCH), $osC_Language->get('sitemap_advanced_search')); ?></li>
        <li><?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'new'), $osC_Language->get('sitemap_products_new')); ?></li>
        <li><?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'specials'), $osC_Language->get('sitemap_specials')); ?></li>
        <li><?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'reviews'), $osC_Language->get('sitemap_reviews')); ?></li>
        <li><?php echo osc_link_object(osc_href_link(FILENAME_INFO), $osC_Language->get('box_information_heading')); ?>
          <ul>
            <?php
                while($Qarticles_listing->next()) {
            ?>
            <li><?php echo osc_link_object(osc_href_link(FILENAME_INFO, 'articles&articles_id=' . $Qarticles_listing->valueInt('articles_id')), $Qarticles_listing->value('articles_name')); ?></li>
            <?php
                }
            ?>
          </ul>
        </li>
        <li>
          <?php echo osc_link_object(osc_href_link(FILENAME_INFO, 'faqs'), $osC_Language->get('info_faqs_heading'));?>
          <ul>
            <?php
                while($Qfaqs_listing->next()) {
            ?>
            <li><?php echo osc_link_object(osc_href_link(FILENAME_INFO, 'faqs&faqs_id=' . $Qfaqs_listing->valueInt('faqs_id')), $Qfaqs_listing->value('faqs_question')); ?></li>
            <?php
                }
            ?>
          </ul>
        </li>
      </ul>
    </div>
  
    <div style="width: 48%;">
      <?php echo $osC_CategoryTree->buildTree(); ?>
    </div>
    
    <div style="clear: both">&nbsp;</div>
  </div>
</div>

<div class="submitFormButtons" style="text-align: right;">
  <?php echo osc_link_object(osc_href_link(FILENAME_DEFAULT), osc_draw_image_button('button_continue.gif', $osC_Language->get('button_continue'))); ?>
</div>
