<?php
/*
  $Id: product_variants.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_ProductVariants_Admin {
    function getData($id, $language_id = null, $key = null) {
      global $osC_Database, $osC_Language;

      if ( empty($language_id) ) {
        $language_id = $osC_Language->getID();
      }

      $Qgroup = $osC_Database->query('select * from :table_products_variants_groups where products_variants_groups_id = :products_variants_groups_id and language_id = :language_id');
      $Qgroup->bindTable(':table_products_variants_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
      $Qgroup->bindInt(':products_variants_groups_id', $id);
      $Qgroup->bindInt(':language_id', $language_id);
      $Qgroup->execute();

      $data = $Qgroup->toArray();

      $Qentries = $osC_Database->query('select count(*) as total_entries from :table_products_variants_values_to_products_variants_groups where products_variants_groups_id = :products_variants_groups_id');
      $Qentries->bindTable(':table_products_variants_values_to_products_variants_groups', TABLE_PRODUCTS_VARIANTS_VALUES_TO_PRODUCTS_VARIANTS_GROUPS);
      $Qentries->bindInt(':products_variants_groups_id', $id);
      $Qentries->execute();

      $data['total_entries'] = $Qentries->valueInt('total_entries');

      $Qproducts = $osC_Database->query('select count(*) as total_products from :table_products_variants_entries where products_variants_groups_id = :products_variants_groups_id');
      $Qproducts->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
      $Qproducts->bindInt(':products_variants_groups_id', $id);
      $Qproducts->execute();

      $data['total_products'] = $Qproducts->valueInt('total_products');

      $Qgroup->freeResult();
      $Qentries->freeResult();
      $Qproducts->freeResult();

      if ( empty($key) ) {
        return $data;
      } else {
        return $data[$key];
      }
    }

    function save($id = null, $data) {
      global $osC_Database, $osC_Language;

      $error = false;

      if ( is_numeric($id) ) {
        $group_id = $id;
      } else {
        $Qcheck = $osC_Database->query('select max(products_variants_groups_id) as products_variants_groups_id from :table_products_variants_entries_groups');
        $Qcheck->bindTable(':table_products_variants_entries_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
        $Qcheck->execute();

        $group_id = $Qcheck->valueInt('products_variants_groups_id') + 1;
      }

      $osC_Database->startTransaction();

      foreach ( $osC_Language->getAll() as $l ) {
        if ( is_numeric($id) ) {
          $Qgroup = $osC_Database->query('update :table_products_variants_groups set products_variants_groups_name = :products_variants_groups_name, sort_order = :sort_order where products_variants_groups_id = :products_variants_groups_id and language_id = :language_id');
        } else {
          $Qgroup = $osC_Database->query('insert into :table_products_variants_groups (products_variants_groups_id, language_id, products_variants_groups_name, sort_order) values (:products_variants_groups_id, :language_id, :products_variants_groups_name, :sort_order)');
        }

        $Qgroup->bindTable(':table_products_variants_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
        $Qgroup->bindInt(':products_variants_groups_id', $group_id);
        $Qgroup->bindValue(':products_variants_groups_name', $data['name'][$l['id']]);
        $Qgroup->bindInt(':language_id', $l['id']);
        $Qgroup->bindInt(':sort_order', $data['sort_order']);
        $Qgroup->setLogging($_SESSION['module'], $group_id);
        $Qgroup->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
          break;
        }
      }

      if ( $error === false ) {
        $osC_Database->commitTransaction();
        
        osC_Cache::clear('product');

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }

    function delete($id) {
      global $osC_Database;

      $error = false;

      $osC_Database->startTransaction();

      $Qentries = $osC_Database->query('select products_variants_values_id from :table_products_variants_values_to_products_variants_groups where products_variants_groups_id = :products_variants_groups_id');
      $Qentries->bindTable(':table_products_variants_values_to_products_variants_groups', TABLE_PRODUCTS_VARIANTS_VALUES_TO_PRODUCTS_VARIANTS_GROUPS);
      $Qentries->bindInt(':products_variants_groups_id', $id);
      $Qentries->execute();

      while ( $Qentries->next() ) {
        $Qdelete = $osC_Database->query('delete from :table_products_variants_values where products_variants_values_id = :products_variants_values_id');
        $Qdelete->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
        $Qdelete->bindInt(':products_variants_values_id', $Qentries->valueInt('products_variants_values_id'));
        $Qdelete->setLogging($_SESSION['module'], $id);
        $Qdelete->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
          break;
        }
      }

      if ( $error === false ) {
        $Qdelete = $osC_Database->query('delete from :table_products_variants_values_to_products_variants_groups where products_variants_groups_id = :products_variants_groups_id');
        $Qdelete->bindTable(':table_products_variants_values_to_products_variants_groups', TABLE_PRODUCTS_VARIANTS_VALUES_TO_PRODUCTS_VARIANTS_GROUPS);
        $Qdelete->bindInt(':products_variants_groups_id', $id);
        $Qdelete->setLogging($_SESSION['module'], $id);
        $Qdelete->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
        }
      }      
      
      if ( $error === false ) {
        $Qdelete = $osC_Database->query('delete from :table_products_variants_groups where products_variants_groups_id = :products_variants_groups_id');
        $Qdelete->bindTable(':table_products_variants_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
        $Qdelete->bindInt(':products_variants_groups_id', $id);
        $Qdelete->setLogging($_SESSION['module'], $id);
        $Qdelete->execute();

        if ( $osC_Database->isError() ) {
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

    function getEntryData($id, $language_id = null) {
      global $osC_Database, $osC_Language;

      if ( empty($language_id) ) {
        $language_id = $osC_Language->getID();
      }

      $Qentry = $osC_Database->query('select * from :table_products_variants_values where products_variants_values_id = :products_variants_values_id and language_id = :language_id');
      $Qentry->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
      $Qentry->bindInt(':products_variants_values_id', $id);
      $Qentry->bindInt(':language_id', $language_id);
      $Qentry->execute();

      $data = $Qentry->toArray();

      $Qproducts = $osC_Database->query('select count(*) as total_products from :table_products_variants_entries where products_variants_values_id = :products_variants_values_id');
      $Qproducts->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
      $Qproducts->bindInt(':products_variants_values_id', $Qentry->valueInt('products_variants_values_id'));
      $Qproducts->execute();

      $data['total_products'] = $Qproducts->valueInt('total_products');

      $Qproducts->freeResult();
      $Qentry->freeResult();

      return $data;
    }

    function saveEntry($id = null, $data) {
      global $osC_Database, $osC_Language;

      $error = false;

      if ( is_numeric($id) ) {
        $entry_id = $id;
      } else {
        $Qcheck = $osC_Database->query('select max(products_variants_values_id) as products_variants_values_id from :table_products_variants_values');
        $Qcheck->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
        $Qcheck->execute();

        $entry_id = $Qcheck->valueInt('products_variants_values_id') + 1;
      }

      $osC_Database->startTransaction();

      foreach ( $osC_Language->getAll() as $l ) {
        if ( is_numeric($id) ) {
          $Qentry = $osC_Database->query('update :table_products_variants_values set products_variants_values_name = :products_variants_values_name, sort_order = :sort_order where products_variants_values_id = :products_variants_values_id and language_id = :language_id');
        } else {
          $Qentry = $osC_Database->query('insert into :table_products_variants_values (products_variants_values_id, language_id, products_variants_values_name, sort_order) values (:products_variants_values_id, :language_id, :products_variants_values_name, :sort_order)');
        }

        $Qentry->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
        $Qentry->bindInt(':products_variants_values_id', $entry_id);
        $Qentry->bindValue(':products_variants_values_name', $data['name'][$l['id']]);
        $Qentry->bindInt(':language_id', $l['id']);
        $Qentry->bindInt(':sort_order', $data['sort_order']);
        $Qentry->setLogging($_SESSION['module'], $entry_id);
        $Qentry->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
          break;
        }
      }

      if ( $error === false ) {
        if ( !is_numeric($id) ) {
          $Qlink = $osC_Database->query('insert into :table_products_variants_values_to_products_variants_groups (products_variants_groups_id, products_variants_values_id) values (:products_variants_groups_id, :products_variants_values_id)');
          $Qlink->bindTable(':table_products_variants_values_to_products_variants_groups', TABLE_PRODUCTS_VARIANTS_VALUES_TO_PRODUCTS_VARIANTS_GROUPS);
          $Qlink->bindInt(':products_variants_groups_id', $data['products_variants_groups_id']);
          $Qlink->bindInt(':products_variants_values_id', $entry_id);
          $Qlink->setLogging($_SESSION['module'], $entry_id);
          $Qlink->execute();

          if ( $osC_Database->isError() ) {
            $error = true;
          }
        }
      }

      if ( $error === false ) {
        $osC_Database->commitTransaction();
        
        osC_Cache::clear('product');

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }

    function deleteEntry($id, $group_id) {
      global $osC_Database;

      $error = false;

      $osC_Database->startTransaction();

      $Qentry = $osC_Database->query('delete from :table_products_variants_values where products_variants_values_id = :products_variants_values_id');
      $Qentry->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
      $Qentry->bindInt(':products_variants_values_id', $id);
      $Qentry->setLogging($_SESSION['module'], $id);
      $Qentry->execute();

      if ( $osC_Database->isError() ) {
        $error = true;
      }

      if ( $error === false ) {
        $Qlink = $osC_Database->query('delete from :table_products_variants_values_to_products_variants_groups where products_variants_groups_id = :products_variants_groups_id and products_variants_values_id = :products_variants_values_id');
        $Qlink->bindTable(':table_products_variants_values_to_products_variants_groups', TABLE_PRODUCTS_VARIANTS_VALUES_TO_PRODUCTS_VARIANTS_GROUPS);
        $Qlink->bindInt(':products_variants_groups_id', $group_id);
        $Qlink->bindInt(':products_variants_values_id', $id);
        $Qlink->setLogging($_SESSION['module'], $id);
        $Qlink->execute();

        if ( $osC_Database->isError() ) {
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
  }
?>
