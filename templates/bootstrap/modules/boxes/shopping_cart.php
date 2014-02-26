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

global $osC_Session, $osC_Language;
?>

<!-- box shopping_cart start //-->

<div class="boxNew boxAjaxShoppingCart">
    <div class="boxTitle">
        <?php echo osc_link_object($osC_Box->getTitleLink(), $osC_Box->getTitle()); ?>
        <?php echo osc_draw_image_button('button_ajax_cart_up.png', null, 'id="ajaxCartCollapse"'); ?>
        <?php echo osc_draw_image_button('button_ajax_cart_down.png', null, 'id="ajaxCartExpand" class="hidden"');?>
    </div>

	<div class="boxContents">
        <div id="ajaxCartContent">
            <div id="ajaxCartContentShort" class="collapsed">
                <span class="cartTotal"></span>  
                <span class="quantity"></span>  <?php echo $osC_Language->get('text_items'); ?> .
            </div>
            <div id="ajaxCartContentLong" class="expanded">
                <ul class="products collapsed" id="ajaxCartContentProducts"></ul>
                <p id="ajaxCartContentNoProducts" class="collapsed"><?php echo $osC_Language->get('No products'); ?></p>
                <div id="ajaxCartButtons">
                	<a class="btn btn-mini" href="<?php echo osc_href_link(FILENAME_CHECKOUT, 'checkout'); ?>"><i class="icon-shopping-cart icon-white"></i> <?php echo $osC_Language->get('checkout');?>&nbsp;</a>
                    <div style="visibility:hidden"> 
                    	<span>clear-bug-div</span>
                    </div>
                </div>
            </div>
        </div>
        
		<script type="text/javascript">
            window.addEvent("domready",function() {
                ajaxCart = new AjaxShoppingCart({
                  	template: "bootstrap",
                    sessionId : "<?php echo $osC_Session->getID(); ?>",
                    currentUrl: "<?php echo osc_get_current_url(); ?>",
                    error_sender_name_empty: "<?php echo $osC_Language->get('error_sender_name_empty'); ?>",
                    error_sender_email_empty: "<?php echo $osC_Language->get('error_sender_email_empty'); ?>",
                    error_recipient_name_empty: "<?php echo $osC_Language->get('error_recipient_name_empty'); ?>",
                    error_recipient_email_empty: "<?php echo $osC_Language->get('error_recipient_email_empty'); ?>",
                    error_message_empty: "<?php echo $osC_Language->get('error_message_empty'); ?>",
                    error_message_open_gift_certificate_amount:"<?php echo $osC_Language->get('error_message_open_gift_certificate_amount'); ?>"
                });
            });
        </script>
	</div>
</div>

<!-- box shopping_cart end //-->
