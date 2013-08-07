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

<form name="account_notifications" action="<?php echo osc_href_link(FILENAME_ACCOUNT, 'notifications=save', 'SSL'); ?>" method="post">

    <div class="moduleBox">
        <h6><?php echo $osC_Language->get('newsletter_product_notifications'); ?></h6>
        
        <div class="content">
            <?php echo $osC_Language->get('newsletter_product_notifications_description'); ?>
        </div>
    </div>

    <div class="moduleBox">
        <h6><?php echo $osC_Language->get('newsletter_product_notifications_global'); ?></h6>
        
        <div class="content">
            <div class="control-group">
            	<label class="checkbox"  for="product_global">
                	<input type="checkbox" id="product_global" name="product_global" value="1" <?php echo (($Qglobal->value('global_product_notifications') == '1') ? ' checked="checked"' : ''); ?> /><?php echo $osC_Language->get('newsletter_product_notifications_global'); ?>
                </label>
            </div>
            
            <p><?php echo $osC_Language->get('newsletter_product_notifications_global_description'); ?></p>
        </div>
    </div>

<?php
  if ($Qglobal->valueInt('global_product_notifications') != '1') {
?>

    <div class="moduleBox">
        <h6><?php echo $osC_Language->get('newsletter_product_notifications_products'); ?></h6>
        
        <div class="content">
    
    <?php
        if ($osC_Template->hasCustomerProductNotifications($osC_Customer->getID())) {
    ?>
    		<p><?php echo $osC_Language->get('newsletter_product_notifications_products_description'); ?></p>
    
    <?php
          $Qproducts = $osC_Template->getListing();
          $counter = 0;
    
          while ($Qproducts->next()) {
            $counter++;
    ?>
            <div class="control-group">
            	<label class="checkbox"  for="<?php echo 'products[' . $counter . ']'; ?>">
                	<input type="checkbox" id="<?php echo 'products[' . $counter . ']'; ?>" name="<?php echo 'products[' . $counter . ']'; ?>" value="<?php echo $Qproducts->valueInt('products_id'); ?>" checked="checked" /><?php echo $Qproducts->value('products_name'); ?>
                </label>
            </div>
            
                <tr>
                    <td width="30"><?php echo osc_draw_checkbox_field('products[' . $counter . ']', $Qproducts->valueInt('products_id'), true); ?></td>
                    <td><b><?php echo osc_draw_label($Qproducts->value('products_name'), 'products[' . $counter . ']'); ?></b></td>
                </tr>
    
    <?php
          }
    ?>
    
    <?php
        } else {
          echo $osC_Language->get('newsletter_product_notifications_products_none');
        }
    ?>
    
		</div>
    </div>

<?php
    }
?>

    <div class="submitFormButtons" style="text-align: right;">
		<button class="btn btn-small pull-right"><i class="icon-chevron-right icon-white"></i> <?php echo $osC_Language->get('button_continue'); ?></button>
    </div>

</form>
