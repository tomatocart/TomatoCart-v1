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

<form name="checkout_payment" id="checkout_payment" action="<?php echo osc_href_link(FILENAME_CHECKOUT, 'confirmation', 'SSL'); ?>" method="post">

<?php
    if (DISPLAY_CONDITIONS_ON_CHECKOUT == '1') {
?>

<div class="moduleBox clearfix">
    <h6><?php echo $osC_Language->get('order_conditions_title'); ?></h6>
    
    <div class="content">
		<p><?php echo sprintf($osC_Language->get('order_conditions_description'), osc_href_link(FILENAME_INFO, 'articles&articles_id=' . 4)); ?></p>  
		
        <div class="control-group">
        	<label class="checkbox" for="conditions">
        	    <?php echo osc_draw_checkbox_field('conditions', array(array('id' => '1', 'text' => $osC_Language->get('order_conditions_acknowledge'))) , false); ?>
        	</label>
        </div>
    </div>
</div>
<?php
    }
?>

<div class="moduleBox">

	<div class="content">

    <?php
        $selection = $osC_Payment->selection();
        
        if (sizeof($selection) > 1) {
    ?>
		<div class="row-fluid">
			<div class="span9"><?php echo $osC_Language->get('choose_payment_method'); ?></div>
			<div class="span3 hidden-phone"><?php echo '<b>' . $osC_Language->get('please_select') . '</b><br />' . osc_image(DIR_WS_IMAGES . 'arrow_east_south.gif'); ?></div>
		</div>
    <?php
        } else {
    ?>
    	<p style="margin-top: 0px;"><?php echo $osC_Language->get('only_one_payment_method_available'); ?></p>
    <?php
        }
    ?>
    <?php
        if ($osC_Customer->isLoggedOn() && $osC_Customer->hasStoreCredit()) {
    ?>
        <div class="control-group">
        	<label class="checkbox" for="create_shipping_address">
        	    <?php echo osc_draw_checkbox_field('payment_method_store_credit', '1', $osC_ShoppingCart->isUseStoreCredit() ? true : false); ?>&nbsp;
        	    <b><?php echo sprintf($osC_Language->get('pay_with_store_credit_title'), $osC_Currencies->format($osC_Customer->getStoreCredit())); ?></b>
        	</label>
        </div>
	<?php 
        }
    ?>
    	<table id="payment_methods" border="0" width="100%" cellspacing="0" cellpadding="2" style="display: <?php echo $osC_ShoppingCart->isTotalZero() ? 'none' : ''; ?>">
        <?php
            $radio_buttons = 0;
            for ($i=0, $n=sizeof($selection); ($i<$n); $i++) {
        ?>
            <tr id="payment_method_<?php echo $selection[$i]['id']; ?>">
            	<td colspan="2">
            		<table border="0" width="100%" cellspacing="0" cellpadding="2">
                		<tr <?php echo ($n == 1) || ($osC_ShoppingCart->hasBillingMethod() && ($selection[$i]['id'] == $osC_ShoppingCart->getBillingMethod('id'))) ? 'id="defaultSelected" class="moduleRowSelected"' : ''; ?> onclick="selectRowEffect('checkout_payment', this)">
            				<td width="10">&nbsp;</td>
                            <?php
                                if ($n > 1) {
                            ?>
                            <td colspan="3"><?php echo '<b>' . $selection[$i]['module'] . '</b>'; ?></td>
                            <td align="right"><?php echo osc_draw_radio_field('payment_method', $selection[$i]['id'], ($osC_ShoppingCart->hasBillingMethod() ? $osC_ShoppingCart->getBillingMethod('id') : null)); ?></td>
                            <?php
                                } else {
                            ?>
            				<td colspan="4"><?php echo '<b>' . $selection[$i]['module'] . '</b>' . osc_draw_hidden_field('payment_method', $selection[$i]['id']); ?></td>
                            <?php
                                }
                            ?>
            				<td width="10">&nbsp;</td>
						</tr>
                        <?php
                            if (isset($selection[$i]['error'])) {
                        ?>
                        <tr>
                            <td width="10">&nbsp;</td>
                            <td colspan="4"><?php echo $selection[$i]['error']; ?></td>
                            <td width="10">&nbsp;</td>
                        </tr>
                        <?php
                            } elseif (isset($selection[$i]['fields']) && is_array($selection[$i]['fields'])) {
                        ?>
                        <tr>
                            <td width="10">&nbsp;</td>
                            <td colspan="4">
                            	<table border="0" cellspacing="0" cellpadding="2">
                                <?php
                                    for ($j=0, $n2=sizeof($selection[$i]['fields']); $j<$n2; $j++) {
                                ?>
                                <tr>
                                    <td width="10">&nbsp;</td>
                                    <td><?php echo $selection[$i]['fields'][$j]['title']; ?></td>
                                    <td width="10">&nbsp;</td>
                                    <td><?php echo $selection[$i]['fields'][$j]['field']; ?></td>
                                    <td width="10">&nbsp;</td>
                                </tr>
                                <?php
                                      }
                                ?>
            					</table>
            				</td>
            				<td width="10">&nbsp;</td>
          				</tr>
                        <?php
                            }
                        ?>
        			</table>
        		</td>
      		</tr>
            <?php
                $radio_buttons++;
              }
            ?>
    	</table>
  	</div>
