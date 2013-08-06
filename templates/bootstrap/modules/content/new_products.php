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

    global $osC_Template, $osC_Language, $osC_Image;
    require_once ('templates/' . $osC_Template->getCode() . '/models/products.php');
    
    $products = get_new_products();
?>

<?php 
    if (sizeof($products)) {
?>
    <!-- module new_products start //-->
    <div class="moduleBox">
        <h6><?php echo $osC_Box->getTitle(); ?></h6>
        
        <ul class="products-list grid clearfix">
        <?php 
            foreach ($products as $product) {
                $osC_Product = new osC_Product($product['products_id']);
        ?>
            <li class="clearfix">
            	<?php 
            	    if ($product['is_specials'] === TRUE):
            	?>
            		<div class="specials-banner"></div>
            	<?php   
            	    elseif ($product['is_featured'] === TRUE):  
            	?>
            		<div class="featured-banner"></div>
            	<?php   
            	    endif;
            	?>
                <div class="left">
                    <?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, $product['products_id']), $product['products_image'], 'id="img_ac_newproductsmodule_' . $product['products_id'] . '"'); ?> 
                    <h3><?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, $product['products_id']), $product['products_name']); ?></h3>
                    <p class="description"><?php echo strip_tags($osC_Product->getDescription()); ?></p>
                </div>
                <div class="right">
                    <span class="price"><?php echo $osC_Product->getPriceFormated(true); ?></span>
                    <span class="buttons hidden-phone">
                        <a id="ac_newproductsmodule_<?php echo $product['products_id']; ?>" class="btn btn-small btn-info ajaxAddToCart" href="<?php echo osc_href_link(FILENAME_PRODUCTS, $product['products_id'] . '&action=cart_add'); ?>">
                        	<i class="icon-shopping-cart icon-white "></i> 
                        	<?php echo $osC_Language->get('button_buy_now'); ?>
                        </a>
                        <br />
                        <?php echo osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), $product['products_id'] . '&action=wishlist_add'), $osC_Language->get('add_to_wishlist'), 'class="wishlist"'); ?>
                        <?php
                          if ($osC_Template->isInstalled('compare_products', 'boxes')) {
                              echo  '<br />' . osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), osc_get_all_get_params() . '&cid=' . $product['products_id'] . '&' . '&action=compare_products_add'), $osC_Language->get('add_to_compare'), 'class="compare"');
                          }
                        ?>
                    </span>
                </div>
            </li>
        <?php 
            }
        ?>
        </ul>
    </div>
    
    <!-- module new_products end //-->
<?php 
    }
?>