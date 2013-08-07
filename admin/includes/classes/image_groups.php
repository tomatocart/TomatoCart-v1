<?php
/*
  $Id: image_groups.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_ImageGroups_Admin {
    function getData($id) {
      global $osC_Database, $osC_Language;

      $Qgroup = $osC_Database->query('select * from :table_products_images_groups where id = :id and language_id = :language_id');
      $Qgroup->bindTable(':table_products_images_groups', TABLE_PRODUCTS_IMAGES_GROUPS);
      $Qgroup->bindInt(':id', $id);
      $Qgroup->bindInt(':language_id', $osC_Language->getID());
      $Qgroup->execute();

      $data = $Qgroup->toArray();

      $Qgroup->freeResult();

      return $data;
    }

    function save($id = null, $data, $default = false) {
      global $osC_Database, $osC_Language;

      if ( is_numeric($id) ) {
        $group_id = $id;
      } else {
        $Qgroup = $osC_Database->query('select max(id) as id from :table_products_images_groups');
        $Qgroup->bindTable(':table_products_images_groups', TABLE_PRODUCTS_IMAGES_GROUPS);
        $Qgroup->execute();

        $group_id = $Qgroup->valueInt('id') + 1;
      }

      $error = false;

      $osC_Database->startTransaction();

      foreach ( $osC_Language->getAll() as $l ) {
        if ( is_numeric($id) ) {
          $Qgroup = $osC_Database->query('update :table_products_images_groups set title = :title, code = :code, size_width = :size_width, size_height = :size_height, force_size = :force_size where id = :id and language_id = :language_id');
        } else {
          $Qgroup = $osC_Database->query('insert into :table_products_images_groups (id, language_id, title, code, size_width, size_height, force_size) values (:id, :language_id, :title, :code, :size_width, :size_height, :force_size)');
        }

        $Qgroup->bindTable(':table_products_images_groups', TABLE_PRODUCTS_IMAGES_GROUPS);
        $Qgroup->bindInt(':id', $group_id);
        $Qgroup->bindValue(':title', $data['title'][$l['id']]);
        $Qgroup->bindValue(':code', $data['code']);
        $Qgroup->bindInt(':size_width', $data['width']);
        $Qgroup->bindInt(':size_height', $data['height']);
        $Qgroup->bindInt(':force_size', ( $data['force_size'] === true ) ? 1 : 0);
        $Qgroup->bindInt(':language_id', $l['id']);
        $Qgroup->setLogging($_SESSION['module'], $group_id);
        $Qgroup->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
          break;
        }
      }

      if ( $error === false ) {
        if ( $default === true ) {
          $Qupdate = $osC_Database->query('update :table_configuration set configuration_value = :configuration_value where configuration_key = :configuration_key');
          $Qupdate->bindTable(':table_configuration', TABLE_CONFIGURATION);
          $Qupdate->bindInt(':configuration_value', $group_id);
          $Qupdate->bindValue(':configuration_key', 'DEFAULT_IMAGE_GROUP_ID');
          $Qupdate->setLogging($_SESSION['module'], $group_id);
          $Qupdate->execute();

          if ( $osC_Database->isError() ) {
            $error = true;
          }
        }
      }

      if ( $error === false ) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('images_groups');

        if ( $default === true ) {
          osC_Cache::clear('configuration');
        }

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }

    function delete($id) {
      global $osC_Database;

      $Qdel = $osC_Database->query('delete from :table_products_images_groups where id = :id');
      $Qdel->bindTable(':table_products_images_groups', TABLE_PRODUCTS_IMAGES_GROUPS);
      $Qdel->bindInt(':id', $id);
      $Qdel->setLogging($_SESSION['module'], $id);
      $Qdel->execute();

      if ( !$osC_Database->isError() ) {
        return true;
      }

      return false;
    }
  }
?>
