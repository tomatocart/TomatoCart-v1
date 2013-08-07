<?php
/*
  $Id: reviews_new.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<div style="float: right;"><?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, $osC_Product->getID()), $osC_Image->show($osC_Product->getImage(), $osC_Product->getTitle(), 'hspace="5" vspace="5"', 'mini')); ?></div>

<h1><?php echo $osC_Template->getPageTitle() . ($osC_Product->hasSKU() ? '<br /><span class="smallText">' . $osC_Product->getSKU() . '</span>' : ''); ?></h1>

<div style="clear: both;"></div>

<?php
  if ($messageStack->size('reviews') > 0) {
    echo $messageStack->output('reviews');
  }
?>

<form name="reviews_new" action="<?php echo osc_href_link(FILENAME_PRODUCTS, 'reviews=new&' . $osC_Product->getID() . '&action=process'); ?>" method="post" onsubmit="return checkForm(this);">

<div class="moduleBox">
  <h6><?php echo $osC_Language->get('new_review_title'); ?></h6>

  <div class="content">
    <ol>

<?php
  if (!$osC_Customer->isLoggedOn()) {
?>

      <li><?php echo osc_draw_label(ENTRY_NAME, null, 'customer_name') . osc_draw_input_field('customer_name'); ?></li>
      <li><?php echo osc_draw_label($osC_Language->get('field_customer_email_address'), null, 'customer_email_address') . osc_draw_input_field('customer_email_address'); ?></li>

<?php
  }
?>

      <li><?php echo osc_draw_textarea_field('review', null, null, 15, 'style="width: 98%;"'); ?></li>
      <li><?php echo $osC_Language->get('field_review_rating') . ' ' . $osC_Language->get('review_lowest_rating_title') . ' ' . osc_draw_radio_field('rating', array('1', '2', '3', '4', '5')) . ' ' . $osC_Language->get('review_highest_rating_title'); ?></li>
    </ol>
  </div>
</div>

<div class="submitFormButtons">
  <span style="float: right;"><?php echo osc_draw_image_submit_button('button_continue.gif', $osC_Language->get('button_continue')); ?></span>

  <?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'reviews&' . $osC_Product->getID()), osc_draw_image_button('button_back.gif', $osC_Language->get('button_back'))); ?>
</div>

</form>
