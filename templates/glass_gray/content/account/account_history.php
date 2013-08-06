<?php
/*
  $Id: account_history.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
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
        <td width="100" align="center">
          <div style = "padding: 2px;"><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'orders=' . $Qhistory->valueInt('orders_id') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'SSL'), osc_draw_image_button('small_view.gif', $osC_Language->get('button_view'))); ?></div>
          <div style = "padding: 2px;"><?php echo osc_link_object(osc_href_link(FILENAME_PDF, 'module=account&pdf=print_order&orders_id=' . $Qhistory->valueInt('orders_id')), osc_draw_image_button('button_print.png', $osC_Language->get('button_print')), "target=_blank"); ?></div>
        <?php 
          if (($Qhistory->valueInt('returns_flag') == 1) && (ALLOW_RETURN_REQUEST == 1)) { 
            $order = new osC_Order($Qhistory->valueInt('orders_id'));
            
            if ($order->hasNotReturnedProduct()) {
        ?>
          <div style = "padding: 2px;"><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'orders=new_return_request&orders_id=' . $Qhistory->valueInt('orders_id'), 'SSL'), osc_draw_image_button('button_return_item.png')); ?></div>
        <?php 
            }
          } ?>    
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
  <div class="content">
    <?php echo $osC_Language->get('no_orders_made_yet'); ?>
  </div>
</div>

<?php
  }
?>

<div class="submitFormButtons">
  <?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, null, 'SSL'), osc_draw_image_button('button_back.gif', $osC_Language->get('button_back'))); ?>
</div>