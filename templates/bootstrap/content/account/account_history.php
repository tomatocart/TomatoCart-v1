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
    if (osC_Order::numberOfEntries() > 0) {
        $Qhistory = osC_Order::getListing(MAX_DISPLAY_ORDER_HISTORY);
        
        while ($Qhistory->next()) {
            if (!osc_empty($Qhistory->value('delivery_name'))) {
                $order_type = $osC_Language->get('order_shipped_to');
                $order_name = $Qhistory->value('delivery_name');
            } else {
                $order_type = $osC_Language->get('order_billed_to');
                $order_name = $Qhistory->value('billing_name');
            }
?>

<div class="moduleBox">
    <h6><span style="float: right;"><?php echo $osC_Language->get('order_status') . ' ' . osC_Order::getLastPublicStatus($Qhistory->value('orders_id')); ?></span><?php echo $osC_Language->get('order_number') . ' ' . $Qhistory->valueInt('orders_id'); ?></h6>
    
    <div class="content">
        <table border="0" width="100%" cellspacing="2" cellpadding="4">
            <tr>
                <td valign="top"><?php echo '<b>' . $osC_Language->get('order_date') . '</b> ' . osC_DateTime::getLong($Qhistory->value('date_purchased')) . '<br /><b>' . $order_type . '</b> ' . osc_output_string_protected($order_name); ?></td>
                <td width="150" valign="top"><?php echo '<b>' . $osC_Language->get('order_products') . '</b> ' . osC_Order::numberOfProducts($Qhistory->valueInt('orders_id')) . '<br /><b>' . $osC_Language->get('order_cost') . '</b> ' . strip_tags($Qhistory->value('order_total')); ?></td>
                <td width="150" align="center" class="btn-toolbar">
					<a href="<?php echo osc_href_link(FILENAME_ACCOUNT, 'orders=' . $Qhistory->valueInt('orders_id') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'SSL'); ?>" class="btn btn-mini" id="btnSaveShippingMethod"><?php echo $osC_Language->get('button_view'); ?></a>
                	<a href="<?php echo osc_href_link(FILENAME_PDF, 'module=account&pdf=print_order&orders_id=' . $Qhistory->valueInt('orders_id')); ?>" class="btn btn-mini" id="btnSaveShippingMethod"><?php echo $osC_Language->get('button_print'); ?></a>
                    <?php 
                        if (($Qhistory->valueInt('returns_flag') == 1) && (ALLOW_RETURN_REQUEST == 1)) { 
                            $order = new osC_Order($Qhistory->valueInt('orders_id'));
                        
                            if ($order->hasNotReturnedProduct()) {
                    ?>
						<a href="<?php echo osc_href_link(FILENAME_ACCOUNT, 'orders=new_return_request&orders_id=' . $Qhistory->valueInt('orders_id'), 'SSL'); ?>" class="btn btn-mini" id="btnSaveShippingMethod"><?php echo $osC_Language->get('button_return'); ?></a>
                    <?php 
                            }
                        } 
                    ?>    
                </td>
            </tr>
        </table>
    </div>
</div>

<?php
    }
?>

<div class="listingPageLinks">
    <span style="float: right;"><?php echo $Qhistory->getBatchPageLinks('page', 'orders'); ?></span>
    
    <?php echo $Qhistory->getBatchTotalPages($osC_Language->get('result_set_number_of_orders')); ?>
</div>

<?php
    } else {
?>

<div class="moduleBox">
    <div class="content btop">
        <?php echo $osC_Language->get('no_orders_made_yet'); ?>
    </div>
</div>

<?php
    }
?>

<div class="submitFormButtons">
	<a href="<?php echo osc_href_link(FILENAME_ACCOUNT, null, 'SSL'); ?>" class="btn btn-small" id="btnSaveShippingMethod"><i class="icon-chevron-left icon-white"></i> <?php echo $osC_Language->get('button_back'); ?></a>
</div>