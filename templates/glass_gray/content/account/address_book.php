<?php
/*
  $Id: address_book.php $
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
  if ($messageStack->size('address_book') > 0) {
    echo $messageStack->output('address_book');
  }
?>

<div class="moduleBox">
  <h6><?php echo $osC_Language->get('primary_address_title'); ?></h6>

  <div class="content">
    <div style="float: right; padding: 0px 0px 10px 20px;">
      <?php echo osC_Address::format($osC_Customer->getDefaultAddressID(), '<br />'); ?>
    </div>

    <div style="float: right; padding: 0px 0px 10px 20px; text-align: center;">
      <?php echo '<b>' . $osC_Language->get('primary_address_title') . '</b><br />' . osc_image(DIR_WS_IMAGES . 'arrow_south_east.gif'); ?>
    </div>

    <?php echo $osC_Language->get('primary_address_description'); ?>

    <div style="clear: both;"></div>
  </div>
</div>

<div class="moduleBox">
  <h6><?php echo $osC_Language->get('address_book_title'); ?></h6>

  <div class="content">
    <table border="0" width="100%" cellspacing="0" cellpadding="2">

<?php
  $Qaddresses = osC_AddressBook::getListing();

  while ($Qaddresses->next()) {
?>

      <tr class="moduleRow" onmouseover="rowOverEffect(this);" onmouseout="rowOutEffect(this);">
        <td>
          <b><?php echo $Qaddresses->valueProtected('firstname') . ' ' . $Qaddresses->valueProtected('lastname'); ?></b>

<?php
    if ($Qaddresses->valueInt('address_book_id') == $osC_Customer->getDefaultAddressID()) {
      echo '&nbsp;<small><i>' . $osC_Language->get('primary_address_marker') . '</i></small>';
    }
?>

        </td>
        <td align="right"><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'address_book=' . $Qaddresses->valueInt('address_book_id') . '&edit', 'SSL'), osc_draw_image_button('small_edit.gif', $osC_Language->get('button_edit'))) . '&nbsp;' . osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'address_book=' . $Qaddresses->valueInt('address_book_id') . '&delete', 'SSL'), osc_draw_image_button('small_delete.gif', $osC_Language->get('button_delete'))); ?></td>
      </tr>
      <tr>
        <td colspan="2" style="padding: 0px 0px 10px 10px;"><?php echo osC_Address::format($Qaddresses->toArray(), '<br />'); ?></td>
      </tr>

<?php
  }
?>

    </table>
  </div>
</div>

<div class="submitFormButtons">
  <span style="float: right;">

<?php
  if ($Qaddresses->numberOfRows() < MAX_ADDRESS_BOOK_ENTRIES) {
    echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'address_book&new', 'SSL'), osc_draw_image_button('button_add_address.gif', $osC_Language->get('button_add_address')));
  } else {
    echo sprintf($osC_Language->get('address_book_maximum_entries'), MAX_ADDRESS_BOOK_ENTRIES);
  }
?>

  </span>

  <?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, null, 'SSL'), osc_draw_image_button('button_back.gif', $osC_Language->get('button_back'))); ?>
</div>
