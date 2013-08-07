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
	<span style="float: right;">
		<a href="<?php echo osc_href_link(FILENAME_ACCOUNT, 'address_book=' . $_GET['address_book'] . '&delete=confirm', 'SSL'); ?>" class="btn btn-small"><i class="icon-ok-sign icon-white"></i> <?php echo $osC_Language->get('button_delete'); ?></a>
	</span>

	<a href="<?php echo osc_href_link(FILENAME_ACCOUNT, 'address_book', 'SSL'); ?>" class="btn btn-small pull-left"><i class="icon-chevron-left icon-white"></i> <?php echo $osC_Language->get('button_back'); ?></a>
</div>
