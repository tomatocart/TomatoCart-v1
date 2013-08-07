<?php
/*
  $Id: also_purchased_products.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Content_also_purchased_products extends osC_Modules {
    var $_title,
        $_code = 'also_purchased_products',
        $_author_name = 'osCommerce',
        $_author_www = 'http://www.oscommerce.com',
        $_group = 'content';

/* Class constructor */

    function osC_Content_also_purchased_products() {
      global $osC_Language;

      $this->_title = $osC_Language->get('customers_also_purchased_title');
    }

    function initialize() {
      global $osC_Database, $osC_Language, $osC_Product, $osC_Image;

      if (isset($osC_Product)) {
        $Qorders = $osC_Database->query('select p.products_id, pd.products_name, pd.products_keyword, i.image from :table_orders_products opa, :table_orders_products opb, :table_orders o, :table_products p left join :table_products_images i on (p.products_id = i.products_id and i.default_flag = :default_flag), :table_products_description pd where opa.products_id = :products_id and opa.orders_id = opb.orders_id and opb.products_id != :products_id and opb.products_id = p.products_id and opb.orders_id = o.orders_id and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id group by p.products_id order by o.date_purchased desc limit :limit');
        $Qorders->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
        $Qorders->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
        $Qorders->bindTable(':table_orders', TABLE_ORDERS);
        $Qorders->bindTable(':table_products', TABLE_PRODUCTS);
        $Qorders->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
        $Qorders->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
        $Qorders->bindInt(':default_flag', 1);
        $Qorders->bindInt(':products_id', $osC_Product->getID());
        $Qorders->bindInt(':products_id', $osC_Product->getID());
        $Qorders->bindInt(':language_id', $osC_Language->getID());
        $Qorders->bindInt(':limit', MODULE_CONTENT_ALSO_PURCHASED_MAX_DISPLAY);

        if (MODULE_CONTENT_ALSO_PURCHASED_PRODUCTS_CACHE > 0) {
          $Qorders->setCache('also_purchased-' . $osC_Product->getID(), MODULE_CONTENT_ALSO_PURCHASED_PRODUCTS_CACHE);
        }

        $Qorders->execute();

        if ($Qorders->numberOfRows() >= MODULE_CONTENT_ALSO_PURCHASED_MIN_DISPLAY) {
          $this->_content = '<div style="overflow: auto;">';

          while ($Qorders->next()) {
            $this->_content .= '<span style="width: 33%; float: left; text-align: center;">';

            if (osc_empty($Qorders->value('image')) === false) {
              $this->_content .= osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qorders->value('products_id')), $osC_Image->show($Qorders->value('image'), $Qorders->value('products_name'))) . '<br />';
            }

            $this->_content .= osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qorders->value('products_id')), $Qorders->value('products_name')) .
                               '</span>';
          }

          $this->_content .= '</div>';
        }

        $Qorders->freeResult();
      }
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Minimum Entries To Display', 'MODULE_CONTENT_ALSO_PURCHASED_MIN_DISPLAY', '1', 'Minimum number of also purchased products to display', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Maximum Entries To Display', 'MODULE_CONTENT_ALSO_PURCHASED_MAX_DISPLAY', '6', 'Maximum number of also purchased products to display', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Cache Contents', 'MODULE_CONTENT_ALSO_PURCHASED_PRODUCTS_CACHE', '60', 'Number of minutes to keep the contents cached (0 = no cache)', '6', '0', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_CONTENT_ALSO_PURCHASED_MIN_DISPLAY', 'MODULE_CONTENT_ALSO_PURCHASED_MAX_DISPLAY', 'MODULE_CONTENT_ALSO_PURCHASED_PRODUCTS_CACHE');
      }

      return $this->_keys;
    }
  }
?>
