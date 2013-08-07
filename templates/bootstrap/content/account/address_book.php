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

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<?php
  if ($messageStack->size('address_book') > 0) {
    echo $messageStack->output('address_book');
  }
?>

<div class="moduleBox">
    <h6><?php echo $osC_Language->get('primary_address_title'); ?></h6>
    
    <div class="content">
        <div class="row-fluid">
            <div class="span6">
                <?php echo osC_Address::format($osC_Customer->getDefaultAddressID(), '<br />'); ?>
            </div>
            
            <div style="span6">
                <?php echo '<b>' . $osC_Language->get('primary_address_title') . '</b><br />' . osc_image(DIR_WS_IMAGES . 'arrow_south_east.gif'); ?>
            </div>
            
            <?php echo $osC_Language->get('primary_address_description'); ?>
        </div>
    </div>
</div>

<div class="moduleBox">
    <h6><?php echo $osC_Language->get('address_book_title'); ?></h6>
    
    <div class="content">
        
        <?php
            $Qaddresses = osC_AddressBook::getListing();
            
            while ($Qaddresses->next()) {
        ?>
        
            <div class="row-fluid">
                <div class="span6">
                    <b><?php echo $Qaddresses->valueProtected('firstname') . ' ' . $Qaddresses->valueProtected('lastname'); ?></b>
                    
                    <?php
                        if ($Qaddresses->valueInt('address_book_id') == $osC_Customer->getDefaultAddressID()) {
                          echo '&nbsp;<small><i>' . $osC_Language->get('primary_address_marker') . '</i></small>';
                        }
                    ?>
                    
                    <p><?php echo osC_Address::format($Qaddresses->toArray(), '<br />'); ?></p>
                </div>
                <div class="span6 right">
                	<div class="pull-right btn-toolbar">
                    	<a href="<?php echo osc_href_link(FILENAME_ACCOUNT, 'address_book=' . $Qaddresses->valueInt('address_book_id') . '&edit', 'SSL'); ?>" class="btn btn-mini btn-inverse pull-left"><i class="icon-edit icon-white"></i> <?php echo $osC_Language->get('button_edit'); ?></a>
                    	<a href="<?php echo osc_href_link(FILENAME_ACCOUNT, 'address_book=' . $Qaddresses->valueInt('address_book_id') . '&delete', 'SSL'); ?>" class="btn btn-mini btn-inverse pull-left"><i class="icon-trash icon-white"></i> <?php echo $osC_Language->get('button_delete'); ?></a>
                	</div>
                </div>
            </div>
        <?php
            }
        ?>
    </div>
</div>

<div class="submitFormButtons">
    <span class="pull-right">
    <?php
        if ($Qaddresses->numberOfRows() < MAX_ADDRESS_BOOK_ENTRIES) {
    ?>
    	<a href="<?php echo osc_href_link(FILENAME_ACCOUNT, 'address_book&new', 'SSL'); ?>" class="btn btn-small pull-left"><i class="icon-plus icon-white"></i> <?php echo $osC_Language->get('button_add_address'); ?></a>
	<?php 
        } else {
            echo sprintf($osC_Language->get('address_book_maximum_entries'), MAX_ADDRESS_BOOK_ENTRIES);
        }
    ?>
    </span>
    
    <a href="<?php echo osc_href_link(FILENAME_ACCOUNT, null, 'SSL'); ?>" class="btn btn-small pull-left"><i class="icon-chevron-left icon-white"></i> <?php echo $osC_Language->get('button_back'); ?></a>
</div>
