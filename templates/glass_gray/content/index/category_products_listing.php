<?php
/*
  $Id: category_listing.php $
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

<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr>

<?php
    if (isset($cPath) && strpos($cPath, '_')) {
// check to see if there are deeper categories within the current category
      $category_links = array_reverse($cPath_array);
      for($i=0, $n=sizeof($category_links); $i<$n; $i++) {
        $Qcategories = $osC_Database->query('select count(*) as total from :table_categories c, :table_categories_description cd where c.parent_id = :parent_id and c.categories_status = 1 and c.categories_id = cd.categories_id and cd.language_id = :language_id');
        $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
        $Qcategories->bindInt(':parent_id', $category_links[$i]);
        $Qcategories->bindInt(':language_id', $osC_Language->getID());
        $Qcategories->execute();

        if ($Qcategories->valueInt('total') < 1) {
          // do nothing, go through the loop
        } else {
          $Qcategories = $osC_Database->query('select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from :table_categories c, :table_categories_description cd where c.parent_id = :parent_id and c.categories_id = cd.categories_id and cd.language_id = :language_id and c.categories_status = 1 order by sort_order, cd.categories_name');
          $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
          $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
          $Qcategories->bindInt(':parent_id', $category_links[$i]);
          $Qcategories->bindInt(':language_id', $osC_Language->getID());
          $Qcategories->execute();
          break; // we've found the deepest category the customer is in
        }
      }
    } else {
      $Qcategories = $osC_Database->query('select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from :table_categories c, :table_categories_description cd where c.parent_id = :parent_id and c.categories_id = cd.categories_id and cd.language_id = :language_id and c.categories_status = 1 order by sort_order, cd.categories_name');
      $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
      $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
      $Qcategories->bindInt(':parent_id', $current_category_id);
      $Qcategories->bindInt(':language_id', $osC_Language->getID());
      $Qcategories->execute();
    }

    $number_of_categories = $Qcategories->numberOfRows();

    $rows = 0;
    while ($Qcategories->next()) {
      $rows++;
      $width = (int)(100 / MAX_DISPLAY_CATEGORIES_PER_ROW) . '%';
      echo '    <td align="center" class="smallText" width="' . $width . '" valign="top">' . osc_link_object(osc_href_link(FILENAME_DEFAULT, 'cPath=' . $osC_CategoryTree->buildBreadcrumb($Qcategories->valueInt('categories_id'))), osc_image(DIR_WS_IMAGES . 'categories/' . $Qcategories->value('categories_image'), $Qcategories->value('categories_name')) . '<br />' . $Qcategories->value('categories_name')) . '</td>' . "\n";
      if ((($rows / MAX_DISPLAY_CATEGORIES_PER_ROW) == floor($rows / MAX_DISPLAY_CATEGORIES_PER_ROW)) && ($rows != $number_of_categories)) {
        echo '  </tr>' . "\n";
        echo '  <tr>' . "\n";
      }
    }
?>

  </tr>
</table>

<?php echo osc_image(DIR_WS_IMAGES . $osC_Template->getPageImage(), $osC_Template->getPageTitle(), HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT, 'id="pageIcon"'); ?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<?php
  //get all the subcategories
  $categories_ids = array();
  $osC_CategoryTree->getChildren($current_category_id, $categories_ids);
  $categories_ids[] = $current_category_id;
      
  //whether the product attributes filter is enabled
  if (defined('PRODUCT_ATTRIBUTES_FILTER') && (PRODUCT_ATTRIBUTES_FILTER == '1')) {
    require('includes/modules/products_attributes.php');
  }

// optional Product List Filter
  if (PRODUCT_LIST_FILTER > 0) {
    if (isset($_GET['manufacturers']) && !empty($_GET['manufacturers'])) {
      $filterlist_sql = "select distinct c.categories_id as id, cd.categories_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where p.products_status = '1' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and p2c.categories_id = cd.categories_id and cd.language_id = '" . (int)$osC_Language->getID() . "' and p.manufacturers_id = '" . (int)$_GET['manufacturers'] . "' order by cd.categories_name";
    } else {
      $filterlist_sql = "select distinct m.manufacturers_id as id, m.manufacturers_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_MANUFACTURERS . " m where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and p.products_id = p2c.products_id and p2c.categories_id in (" . implode(',', $categories_ids) . ") order by m.manufacturers_name";
    }

    $Qfilterlist = $osC_Database->query($filterlist_sql);
    $Qfilterlist->execute();
    
    if ($Qfilterlist->numberOfRows() > 1) {
    
      echo '<form name="filter" action="' . osc_href_link(FILENAME_DEFAULT) . '" method="get">' . $osC_Language->get('filter_show') . '&nbsp;';
      if (isset($_GET['manufacturers']) && !empty($_GET['manufacturers'])) {
        echo osc_draw_hidden_field('manufacturers', $_GET['manufacturers']);
        $options = array(array('id' => '', 'text' => $osC_Language->get('filter_all_categories')));
      } else {
        echo osc_draw_hidden_field('cPath', $cPath);
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

