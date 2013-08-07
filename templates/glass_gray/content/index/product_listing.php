<?php
/*
  $Id: product_listing.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<?php echo osc_image(DIR_WS_IMAGES . $osC_Template->getPageImage(), $osC_Template->getPageTitle(), HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT, 'id="pageIcon"'); ?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<?php
  //whether the product attributes filter is enabled
  if (defined('PRODUCT_ATTRIBUTES_FILTER') && (PRODUCT_ATTRIBUTES_FILTER == '1')) {
    require('includes/modules/products_attributes.php');
  }

// optional Product List Filter
  if (PRODUCT_LIST_FILTER > 0) {
    if (isset($_GET['manufacturers']) && !empty($_GET['manufacturers'])) {
      $filterlist_sql = "select distinct c.categories_id as id, cd.categories_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where p.products_status = '1' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and p2c.categories_id = cd.categories_id and cd.language_id = '" . (int)$osC_Language->getID() . "' and p.manufacturers_id = '" . (int)$_GET['manufacturers'] . "' order by cd.categories_name";
    } else {
      $filterlist_sql = "select distinct m.manufacturers_id as id, m.manufacturers_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_MANUFACTURERS . " m where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and p.products_id = p2c.products_id and p2c.categories_id = '" . (int)$current_category_id . "' order by m.manufacturers_name";
    }

    $Qfilterlist = $osC_Database->query($filterlist_sql);
    $Qfilterlist->execute();

    if ($Qfilterlist->numberOfRows() > 1) {
      //products listing page for specific manufactuer
      if (isset($_GET['manufacturers']) && !empty($_GET['manufacturers'])) {
        $action = osc_href_link(FILENAME_DEFAULT, 'manufacturers=' . $_GET['manufacturers']);
      //product listing page for specific category
      }else {
        $action = osc_href_link(FILENAME_DEFAULT, 'cPath=' . $cPath);
      }

      echo '<form name="filter" action="' . $action . '" method="get">' . $osC_Language->get('filter_show') . '&nbsp;';
      if (isset($_GET['manufacturers']) && !empty($_GET['manufacturers'])) {
        //pass the manufacturers param as seo is disabled
        if (!$osC_Services->isStarted('sefu')) {
          echo osc_draw_hidden_field('manufacturers', $_GET['manufacturers']);
        }
        
        $options = array(array('id' => '', 'text' => $osC_Language->get('filter_all_categories')));
      } else if (isset($_GET['cPath']) && !empty($_GET['cPath'])) {
        //pass the cPath param as seo is disabled
        if (!$osC_Services->isStarted('sefu')) {
          echo osc_draw_hidden_field('cPath', $_GET['cPath']);
        }
        
        $options = array(array('id' => '', 'text' => $osC_Language->get('filter_all_manufacturers')));
      }
      
      //whether the products attributes filter and the category/manufacturer filter is linked
      if (defined('PRODUCT_LINK_FILTER') && (PRODUCT_LINK_FILTER == '1')) {
        if (isset($_GET['products_attributes']) && is_array($_GET['products_attributes'])) {
          foreach($_GET['products_attributes'] as $att_value_id => $att_value) {
            echo osc_draw_hidden_field('products_attributes[' . $att_value_id . ']', $att_value);
          }
        }
      }

      if (isset($_GET['sort'])) {
        echo osc_draw_hidden_field('sort', $_GET['sort']);
      }

      while ($Qfilterlist->next()) {
        $options[] = array('id' => $Qfilterlist->valueInt('id'), 'text' => $Qfilterlist->value('name'));
      }
      echo osc_draw_pull_down_menu('filter', $options, (isset($_GET['filter']) ? $_GET['filter'] : null), 'onchange="this.form.submit()"');
      echo osc_draw_hidden_session_id_field() . '</form>' . "\n";
    }
  }

  $Qlisting = $osC_Products->execute();
  require('includes/modules/product_listing.php');
?>
