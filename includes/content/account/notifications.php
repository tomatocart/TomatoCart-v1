<?php
/*
  $Id: notifications.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Account_Notifications extends osC_Template {

/* Private variables */

    var $_module = 'notifications',
        $_group = 'account',
        $_page_title,
        $_page_contents = 'account_notifications.php',
        $_page_image = 'table_background_account.gif';

/* Class constructor */

    function osC_Account_Notifications() {
      global $osC_Language, $osC_Services, $breadcrumb, $osC_Database, $osC_Customer, $Qglobal;

      $this->_page_title = $osC_Language->get('notifications_heading');

      if ($osC_Services->isStarted('breadcrumb')) {
        $breadcrumb->add($osC_Language->get('breadcrumb_notifications'), osc_href_link(FILENAME_ACCOUNT, $this->_module, 'SSL'));
      }

/////////////////////// HPDL /////// Should be moved to the customers class!
      $Qglobal = $osC_Database->query('select global_product_notifications from :table_customers where customers_id = :customers_id');
      $Qglobal->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qglobal->bindInt(':customers_id', $osC_Customer->getID());
      $Qglobal->execute();

      if ($_GET[$this->_module] == 'save') {
        $this->_process();
      }
    }

/* Public methods */

    function &getListing() {
      global $osC_Database, $osC_Session, $osC_Customer, $osC_Language;

      $Qproducts = $osC_Database->query('select pd.products_id, pd.products_name from :table_products_description pd, :table_products_notifications pn where pn.customers_id = :customers_id and pn.products_id = pd.products_id and pd.language_id = :language_id order by pd.products_name');
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindTable(':table_products_notifications', TABLE_PRODUCTS_NOTIFICATIONS);
      $Qproducts->bindInt(':customers_id', $osC_Customer->getID());
      $Qproducts->bindInt(':language_id', $osC_Language->getID());
      $Qproducts->execute();

      return $Qproducts;
    }

    function hasCustomerProductNotifications($id) {
      global $osC_Database;

      $Qcheck = $osC_Database->query('select count(*) as total from :table_products_notifications where customers_id = :customers_id');
      $Qcheck->bindTable(':table_products_notifications', TABLE_PRODUCTS_NOTIFICATIONS);
      $Qcheck->bindInt(':customers_id', $id);
      $Qcheck->execute();

      return ($Qcheck->valueInt('total') > 0);
    }

/* Private methods */

    function _process() {
      global $messageStack, $osC_Database, $osC_Language, $osC_Customer, $Qglobal;

      $updated = false;

      if (isset($_POST['product_global']) && is_numeric($_POST['product_global'])) {
        $product_global = $_POST['product_global'];
      } else {
        $product_global = '0';
      }

      if (isset($_POST['products'])) {
        (array)$products = $_POST['products'];
      } else {
        $products = array();
      }

      if ($product_global != $Qglobal->valueInt('global_product_notifications')) {
        $product_global = (($Qglobal->valueInt('global_product_notifications') == '1') ? '0' : '1');

        $Qupdate = $osC_Database->query('update :table_customers set global_product_notifications = :global_product_notifications where customers_id = :customers_id');
        $Qupdate->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qupdate->bindInt(':global_product_notifications', $product_global);
        $Qupdate->bindInt(':customers_id', $osC_Customer->getID());
        $Qupdate->execute();

        if ($Qupdate->affectedRows() == 1) {
          $updated = true;
        }
      } elseif (sizeof($products) > 0) {
        $products_parsed = array_filter($products, 'is_numeric');

        if (sizeof($products_parsed) > 0) {
          $Qcheck = $osC_Database->query('select count(*) as total from :table_products_notifications where customers_id = :customers_id and products_id not in :products_id');
          $Qcheck->bindTable(':table_products_notifications', TABLE_PRODUCTS_NOTIFICATIONS);
          $Qcheck->bindInt(':customers_id', $osC_Customer->getID());
          $Qcheck->bindRaw(':products_id', '(' . implode(',', $products_parsed) . ')');
          $Qcheck->execute();

          if ($Qcheck->valueInt('total') > 0) {
            $Qdelete = $osC_Database->query('delete from :table_products_notifications where customers_id = :customers_id and products_id not in :products_id');
            $Qdelete->bindTable(':table_products_notifications', TABLE_PRODUCTS_NOTIFICATIONS);
            $Qdelete->bindInt(':customers_id', $osC_Customer->getID());
            $Qdelete->bindRaw(':products_id', '(' . implode(',', $products_parsed) . ')');
            $Qdelete->execute();

            if ($Qdelete->affectedRows() > 0) {
              $updated = true;
            }
          }
        }
      } else {
        $Qcheck = $osC_Database->query('select count(*) as total from :table_products_notifications where customers_id = :customers_id');
        $Qcheck->bindTable(':table_products_notifications', TABLE_PRODUCTS_NOTIFICATIONS);
        $Qcheck->bindInt(':customers_id', $osC_Customer->getID());
        $Qcheck->execute();

        if ($Qcheck->valueInt('total') > 0) {
          $Qdelete = $osC_Database->query('delete from :table_products_notifications where customers_id = :customers_id');
          $Qdelete->bindTable(':table_products_notifications', TABLE_PRODUCTS_NOTIFICATIONS);
          $Qdelete->bindInt(':customers_id', $osC_Customer->getID());
          $Qdelete->execute();

          if ($Qdelete->affectedRows() > 0) {
            $updated = true;
          }
        }
      }

      if ($updated === true) {
        $messageStack->add_session('account', $osC_Language->get('success_notifications_updated'), 'success');
      }

      osc_redirect(osc_href_link(FILENAME_ACCOUNT, null, 'SSL'));
    }
  }
?>
