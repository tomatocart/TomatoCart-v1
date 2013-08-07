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
    //load products helper
    require_once('templates/' . $osC_Template->getCode() . '/models/products.php');

    //require category_listing view
    require_once('templates/' . $osC_Template->getCode() . '/content/index/category_listing.php');

    //get all the subcategories
    $categories_ids = array();
    $osC_CategoryTree->getChildren($current_category_id, $categories_ids);
    $categories_ids[] = $current_category_id;
      
    //whether the product attributes filter is enabled
    if (defined('PRODUCT_ATTRIBUTES_FILTER') && (PRODUCT_ATTRIBUTES_FILTER == '1')) {
        require_once('templates/' . $osC_Template->getCode() . '/modules/products_attributes.php');
    }
    
    $filters = NULL;

    // optional Product List Filter
    if (PRODUCT_LIST_FILTER > 0) {
        if (isset($_GET['manufacturers']) && !empty($_GET['manufacturers'])) {
            $filters = get_categories_filters($_GET['manufacturers']);
        } else {
            $filters = get_manufactuers_filters($categories_ids);
        }
    }
  
    $Qlisting = $osC_Products->execute();
    
    require('templates/' . $osC_Template->getCode() . '/modules/product_listing.php');