<?php
/*
  $Id: unit_classes.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Unit_Class_Admin {

    function save($id = null, $data, $default = false) {
      global $osC_Database, $osC_Language;

      $error = false;
      
      $osC_Database->startTransaction();

      if (is_numeric($id)) {
        $unit_class_id = $id;
      } else {
        $Qunit = $osC_Database->query('select max(quantity_unit_class_id) as quantity_unit_class_id from :table_quantity_unit_classes');
        $Qunit->bindTable(':table_quantity_unit_classes', TABLE_QUANTITY_UNIT_CLASSES);
        $Qunit->execute();

        $unit_class_id = $Qunit->valueInt('quantity_unit_class_id') + 1;
      }

      foreach ( $osC_Language->getAll() as $l ) {
        if (is_numeric($id)) {
          $Qunit = $osC_Database->query('update :table_quantity_unit_classes set quantity_unit_class_title = :quantity_unit_class_title where quantity_unit_class_id = :quantity_unit_class_id and language_id = :language_id');
        } else {
          $Qunit = $osC_Database->query('insert into :table_quantity_unit_classes (quantity_unit_class_id, language_id, quantity_unit_class_title) values (:quantity_unit_class_id, :language_id, :quantity_unit_class_title)');
        }

        $Qunit->bindTable(':table_quantity_unit_classes', TABLE_QUANTITY_UNIT_CLASSES);
        $Qunit->bindInt(':quantity_unit_class_id', $unit_class_id);
        $Qunit->bindValue(':quantity_unit_class_title', $data['unit_class_title'][$l['id']]);
        $Qunit->bindInt(':language_id', $l['id']);
        $Qunit->setLogging($_SESSION['module'], $unit_class_id);
        $Qunit->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
          break;
        }
      }
      
      if ( $error === false ) {
        if ( $default === true ) {
          $Qupdate = $osC_Database->query('update :table_configuration set configuration_value = :configuration_value where configuration_key = :configuration_key');
          $Qupdate->bindTable(':table_configuration', TABLE_CONFIGURATION);
          $Qupdate->bindInt(':configuration_value', $unit_class_id);
          $Qupdate->bindValue(':configuration_key', 'DEFAULT_UNIT_CLASSES');
          $Qupdate->setLogging($_SESSION['module'], $unit_class_id);
          $Qupdate->execute();

          if ( $osC_Database->isError() ) {
            $error = true;
          }
        }
      }
      if ( $error === false ) {
        $osC_Database->commitTransaction();

        if ( $default === true ) {
          osC_Cache::clear('configuration');
        }
        return true;
      }
     
      return false;
    }

    function delete ($unit_class_id) {
      global $osC_Database;

      $Qunit = $osC_Database->query('delete from :table_quantity_unit_classes where quantity_unit_class_id = :quantity_unit_class_id');
      $Qunit->bindTable(':table_quantity_unit_classes', TABLE_QUANTITY_UNIT_CLASSES);
      $Qunit->bindInt(':quantity_unit_class_id', $unit_class_id);
      $Qunit->execute();

      if ( !$osC_Database->isError() ) {
        return true;
      }

      return false;
    }
    
  }
?>