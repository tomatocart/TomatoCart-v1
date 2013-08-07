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

<div class="moduleBox">

    <table border="0" width="100%" cellspacing="0" cellpadding="10" class="productListing">
      <tr>
        <td class="productListing-heading" align="center"><?php echo $osC_Language->get('listing_products_heading'); ?></td>
        <td class="productListing-heading"><?php echo $osC_Language->get('listing_comments_heading'); ?></td>
        <td class="productListing-heading" align="center" width="70"><?php echo $osC_Language->get('listing_date_added_heading'); ?></td>
        <td class="productListing-heading">&nbsp;</td>
      </tr>

  <?php
    $Qproducts = $osC_Database->query('select wp.products_id, wp.comments, wp.date_added from :table_wishlist_products wp, :table_wishlist w where w.wishlists_id = wp.wishlists_id and w.wishlists_token = :token');
    $Qproducts->bindTable(':table_wishlist_products', TABLE_WISHLISTS_PRODUCTS);
    $Qproducts->bindTable(':table_wishlist', TABLE_WISHLISTS);
    $Qproducts->bindValue(':token', $_GET['token']);
    $Qproducts->execute();
          
    $rows = 0;
    while($Qproducts->next()) {    
      $rows++;

      $osC_Product = new osC_Product($Qproducts->valueInt('products_id'));
  ?>

       <tr class="<?php echo ((($rows/2) == floor($rows/2)) ? 'productListing-even' : 'productListing-odd'); ?>">        
         <td align="center"><?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, $osC_Product->getID()), $osC_Image->show($osC_Product->getImage(), $osC_Product->getTitle(), 'hspace="5" vspace="5"')) . '<br />' . $osC_Product->getTitle() . '<br />' . $osC_Currencies->format($osC_Product->getPrice()); ?></td>         
         <td valign="top"><?php echo $Qproducts->value('comments'); ?></td>
         <td align="center" valign="top"><?php echo osC_DateTime::getShort($Qproducts->value('date_added')); ?></td>
         <td align="center" valign="top"><?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, $osC_Product->getID() . '&action=cart_add'), osc_draw_image_button('button_in_cart.gif', $osC_Language->get('button_add_to_cart'))); ?></td>
       </tr>    
              
  <?php    
    }
  ?>
    </table>
    
    <div class="submitFormButtons" style="text-align: right;">
      <?php echo osc_link_object(osc_href_link(FILENAME_DEFAULT), osc_draw_image_button('button_continue.gif', $osC_Language->get('button_continue'))); ?>
    </div>
</div>