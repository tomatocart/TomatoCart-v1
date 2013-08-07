<?php
/*
  $Id: order_history.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Boxes_order_history extends osC_Modules {
    var $_title,
        $_code = 'order_history',
        $_author_name = 'osCommerce',
        $_author_www = 'http://www.oscommerce.com',
        $_group = 'boxes';

    function osC_Boxes_order_history() {
      global $osC_Language;

      $this->_title = $osC_Language->get('box_order_history_heading');
    }

    function initialize() {
      global $osC_Database, $osC_Language, $osC_Customer;

      if ($osC_Customer->isLoggedOn()) {
        $Qorders = $osC_Database->query('select distinct op.products_id from :table_orders o, :table_orders_products op, :table_products p where o.customers_id = :customers_id and o.orders_id = op.orders_id and op.products_id = p.products_id and p.products_status = 1 group by products_id order by o.date_purchased desc limit :limit');
        $Qorders->bindTable(':table_orders', TABLE_ORDERS);
        $Qorders->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
        $Qorders->bindTable(':table_products', TABLE_PRODUCTS);
        $Qorders->bindInt(':customers_id', $osC_Customer->getID());
        $Qorders->bindInt(':limit', BOX_ORDER_HISTORY_MAX_LIST);
        $Qorders->execute();

        if ($Qorders->numberOfRows()) {
          $product_ids = '';

          while ($Qorders->next()) {
            $product_ids .= $Qorders->valueInt('products_id') . ',';
          }

          $product_ids = substr($product_ids, 0, -1);

          $Qproducts = $osC_Database->query('select products_id, products_name, products_keyword from :table_products_description where products_id in (:products_id) and language_id = :language_id order by products_name');
          $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
          $Qproducts->bindRaw(':products_id', $product_ids);
          $Qproducts->bindInt(':language_id', $osC_Language->getID());
          $Qproducts->execute();

          $this->_content = '<ol style="list-style: none; margin: 0; padding: 0;">';

          while ($Qproducts->next()) {
            $this->_content .= '<li>' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qproducts->value('products_id')), $Qproducts->value('products_name')) . '</li>';
          }

          $this->_content .= '</ol>';
        }
      }
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Maximum List Size', 'BOX_ORDER_HISTORY_MAX_LIST', '5', 'Maximum amount of products to show in the listing', '6', '0', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('BOX_ORDER_HISTORY_MAX_LIST');
      }

      return $this->_keys;
    }
  }
?>
