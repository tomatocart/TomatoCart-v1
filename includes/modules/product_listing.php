<?php
/*
  $Id: product_listing.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

// create column list
  $define_list = array('PRODUCT_LIST_SKU' => PRODUCT_LIST_SKU, 
                       'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
                       'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
                       'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
                       'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
                       'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
                       'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE,
                       'PRODUCT_LIST_BUY_NOW' => PRODUCT_LIST_BUY_NOW);

  asort($define_list);

  $column_list = array();
  reset($define_list);
  while (list($key, $value) = each($define_list)) {
    if ($value > 0) $column_list[] = $key;
  }

  if ( ($Qlisting->numberOfRows() > 0) && ( (PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3') ) ) {
?>

<div class="listingPageLinks clearfix">
  <?php echo $Qlisting->getBatchPageLinks('page', osc_get_all_get_params(array('page', 'info', 'x', 'y')), false); ?>

  <div class="totalPages"><?php echo $Qlisting->getBatchTotalPages($osC_Language->get('result_set_number_of_products')); ?></div>
</div>

<?php
  }
?>

<div>

<?php
  if ($Qlisting->numberOfRows() > 0) {
?>

  <table border="0" width="100%" cellspacing="0" cellpadding="2" class="productListing">
    <tr>

<?php
    for ($col=0, $n=sizeof($column_list); $col<$n; $col++) {
      $lc_key = false;
      $lc_align = 'center';

      switch ($column_list[$col]) {
        case 'PRODUCT_LIST_SKU':
          $lc_text = $osC_Language->get('listing_sku_heading');
          $lc_key = 'sku';
          break;
        case 'PRODUCT_LIST_NAME':
          $lc_text = $osC_Language->get('listing_products_heading');
          $lc_key = 'name';
          break;
        case 'PRODUCT_LIST_MANUFACTURER':
          $lc_text = $osC_Language->get('listing_manufacturer_heading');
          $lc_key = 'manufacturer';
          break;
        case 'PRODUCT_LIST_PRICE':
          $lc_text = $osC_Language->get('listing_price_heading');
          $lc_key = 'price';
          $lc_align = 'right';
          break;
        case 'PRODUCT_LIST_QUANTITY':
          $lc_text = $osC_Language->get('listing_quantity_heading');
          $lc_key = 'quantity';
          $lc_align = 'right';
          break;
        case 'PRODUCT_LIST_WEIGHT':
          $lc_text = $osC_Language->get('listing_weight_heading');
          $lc_key = 'weight';
          $lc_align = 'right';
          break;
        case 'PRODUCT_LIST_IMAGE':
          $lc_text = $osC_Language->get('listing_image_heading');
          $lc_align = 'center';
          break;
        case 'PRODUCT_LIST_BUY_NOW':
          $lc_text = $osC_Language->get('listing_buy_now_heading');
          $lc_align = 'center';
          break;
      }

      if ($lc_key !== false) {
        $lc_text = osc_create_sort_heading($lc_key, $lc_text);
      }

      echo '      <td align="' . $lc_align . '" class="productListing-heading">&nbsp;' . $lc_text . '&nbsp;</td>' . "\n";
    }
?>

    </tr>

<?php
    $rows = 0;

    while ($Qlisting->next()) {
      $rows++;

      echo '    <tr class="' . ((($rows/2) == floor($rows/2)) ? 'productListing-even' : 'productListing-odd') . '">' . "\n";

      for ($col=0, $n=sizeof($column_list); $col<$n; $col++) {
        $lc_align = '';

        switch ($column_list[$col]) {
          case 'PRODUCT_LIST_SKU':
            $lc_align = '';
            $lc_text = '&nbsp;' . $Qlisting->value('products_sku') . '&nbsp;';
            break;
          case 'PRODUCT_LIST_NAME':
            $lc_align = '';
            if (isset($_GET['manufacturers'])) {
              $lc_text = osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qlisting->value('products_id') . '&manufacturers=' . $_GET['manufacturers']), $Qlisting->value('products_name')) . (($Qlisting->value('products_short_description') === NULL) || ($Qlisting->value('products_short_description') === '') ? '' : '<p>' . $Qlisting->value('products_short_description') . '</p>');
            } else {
              $lc_text = '&nbsp;' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qlisting->value('products_id') . ($cPath ? '&cPath=' . $cPath : '')), $Qlisting->value('products_name')) . (($Qlisting->value('products_short_description') === NULL) || ($Qlisting->value('products_short_description') === '') ? '' : '<p>' . $Qlisting->value('products_short_description') . '</p>') . '&nbsp;';
            }
            break;
          case 'PRODUCT_LIST_MANUFACTURER':
            $lc_align = '';
            $lc_text = '&nbsp;' . osc_link_object(osc_href_link(FILENAME_DEFAULT, 'manufacturers=' . $Qlisting->valueInt('manufacturers_id')), $Qlisting->value('manufacturers_name')) . '&nbsp;';
            break;
          case 'PRODUCT_LIST_PRICE':
            $lc_align = 'right';
            $osC_Product = new osC_Product($Qlisting->value('products_id'));
            $lc_text = $osC_Product->getPriceFormated(true);
            break;
          case 'PRODUCT_LIST_QUANTITY':
            $lc_align = 'right';
            $lc_text = '&nbsp;' . $Qlisting->valueInt('products_quantity') . '&nbsp;';
            break;
          case 'PRODUCT_LIST_WEIGHT':
            $lc_align = 'right';
            $lc_text = '&nbsp;' . $osC_Weight->display($Qlisting->value('products_weight'), $Qlisting->value('products_weight_class')) . '&nbsp;';
            break;
          case 'PRODUCT_LIST_IMAGE':
            $lc_align = 'center';
            if (isset($_GET['manufacturers'])) {
              if ($Qlisting->value('products_type') == PRODUCT_TYPE_SIMPLE) {
                $lc_text = osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qlisting->value('products_id') . '&manufacturers=' . $_GET['manufacturers']), $osC_Image->show($Qlisting->value('image'), $Qlisting->value('products_name')), 'id="img_ac_productlisting_'. $Qlisting->value('products_id') . '"');
              }else {
                $lc_text = osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qlisting->value('products_id') . '&manufacturers=' . $_GET['manufacturers']), $osC_Image->show($Qlisting->value('image'), $Qlisting->value('products_name')));
              }  
            } else {
              if ($Qlisting->value('products_type') == PRODUCT_TYPE_SIMPLE) {
                $lc_text = osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qlisting->value('products_id') . ($cPath ? '&cPath=' . $cPath : '')), $osC_Image->show($Qlisting->value('image'), $Qlisting->value('products_name')), 'id="img_ac_productlisting_'. $Qlisting->value('products_id') . '"');
              }else {
                $lc_text = osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qlisting->value('products_id') . ($cPath ? '&cPath=' . $cPath : '')), $osC_Image->show($Qlisting->value('image'), $Qlisting->value('products_name')));
              }                
            }
            break;
          case 'PRODUCT_LIST_BUY_NOW':
            $lc_align = 'center';
            
            if ($Qlisting->value('products_type') == PRODUCT_TYPE_SIMPLE) {
							$lc_text = '<input type="text" id="qty_' . $Qlisting->value('products_id') . '" value="1" size="1" class="qtyField" />';
              $lc_text .= osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), $Qlisting->value('products_id') . '&' . osc_get_all_get_params(array('action')) . '&action=cart_add'), osc_draw_image_button('button_buy_now.gif', $osC_Language->get('button_buy_now'), 'class="ajaxAddToCart" id="ac_productlisting_'. $Qlisting->value('products_id') . '"')) . '&nbsp;<br />';
            }else {
              $lc_text = osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), $Qlisting->value('products_id') . '&' . osc_get_all_get_params(array('action')) . '&action=cart_add'), osc_draw_image_button('button_buy_now.gif', $osC_Language->get('button_buy_now'))) . '&nbsp;<br />';
            }
            
            if ($osC_Template->isInstalled('compare_products', 'boxes')) {
              $lc_text .= osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), 'cid=' . $Qlisting->value('products_id') . '&' . osc_get_all_get_params(array('action')) . '&action=compare_products_add'), $osC_Language->get('add_to_compare')) . '&nbsp;<br />';
            }  
            
            $lc_text .= osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), $Qlisting->value('products_id') . '&' . osc_get_all_get_params(array('action')) . '&action=wishlist_add'), $osC_Language->get('add_to_wishlist')); 
                        
            break;
        }

        echo '      <td ' . ((empty($lc_align) === false) ? 'align="' . $lc_align . '" ' : '') . ' valign="top" class="productListing-data">' . $lc_text . '</td>' . "\n";
      }

      echo '    </tr>' . "\n";
    }
?>

  </table>

<?php
  } else {
    echo $osC_Language->get('no_products_in_category');
  }
?>

</div>

<?php
  if ( ($Qlisting->numberOfRows() > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3')) ) {
?>

<div class="listingPageLinks clearfix">
  <?php echo $Qlisting->getBatchPageLinks('page', osc_get_all_get_params(array('page', 'info', 'x', 'y')), false); ?>

  <div class="totalPages"><?php echo $Qlisting->getBatchTotalPages($osC_Language->get('result_set_number_of_products')); ?></div>
</div>

<?php
  }
?>
