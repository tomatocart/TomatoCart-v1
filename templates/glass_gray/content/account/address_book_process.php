<?php
/*
  $Id: address_book_process.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  if (isset($_GET['edit'])) {
    $Qentry = osC_AddressBook::getEntry($_GET['address_book']);
  } else {
    if (osC_AddressBook::numberOfEntries() >= MAX_ADDRESS_BOOK_ENTRIES) {
      $messageStack->add('address_book', $osC_Language->get('error_address_book_full'));
    }
  }
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<?php
  if ($messageStack->size('address_book') > 0) {
    echo $messageStack->output('address_book');
  }

  if ( ($osC_Customer->hasDefaultAddress() === false) || (isset($_GET['new']) && (osC_AddressBook::numberOfEntries() < MAX_ADDRESS_BOOK_ENTRIES)) || (isset($Qentry) && ($Qentry->numberOfRows() === 1)) ) {
?>

<form name="address_book" action="<?php echo osc_href_link(FILENAME_ACCOUNT, 'address_book=' . $_GET['address_book'] . '&' . (isset($_GET['edit']) ? 'edit' : 'new') . '=save', 'SSL'); ?>" method="post" onsubmit="return check_form(address_book);">

<div class="moduleBox">

  <h6><em><?php echo $osC_Language->get('form_required_information'); ?></em><?php echo $osC_Language->get('address_book_new_address_title'); ?></h6>

  <div class="content">

<?php
    include('includes/modules/address_book_details.php');
?>
  
  </div>
</div>

<div class="submitFormButtons">
  <span style="float: right;"><?php echo osc_draw_image_submit_button('button_continue.gif', $osC_Language->get('button_continue')); ?></span>
  
<?php
    if ($osC_NavigationHistory->hasSnapshot()) {
      $back_link = $osC_NavigationHistory->getSnapshotURL();
    } elseif ($osC_Customer->hasDefaultAddress() === false) {
      $back_link = osc_href_link(FILENAME_ACCOUNT, null, 'SSL');
    } else {
      $back_link = osc_href_link(FILENAME_ACCOUNT, 'address_book', 'SSL');
    }
      
    echo osc_link_object($back_link, osc_draw_image_button('button_back.gif', $osC_Language->get('button_back')));
?>

</div>

</form>

<?php
  } else {
?>

<div class="submitFormButtons">
  <?php osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'address_book', 'SSL'), osc_draw_image_button('button_back.gif', $osC_Language->get('button_back'))); ?>
</div>

<?php
  }
?>
