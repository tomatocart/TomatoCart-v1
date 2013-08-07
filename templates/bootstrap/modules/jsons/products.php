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

class toC_Json_Products {

    function compareProducts() {
        global $osC_Language, $toC_Compare_Products;

        $osC_Language->load('products');

        ob_start();

        require_once('templates/' . $_GET['template'] . '/modules/compare_products.php');
        $content = ob_get_contents();

        ob_end_clean();

        echo $content;
    }

    function getVariantsFormattedPrice() {
        global $toC_Json;

        $response = array();

        if (isset($_POST['products_id_string']) && preg_match('/^[0-9]+(#([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)$/', $_POST['products_id_string'])) {
            $response['success'] = true;

            $variants = osc_parse_variants_from_id_string($_POST['products_id_string']);
            $osC_Product = new osC_Product($_POST['products_id_string']);
            $formatted_price = $osC_Product->getPriceFormated(true, $variants);

            $response['formatted_price'] = $formatted_price;
        }else {
            $response['success'] = false;
            $response['feedback'] = 'The products id string is not valid';
        }

        echo $toC_Json->encode($response);
    }
}
