<?php
/*
  $Id: products_attributes.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Products_Attributes_Admin {

    function getData($id, $language_id = null, $key = null) {
      global $osC_Database, $osC_Language;

      if (empty($language_id)) {
        $language_id = $osC_Language->getID();
      }

      $Qgroup = $osC_Database->query('select * from :table_products_attributes_groups where products_attributes_groups_id = :products_attributes_groups_id ');
      $Qgroup->bindTable(':table_products_attributes_groups', TABLE_PRODUCTS_ATTRIBUTES_GROUPS);
      $Qgroup->bindInt(':products_attributes_groups_id', $id);
      $Qgroup->execute();

      $data = $Qgroup->toArray();

      $Qentries = $osC_Database->query('select count(*) as total_entries from :table_products_attributes_values where products_attributes_groups_id = :products_attributes_groups_id and language_id = :language_id ');
      $Qentries->bindTable(':table_products_attributes_values', TABLE_PRODUCTS_ATTRIBUTES_VALUES);
      $Qentries->bindInt(':products_attributes_groups_id', $id);
      $Qentries->bindInt(':language_id', $language_id);
      $Qentries->execute();

      $data['total_entries'] = $Qentries->valueInt('total_entries');

      $Qproducts = $osC_Database->query('select count(*) as total_products from :table_products where products_attributes_groups_id =: products_attributes_groups_id ');
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindInt(':products_attributes_groups_id', $id);
      $Qproducts->execute();

      $data['total_products'] = $Qproducts->valueInt('total_products');

      $Qgroup->freeResult();
      $Qentries->freeResult();
      $Qproducts->freeResult();

      if (empty($key)) {
        return $data;
      } else {
        return $data[$key];
      }
    }

    function save($id = null, $data) {
      global $osC_Database;

      if (is_numeric($id)) {
        $Qgroup = $osC_Database->query('update :table_products_attributes_groups set products_attributes_groups_name = :products_attributes_groups_name where products_attributes_groups_id = :products_attributes_groups_id ');
        $Qgroup->bindInt(':products_attributes_groups_id', $id);
      } else {
        $Qgroup = $osC_Database->query('insert into :table_products_attributes_groups (products_attributes_groups_id, products_attributes_groups_name) values ( null, :products_attributes_groups_name)');
      }

      $Qgroup->bindTable(':table_products_attributes_groups', TABLE_PRODUCTS_ATTRIBUTES_GROUPS);
      $Qgroup->bindValue(':products_attributes_groups_name', $data['name']);
      $Qgroup->setLogging($_SESSION['module'], $id);
      $Qgroup->execute();

      if ($osC_Database->isError()) {
        return false;
      }

      return true;
    }

    function delete($id) {
      global $osC_Database, $osC_Language;

      $error = false;

      $osC_Database->startTransaction();

      $Qentries = $osC_Database->query('delete from :table_products_attributes_values where products_attributes_groups_id = :products_attributes_groups_id ');
      $Qentries->bindTable(':table_products_attributes_values', TABLE_PRODUCTS_ATTRIBUTES_VALUES);
      $Qentries->bindInt(':products_attributes_groups_id', $id);
      $Qentries->setLogging($_SESSION['module'], $id);
      $Qentries->execute();

      if (!$osC_Database->isError()) {
        $Qgroup = $osC_Database->query('delete from :table_products_attributes_groups where products_attributes_groups_id = :products_attributes_groups_id');
        $Qgroup->bindTable(':table_products_attributes_groups', TABLE_PRODUCTS_ATTRIBUTES_GROUPS);
        $Qgroup->bindInt(':products_attributes_groups_id', $id);
        $Qgroup->setLogging($_SESSION['module'], $id);
        $Qgroup->execute();

        if ($osC_Database->isError()) {
          $error = true;
        }
      } else {
        $error = true;
      }

      if ($error === false) {
        $osC_Database->commitTransaction();

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }

    function getEntryData($id, $language_id = null) {
      global $osC_Database, $osC_Language;

      if (empty($language_id)) {
        $language_id = $osC_Language->getID();
      }

      $Qentry = $osC_Database->query('select * from :table_products_attributes_values where products_attributes_values_id = :products_attributes_values_id and language_id = :language_id');
      $Qentry->bindTable(':table_products_attributes_values', TABLE_PRODUCTS_ATTRIBUTES_VALUES);
      $Qentry->bindInt(':products_attributes_values_id', $id);
      $Qentry->bindInt(':language_id', $language_id);
      $Qentry->execute();

      $data = $Qentry->toArray();

      $Qproducts = $osC_Database->query('select count(*) as total_products from :table_products_attributes where products_attributes_values_id = :products_attributes_values_id');
      $Qproducts->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
      $Qproducts->bindInt(':products_attributes_values_id', $Qentry->valueInt('products_attributes_values_id'));
      $Qproducts->execute();

      $data['total_products'] = $Qproducts->valueInt('total_products');

      $Qproducts->freeResult();
      $Qentry->freeResult();

      return $data;
    }

    function saveEntry($id = null, $data) {
      global $osC_Database, $osC_Language;

      $error = false;

      $osC_Database->startTransaction();

      if (is_numeric($id)) {
        $entry_id = $id;
      } else {
        $Qcheck = $osC_Database->query('select max(products_attributes_values_id) + 1 as products_attributes_values_id from :table_products_attributes_values ');
        $Qcheck->bindTable(':table_products_attributes_values', TABLE_PRODUCTS_ATTRIBUTES_VALUES);
        $Qcheck->execute();
        
        $entry_id = ($Qcheck->valueInt('products_attributes_values_id') == 0) ? 1 : $Qcheck->valueInt('products_attributes_values_id');
      }

      foreach ( $osC_Language->getAll() as $l) {
        $value = (isset($data['value'])  && isset($data['value'][$l['id']])) ? trim(trim($data['value'][$l['id']]), ',') : '';
          
        if (is_numeric($id)) {
          $Qentry = $osC_Database->query('update :table_products_attributes_values set name = :name, module = :module, value = :value, status = :status, sort_order = :sort_order where products_attributes_values_id = :products_attributes_values_id and products_attributes_groups_id = :products_attributes_groups_id and language_id = :language_id');
        } else {
          $Qentry = $osC_Database->query('insert into :table_products_attributes_values (products_attributes_values_id, products_attributes_groups_id, language_id, name, module, value, status, sort_order) values (:products_attributes_values_id, :products_attributes_groups_id, :language_id, :name, :module, :value, :status, :sort_order)');
        }

        $Qentry->bindTable(':table_products_attributes_values', TABLE_PRODUCTS_ATTRIBUTES_VALUES);
        $Qentry->bindInt(':products_attributes_values_id', $entry_id);
        $Qentry->bindInt(':products_attributes_groups_id', $data['products_attributes_groups_id']);
        $Qentry->bindInt(':language_id', $l['id']);
        $Qentry->bindInt(':status', $data['status']);
        $Qentry->bindValue(':name', $data['name'][$l['id']]);
        $Qentry->bindValue(':module', $data['module']);
        $Qentry->bindValue(':value', $value);
        $Qentry->bindValue(':sort_order', $data['sort_order']);
        
        $Qentry->setLogging($_SESSION['module'], $entry_id);
        $Qentry->execute();

        if ($osC_Database->isError()) {
          $error = true;
          break;
        }
      }

      if ($error === false) {
        $osC_Database->commitTransaction();

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }

    function deleteEntry($id, $group_id) {
      global $osC_Database;

      $error = false;

      $osC_Database->startTransaction();

      $Qproducts = $osC_Database->query('delete from :table_products_attributes where products_attributes_values_id = :products_attributes_values_id ');
      $Qproducts->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
      $Qproducts->bindInt(':products_attributes_values_id', $id);
      $Qproducts->setLogging($_SESSION['module'], $id);
      $Qproducts->execute();

      if (!$osC_Database->isError()) {
        $Qentry = $osC_Database->query('delete from :table_products_attributes_values where products_attributes_values_id = :products_attributes_values_id and products_attributes_groups_id = :products_attributes_groups_id ');
        $Qentry->bindTable(':table_products_attributes_values', TABLE_PRODUCTS_ATTRIBUTES_VALUES);
        $Qentry->bindInt(':products_attributes_values_id', $id);
        $Qentry->bindInt(':products_attributes_groups_id', $group_id);
        $Qentry->setLogging($_SESSION['module'], $id);
        $Qentry->execute();

        if ($osC_Database->isError()) {
          $error = true;
        }
      }

      if ($error === false) {
        $osC_Database->commitTransaction();

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }
    
    function setEntryStatus($id, $flag) {
      global $osC_Database;

      $Qstatus = $osC_Database->query('update :table_products_attributes_values set status = :status where products_attributes_values_id = :products_attributes_values_id ');
      $Qstatus->bindTable(':table_products_attributes_values', TABLE_PRODUCTS_ATTRIBUTES_VALUES);
      $Qstatus->bindInt(':products_attributes_values_id', $id);
      $Qstatus->bindInt(':status', $flag);
      $Qstatus->setLogging($_SESSION['module'], $id);
      $Qstatus->execute();

      return true;
    }
  }
?>