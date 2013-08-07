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
?>

<?php echo osc_image(DIR_WS_IMAGES . $osC_Template->getPageImage(), $osC_Template->getPageTitle(), HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT, 'id="pageIcon" class="pull-right" style="width: ' . HEADING_IMAGE_WIDTH . 'px; height: ' . HEADING_IMAGE_HEIGHT . 'px"'); ?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<?php
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
            $filters = get_manufactuers_filters(array($current_category_id));
        }
    }

    $Qlisting = $osC_Products->execute();
    
    require('templates/' . $osC_Template->getCode() . '/modules/product_listing.php');