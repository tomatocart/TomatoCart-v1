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

    $order = new osC_Order($_GET['orders_id']);
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<?php
    if ($messageStack->size('orders') > 0) {
        echo $messageStack->output('orders');
    }
?>

<form name="return_request" action="<?php echo osc_href_link(FILENAME_ACCOUNT, 'orders=save_return_request&orders_id=' . $order->getID(), 'SSL'); ?>" method="post">

    <div class="moduleBox">
        
		<table class="table table-hover table-striped">    
			<thead>
                <tr>
                    <th><?php echo $osC_Language->get('listing_products_heading'); ?></td>
                    <th align="center"><?php echo $osC_Language->get('listing_price_heading'); ?></td>
                    <th align="center"><?php echo $osC_Language->get('listing_quantity_heading'); ?></td>
                </tr>
            </thead>
            <tbody>
            <?php 
                $rows = 1;
                foreach ($order->products as $product) {
                    $available_return_qty = $product['qty'] - $order->getProductReturnedQuantity($product['orders_products_id']);
                    
                    if (ALLOW_RETURN_REQUEST == '1') {
                        $allow_return = true;
                        
                        if (($product['type'] == PRODUCT_TYPE_DOWNLOADABLE) && (ALLOW_DOWNLOADABLE_RETURN == '-1')) {
                            $allow_return = false;
                        } else if (($product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) && (ALLOW_GIFT_CERTIFICATE_RETURN == '-1')) {
                            $allow_return = false;
                        }
                    } else {
                        $allow_return = false;
                    }
                    
                    if (($available_return_qty > 0) && $allow_return) {      
            ?>
                <tr>
                    <td>
                        <?php echo osc_draw_hidden_field('products_name[' . $product['orders_products_id'] . ']', $product['name']) . osc_draw_checkbox_field('return_items[' . $product['orders_products_id'] . ']') . '&nbsp;' . $product['qty'] . '&nbsp;x&nbsp;' . $product['name']; ?>
                        <?php 
                            if (isset($product['type']) && ($product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE)) {
                                echo '<br /><nobr><small style="padding-left: 20px">&nbsp;<i> - ' . $osC_Language->get('senders_name') . ': ' . $product['senders_name'] . '</i></small></nobr>';
                                
                                if ($product['gift_certificates_type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
                                    echo '<br /><nobr><small style="padding-left: 20px">&nbsp;<i> - ' . $osC_Language->get('senders_email') . ': ' . $product['senders_email'] . '</i></small></nobr>';
                                }
                                
                                echo '<br /><nobr><small style="padding-left: 20px">&nbsp;<i> - ' . $osC_Language->get('recipients_name') . ': ' . $product['recipients_name'] . '</i></small></nobr>';
                                
                                if ($product['gift_certificates_type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
                                    echo '<br /><nobr><small style="padding-left: 20px">&nbsp;<i> - ' . $osC_Language->get('recipients_email') . ': ' . $product['recipients_email'] . '</i></small></nobr>';
                                }
                                
                                echo '<br /><nobr><small style="padding-left: 20px">&nbsp;<i> - ' . $osC_Language->get('messages') . ': ' . $product['messages'] . '</i></small></nobr>';
                            }
                            
                            if (isset($product['variants']) && (sizeof($product['variants']) > 0)) {
                                foreach ($product['variants'] as $variant) {
                                    echo '<br /><nobr><small style="padding-left: 20px">&nbsp;<i> - ' . $variant['groups_name'] . ': ' . $variant['values_name'] . '</i></small></nobr>';
                                }
                            }
                        ?>
                    </td> 
                    <td align="center" valign="top"><?php echo $osC_Currencies->displayPriceWithTaxRate($product['final_price'], $product['tax'], $product['qty'], $order->info['currency'], $order->info['currency_value']); ?></td>
                    <td align="center" valign="top">
                        <?php 
                            for($i = 0, $selections = array(); $i <= $available_return_qty; $i++) {
                              $selections[] = array('id' => $i, 'text' => $i);
                            }
                            
                            echo osc_draw_pull_down_menu('quantity[' . $product['orders_products_id'] . ']', $selections); 
                        ?>
                    </td>
                </tr>
                <?php
                            $rows++;
                        }
                    }
                  
                    if ($rows > 1) {
                ?>
                <tr>
					<td colspan="4"><?php echo osc_draw_label($osC_Language->get('field_return_comments'), 'comments', null, true) . osc_draw_textarea_field('comments', null, 45); ?></td>
                </tr>    
                <?php
                    } else {
                ?>
                <tr>
					<td colspan="4"><?php echo $osC_Language->get('no_products_available_for_return'); ?></td>
                </tr>
                <?php
                    }
                ?>
    		</tbody>
		</table>
    </div>

    <div class="submitFormButtons">
    	<button type="submit" class="btn btn-small pull-right"><i class="icon-ok-sign icon-white"></i> <?php echo $osC_Language->get('button_continue'); ?></button>
        
    	<a href="javascript:window.history.go(-1);" class="btn btn-small pull-left"><i class="icon-chevron-left icon-white"></i> <?php echo $osC_Language->get('button_back'); ?></a>
    </div>
</form>