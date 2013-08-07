<?php
/*
  $Id: quantity_discount_groups.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Quantity_Discount_Groups_Admin {

    function getData($id) {
      global $osC_Database;

      $Qgroup = $osC_Database->query('select * from :table_quantity_discount_groups where quantity_discount_groups_id = :quantity_discount_groups_id');
      $Qgroup->bindTable(':table_quantity_discount_groups', TABLE_QUANTITY_DISCOUNT_GROUPS);
      $Qgroup->bindInt(':quantity_discount_groups_id', $id);
      $Qgroup->execute();

      $data = $Qgroup->toArray();

      $Qentries = $osC_Database->query('select count(*) as total_entries from :table_quantity_discount_groups_values where quantity_discount_groups_id = :quantity_discount_groups_id');
      $Qentries->bindTable(':table_quantity_discount_groups_values', TABLE_QUANTITY_DISCOUNT_GROUPS_VALUES);
      $Qentries->bindInt(':quantity_discount_groups_id', $id);
      $Qentries->execute();

      $data['total_entries'] = $Qentries->valueInt('total_entries');

      $Qproducts = $osC_Database->query('select count(*) as total_products from :table_products where quantity_discount_groups_id = :quantity_discount_groups_id');
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindInt(':quantity_discount_groups_id', $id);
      $Qproducts->execute();

      $data['total_products'] = $Qproducts->valueInt('total_products');

      $Qproducts->freeResult();
      $Qentries->freeResult();
      $Qgroup->freeResult();

      return $data;
    }

    function save($id = null, $data) {
      global $osC_Database;

      if ( is_numeric($id) ) {
        $Qgroup = $osC_Database->query('update :table_quantity_discount_groups set quantity_discount_groups_name = :quantity_discount_groups_name where quantity_discount_groups_id = :quantity_discount_groups_id');
        $Qgroup->bindInt(':quantity_discount_groups_id', $id);
      }else {
        $Qgroup = $osC_Database->query('insert into :table_quantity_discount_groups (quantity_discount_groups_name) values (:quantity_discount_groups_name)');
      }

      $Qgroup->bindTable(':table_quantity_discount_groups', TABLE_QUANTITY_DISCOUNT_GROUPS);
      $Qgroup->bindValue(':quantity_discount_groups_name', $data['quantity_discount_groups_name']);
      $Qgroup->setLogging($_SESSION['module'], $id);
      $Qgroup->execute();

      if ( !$osC_Database->isError() ) {
        return true;
      }

      return false;
    }

    function delete($id) {
      global $osC_Database;

      $error = false;

      $osC_Database->startTransaction();

      $Qentries = $osC_Database->query('delete from :table_quantity_discount_groups_values where quantity_discount_groups_id = :quantity_discount_groups_id');
      $Qentries->bindTable(':table_quantity_discount_groups_values', TABLE_QUANTITY_DISCOUNT_GROUPS_VALUES);
      $Qentries->bindInt(':quantity_discount_groups_id', $id);
      $Qentries->setLogging($_SESSION['module'], $id);
      $Qentries->execute();

      if ($osC_Database->isError() === false) {
        $Qgroup = $osC_Database->query('delete from :table_quantity_discount_groups where quantity_discount_groups_id = :quantity_discount_groups_id');
        $Qgroup->bindTable(':table_quantity_discount_groups', TABLE_QUANTITY_DISCOUNT_GROUPS);
        $Qgroup->bindInt(':quantity_discount_groups_id', $id);
        $Qgroup->setLogging($_SESSION['module'], $id);
        $Qgroup->execute();

        if ($osC_Database->isError()) {
          $error = true;
        }
      }

      if ( $error === false ) {
        $osC_Database->commitTransaction();

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }


    function getEntryData($id, $qdgid) {
      global $osC_Database, $osC_Language;

      $Qentries = $osC_Database->query('select qdgv.quantity_discount_groups_values_id, qdg.quantity_discount_groups_name, qdgv.quantity_discount_groups_id, qdgv.customers_groups_id, qdgv.quantity, qdgv.discount, gd.customers_groups_name from :table_quantity_discount_groups_values qdgv left join :table_customers_groups_description gd on (qdgv.customers_groups_id = gd.customers_groups_id and gd.language_id = :language_id), :table_quantity_discount_groups qdg  where qdgv.quantity_discount_groups_id = :quantity_discount_groups_id and qdgv.quantity_discount_groups_values_id= :quantity_discount_groups_values_id and qdg.quantity_discount_groups_id = qdgv.quantity_discount_groups_id');
      $Qentries->bindTable(':table_quantity_discount_groups_values', TABLE_QUANTITY_DISCOUNT_GROUPS_VALUES);
      $Qentries->bindTable(':table_customers_groups_description', TABLE_CUSTOMERS_GROUPS_DESCRIPTION);
      $Qentries->bindTable(':table_quantity_discount_groups',TABLE_QUANTITY_DISCOUNT_GROUPS);
      $Qentries->bindInt(':quantity_discount_groups_id', $qdgid);
      $Qentries->bindInt(':quantity_discount_groups_values_id', $id);
      $Qentries->bindInt(':language_id', $osC_Language->getID());
      $Qentries->execute();

      $data = $Qentries->toArray();

      $Qentries->freeResult();

      return $data;
    }

    function checkEntry($data){
      global $osC_Database;

      $Qcheck = $osC_Database->query('select * from :table_quantity_discount_groups_values where quantity_discount_groups_id = :quantity_discount_groups_id and quantity = :quantity ');
      $Qcheck->bindTable(':table_quantity_discount_groups_values', TABLE_QUANTITY_DISCOUNT_GROUPS_VALUES);
      $Qcheck->bindInt(':quantity_discount_groups_id', $data['quantity_discount_groups_id']);
      $Qcheck->bindInt(':quantity', $data['quantity']);

      if ( !empty($data['customers_groups_id']) && is_numeric($data['customers_groups_id']) ) {
        $Qcheck->appendQuery(' and customers_groups_id = :customers_groups_id ');
        $Qcheck->bindInt(':customers_groups_id', $data['customers_groups_id']);
      } else {
        $Qcheck->appendQuery(' and customers_groups_id is null ');
      }

      $Qcheck->execute();

      if ($Qcheck->numberOfRows() > 0) {
        $Qcheck->freeResult();

        return false;
      }

      return true;
    }

    function saveEntry($id = null, $data) {
      global $osC_Database;

      if ( is_numeric($id)){
        $Qentry = $osC_Database->query('update :table_quantity_discount_groups_values set quantity_discount_groups_id = :quantity_discount_groups_id, customers_groups_id = :customers_groups_id, quantity = :quantity, discount = :discount where quantity_discount_groups_values_id = :quantity_discount_groups_values_id');
        $Qentry->bindInt(':quantity_discount_groups_values_id', $id);
      }else{
        $Qentry = $osC_Database->query('insert into :table_quantity_discount_groups_values (quantity_discount_groups_id,  customers_groups_id,  quantity,  discount) values (:quantity_discount_groups_id,  :customers_groups_id,  :quantity,  :discount)');
      }

      $Qentry->bindTable(':table_quantity_discount_groups_values', TABLE_QUANTITY_DISCOUNT_GROUPS_VALUES);
      $Qentry->bindInt(':quantity_discount_groups_id', $data['quantity_discount_groups_id']);
      $Qentry->bindInt(':quantity', $data['quantity']);
      $Qentry->bindInt(':discount', $data['discount']);
      $Qentry->bindInt(':customers_groups_id', $data['customers_groups_id']);
      $Qentry->setLogging($_SESSION['module'], $id);
      $Qentry->execute();

      if ( !$osC_Database->isError() ) {
        return true;
      }

      return false;
    }

    function deleteEntry($id) {
      global $osC_Database;

      $Qentry = $osC_Database->query('delete from :table_quantity_discount_groups_values where quantity_discount_groups_values_id = :quantity_discount_groups_values_id');
      $Qentry->bindTable(':table_quantity_discount_groups_values', TABLE_QUANTITY_DISCOUNT_GROUPS_VALUES);
      $Qentry->bindInt(':quantity_discount_groups_values_id', $id);
      $Qentry->setLogging($_SESSION['module'], $id);
      $Qentry->execute();

      if ( !$osC_Database->isError() ) {
        return true;
      }

      return false;
    }
  }
?>
