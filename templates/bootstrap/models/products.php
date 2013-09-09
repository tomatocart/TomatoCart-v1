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

/**
 * Short function for get languages resource
 *
 * @access public
 * @param $key
 * @return string
 */
function get_manufactuers_filters($categories_ids) {
    global $osC_Database, $osC_Language;

    $filterlist_sql = "select distinct m.manufacturers_id as id, m.manufacturers_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_MANUFACTURERS . " m where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and p.products_id = p2c.products_id and p2c.categories_id in (" . implode(',', $categories_ids) . ") order by m.manufacturers_name";
    $Qfilterlist = $osC_Database->query($filterlist_sql);
    $Qfilterlist->execute();

    if ($Qfilterlist->numberOfRows() > 1) {
        $manufacturers = array(array('id' => '', 'text' => $osC_Language->get('filter_all_manufacturers')));

        while ($Qfilterlist->next()) {
            $manufacturers[] = array('id' => $Qfilterlist->valueInt('id'), 'text' => $Qfilterlist->value('name'));
        }

        return $manufacturers;
    }

    return NULL;
}

/**
 * Short function for get languages resource
 *
 * @access public
 * @param $key
 * @return array
 */
function get_categories_filters($manufacturers_id) {
    global $osC_Database, $osC_Language;

    $filterlist_sql = "select distinct c.categories_id as id, cd.categories_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where p.products_status = '1' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and p2c.categories_id = cd.categories_id and cd.language_id = '" . (int)$osC_Language->getID() . "' and p.manufacturers_id = '" . (int)$manufacturers_id . "' order by cd.categories_name";
    $Qfilterlist = $osC_Database->query($filterlist_sql);
    $Qfilterlist->execute();

    if ($Qfilterlist->numberOfRows() > 1) {
        $categories = array(array('id' => '', 'text' => $osC_Language->get('filter_all_categories')));

        while ($Qfilterlist->next()) {
            $categories[] = array('id' => $Qfilterlist->valueInt('id'), 'text' => $Qfilterlist->value('name'));
        }

        return $categories;
    }

    return NULL;
}

/**
 * Short function for get new products
 *
 * @return mixed
 */
