<?php
/*
  $Id: notify_remove.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Actions_notify_remove {
    function execute() {
      global $osC_Database, $osC_Session, $osC_NavigationHistory, $osC_Customer;

      if (!$osC_Customer->isLoggedOn()) {
        $osC_NavigationHistory->setSnapshot();

        osc_redirect(osc_href_link(FILENAME_ACCOUNT, 'login', 'SSL'));

        return false;
      }

      $id = false;

      foreach ($_GET as $key => $value) {
        if ( (ereg('^[0-9]+(#?([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)*$', $key) || ereg('^[a-zA-Z0-9 -_]*$', $key)) && ($key != $osC_Session->getName()) ) {
          $id = $key;
        }

        break;
      }

      if (($id !== false) && osC_Product::checkEntry($id)) {
        $osC_Product = new osC_Product($id);

        $Qcheck = $osC_Database->query('select products_id from :table_products_notifications where customers_id = :customers_id and products_id = :products_id limit 1');
        $Qcheck->bindTable(':table_products_notifications', TABLE_PRODUCTS_NOTIFICATIONS);
        $Qcheck->bindInt(':customers_id', $osC_Customer->getID());
        $Qcheck->bindInt(':products_id', $osC_Product->getID());
        $Qcheck->execute();

        if ($Qcheck->numberOfRows() > 0) {
          $Qn = $osC_Database->query('delete from :table_products_notifications where customers_id = :customers_id and products_id = :products_id');
          $Qn->bindTable(':table_products_notifications', TABLE_PRODUCTS_NOTIFICATIONS);
          $Qn->bindInt(':customers_id', $osC_Customer->getID());
          $Qn->bindInt(':products_id', $osC_Product->getID());
          $Qn->execute();
        }
      }

      osc_redirect(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), osc_get_all_get_params(array('action'))));
    }
  }
?>
