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

class toC_Json_Auto_Completer {

    function getProducts() {
        global $osC_Database, $osC_Language, $toC_Json;

        $products = array();
        if (isset($_POST['keywords']) && !empty($_POST['keywords'])) {
            $Qproducts = $osC_Database->query("select distinct products_name from :table_products_description pd, :table_products p where pd.products_id = p.products_id and p.products_status = :products_status and products_name like :keywords and language_id =" . $osC_Language->getID());
            $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
            $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
            $Qproducts->bindInt(':products_status', 1);
            $Qproducts->bindValue(':keywords', '%' . $_POST['keywords'] . '%');
            $Qproducts->execute();

            while($Qproducts->next()) {
                $products[] = $Qproducts->value('products_name');
            }
        }

        echo $toC_Json->encode($products);
    }
}
?>