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

  $Qreviews = osC_Reviews::getListing();
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<?php
  while ($Qreviews->next()) {
?>

<div class="moduleBox">
  <h6>
    <span style="float: right; margin-right: 5px"><?php echo osC_DateTime::getShort($Qreviews->value('date_added')); ?></span>
    <?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qreviews->valueInt('products_id')), $Qreviews->value('products_name')); ?> (<?php echo sprintf($osC_Language->get('reviewed_by'), $Qreviews->valueProtected('customers_name')); ?>)
  </h6>

  <div class="content">

<?php
    if (!osc_empty($Qreviews->value('image'))) {
      echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qreviews->valueInt('products_id')), $osC_Image->show($Qreviews->value('image'), $Qreviews->value('products_name'), 'style="float: left"'));
    }
?>

    <p style="padding-left: 120px;"><?php echo wordwrap($Qreviews->valueProtected('reviews_text'), 60, '&nbsp;') . ((strlen($Qreviews->valueProtected('reviews_text')) >= 100) ? '..' : '') . '<br /><br /><i>' . sprintf($osC_Language->get('review_rating'), osc_image(DIR_WS_IMAGES . 'stars_' . $Qreviews->valueInt('reviews_rating') . '.png', sprintf($osC_Language->get('rating_of_5_stars'), $Qreviews->valueInt('reviews_rating'))), sprintf($osC_Language->get('rating_of_5_stars'), $Qreviews->valueInt('reviews_rating'))) . '</i>'; ?></p>

    <div style="clear: both;"></div>
  </div>
</div>

<?php
  }
?>

<div class="listingPageLinks">
  <span style="float: right;"><?php echo $Qreviews->getBatchPageLinks('page', 'reviews'); ?></span>

  <?php echo $Qreviews->getBatchTotalPages($osC_Language->get('result_set_number_of_reviews')); ?>
</div>
