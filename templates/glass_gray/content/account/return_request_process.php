<?php
/*
  $Id: orders_returns_process.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
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
    
  <table class="productListing" border="0" width="100%" cellspacing="0" cellpadding="4">    
    <tr>
      <td class="productListing-heading"><?php echo $osC_Language->get('listing_products_heading'); ?></td>
      <td class="productListing-heading" align="center"><?php echo $osC_Language->get('listing_price_heading'); ?></td>
      <td class="productListing-heading" align="center"><?php echo $osC_Language->get('listing_quantity_heading'); ?></td>
    </tr>
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
      <tr class="<?php echo ((($rows/2) == floor($rows/2)) ? 'productListing-even' : 'productListing-odd'); ?>">
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
    <tr class="<?php echo ((($rows/2) == floor($rows/2)) ? 'productListing-even' : 'productListing-odd'); ?>">
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
  </table>
</div>

<div class="submitFormButtons" style="float: right">
  <?php echo osc_draw_image_submit_button('button_continue.gif', $osC_Language->get('button_continue')) . '&nbsp;' . osc_link_object('javascript:window.history.go(-1);', osc_draw_image_button('button_back.gif', $osC_Language->get('button_back'))); ?>
</div>

</form>