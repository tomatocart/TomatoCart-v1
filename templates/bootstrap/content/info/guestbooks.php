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
  
  $Qlisting = toC_Guestbook::getListing();
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<?php 
    if ($messageStack->size('guestbook') > 0) {
        echo $messageStack->output('guestbook');
    }
?>   

<div class="moduleBox">
	<h6><?php echo $osC_Template->getPageTitle(); ?></h6>

    <div class="content">
        <?php 
            if ($Qlisting->numberOfRows() > 0) { 
        ?>
        <dl id="guestbook">
            <?php 
                while ($Qlisting->next()){
            ?>
            <dt>
            	<span class="pull-right"><?php echo osC_DateTime::getShort($Qlisting->value('date_added'));  ?></span>
                <?php echo $Qlisting->value('title'); ?>
            </dt>
            <dd><?php echo $Qlisting->value('content'); ?></dd>
            <?php 
               }
               
               $Qlisting->freeResult();
            ?>
        </dl>          
        <?php 
            } else {
        ?>
        <p><?php echo $osC_Language->get('field_guestbook_no_records'); ?></p>
        <?php 
            }
        ?>
    </div>
</div>

<div class="submitFormButtons">
	<a class="btn btn-small pull-right" href="<?php echo osc_href_link(FILENAME_INFO, 'guestbook=new'); ?>"><i class="icon-plus icon-white"></i> <?php echo $osC_Language->get('button_write_message'); ?></a>
</div>