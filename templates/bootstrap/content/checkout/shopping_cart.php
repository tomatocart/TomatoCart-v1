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
    if ($messageStack->size('shopping_cart') > 0) {
        echo $messageStack->output('shopping_cart');
    }
?>

<?php
    if ($osC_ShoppingCart->hasContents()) {
?>

<form name="shopping_cart" action="<?php echo osc_href_link(FILENAME_CHECKOUT, 'action=cart_update', 'SSL'); ?>" method="post">

    <div class="moduleBox">
      <h6><?php echo $osC_Language->get('shopping_cart_heading'); ?></h6>
    
      <div class="content">
        <table border="0" width="100%" cellspacing="5" cellpadding="2">
    
    <?php
        $_cart_date_added = null;
    
        foreach ($osC_ShoppingCart->getProducts() as $products_id_string => $products) {
        
          if ($products['date_added'] != $_cart_date_added) {
            $_cart_date_added = $products['date_added'];
    ?>
    
          <tr>
            <td colspan="4"><?php echo sprintf($osC_Language->get('date_added_to_shopping_cart'), $products['date_added']); ?></td>
          </tr>
    
    <?php
          }
    ?>
    
          <tr>
            <td valign="top" width="30" align="center">
    
    <?php
          $variants_string = null;
          if (!is_numeric($products_id_string) && (strpos($products_id_string, '#') != false)) {
            $tmp = explode('#', $products_id_string);
            $variants_string = $tmp[1];
          }
           
          echo osc_link_object(osc_href_link(FILENAME_CHECKOUT, osc_get_product_id($products['id']) . (!empty($variants_string) ? '&variants=' . $variants_string : '') . '&action=cart_remove', 'SSL'), '<i class="icon-trash icon-white" title="' . $osC_Language->get('button_delete') . '"></i>', 'class="btn btn-mini"');
    ?>
    
            </td>
            <td valign="top">
    
            <?php
            echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, $products['id']), '<b>' . $products['name'] . '</b>');
            
            if ( (STOCK_CHECK == '1') && ($osC_ShoppingCart->isInStock($products['id']) === false) ) {
              echo '<span class="markProductOutOfStock">' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '</span>';
            }
      
            echo '&nbsp;(Top Category)';
            
            if (isset($products['error'])) {
              echo '<br /><span class="markProductError">' . $products['error'] . '</span>';
              $osC_ShoppingCart->clearError($products_id_string);
            }
            
            if ($products['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
              echo '<br />- ' . $osC_Language->get('senders_name') . ': ' . $products['gc_data']['senders_name'];
              
              if ($products['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
                echo '<br />- ' . $osC_Language->get('senders_email')  . ': ' . $products['gc_data']['senders_email'];
              }
              
              echo '<br />- ' . $osC_Language->get('recipients_name') . ': ' . $products['gc_data']['recipients_name'];
              
              if ($products['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
                echo '<br />- ' . $osC_Language->get('recipients_email')  . ': ' . $products['gc_data']['recipients_email'];
              }
              
              echo '<br />- ' . $osC_Language->get('message')  . ': ' . $products['gc_data']['message'];
            }
            
            $atttributes_array = array();
      
            if ($osC_ShoppingCart->hasVariants($products['id'])) {
              foreach ($osC_ShoppingCart->getVariants($products['id']) as $variants) {
                $atttributes_array[$variants['groups_id']] = $variants['variants_values_id'];
      
                echo '<br />- ' . $variants['groups_name'] . ': ' . $variants['values_name'];
              }
            }
            
            if ( isset($products['customizations']) && !empty($products['customizations']) ) {
            ?>
              <p>
                <?php      
                  foreach ($products['customizations'] as $key => $customization) {
                ?>
                  <div style="float: left">
                    <?php echo osc_draw_input_field('products[' . $products_id_string . '][' . $key . ']', $customization['qty'], 'size="4" style="width: 20px"') . ' x '; ?>
                  </div>
                  <div style="margin-left: 40px">
                    <?php
                      foreach ($customization['fields'] as $field) {
                        echo $field['customization_fields_name'] . ': ' . $field['customization_value'] . '<br />';
                      }
                    ?>
                  </div>
                  <div style="clear: both"></div>
                <?php } ?>
              </p>
            <?php } ?>
            </td>
            <td valign="top">
              <?php
                if (!isset($products['customizations'])) { 
                  echo osc_draw_input_field('products[' . $products_id_string . ']', $products['quantity'], 'id="products_' . $products_id_string. '" size="4" style="width: 40px"'); 
                }
              ?>
            </td>
            <td valign="top" align="right"><?php echo '<b>' . $osC_Currencies->displayPrice($products['final_price'], $products['tax_class_id'], $products['quantity']) . '</b>'; ?></td>
          </tr>
    
    <?php
        }
    ?>
    
        </table>
      </div>
    
     
        <table border="0" width="100%" cellspacing="0" cellpadding="2">
      
        <?php
            foreach ($osC_ShoppingCart->getOrderTotals() as $module) {
        ?>
			<tr>
            	<td align="right"><?php echo $module['title']; ?></td>
                <td align="right" width="100"><?php echo $module['text']; ?>&nbsp;</td>
            </tr>
		<?php 
            }
        ?>
      
        </table>
    
    <?php
        if ( (STOCK_CHECK == '1') && ($osC_ShoppingCart->hasStock() === false) ) {
            if (STOCK_ALLOW_CHECKOUT == '1') {
                echo '<p class="stockWarning" align="center">' . sprintf($osC_Language->get('products_out_of_stock_checkout_possible'), STOCK_MARK_PRODUCT_OUT_OF_STOCK) . '</p>';
            } else {
                echo '<p class="stockWarning" align="center">' . sprintf($osC_Language->get('products_out_of_stock_checkout_not_possible'), STOCK_MARK_PRODUCT_OUT_OF_STOCK) . '</p>';
            }
        }
    ?>
    
    </div>

    <div class="row-fluid submitFormButtons">
        <div class="span4"><a href="javascript:void(0);" class="btn btn-small" onclick="javascript:history.go(-1);return false;"><i class="icon-chevron-right icon-white"></i> <?php echo $osC_Language->get('button_continue_shopping'); ?></a></div>
        
        <div class="span5"><button type="submit" class="btn btn-small"><i class="icon-refresh icon-white"></i> <?php echo $osC_Language->get('button_update_cart'); ?></button></div>

        <div class="span3"><a class="btn btn-small btn-small pull-right" href="<?php echo osc_href_link(FILENAME_CHECKOUT, 'checkout', 'SSL'); ?>"><i class="icon-ok-sign icon-white"></i> <?php echo $osC_Language->get('button_checkout');?></a></div>
    </div>

</form>
  <?php
    $initialize_checkout_methods = $payment_modules->get_checkout_initialization_methods();
    
    if ( !empty($initialize_checkout_methods) && is_array($initialize_checkout_methods) ) {
      reset($initialize_checkout_methods);
    
  ?>
  
    <div align="right">
      <p align="right"><?php echo $osC_Language->get('alternative_checkout_methods'); ?></p>

      <?php 
        foreach($initialize_checkout_methods as $value) {
          echo $value;
        }        
      ?>
    </div>
    
  <?php 
    } 
  } else {
?>

<p><?php echo $osC_Language->get('shopping_cart_empty'); ?></p>

<div class="submitFormButtons" style="text-align: right;">
  	<a class="btn btn-small" href="<?php echo osc_href_link(FILENAME_DEFAULT); ?>"><i class="icon-chevron-right icon-white"></i> <?php echo $osC_Language->get('button_continue'); ?></a>
</div>

<?php
    }
?>
