<?php
/*
  $Id: account_history_info.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $order = new osC_Order($_GET['orders']);
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<div class="moduleBox">

  <h6><span style="float: right;"><?php echo $osC_Language->get('order_total_heading') . ' ' . $order->info['total']; ?></span><?php echo  $osC_Language->get('order_date_heading') . ' ' . osC_DateTime::getShort($order->info['date_purchased']) . ' <small>(' . $order->info['orders_status'] . ')</small>'; ?></h6>

  <div class="content">
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="50%" valign="top">
          <h6><?php echo $osC_Language->get('order_billing_address_title'); ?></h6>

          <p><?php echo osC_Address::format($order->billing, '<br />'); ?></p>

          <h6><?php echo $osC_Language->get('order_payment_method_title'); ?></h6>

          <p><?php echo $order->info['payment_method']; ?></p>
        </td>
        <td valign="top">
<?php
  if ($order->delivery != false) {
?>

          <h6><?php echo $osC_Language->get('order_delivery_address_title'); ?></h6>

          <p><?php echo osC_Address::format($order->delivery, '<br />'); ?></p>

<?php
    if (!empty($order->info['shipping_method'])) {
?>

          <h6><?php echo $osC_Language->get('order_shipping_method_title'); ?></h6>

          <p><?php echo $order->info['shipping_method']; ?></p>

<?php
    }

    if (!empty($order->info['tracking_no'])) {
?>    
          <h6><?php echo $osC_Language->get('order_shipping_tracking_no_title'); ?></h6>

          <p><?php echo $order->info['tracking_no']; ?></p>
<?php
    }
  }
?>
        </td>
      </tr>
    </table>
  </div>
</div>

<div class="moduleBox">

  <h6><?php echo $osC_Language->get('order_products_title'); ?></h6>
  
  <div class="content">
    <table border="0" width="100%" cellspacing="0" cellpadding="2">

<?php
  if (sizeof($order->info['tax_groups']) > 1) {
?>

      <tr>
        <td colspan="2"><h6><?php echo $osC_Language->get('order_products_title'); ?></h6></td>
        <td align="right"><h6><?php echo $osC_Language->get('order_tax_title'); ?></h6></td>
        <td align="right"><h6><?php echo $osC_Language->get('order_total_title'); ?></h6></td>
      </tr>

<?php
  } else {
?>

      <tr>
        <td colspan="3"></td>
      </tr>

<?php
  }

  foreach ($order->products as $product) {
    echo '      <tr>' . "\n" .
         '        <td align="right" valign="top" width="30">' . $product['qty'] . '&nbsp;x</td>' . "\n" .
         '        <td valign="top">' . $product['name'];

    if (isset($product['type']) && ($product['type'] == PRODUCT_TYPE_DOWNLOADABLE) && (isset($product['downloads_status']) && ($product['downloads_status'] == 1))) {
      echo osc_link_object(osc_href_link(FILENAME_DOWNLOAD, 'id=' . $product['orders_products_download_id'] . '&order=' . $_GET['orders']), $osC_Language->get('download_file'), 'data-max="' . $product['number_of_downloads'] * $product['qty'] . '" data-downloads="0" class="downloadable button"');
    }
    
    if (isset($product['type']) && ($product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE)) {
      echo '<br /><nobr><small>&nbsp;<i> - ' . $osC_Language->get('senders_name') . ': ' . $product['senders_name'] . '</i></small></nobr>';
      
      if ($product['gift_certificates_type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
        echo '<br /><nobr><small>&nbsp;<i> - ' . $osC_Language->get('senders_email') . ': ' . $product['senders_email'] . '</i></small></nobr>';
      }
      
      echo '<br /><nobr><small>&nbsp;<i> - ' . $osC_Language->get('recipients_name') . ': ' . $product['recipients_name'] . '</i></small></nobr>';
      
      if ($product['gift_certificates_type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
        echo '<br /><nobr><small>&nbsp;<i> - ' . $osC_Language->get('recipients_email') . ': ' . $product['recipients_email'] . '</i></small></nobr>';
      }

      echo '<br /><nobr><small>&nbsp;<i> - ' . $osC_Language->get('messages') . ': ' . $product['messages'] . '</i></small></nobr>';
    }
    
    if (isset($product['variants']) && (sizeof($product['variants']) > 0)) {
      foreach ($product['variants'] as $variant) {
        echo '<br /><nobr><small>&nbsp;<i> - ' . $variant['groups_name'] . ': ' . $variant['values_name'] . '</i></small></nobr>';
      }
    }
    
    if ( isset($product['customizations']) && !empty($product['customizations']) ) {
      echo '<p>';
        foreach ($product['customizations'] as $key => $customization) {
          echo '<div style="float: left">' . $customization['qty'] . ' x ' . '</div>';
          echo '<div style="margin-left: 25px">';
            foreach ($customization['fields'] as $field) {
              echo $field['customization_fields_name'] . ': ' . $field['customization_value'] . '<br />';
            }
          echo '</div>';
        }
      echo '</p>';
    }

    echo '        </td>' . "\n";

    if (sizeof($order->info['tax_groups']) > 1) {
      echo '      <td valign="top" align="right">' . osC_Tax::displayTaxRateValue($product['tax']) . '</td>' . "\n";
    }

    echo '        <td align="right" valign="top">' . $osC_Currencies->displayPriceWithTaxRate($product['final_price'], $product['tax'], $product['qty'], $order->info['currency'], $order->info['currency_value']) . '</td>' . "\n" .
         '       </tr>' . "\n";
  }
?>

    </table>

    <p>&nbsp;</p>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">

<?php
  foreach ($order->totals as $total) {
    echo '        <tr>' . "\n" .
         '         <td align="right">' . $total['title'] . '</td>' . "\n" .
         '         <td align="right" width="100">' . $total['text'] . '</td>' . "\n" .
         '       </tr>' . "\n";
  }
?>

    </table>
  </div>
</div>

<?php
 if ( !empty($order->info['wrapping_message']) ) {
?>

<div class="moduleBox">
  <h6><?php echo $osC_Language->get('gift_wrapping_message_heading'); ?></h6>

  <div class="content">
    <?php echo $order->info['wrapping_message']; ?>
  </div>
</div>

<?php
 }
?>

<?php
  $Qstatus = $order->getStatusListing();

  if ($Qstatus->numberOfRows() > 0) {
?>

<div class="moduleBox">
  <h6><?php echo $osC_Language->get('order_history_heading'); ?></h6>

  <div class="content">
    <table border="0" width="100%" cellspacing="0" cellpadding="2">

<?php
    while ($Qstatus->next()) {
      echo '    <tr>' . "\n" .
           '      <td valign="top" width="70">' . osC_DateTime::getShort($Qstatus->value('date_added')) . '</td>' . "\n" .
           '      <td valign="top" width="200">' . $Qstatus->value('orders_status_name') . '</td>' . "\n" .
           '      <td valign="top">' . (!osc_empty($Qstatus->valueProtected('comments')) ? nl2br($Qstatus->valueProtected('comments')) : '&nbsp;') . '</td>' . "\n" .
           '    </tr>' . "\n";
    }
?>

    </table>
  </div>
</div>

<?php
  }
?>
<div class="submitFormButtons">
  <?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'orders' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'SSL'), osc_draw_image_button('button_back.gif', $osC_Language->get('button_back'))); ?>
</div>

<script type="text/javascript">
	window.addEvent('domready', function() {
		if ($$('.downloadable').length > 0) {
    	$$('.downloadable').each(function(item) {
       	var downlodableMax = item.getProperty('data-max').toInt(),
       			downloadedCounts,
       			dlgWaring;
			
        item.addEvent('click', function(e) {
					downloadedCounts = item.getProperty('data-downloads').toInt();
					
					//check whether it is allowed to be downloaded
					if (downloadedCounts < downlodableMax) {
						item.setProperty('data-downloads', downloadedCounts + 1);

						return true;
					}else {
						dlgWaring = new popDialog('<?php echo '<p><strong>' . $osC_Language->get('error_download_max_num_of_times') . '</strong></p>';?>');
						dlgWaring.show();

						return false;
					}
        });
    	});
		}
	});
</script>
