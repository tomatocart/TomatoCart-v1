<?php
/*
  $Id: shipping_method_form.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<form id="checkout_address" name="checkout_address" action="<?php echo osc_href_link(FILENAME_CHECKOUT, 'shipping=process', 'SSL'); ?>" method="post">

<?php
  if ($osC_Shipping->hasQuotes()) {
?>

<div class="moduleBox">
  <div class="content">

<?php
    if ($osC_Shipping->numberOfQuotes() > 1) {
?>

    <div style="float: right; padding: 0px 0px 10px 20px; text-align: center;">
      <?php echo '<b>' . $osC_Language->get('please_select') . '</b><br />' . osc_image(DIR_WS_IMAGES . 'arrow_east_south.gif'); ?>
    </div>

    <p style="margin-top: 0px;"><?php echo $osC_Language->get('choose_shipping_method'); ?></p>

<?php
    } else {
?>

    <p style="margin-top: 0px;"><?php echo $osC_Language->get('only_one_shipping_method_available'); ?></p>

<?php
    }
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">

<?php
    $radio_buttons = 0;
    foreach ($osC_Shipping->getQuotes() as $quotes) {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td width="10">&nbsp;</td>
            <td colspan="3"><b><?php echo $quotes['module']; ?></b>&nbsp;<?php if (isset($quotes['icon']) && !empty($quotes['icon'])) { echo $quotes['icon']; } ?></td>
            <td width="10">&nbsp;</td>
          </tr>
<?php
      if (isset($quotes['error'])) {
?>
          <tr>
            <td width="10">&nbsp;</td>
            <td colspan="3"><?php echo $quotes['error']; ?></td>
            <td width="10">&nbsp;</td>
          </tr>
<?php
      } else {
        foreach ($quotes['methods'] as $methods) {
          if ($quotes['id'] . '_' . $methods['id'] == $osC_ShoppingCart->getShippingMethod('id')) {
            echo '          <tr id="defaultSelected" class="moduleRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(\'checkout_address\', this)">' . "\n";
          } else {
            echo '          <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(\'checkout_address\', this)">' . "\n";
          }
?>
            <td width="10">&nbsp;</td>
            <td width="75%"><?php echo $methods['title']; ?></td>
<?php
          if ( ($osC_Shipping->numberOfQuotes() > 1) || (sizeof($quotes['methods']) > 1) ) {
?>
            <td><?php echo $osC_Currencies->displayPrice($methods['cost'], $quotes['tax_class_id']); ?></td>
            <td align="right"><?php echo osc_draw_radio_field('shipping_mod_sel', $quotes['id'] . '_' . $methods['id'], $osC_ShoppingCart->getShippingMethod('id')); ?></td>
<?php
          } else {
?>
            <td align="right" colspan="2"><?php echo $osC_Currencies->displayPrice($methods['cost'], $quotes['tax_class_id']) . osc_draw_hidden_field('shipping_mod_sel', $quotes['id'] . '_' . $methods['id']); ?></td>
<?php
          }
?>
            <td width="10">&nbsp;</td>
          </tr>
<?php
          $radio_buttons++;
        }
      }
?>
        </table></td>
      </tr>
<?php
    }
?>

    </table>
  </div>
</div>

<?php
  }
?>

<?php
  global $osC_OrderTotal_gift_wrapping;
  if(isset($osC_OrderTotal_gift_wrapping) && is_object($osC_OrderTotal_gift_wrapping) && $osC_OrderTotal_gift_wrapping->isEnabled()){
?>
<div class="moduleBox">
  <h6><?php echo '<b>' . $osC_Language->get('gift_wrapping_heading') . '</b>'; ?></h6>

  <div class="content">
    <?php 
      $price = MODULE_ORDER_TOTAL_GIFT_WRAPPING_PRICE;
      echo osc_draw_checkbox_field('gift_wrapping', '1', $osC_ShoppingCart->isGiftWrapping() ? true : false) . '&nbsp;<b>' . sprintf($osC_Language->get('gift_wrapping_description'), $osC_Currencies->format(MODULE_ORDER_TOTAL_GIFT_WRAPPING_PRICE)) . '</b>'; ?>
    
    <h6><?php echo '<b>' . $osC_Language->get('gift_wrapping_heading') . '</b>'; ?></h6>
    <?php echo osc_draw_textarea_field('gift_wrapping_comments', (isset($_SESSION['gift_wrapping_comments']) ? $_SESSION['gift_wrapping_comments'] : null), 60, 4, 'style="width: 98%;"'); ?>
  </div>
</div>
<?php
  }
?>

<div class="moduleBox">
  <h6><?php echo $osC_Language->get('add_comment_to_order_title'); ?></h6>

  <div class="content">
    <?php echo osc_draw_textarea_field('shipping_comments', (isset($_SESSION['comments']) ? $_SESSION['comments'] : null), 60, 4, 'style="width: 98%;"'); ?>
  </div>
</div>

<br />

<div class="submitFormButtons" style="text-align: right;">
  <?php echo osc_draw_image_button('button_continue.gif', $osC_Language->get('button_continue'), 'id="btnSaveShippingMethod" style="cursor: pointer"'); ?>
</div>

</form>
