<?php
/*
  $Id: reviews_info.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $Qreviews = osC_Reviews::getEntry($_GET[$osC_Template->getModule()]);
?>

<h1 style="float: right;"><?php echo $osC_Product->getPriceFormated(true); ?></h1>

<h1><?php echo $osC_Template->getPageTitle() . ($osC_Product->hasSKU() ? '<br /><span class="smallText">' . $osC_Product->getSKU() . '</span>' : ''); ?></h1>

<?php
  if ($messageStack->size('reviews') > 0) {
    echo $messageStack->output('reviews');
  }

  if ($osC_Product->hasImage()) {
?>

<div style="float: right; text-align: center; margin: 10px">
  <?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'images&' . $osC_Product->getID()), $osC_Image->show($osC_Product->getImage(), $osC_Product->getTitle(), 'hspace="5" vspace="5"', 'thumbnail'), 'target="_blank" onclick="window.open(\'' . osc_href_link(FILENAME_PRODUCTS, 'images&' . $osC_Product->getID()) . '\', \'popUp\', \'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=' . (($osC_Product->numberOfImages() > 1) ? $osC_Image->getWidth('large') + ($osC_Image->getWidth('thumbnails') * 2) + 70 : $osC_Image->getWidth('large') + 20) . ',height=' . ($osC_Image->getHeight('large') + 20) . '\'); return false;"'); ?>
  <?php echo '<p>' . osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), $osC_Product->getID() . '&' . osc_get_all_get_params(array('action')) . '&action=cart_add'), osc_draw_image_button('button_in_cart.gif', $osC_Language->get('button_add_to_cart'))) . '</p>'; ?>
</div>

<?php
  }
?>

<p><?php echo osc_image(DIR_WS_IMAGES . 'stars_' . $Qreviews->valueInt('reviews_rating') . '.png', sprintf($osC_Language->get('rating_of_5_stars'), $Qreviews->valueInt('reviews_rating'))) . '&nbsp;' . sprintf($osC_Language->get('reviewed_by'), $Qreviews->valueProtected('customers_name')) . '; ' . osC_DateTime::getLong($Qreviews->value('date_added')); ?></p>

<p><?php echo nl2br(wordwrap($Qreviews->valueProtected('reviews_text'), 60, '&shy;')); ?></p>

<div class="submitFormButtons">

<?php
  if ($osC_Reviews->is_enabled === true) {
?>

    <span style="float: right;"><?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'reviews=new&' . $osC_Product->getID()), osc_draw_image_button('button_write_review.gif', $osC_Language->get('button_write_review'))); ?></span>

<?php
  }
?>

  <?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'reviews&' . $osC_Product->getID()), osc_draw_image_button('button_back.gif', $osC_Language->get('button_back'))); ?>
</div>
