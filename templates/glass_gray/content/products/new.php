<?php
/*
  $Id: new.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<div class="moduleBox">

  <div class="content">
    <table border="0" width="100%" cellspacing="20" cellpadding="2">
    <?php
      $Qproducts = osC_Product::getListingNew();
    
      if ($Qproducts->numberOfRows() > 0) {
        while ($Qproducts->next()) {
          $osC_Product = new osC_Product($Qproducts->value('products_id'));
          
          $products_price = $osC_Product->getPriceFormated(true);
    ?>
    
      <tr>
        <td width="<?php echo $osC_Image->getWidth('thumbnails') + 10; ?>" valign="top" align="left">
    
    <?php
        echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qproducts->value('products_id')), $osC_Image->show($Qproducts->value('image'), $Qproducts->value('products_name')), 'id="img_ac_productsnew_' . $Qproducts->value('products_id') . '"');
    ?>
    
        </td>
        <td valign="top"><?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qproducts->value('products_id')), '<b><u>' . $Qproducts->value('products_name') . '</u></b>') . '<br />' . $osC_Language->get('date_added') . ' ' . osC_DateTime::getLong($Qproducts->value('products_date_added')) . '<br />' . $osC_Language->get('manufacturer') . ' ' . $Qproducts->value('manufacturers_name') . '<br /><br />' . $osC_Language->get('price') . ' ' . $products_price; ?></td>
        
        <?php 
	        if ($Qproducts->value('products_type') == PRODUCT_TYPE_SIMPLE) {
        ?>
        <td>
        	<input type="text" id="qty_<?php echo $Qproducts->valueInt('products_id'); ?>" value="1" size="1" class="qtyField" />
        </td>
        <?php
        	}else {
        ?>
        <td></td>
        <?php 
        	}
        ?>
        
        <td align="right" valign="middle">
          <?php 
            echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qproducts->value('products_id') . '&action=cart_add'), osc_draw_image_button('button_in_cart.gif', $osC_Language->get('button_add_to_cart'), 'class="ajaxAddToCart" id="ac_productsnew_' . $Qproducts->value('products_id') . '"'));
          ?>
        </td>
      </tr>
    
    <?php
        }
      } else {
    ?>
    
      <tr>
        <td><?php echo $osC_Language->get('no_new_products'); ?></td>
      </tr>
      <tr>
        <td colspan="3">&nbsp;</td>
      </tr>
    
    <?php
      }
    ?>
    
    </table>
    
    <div class="listingPageLinks">
      <div style="float: right;"><?php echo $Qproducts->getBatchPageLinks('page', 'new'); ?></div>
    
      <?php echo $Qproducts->getBatchTotalPages($osC_Language->get('result_set_number_of_products')); ?>
    </div>
  </div>
</div>