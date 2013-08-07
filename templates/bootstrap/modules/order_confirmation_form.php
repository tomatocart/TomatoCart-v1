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

<div class="moduleBox">
	<div class="content">
		<div class="row-fluid">
			<div class="span6">
				<p><?php echo '<b>' . $osC_Language->get('order_delivery_address_title') . '</b> '; ?></p>
				
                <?php
                    if ($osC_ShoppingCart->hasShippingAddress()) {
                ?>
                <p><?php echo osC_Address::format($osC_ShoppingCart->getShippingAddress(), '<br />'); ?></p>
                
                <p><?php echo '<b>' . $osC_Language->get('order_shipping_method_title') . '</b> '; ?></p>
                
                <?php
                    if ($osC_ShoppingCart->hasShippingMethod()) {
                ?>

                <p><?php echo $osC_ShoppingCart->getShippingMethod('title'); ?></p>
                    
                <?php 
                        }
                    }
                ?>
			</div>
			<div class="span6">
                <p><?php echo '<b>' . $osC_Language->get('order_billing_address_title') . '</b> '; ?></p>
                <p><?php echo osC_Address::format($osC_ShoppingCart->getBillingAddress(), '<br />'); ?></p>
                
                <p><?php echo '<b>' . $osC_Language->get('order_payment_method_title') . '</b> '; ?></p>
                <p><?php echo implode(', ', $osC_ShoppingCart->getCartBillingMethods()); ?></p>
			</div>
		</div>
		<div class="row-fluid">
			<table class="table">
				<thead>
                    <?php
                        if ($osC_ShoppingCart->numberOfTaxGroups() > 1) {
                    ?>
					<tr>
                        <th colspan="2"><?php echo $osC_Language->get('order_products_title'); ?></th>
                        <th align="right"><b><?php echo $osC_Language->get('order_tax_title'); ?></b></th>
                        <th align="right"><b><?php echo $osC_Language->get('order_total_title'); ?></b></th>
					</tr>
        			<?php 
                        } else {
                    ?>
                    <tr>
                    	<th colspan="3"><?php echo $osC_Language->get('order_products_title'); ?></th>
                    </tr>
                    <?php 
                        }
        			?>
				</thead>
				<tbody>
					<?php 
					    foreach ($osC_ShoppingCart->getProducts() as $products) {
					?>
					<tr>
						<td><?php echo $products['quantity']; ?>&nbsp;x&nbsp;</td>
						<td>
							<?php 
							    echo $products['name']; 
					        
							    if ( (STOCK_CHECK == '1') && !$osC_ShoppingCart->isInStock($products['id']) ) {
                                    echo '<span class="markProductOutOfStock">' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '</span>';
                                }
                                
					            if ($products['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
                                    echo '<br /><nobr><small>&nbsp;<i> - ' . $osC_Language->get('senders_name') . ': ' . $products['gc_data']['recipients_name'] . '</i></small></nobr>';
                                    
                                    if ($products['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
                                    echo '<br /><nobr><small>&nbsp;<i> - ' . $osC_Language->get('senders_email')  . ': ' . $products['gc_data']['recipients_email'] . '</i></small></nobr>';
                                    }
                                    
                                    echo '<br /><nobr><small>&nbsp;<i> - ' . $osC_Language->get('recipients_name') . ': ' . $products['gc_data']['recipients_name'] . '</i></small></nobr>';
                                    
                                    if ($products['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
                                    echo '<br /><nobr><small>&nbsp;<i> - ' . $osC_Language->get('recipients_email')  . ': ' . $products['gc_data']['recipients_email'] . '</i></small></nobr>';
                                    }
                                    
                                    echo '<br /><nobr><small>&nbsp;<i> - ' . $osC_Language->get('message')  . ': ' . $products['gc_data']['message'] . '</i></small></nobr>';
                                }
                                
					            if ( (isset($products['variants'])) && (sizeof($products['variants']) > 0) ) {
                                    foreach ($products['variants'] as $variants) {
                                        echo '<br /><nobr><small>&nbsp;<i> - ' . $variants['groups_name'] . ': ' . $variants['values_name'] . '</i></small></nobr>';
                                    }
                                }
                                
    					        if ( isset($products['customizations']) && !empty($products['customizations']) ) {
                            ?>
                                <p>
                                    <?php      
                                        foreach ($products['customizations'] as $key => $customization) {
                                    ?>
									<div style="float: left">
                                    <?php 
                                        echo $customization['qty'] . ' x '; 
                                    ?>
									</div>
                                    <div style="margin-left: 30px">
                                    <?php
                                        foreach ($customization['fields'] as $field) {
                                            echo $field['customization_fields_name'] . ': ' . $field['customization_value'] . '<br />';
                                        }
                                    ?>
									</div>
                                    <?php 
                                        }
                                    ?>
                                </p>
                                <?php 
                                    }
                                ?>
						</td>
                        <?php
                            if ($osC_ShoppingCart->numberOfTaxGroups() > 1) {
                        ?>
                        <td valign="top" align="right"><?php echo osC_Tax::displayTaxRateValue($osC_Tax->getTaxRate($products['tax_class_id'], $osC_ShoppingCart->getTaxingAddress('country_id'), $osC_ShoppingCart->getTaxingAddress('zone_id'))); ?></td>
                        <?php
                            }
                        ?>
                        <td align="right" valign="top"><?php echo $osC_Currencies->displayPrice($products['final_price'], $products['tax_class_id'], $products['quantity']); ?></td>
					</tr>
					<?php 
					    }
					?>
				</tbody>
			</table>
			<table class="pull-right">
            <?php
                foreach ($osC_ShoppingCart->getOrderTotals() as $module) {
            ?>
                <tr>
					<td align="right"><?php echo $module['title']; ?></td>
                    <td align="right"><?php echo $module['text']; ?></td>
                </tr>
            <?php 
                }
            ?>
			</table>
		</div>
	</div>
</div>

<?php
    if (isset($_SESSION['comments']) && !empty($_SESSION['comments'])) {
?>

<div class="moduleBox">
    <h6><?php echo '<b>' . $osC_Language->get('order_comments_title') . '</b> '; ?></h6>
    
    <div class="content">
	    <?php echo nl2br(osc_output_string_protected($_SESSION['comments'])) . osc_draw_hidden_field('comments', $_SESSION['comments']); ?>
    </div>
</div>
<?php
    }
?>

<div class="submitFormButtons">
    <?php
        if ($osC_Payment->hasActionURL()) {
            $form_action_url = $osC_Payment->getActionURL();
        } else {
            $form_action_url = osc_href_link(FILENAME_CHECKOUT, 'process', 'SSL');
        }
    ?>
    <form name="checkout_confirmation" action="<?php echo $form_action_url; ?>" method="post">

    <?php    
        if ($osC_Payment->hasActive()) {
            if ($confirmation = $osC_Payment->confirmation()) {
    ?>

        <div class="moduleBox">
			<h6><?php echo $osC_Language->get('order_payment_information_title'); ?></h6>
        
          	<div class="content">
            	<p><?php echo $confirmation['title']; ?></p>
        
                <?php
                    if (isset($confirmation['fields'])) {
                ?>
        
            	<table border="0" cellspacing="3" cellpadding="2">
                    <?php
                        for ($i=0, $n=sizeof($confirmation['fields']); $i<$n; $i++) {
                    ?>
            
                    <tr>
                        <td width="10">&nbsp;</td>
                        <td><?php echo $confirmation['fields'][$i]['title']; ?></td>
                        <td width="10">&nbsp;</td>
                        <td><?php echo $confirmation['fields'][$i]['field']; ?></td>
                    </tr>
            
                    <?php
                        }
                    ?>
				</table>
                <?php
                    }
                
                    if (isset($confirmation['text'])) {
                ?>
        
            	<p><?php echo $confirmation['text']; ?></p>
        
                <?php
                    }
                ?>
          	</div>
        </div>
    <?php
            }
        }
  
        if ($osC_Payment->hasActive()) {
            echo $osC_Payment->process_button();
        }
?>
		<button type="submit" class="btn btn-small pull-right" id="btnConfirmOrder"><i class="icon-chevron-right icon-white"></i> <?php echo $osC_Language->get('button_confirm_order'); ?></button>
	</form>
</div>