<?php
  /*$Id: credit_slips.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<?php
  $Qslips = $osC_Database->query('select r.*, o.orders_id from :table_orders_refunds r, :table_orders o where r.orders_refunds_type = :orders_refunds_type and r.orders_id = o.orders_id and o.customers_id = :customers_id order by credit_slips_id desc');
  $Qslips->bindTable(':table_orders_refunds', TABLE_ORDERS_REFUNDS);
  $Qslips->bindTable(':table_orders', TABLE_ORDERS);
  $Qslips->bindInt(':orders_refunds_type', ORDERS_RETURNS_TYPE_CREDIT_SLIP);
  $Qslips->bindInt(':customers_id', $osC_Customer->getID());
  $Qslips->setBatchLimit($_GET['page'], MAX_DISPLAY_ORDER_HISTORY);
  $Qslips->execute();
  
  if ($Qslips->numberOfRows()) {
  
    while($Qslips->next()) {

?>

<div class="moduleBox">
  
  <h6><span style="float: right;"><?php echo $osC_Language->get('date_added') . '&nbsp;' . osC_DateTime::getShort($Qslips->value('date_added')) ; ?></span><?php echo $osC_Language->get('credit_slip_number') . '&nbsp;' . $Qslips->value('credit_slips_id');?> </h6>
  
   <div class="content">
     <table border="0" width="100%" cellspacing="2" cellpadding="4">
       <tr>
          <td width="50%"><b><?php echo $osC_Language->get('order_return_products'); ?></b></td>
          <td><b><?php echo $osC_Language->get('order_return_comments'); ?></b></td>
        </tr>
        <tr>
         <td valign="top">
          <?php 
            $Qproducts = $osC_Database->query('select op.orders_products_id, op.products_name, orp.products_quantity from :table_orders_refunds_products orp, :table_orders_products op where orp.orders_products_id = op.orders_products_id and orp.orders_refunds_id = :orders_refunds_id');
            $Qproducts->bindTable(':table_orders_refunds_products', TABLE_ORDERS_REFUNDS_PRODUCTS);
            $Qproducts->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
            $Qproducts->bindInt(':orders_refunds_id', $Qslips->valueInt('orders_refunds_id'));
            $Qproducts->execute();
          
            while($Qproducts->next()) {
              echo $Qproducts->value('products_quantity') . '&nbsp;x&nbsp;' . $Qproducts->value('products_name') . '<br />';
              
              $Qvariants = $osC_Database->query('select products_variants_groups_id as groups_id, products_variants_groups as groups_name, products_variants_values_id as values_id, products_variants_values as values_name from :table_orders_products_variants where orders_id = :orders_id and orders_products_id = :orders_products_id');
              $Qvariants->bindTable(':table_orders_products_variants', TABLE_ORDERS_PRODUCTS_VARIANTS);
              $Qvariants->bindInt(':orders_id', $Qslips->valueInt('orders_id'));
              $Qvariants->bindInt(':orders_products_id', $Qproducts->valueInt('orders_products_id'));
              $Qvariants->execute();
              
              while($Qvariants->next()) {
                echo '<nobr><small>&nbsp;<i> - ' . $Qvariants->value('groups_name') . ': ' . $Qvariants->value('values_name') . '</i></small></nobr><br />';
              }
            }
          ?>
         </td>
          <td valign="top"><?php echo nl2br($Qslips->value('comments'));?></td>
        </tr>
    </table>    
    <p align="right"><?php echo osc_link_object(osc_href_link(FILENAME_PDF, 'module=account&pdf=credit_slip&credit_slip_id=' . $Qslips->value('credit_slips_id')), osc_draw_image_button('button_print.png', $osC_Language->get('credit_slips'))); ?></p>
  </div>
</div>

<?php
    }
?>

<div class="listingPageLinks">
  <span style="float: right;"><?php echo $Qslips->getBatchPageLinks('page', 'orders=list_credit_slips'); ?></span>
  
  <?php echo $Qslips->getBatchTotalPages($osC_Language->get('result_set_number_of_credit_slips'));?>
</div>

<?php    
  }else {
?>

<div class="moduleBox">
  <div class="content">
    <span><?php echo $osC_Language->get('no_credit_slips'); ?></span>
  </div>
</div>

<?php
  }
?>
<div class="submitFormButtons">
  <?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT . '?orders', null, 'SSL'), osc_draw_image_button('button_back.gif', $osC_Language->get('button_back'))); ?>
</div>