</div>

<?php
    if(defined('MODULE_ORDER_TOTAL_COUPON_STATUS') && (MODULE_ORDER_TOTAL_COUPON_STATUS == 'true')) {
?>
<div class="moduleBox">
	<h6><?php echo '<b>' . $osC_Language->get('coupons_redeem_heading') . '</b>'; ?></h6>
  
	<div class="content" id="couponRedeem">
    <?php
        if(!$osC_ShoppingCart->hasCoupon()){
    ?>	
    	<p><?php echo '<b>' . $osC_Language->get('coupons_redeem_information_title') . '</b>'; ?></p>
    	
        <div class="form-inline">
        	<p><?php echo '<b>' . $osC_Language->get('fields_coupons_redeem_code') . '</b>'; ?></p>
            
            <?php echo osc_draw_input_field('coupon_redeem_code'); ?>&nbsp;&nbsp;
            
            <button class="btn btn-mini" id="btnRedeemCoupon"><?php echo $osC_Language->get('button_coupon_redeem'); ?></button>
        </div>
    <?php
        }else{
    ?>
    	<p><?php echo '<b>' . $osC_Language->get('coupons_redeem_information_title') . '</b>'; ?></p>
    	
        <div class="form-inline">
        	<p><?php echo '<b>' . $osC_Language->get('fields_coupons_redeem_code') . '</b>'; ?></p>
            
            <?php echo $osC_ShoppingCart->getCouponCode(); ?>&nbsp;&nbsp;
            
            <button class="btn btn-mini" id="btnDeleteCoupon"><?php echo $osC_Language->get('button_delete'); ?></button>
        </div>
    <?php
        }
    ?>
	</div>
</div>
<?php
    }
?>

<?php
    if(defined('MODULE_ORDER_TOTAL_GIFT_CERTIFICATE_STATUS') && (MODULE_ORDER_TOTAL_GIFT_CERTIFICATE_STATUS == 'true')){
?>
<div class="moduleBox">
    <h6><?php echo '<b>' . $osC_Language->get('gift_certificates_redeem_heading') . '</b>'; ?></h6>
    
    <div class="content">
        <?php echo '<b>' . $osC_Language->get('gift_certificates_redeem_information_title') . '</b>'; ?><br/>
        <?php
            if  ($osC_ShoppingCart->hasGiftCertificate()){
                foreach ($osC_ShoppingCart->getGiftCertificateCodes() as $gift_certificate) {
        ?>
    	<p id="<?php echo $gift_certificate; ?>">
    	    <?php echo $gift_certificate; ?>&nbsp;[<?php echo $osC_Currencies->format($osC_ShoppingCart->getGiftCertificateRedeemAmount($gift_certificate)); ?>]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    		<button class="btn btn-mini btn-inverse btnDeleteGiftCertificate" id="btnRedeemGiftCertificate"><i class="icon-chevron-right icon-white"></i> <?php echo $osC_Language->get('button_delete'); ?></button>
    	</p>
        <?php 
                }
            }
        ?>
        
        <div class="form-inline">
        	<p><?php echo '<b>' . $osC_Language->get('fields_gift_certificates_redeem_code') . '</b>'; ?></p>
            
            <?php echo osc_draw_input_field('gift_certificate_redeem_code', null, 'id="gift_certificate_code"'); ?>&nbsp;&nbsp;
            
            <button class="btn btn-mini" id="btnRedeemGiftCertificate"><?php echo $osC_Language->get('button_gift_certificate_redeem'); ?></button>
        </div>
    </div>
</div>
<?php
    }
?>

<div class="moduleBox">
    <h6><?php echo $osC_Language->get('add_comment_to_order_title'); ?></h6>
    
    <div class="content">
        <?php echo osc_draw_textarea_field('payment_comments', (isset($_SESSION['comments']) ? $_SESSION['comments'] : null), 60, 4, 'style="width: 98%;"'); ?>
    </div>
</div>

<div class="submitFormButtons" style="text-align: right;">
	<button type="button" class="btn btn-small" id="btnSavePaymentMethod"><i class="icon-chevron-right icon-white"></i> <?php echo $osC_Language->get('button_continue'); ?></button>
</div>

</form>