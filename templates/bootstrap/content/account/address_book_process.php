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

<form name="address_book" action="<?php echo osc_href_link(FILENAME_ACCOUNT, 'address_book=' . $_GET['address_book'] . '&' . (isset($_GET['edit']) ? 'edit' : 'new') . '=save', 'SSL'); ?>" method="post" onsubmit="return check_form(address_book);" class="form-horizontal">

    <div class="moduleBox">
        <h6><em class="pull-right"><?php echo $osC_Language->get('form_required_information'); ?></em><?php echo $osC_Language->get('address_book_new_address_title'); ?></h6>
        
        <div class="content">
        
        <?php
            include('templates/' . $osC_Template->getCode() . '/modules/address_book_details.php');
        ?>
        
        </div>
    </div>
    
    <div class="submitFormButtons">
    	<button type="submit" class="btn btn-small pull-right"><i class="icon-ok-sign icon-white"></i> <?php echo $osC_Language->get('button_continue'); ?></button>
        
    	<a href="<?php echo osc_href_link(FILENAME_ACCOUNT, 'address_book', 'SSL'); ?>" class="btn btn-small pull-left"><i class="icon-chevron-left icon-white"></i> <?php echo $osC_Language->get('button_back'); ?></a>
    </div>

</form>
<?php
  } else {
?>

    <div class="submitFormButtons">
    	<a href="<?php echo osc_href_link(FILENAME_ACCOUNT, 'address_book', 'SSL'); ?>" class="btn btn-small pull-left"><i class="icon-chevron-left icon-white"></i> <?php echo $osC_Language->get('button_back'); ?></a>
    </div>
<?php
  }
?>
