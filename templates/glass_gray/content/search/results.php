<?php
/*
  $Id: results.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<?php
  require('includes/modules/product_listing.php');
?>

<div class="submitFormButtons">
  <?php echo osc_link_object(osc_href_link(FILENAME_SEARCH), osc_draw_image_button('button_back.gif', $osC_Language->get('button_back'))); ?>
</div>