function get_new_products() {
    global $osC_Database, $osC_Services, $osC_Language, $osC_Currencies, $osC_Image, $osC_Specials, $current_category_id;
    
    if ($current_category_id < 1) {
        $Qfeatureproducts = $osC_Database->query('select p.products_id, p.products_tax_class_id, p.products_price, pd.products_name, pd.products_keyword, i.image, s.specials_new_products_price as specials_price, f.products_id as featured_products_id from :table_products p inner join :table_products_description pd on (p.products_id = pd.products_id and pd.language_id = :language_id) left join :table_products_frontpage f on (p.products_id = f.products_id) left join :table_products_images i on (p.products_id = i.products_id and i.default_flag = :default_flag) left join :table_specials s on (p.products_id = s.products_id and s.status = 1 and s.start_date <= now() and s.expires_date >= now()) where p.products_status = 1 order by p.products_date_added desc limit :max_display_new_products');
    } else {
        $Qfeatureproducts = $osC_Database->query('
        select distinct p.products_id, p.products_tax_class_id, p.products_price, pd.products_name, pd.products_keyword, i.image, s.specials_new_products_price as specials_price, f.products_id as featured_products_id from :table_products p inner join :table_products_description pd on (p.products_id = pd.products_id and pd.language_id = :language_id) left join :table_products_frontpage f on (p.products_id = f.products_id) left join :table_products_images i on (p.products_id = i.products_id and i.default_flag = :default_flag) left join :table_specials s on (p.products_id = s.products_id and s.status = 1 and s.start_date <= now() and s.expires_date >= now()) left join :table_products_to_categories p2c on (p2c.products_id = p.products_id) inner join :table_categories c on (c.parent_id = :parent_id and c.categories_id = p2c.categories_id) where p.products_status = 1 order by p.products_date_added desc limit :max_display_new_products');
        $Qfeatureproducts->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
        $Qfeatureproducts->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qfeatureproducts->bindInt(':parent_id', $current_category_id);
    }

    $Qfeatureproducts->bindTable(':table_products', TABLE_PRODUCTS);
    $Qfeatureproducts->bindTable(':table_products_frontpage', TABLE_PRODUCTS_FRONTPAGE);
    $Qfeatureproducts->bindTable(':table_specials', TABLE_SPECIALS);
    $Qfeatureproducts->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
    $Qfeatureproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
    $Qfeatureproducts->bindInt(':default_flag', 1);
    $Qfeatureproducts->bindInt(':language_id', $osC_Language->getID());
    $Qfeatureproducts->bindInt(':max_display_new_products', MODULE_CONTENT_NEW_PRODUCTS_MAX_DISPLAY);
    
    //set the cache key for new products module in bootstrap
	if (MODULE_CONTENT_NEW_PRODUCTS_CACHE > 0) {
        $Qfeatureproducts->setCache('new_products-bootstrap-' . $osC_Language->getCode() . '-' . $osC_Currencies->getCode() . '-' . $current_category_id, MODULE_CONTENT_NEW_PRODUCTS_CACHE);
    }

    $Qfeatureproducts->execute();

    if ($Qfeatureproducts->numberOfRows()) {
        $data = array();

        while ($Qfeatureproducts->next()) {
            $product = new osC_Product($Qfeatureproducts->valueInt('products_id'));

            $data[] = array(
            	'products_id' => $Qfeatureproducts->value('products_id'),
                'products_name' => $Qfeatureproducts->value('products_name'),
                'products_price' => $product->getPriceFormated(true),
                'is_specials' => ($Qfeatureproducts->value('specials_price') == NULL) ? FALSE : TRUE,
                'is_featured' => ($Qfeatureproducts->value('featured_products_id') == NULL) ? FALSE : TRUE,
                'products_image' => $osC_Image->show($Qfeatureproducts->value('image'), $Qfeatureproducts->value('products_name')));

        }
        
        return $data;
    }

    $Qfeatureproducts->freeResult();
}

/**
 * Short function for get feature products
 *
 * @return mixed
 */
function get_feature_products() {
    global $osC_Database, $osC_Services, $osC_Language, $osC_Currencies, $osC_Image, $osC_Specials, $current_category_id;

    if ($current_category_id < 1) {
        $Qfeatureproducts = $osC_Database->query('
        select p.products_id, p.products_tax_class_id, p.products_price, pd.products_name, pd.products_keyword, pf.sort_order, i.image, s.specials_new_products_price as specials_price from :table_products p inner join :table_products_description pd on (p.products_id = pd.products_id and pd.language_id = :language_id) left join :table_products_images i on (p.products_id = i.products_id and i.default_flag = :default_flag) inner join :table_products_frontpage pf on (p.products_id = pf.products_id) left join :table_specials s on (p.products_id = s.products_id and s.status = 1 and s.start_date <= now() and s.expires_date >= now()) where p.products_status = 1 order by pf.sort_order limit :max_display_feature_products');
    } else {
        $Qfeatureproducts = $osC_Database->query('select distinct p.products_id, p.products_tax_class_id, p.products_price, pd.products_name, pf.sort_order, pd.products_keyword, i.image, s.specials_new_products_price as specials_price from :table_products p inner join :table_products_description pd on (p.products_id = pd.products_id and pd.language_id = :language_id) left join :table_products_images i on (p.products_id = i.products_id and i.default_flag = :default_flag) inner join :table_products_frontpage pf on (p.products_id = pf.products_id) left join :table_specials s on (p.products_id = s.products_id and s.status = 1 and s.start_date <= now() and s.expires_date >= now())left join :table_products_to_categories p2c on (p2c.products_id = p.products_id) inner join :table_categories c on (c.parent_id = :parent_id and c.categories_id = p2c.categories_id) where p.products_status = 1 order by pf.sort_order limit :max_display_feature_products');
        $Qfeatureproducts->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
        $Qfeatureproducts->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qfeatureproducts->bindInt(':parent_id', $current_category_id);
    }

    $Qfeatureproducts->bindTable(':table_products', TABLE_PRODUCTS);
    $Qfeatureproducts->bindTable(':table_products_frontpage', TABLE_PRODUCTS_FRONTPAGE);
    $Qfeatureproducts->bindTable(':table_specials', TABLE_SPECIALS);
    $Qfeatureproducts->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
    $Qfeatureproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
    $Qfeatureproducts->bindInt(':default_flag', 1);
    $Qfeatureproducts->bindInt(':language_id', $osC_Language->getID());
    $Qfeatureproducts->bindInt(':max_display_feature_products', MODULE_CONTENT_FEATURE_PRODUCTS_MAX_DISPLAY);
    
    //set the cache key for feature products module in bootstrap
    if (MODULE_CONTENT_NEW_PRODUCTS_CACHE > 0) {
		$Qfeatureproducts->setCache('feature-products-bootstrap-' . $osC_Language->getCode() . '-' . $osC_Currencies->getCode() . '-' . $current_category_id, MODULE_CONTENT_NEW_PRODUCTS_CACHE);
    }
    
    $Qfeatureproducts->execute();

    if ($Qfeatureproducts->numberOfRows()) {
        $data = array();

        while ($Qfeatureproducts->next()) {
            $product = new osC_Product($Qfeatureproducts->valueInt('products_id'));

            $data[] = array(
            	'products_id' => $Qfeatureproducts->value('products_id'),
                'products_name' => $Qfeatureproducts->value('products_name'),
                'products_price' => $product->getPriceFormated(true),
                'is_specials' => ($Qfeatureproducts->value('specials_price') == NULL) ? FALSE : TRUE,
                'products_image' => $osC_Image->show($Qfeatureproducts->value('image'), $Qfeatureproducts->value('products_name')));

        }
        return $data;
    }

    $Qfeatureproducts->freeResult();
}