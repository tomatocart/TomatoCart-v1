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
  if ($messageStack->size('wishlist') > 0) {
    echo $messageStack->output('wishlist');
  }
?>

<div class="moduleBox">
    <?php 
        if ($toC_Wishlist->hasContents()) {
    ?>
	<form name="update_wishlist" method="post" action="<?php echo osc_href_link(FILENAME_ACCOUNT, 'wishlist=update', 'SSL'); ?>">
    
		<table class="table table-hover table-striped">
          	<thead>
                <tr>
                  <th align="center"><?php echo $osC_Language->get('listing_products_heading'); ?></th>
                  <th><?php echo $osC_Language->get('listing_comments_heading'); ?></th>
                  <th width="100" class="visible-desktop"><?php echo $osC_Language->get('listing_date_added_heading'); ?></th>
                  <th class="visible-desktop"></th>
                </tr>
          	</thead>
          	<tbody>
  <?php
      $rows = 0;
      foreach($toC_Wishlist->getProducts() as $product) {    
        $rows++;

  ?>
                <tr>        
                    <td class="center" width="150"><?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, (!osc_empty($product['variants']) ? str_replace('#', '_', osc_get_product_id_string($product['products_id'], $product['variants'])) : $product['products_id'])), $osC_Image->show($product['image'], $product['name'], 'hspace="5" vspace="5"')) . '<br />' . $product['name'] . '<br />' . $osC_Currencies->format($product['price']); ?></td>         
                    <td><?php echo osc_draw_textarea_field('comments[' . $product['products_id'] . ']', $product['comments'], 20, 5, 'id="comments_' . $product['products_id'] . '"'); ?></td>
                    <td width="96" class="visible-desktop"><?php echo $product['date_added']; ?></td>
                    <td class="center btn-toolbar visible-desktop">
            			<a href="<?php echo osc_href_link(FILENAME_ACCOUNT, 'wishlist=delete&products_id=' . $product['products_id']); ?>" class="btn btn-mini btn-inverse pull-left"><?php echo $osC_Language->get('button_delete'); ?></a>&nbsp;
						<a href="<?php echo osc_href_link(FILENAME_PRODUCTS, $product['products_id'] . '&action=cart_add' . (!osc_empty($product['variants']) ? '&variants=' . osc_parse_variants_array($product['variants']) : '')); ?>" class="btn btn-mini btn-inverse"><?php echo $osC_Language->get('button_buy_now'); ?></a>
                    </td>
                </tr>    
  <?php    
      }
  ?>
			</tbody>
		</table>
        <div class="submitFormButtons right">
            <a href="javascript:void(0);" class="btn btn-small pull-left" onclick="javascript:window.history.go(-1);return false;"><i class="icon-chevron-left icon-white"></i> <?php echo $osC_Language->get('button_back'); ?></a>
            
            <button type="submit" class="btn btn-small pull-right"><i class="icon-ok-sign icon-white"></i> <?php echo $osC_Language->get('button_continue'); ?></button>
        </div>
     </form>
	<?php        
        }else { 
    ?>            
    <div class="content btop">
		<p><?php echo $osC_Language->get('wishlist_empty'); ?></p>
    </div>
      
    <div class="submitFormButtons right">
	    <a href="javascript:void(0);" class="btn btn-small pull-left" onclick="javascript:window.history.go(-1);return false;"><i class="icon-chevron-left icon-white"></i> <?php echo $osC_Language->get('button_back'); ?></a>
    </div>
	<?php
        } 
    ?>
</div>

<?php 
    if ($toC_Wishlist->hasContents()) {
?>
<div class="moduleBox">

	<h6><em class="pull-right"><?php echo $osC_Language->get('form_required_information'); ?></em><?php echo $osC_Language->get('share_your_wishlist_title'); ?></h6>

    <form name="share_wishlist" id="share_wishlist" method="post" action="<?php echo osc_href_link(FILENAME_ACCOUNT, 'wishlist=share_wishlist', 'SSL'); ?>" class="form-horizontal">     
        <div class="content">   
            <div class="control-group">
                <label class="control-label" for="wishlist_customer"><?php echo $osC_Language->get('field_share_wishlist_customer_name'); ?><em>*</em></label>
                <div class="controls">
                	<?php echo osc_draw_input_field('wishlist_customer', ($osC_Customer->isLoggedOn() ? $osC_Customer->getName() : null)); ?>
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label" for="wishlist_from_email"><?php echo $osC_Language->get('field_share_wishlist_customer_email'); ?><em>*</em></label>
                <div class="controls">
                	<?php echo osc_draw_input_field('wishlist_from_email', ($osC_Customer->isLoggedOn() ? $osC_Customer->getEmailAddress() : null)); ?>
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label" for="wishlist_emails"><?php echo $osC_Language->get('field_share_wishlist_emails'); ?><em>*</em></label>
                <div class="controls">
                	<?php echo osc_draw_textarea_field('wishlist_emails', null, 40, 5); ?>
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label" for="wishlist_message"><?php echo $osC_Language->get('field_share_wishlist_message'); ?><em>*</em></label>
                <div class="controls">
                	<?php echo osc_draw_textarea_field('wishlist_message', null, 40, 5); ?>
                </div>
            </div>
        </div>   
        
        <div class="submitFormButtons right">
            <button type="submit" class="btn btn-small pull-right"><i class="icon-ok-sign icon-white"></i> <?php echo $osC_Language->get('button_continue'); ?></button>
        </div>
    </form>
</div>
<?php 
    }
?>    