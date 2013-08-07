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

  if ( isset($current_category_id) && !empty($current_category_id) ) {
    if (isset($categories_ids) && !empty($categories_ids)) {
      $Qgroups = $osC_Database->query('select distinct(p.products_attributes_groups_id) from :table_products p, :table_products_to_categories ptc where p.products_id = ptc.products_id and p.products_attributes_groups_id is not null and ptc.categories_id in (:categories_id)');
      $Qgroups->bindTable(':table_products', TABLE_PRODUCTS);
      $Qgroups->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
      $Qgroups->bindRaw(':categories_id', implode(',', $categories_ids));
      $Qgroups->execute();
    }else {
      $Qgroups = $osC_Database->query('select distinct(p.products_attributes_groups_id) from :table_products p, :table_products_to_categories ptc where p.products_id = ptc.products_id and p.products_attributes_groups_id is not null and ptc.categories_id = :categories_id ');
      $Qgroups->bindTable(':table_products', TABLE_PRODUCTS);
      $Qgroups->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
      $Qgroups->bindInt(':categories_id', $current_category_id);
      $Qgroups->execute();
    }
  }else if (isset($_GET['manufacturers']) && !empty($_GET['manufacturers'])) {
    $Qgroups = $osC_Database->query('select distinct(p.products_attributes_groups_id) from :table_products p, :table_products_to_categories ptc where p.products_id = ptc.products_id and p.products_attributes_groups_id is not null and p.manufacturers_id = :manufacturers_id ');
    $Qgroups->bindTable(':table_products', TABLE_PRODUCTS);
    $Qgroups->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
    $Qgroups->bindInt(':manufacturers_id', (int)$_GET['manufacturers']);
    $Qgroups->execute();
  }
  
  if (isset($Qgroups) && ($Qgroups->numberOfRows() > 0)) {
    //products listing page for specific manufactuer
    if (isset($_GET['manufacturers']) && !empty($_GET['manufacturers'])) {
      $action = osc_href_link(FILENAME_DEFAULT, 'manufacturers=' . $_GET['manufacturers']);
      
    //product listing page for specific category
    }else {
      $action = osc_href_link(FILENAME_DEFAULT, 'cPath=' . $cPath);
    }
      
    echo '<form name="filter" action="' . $action . '" method="get">';
    
    //pass the manufacturers or cPath param as seo is disabled
    if (!$osC_Services->isStarted('sefu')) {
      if (isset($_GET['manufacturers']) && !empty($_GET['manufacturers'])) {
        echo osc_draw_hidden_field('manufacturers', $_GET['manufacturers']);
      } else if (isset($_GET['cPath']) && !empty($_GET['cPath'])) {
        echo osc_draw_hidden_field('cPath', $cPath);
      }
    }
    
    echo osc_draw_hidden_session_id_field();
    
    echo '    <div class="moduleBox">' . "\n" . 
         '        <h6>'.$osC_Language->get('products_attributes_filter').'</h6>' . "\n" . 
         '        <div id="productAttributes" class="content col3 clearfix">' . "\n";

    //whether the products attributes filter and the category/manufacturer filter is linked
    if (defined('PRODUCT_LINK_FILTER') && (PRODUCT_LINK_FILTER == '1')) {
      if ( isset($_GET['filter']) && !empty($_GET['filter']) ) {
        if (!is_array($_GET['filter'])) {
          echo osc_draw_hidden_field('filter', $_GET['filter']);
        }else {
          foreach($_GET['filter'] as $filter) {
            echo osc_draw_hidden_field('filter', $filter);
          }
        }
      }
    }
    
    //add sort parameter to the form
    if (isset($_GET['sort'])) {
      echo osc_draw_hidden_field('sort', $_GET['sort']);
    }
    
    //add the view type parameter to the form
    if (isset($_GET['view'])) {
      echo osc_draw_hidden_field('view', $_GET['view']);
    }
    
    while ($Qgroups->next()) {
      $Qentries = $osC_Database->query('select * from :table_products_attributes_values where products_attributes_groups_id = :products_attributes_groups_id and language_id = :language_id  order by sort_order');
      $Qentries->bindTable(':table_products_attributes_values', TABLE_PRODUCTS_ATTRIBUTES_VALUES);
      $Qentries->bindInt(':products_attributes_groups_id', $Qgroups->valueInt('products_attributes_groups_id'));
      $Qentries->bindInt(':language_id', $osC_Language->getID());
      $Qentries->execute();
      
      while ($Qentries->next()) {
  
        $data = array();
        $data[] = array('id'=>'', 'text' => $osC_Language->get('pull_down_default'));

        if ($Qentries->value('module') == 'text_field') {

          $Qvalues = $osC_Database->query('select distinct value from :table_products_attributes where products_attributes_values_id = :products_attributes_values_id and language_id = :language_id ');
          $Qvalues->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
          $Qvalues->bindInt(':products_attributes_values_id', $Qentries->value('products_attributes_values_id'));
          $Qvalues->bindInt(':language_id', $osC_Language->getID());
          $Qvalues->execute();

          while ($Qvalues->next()){
            $fields_value = $Qvalues->value('value');

            if(!empty($fields_value))
              $data[] = array('id'=> $fields_value, 'text' => $fields_value);
          }

          $Qvalues->freeResult();
        } else {
          $values = explode(',', $Qentries->value('value'));

          for ($i = 1; $i <= sizeof($values); $i++) {
            $data[] = array('id' => $i, 'text' => $values[$i - 1]);
          }
        }

        $default = '';
        $products_attributes_values_id = $Qentries->value('products_attributes_values_id');

        if ( isset($_GET['products_attributes']) && is_array($_GET['products_attributes']) && isset($_GET['products_attributes'][$products_attributes_values_id]) ) {
          $default = $_GET['products_attributes'][$products_attributes_values_id];
        }

        echo '<div>' . osc_draw_label($Qentries->value('name') . ' :', 'products_attributes[' . $products_attributes_values_id . ']') . '&nbsp;' . osc_draw_pull_down_menu('products_attributes[' . $products_attributes_values_id . ']', $data, $default, 'onchange="this.form.submit()"').'</div>';
    
      }
    }

    $Qgroups->freeResult();

    echo '        </div>' . "\n" . 
         '    </div>' . "\n" .
         '</form>';
  }
?>

