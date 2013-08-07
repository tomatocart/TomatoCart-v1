<?php
/*
  $Id: product_notifications.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Boxes_product_notifications extends osC_Modules {
    var $_title,
        $_code = 'product_notifications',
        $_author_name = 'osCommerce',
        $_author_www = 'http://www.oscommerce.com',
        $_group = 'boxes';

    function osC_Boxes_product_notifications() {
      global $osC_Language;

      $this->_title = $osC_Language->get('box_product_notifications_heading');
    }

    function initialize() {
      global $osC_Database, $osC_Language, $osC_Product, $osC_Customer;

      $this->_title_link = osc_href_link(FILENAME_ACCOUNT, 'notifications', 'SSL');

      if (isset($osC_Product) && is_a($osC_Product, 'osC_Product')) {
        if ($osC_Customer->isLoggedOn()) {
          $Qcheck = $osC_Database->query('select global_product_notifications from :table_customers where customers_id = :customers_id');
          $Qcheck->bindTable(':table_customers', TABLE_CUSTOMERS);
          $Qcheck->bindInt(':customers_id', $osC_Customer->getID());
          $Qcheck->execute();

          if ($Qcheck->valueInt('global_product_notifications') === 0) {
            $Qcheck = $osC_Database->query('select products_id from :table_products_notifications where products_id = :products_id and customers_id = :customers_id limit 1');
            $Qcheck->bindTable(':table_products_notifications', TABLE_PRODUCTS_NOTIFICATIONS);
            $Qcheck->bindInt(':products_id', $osC_Product->getID());
            $Qcheck->bindInt(':customers_id', $osC_Customer->getID());
            $Qcheck->execute();

            if ($Qcheck->numberOfRows() > 0) {
              $this->_content = '<div style="float: left; width: 55px;">' . osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), osc_get_all_get_params(array('action')) . '&action=notify_remove', 'AUTO'), osc_image(DIR_WS_IMAGES . 'box_products_notifications_remove.gif', sprintf($osC_Language->get('box_product_notifications_remove'), $osC_Product->getTitle()))) . '</div>' .
                                osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), osc_get_all_get_params(array('action')) . '&action=notify_remove', 'AUTO'), sprintf($osC_Language->get('box_product_notifications_remove'), $osC_Product->getTitle()));
            } else {
              $this->_content = '<div style="float: left; width: 55px;">' . osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), osc_get_all_get_params(array('action')) . '&action=notify_add', 'AUTO'), osc_image(DIR_WS_IMAGES . 'box_products_notifications.gif', sprintf($osC_Language->get('box_product_notifications_add'), $osC_Product->getTitle()))) . '</div>' .
                                osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), osc_get_all_get_params(array('action')) . '&action=notify_add', 'AUTO'), sprintf($osC_Language->get('box_product_notifications_add'), $osC_Product->getTitle()));
            }

            $this->_content .= '<div style="clear: both;"></div>';
          }
        } else {
              $this->_content = '<div style="float: left; width: 55px;">' . osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), osc_get_all_get_params(array('action')) . '&action=notify_add', 'AUTO'), osc_image(DIR_WS_IMAGES . 'box_products_notifications.gif', sprintf($osC_Language->get('box_product_notifications_add'), $osC_Product->getTitle()))) . '</div>' .
                                osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), osc_get_all_get_params(array('action')) . '&action=notify_add', 'AUTO'), sprintf($osC_Language->get('box_product_notifications_add'), $osC_Product->getTitle())) . 
                                '<div style="clear: both;"></div>';
        }
      }
    }
  }
?>
