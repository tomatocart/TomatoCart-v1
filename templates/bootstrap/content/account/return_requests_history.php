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
    $Qhistory = $osC_Database->query('select o.orders_id, ore.orders_returns_id, ore.customers_comments, ore.date_added, ors.orders_returns_status_name, ors.orders_returns_status_id from :table_orders o, :table_orders_returns ore, :table_orders_returns_status ors where o.customers_id = :customers_id and o.orders_id = ore.orders_id and ore.orders_returns_status_id = ors.orders_returns_status_id and ors.languages_id = :languages_id order by orders_returns_id desc');
    $Qhistory->bindTable(':table_orders', TABLE_ORDERS);
    $Qhistory->bindTable(':table_orders_returns', TABLE_ORDERS_RETURNS);
    $Qhistory->bindTable(':table_orders_returns_status', TABLE_ORDERS_RETURNS_STATUS);
    $Qhistory->bindInt(':customers_id', $osC_Customer->getID());
    $Qhistory->bindInt(':languages_id', $osC_Language->getID());
    $Qhistory->setBatchLimit($_GET['page'], MAX_DISPLAY_ORDER_HISTORY);
    $Qhistory->execute();  
    
    if ($Qhistory->numberOfRows()) {
        while($Qhistory->next()) {
?>

<div class="moduleBox">
    <h6><span style="float: right;"><?php echo $osC_Language->get('order_return_date_added') . ' ' . osC_DateTime::getShort($Qhistory->value('date_added')); ?></span><?php echo $osC_Language->get('order_return_number') . ' ' . $Qhistory->value('orders_returns_id') . ' (' . $Qhistory->value('orders_returns_status_name') . ')';?> </h6>
    
    <div class="content">
         <table border="0" width="100%" cellspacing="2" cellpadding="4">
            <tr>
                <td width="50%"><b><?php echo $osC_Language->get('order_return_products'); ?></b></td>
                <td><b><?php echo $osC_Language->get('order_return_comments'); ?></b></td>
            </tr>
            <tr>
                <td valign="top">
                <?php 
                    $Qproducts = $osC_Database->query('select op.orders_id, op.orders_products_id, op.products_name, orp.products_quantity, op.products_type from :table_orders_returns r, :table_orders_returns_products orp, :table_orders_products op where r.orders_returns_id = orp.orders_returns_id and orp.orders_products_id = op.orders_products_id and r.orders_returns_id = :orders_returns_id');
                    $Qproducts->bindTable(':table_orders_returns', TABLE_ORDERS_RETURNS);
                    $Qproducts->bindTable(':table_orders_returns_products', TABLE_ORDERS_RETURNS_PRODUCTS);
                    $Qproducts->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
                    $Qproducts->bindInt(':orders_returns_id', $Qhistory->valueInt('orders_returns_id'));
                    $Qproducts->execute();
                    
                    while($Qproducts->next()) {
                        echo $Qproducts->value('products_quantity') . '&nbsp;x&nbsp;' . $Qproducts->value('products_name') . '<br />';
                        
                        if ($Qproducts->valueInt('products_type') == PRODUCT_TYPE_GIFT_CERTIFICATE) {
                            $Qcertificate = $osC_Database->query('select gift_certificates_type, senders_name, senders_email, recipients_name, recipients_email, messages from :table_gift_certificates where orders_id = :orders_id and orders_products_id = :orders_products_id');
                            $Qcertificate->bindTable(':table_gift_certificates', TABLE_GIFT_CERTIFICATES);
                            $Qcertificate->bindInt(':orders_id', $Qproducts->valueInt('orders_id'));
                            $Qcertificate->bindInt(':orders_products_id', $Qproducts->valueInt('orders_products_id'));
                            $Qcertificate->execute();
                            
                            echo '<nobr><small>&nbsp;<i> - ' . $osC_Language->get('senders_name') . ': ' . $Qcertificate->value('senders_name') . '</i></small></nobr><br />';
                            if ($Qcertificate->valueInt('gift_certificates_type') == GIFT_CERTIFICATE_TYPE_EMAIL) {
                                echo '<nobr><small>&nbsp;<i> - ' . $osC_Language->get('senders_email') . ': ' . $Qcertificate->value('senders_email') . '</i></small></nobr><br />';
                            }
                            echo '<nobr><small>&nbsp;<i> - ' . $osC_Language->get('recipients_name') . ': ' . $Qcertificate->value('recipients_name') . '</i></small></nobr><br />';
                            if ($Qcertificate->valueInt('gift_certificates_type') == GIFT_CERTIFICATE_TYPE_EMAIL) {
                                echo '<nobr><small>&nbsp;<i> - ' . $osC_Language->get('recipients_email') . ': ' . $Qcertificate->value('recipients_email') . '</i></small></nobr><br />';
                            }
                            echo '<nobr><small>&nbsp;<i> - ' . $osC_Language->get('messages') . ': ' . $Qcertificate->value('messages') . '</i></small></nobr><br />';
                        }
                        
                        $Qvariants = $osC_Database->query('select products_variants_groups_id as groups_id, products_variants_groups as groups_name, products_variants_values_id as values_id, products_variants_values as values_name from :table_orders_products_variants where orders_id = :orders_id and orders_products_id = :orders_products_id');
                        $Qvariants->bindTable(':table_orders_products_variants', TABLE_ORDERS_PRODUCTS_VARIANTS);
                        $Qvariants->bindInt(':orders_id', $Qhistory->valueInt('orders_id'));
                        $Qvariants->bindInt(':orders_products_id', $Qproducts->valueInt('orders_products_id'));
                        $Qvariants->execute();
                        
                        while($Qvariants->next()) {
                            echo '<nobr><small>&nbsp;<i> - ' . $Qvariants->value('groups_name') . ': ' . $Qvariants->value('values_name') . '</i></small></nobr><br />';
                        }
                    }
                ?>
                </td>
                <td valign="top"><?php echo nl2br($Qhistory->value('customers_comments'));?></td>
            </tr>
            <tr>
                <?php 
                    if ($Qhistory->value('orders_returns_status_id') == ORDERS_RETURNS_STATUS_CONFIRMED) { 
                ?>
                  <td colspan="2" align="right"><?php echo osc_link_object(osc_href_link(FILENAME_PDF, 'module=account&pdf=return_slip&orders_returns_id=' . $Qhistory->value('orders_returns_id')), osc_draw_image_button('button_print.png', $osC_Language->get('return_slip'))); ?></td>
                <?php 
                    }              
                ?>
            </tr>
        </table>    
    </div>
</div>

<?php
    }
?>

<div class="listingPageLinks">
    <span style="float: right;"><?php echo $Qhistory->getBatchPageLinks('page', 'orders=list_return_requests'); ?></span>
    
    <?php echo $Qhistory->getBatchTotalPages($osC_Language->get('result_set_number_of_orders_returns'));?>
</div>

<?php    
    }else {
?>

<div class="moduleBox">
    <div class="content btop">
    	<span><?php echo $osC_Language->get('no_orders_returns_made_yet'); ?></span>
    </div>
</div>

<?php
    }
?>
<div class="submitFormButtons">
	<a href="<?php echo osc_href_link(FILENAME_ACCOUNT . '?orders', null, 'SSL'); ?>" class="btn btn-small pull-left"><i class="icon-chevron-left icon-white"></i> <?php echo $osC_Language->get('button_back'); ?></a>
</div>
