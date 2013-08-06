<?php
/*
  $Id: customers_groups.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Customers_Groups_Admin {

    function getData($groups_id) {
      global $osC_Database, $osC_Language;

      $Qgroups = $osC_Database->query('select cg.*, cgd.* from :table_customers_groups cg, :table_customers_groups_description cgd where cg.customers_groups_id = cgd.customers_groups_id and cg.customers_groups_id = :customers_groups_id and cgd.language_id = :language_id');
      $Qgroups->bindTable(':table_customers_groups', TABLE_CUSTOMERS_GROUPS);
      $Qgroups->bindTable(':table_customers_groups_description', TABLE_CUSTOMERS_GROUPS_DESCRIPTION);
      $Qgroups->bindInt(':customers_groups_id', $groups_id);
      $Qgroups->bindInt(':language_id', $osC_Language->getID());
      $Qgroups->execute();

      $data = $Qgroups->toArray();

      $Qgroups->freeResult();

      return $data;
    }

    function save($id = null, $data) {
      global $osC_Database, $osC_Language;

      $error = false;

      $osC_Database->startTransaction();

      if ( is_numeric($id) ) {
        $Qgroups = $osC_Database->query('update :table_customers_groups set customers_groups_discount = :customers_groups_discount, is_default = :is_default where customers_groups_id = :customers_groups_id');
        $Qgroups->bindInt(':customers_groups_id', $id);
      }else {
        $Qgroups = $osC_Database->query('insert into :table_customers_groups (customers_groups_discount) values (:customers_groups_discount)');
      }
      $Qgroups->bindTable(':table_customers_groups', TABLE_CUSTOMERS_GROUPS);
      $Qgroups->bindValue(':customers_groups_discount', $data['customers_groups_discount']);
      $Qgroups->bindValue(':is_default', $data['is_default']);
      $Qgroups->setLogging($_SESSION['module'], $id);
      $Qgroups->execute();

      if ($osC_Database->isError()) {
        $error = true;
      } else {
        if ( is_numeric($id) ) {
          $group_id = $id;
        } else {
          $group_id = $osC_Database->nextID();
        }
      }

      if ( $error === false ) {
        foreach ($osC_Language->getAll() as $l) {
          if ( is_numeric($id) ) {
            $Qdesc = $osC_Database->query('update :table_customers_groups_description set customers_groups_name = :customers_groups_name where customers_groups_id = :customers_groups_id and language_id = :language_id');
          } else {
            $Qdesc = $osC_Database->query('insert into :table_customers_groups_description (customers_groups_id, language_id, customers_groups_name) values (:customers_groups_id, :language_id, :customers_groups_name)');
          }
          $Qdesc->bindTable(':table_customers_groups_description',  TABLE_CUSTOMERS_GROUPS_DESCRIPTION);
          $Qdesc->bindInt(':customers_groups_id', $group_id);
          $Qdesc->bindInt(':language_id', $l['id']);
          $Qdesc->bindValue(':customers_groups_name', $data['customers_groups_name'][$l['id']]);
          $Qdesc->setLogging($_SESSION['module'], $id);
          $Qdesc->execute();

          if ( $osC_Database->isError() ) {
            $error = true;
            break;
          }
        }
      }

      if ($error === false) {
        if ($data['is_default'] == 1) {
          $Qclear = $osC_Database->query('update :table_customers_groups set is_default = 0');
          $Qclear->bindTable(':table_customers_groups', TABLE_CUSTOMERS_GROUPS);
          $Qclear->setLogging($_SESSION['module'], $group_id);
          $Qclear->execute();

          if (!$osC_Database->isError()) {
            $Qupdate = $osC_Database->query('update :table_customers_groups set is_default = 1 where customers_groups_id = :customers_groups_id');
            $Qupdate->bindTable(':table_customers_groups', TABLE_CUSTOMERS_GROUPS);
            $Qupdate->bindInt(':customers_groups_id', $group_id);
            $Qupdate->setLogging($_SESSION['module'], $group_id);
            $Qupdate->execute();

            if ($osC_Database->isError()) {
              $error = true;
            }
          }
        }
      }

      if ($error === false) {
        $osC_Database->commitTransaction();
        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
   }

    function delete($id) {
      global $osC_Database;

      $error = false;

      $osC_Database->startTransaction();

      $Qgroups = $osC_Database->query('delete from :table_customers_groups where customers_groups_id = :customers_groups_id');
      $Qgroups->bindTable(':table_customers_groups', TABLE_CUSTOMERS_GROUPS);
      $Qgroups->bindInt(':customers_groups_id', $id);
      $Qgroups->setLogging($_SESSION['module'], $id);
      $Qgroups->execute();

      if (!$osC_Database->isError()) {
        $Qdesc = $osC_Database->query('delete from :table_customers_groups_description where customers_groups_id = :customers_groups_id');
        $Qdesc->bindTable(':table_customers_groups_description',  TABLE_CUSTOMERS_GROUPS_DESCRIPTION);
        $Qdesc->bindInt(':customers_groups_id', $id);
        $Qdesc->setLogging($_SESSION['module'], $id);
        $Qdesc->execute();

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
  }
?>
