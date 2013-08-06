<?php
/*
  $Id: checkout_success.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $Qglobal = $osC_Database->query('select global_product_notifications from :table_customers where customers_id =:customers_id');
  $Qglobal->bindTable(':table_customers', TABLE_CUSTOMERS);
  $Qglobal->bindInt(':customers_id', $osC_Customer->getID());
  $Qglobal->execute();

  if ($Qglobal->valueInt('global_product_notifications') !== 1) {
    $Qorder = $osC_Database->query('select orders_id from :table_orders where customers_id = :customers_id order by date_purchased desc limit 1');
    $Qorder->bindTable(':table_orders', TABLE_ORDERS);
    $Qorder->bindInt(':customers_id', $osC_Customer->getID());
    $Qorder->execute();

    $Qproducts = $osC_Database->query('select products_id, products_name, products_type from :table_orders_products where orders_id = :orders_id order by products_name');
    $Qproducts->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
    $Qproducts->bindInt(':orders_id', $Qorder->valueInt('orders_id'));
    $Qproducts->execute();

    $products_array = array();
    $has_downloadable = false;
    $has_simple = false;
    while ($Qproducts->next()) {
      if ($Qproducts->valueInt('products_type') == PRODUCT_TYPE_SIMPLE) {
        if ($has_simple === false) {
          $has_simple = true;
        }
      }
      
      if ($Qproducts->valueInt('products_type') == PRODUCT_TYPE_DOWNLOADABLE) {
        if ($has_downloadable === false) {
          $has_downloadable = true;
        }
      }
      
      $products_array[] = array('id' => $Qproducts->valueInt('products_id'),
                                'text' => $Qproducts->value('products_name'));
    }
  }
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<form name="order" action="<?php echo osc_href_link(FILENAME_CHECKOUT, 'success=update', 'SSL'); ?>" method="post">

<div>
  <div style="float: left;"><?php echo osc_image('templates/' . $osC_Template->getCode() . '/images/order_process_success.png', $osC_Template->getPageTitle()); ?></div>

  <div style="padding-top: 30px;">
    <?php
      if (isset($has_simple)) {
        if ($has_simple === true) {
    ?>
    <p><?php echo $osC_Language->get('order_processed_successfully'); ?></p>
    <?php
        }
      }else {
    ?>
    <p><?php echo $osC_Language->get('order_processed_successfully'); ?></p>
    <?php
      }
    ?>

    <p>

<?php
  if ($Qglobal->valueInt('global_product_notifications') != 1) {
    echo $osC_Language->get('add_selection_to_product_notifications') . '<br /><p class="productsNotifications">';

    $products_displayed = array();
    for ($i=0, $n=sizeof($products_array); $i<$n; $i++) {
      if (!in_array($products_array[$i]['id'], $products_displayed)) {
        echo osc_draw_checkbox_field('notify[]', $products_array[$i]['id']) . ' ' . $products_array[$i]['text'] . '<br />';
        $products_displayed[] = $products_array[$i]['id'];
      }
    }

    echo '</p>';
  }
  
  echo sprintf($osC_Language->get('view_order_history'), osc_href_link(FILENAME_ACCOUNT, null, 'SSL'), osc_href_link(FILENAME_ACCOUNT, 'orders', 'SSL')) . ((isset($has_downloadable) && $has_downloadable === true) ? $osC_Language->get('view_downloadable_products_link') : '') . '<br /><br />' . sprintf($osC_Language->get('contact_store_owner'), osc_href_link(FILENAME_INFO, 'contact'));
?>

    </p>

    <h2 style="text-align: center;"><?php echo $osC_Language->get('thanks_for_shopping_with_us'); ?></h2>
  </div>
</div>

<div class="submitFormButtons" style="text-align: right;">
  <?php echo osc_draw_image_submit_button('button_continue.gif', $osC_Language->get('button_continue')); ?>
</div>

<?php
  if (DOWNLOAD_ENABLED == '1') {
?>

<table border="0" width="100%" cellspacing="0" cellpadding="0">

<?php
  include('includes/modules/downloads.php');
?>

</table>

<?php
  }
?>

</form>
