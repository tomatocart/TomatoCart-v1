<?php
/*
  $Id: weight_classes.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_WeightClasses_Admin {
    function getData($id) {
      global $osC_Database, $osC_Language;

      $Qclass = $osC_Database->query('select * from :table_weight_classes where weight_class_id = :weight_class_id and language_id = :language_id');
      $Qclass->bindTable(':table_weight_classes', TABLE_WEIGHT_CLASS);
      $Qclass->bindInt(':weight_class_id', $id);
      $Qclass->bindInt(':language_id', $osC_Language->getID());
      $Qclass->execute();

      $data = $Qclass->toArray();

      $Qclass->freeResult();

      return $data;
    }

    function save($id = null, $data, $default = false) {
      global $osC_Database, $osC_Language;

      $error = false;

      $osC_Database->startTransaction();

      if ( is_numeric($id) ) {
        $weight_class_id = $id;
      } else {
        $Qwc = $osC_Database->query('select max(weight_class_id) as weight_class_id from :table_weight_classes');
        $Qwc->bindTable(':table_weight_classes', TABLE_WEIGHT_CLASS);
        $Qwc->execute();

        $weight_class_id = $Qwc->valueInt('weight_class_id') + 1;
      }

      foreach ( $osC_Language->getAll() as $l ) {
        if ( is_numeric($id) ) {
          $Qwc = $osC_Database->query('update :table_weight_classes set weight_class_key = :weight_class_key, weight_class_title = :weight_class_title where weight_class_id = :weight_class_id and language_id = :language_id');
        } else {
          $Qwc = $osC_Database->query('insert into :table_weight_classes (weight_class_id, language_id, weight_class_key, weight_class_title) values (:weight_class_id, :language_id, :weight_class_key, :weight_class_title)');
        }

        $Qwc->bindTable(':table_weight_classes', TABLE_WEIGHT_CLASS);
        $Qwc->bindInt(':weight_class_id', $weight_class_id);
        $Qwc->bindInt(':language_id', $l['id']);
        $Qwc->bindValue(':weight_class_key', $data['key'][$l['id']]);
        $Qwc->bindValue(':weight_class_title', $data['name'][$l['id']]);
        $Qwc->setLogging($_SESSION['module'], $weight_class_id);
        $Qwc->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
          break;
        }
      }

      if ( $error === false ) {
        if ( is_numeric($id) ) {
          $Qrules = $osC_Database->query('select weight_class_to_id from :table_weight_classes_rules where weight_class_from_id = :weight_class_from_id and weight_class_to_id != :weight_class_to_id');
          $Qrules->bindTable(':table_weight_classes_rules', TABLE_WEIGHT_CLASS_RULES);
          $Qrules->bindInt(':weight_class_from_id', $weight_class_id);
          $Qrules->bindInt(':weight_class_to_id', $weight_class_id);
          $Qrules->execute();

          while ( $Qrules->next() ) {
            $Qrule = $osC_Database->query('update :table_weight_classes_rules set weight_class_rule = :weight_class_rule where weight_class_from_id = :weight_class_from_id and weight_class_to_id = :weight_class_to_id');
            $Qrule->bindTable(':table_weight_classes_rules', TABLE_WEIGHT_CLASS_RULES);
            $Qrule->bindValue(':weight_class_rule', $data['rules'][$Qrules->valueInt('weight_class_to_id')]);
            $Qrule->bindInt(':weight_class_from_id', $weight_class_id);
            $Qrule->bindInt(':weight_class_to_id', $Qrules->valueInt('weight_class_to_id'));
            $Qrule->setLogging($_SESSION['module'], $weight_class_id);
            $Qrule->execute();

            if ( $osC_Database->isError() ) {
              $error = true;
              break;
            }
          }
        } else {
          $Qclasses = $osC_Database->query('select weight_class_id from :table_weight_classes where weight_class_id != :weight_class_id and language_id = :language_id');
          $Qclasses->bindTable(':table_weight_classes', TABLE_WEIGHT_CLASS);
          $Qclasses->bindInt(':weight_class_id', $weight_class_id);
          $Qclasses->bindInt(':language_id', $osC_Language->getID());
          $Qclasses->execute();

          while ( $Qclasses->next() ) {
            $Qdefault = $osC_Database->query('insert into :table_weight_classes_rules (weight_class_from_id, weight_class_to_id, weight_class_rule) values (:weight_class_from_id, :weight_class_to_id, :weight_class_rule)');
            $Qdefault->bindTable(':table_weight_classes_rules', TABLE_WEIGHT_CLASS_RULES);
            $Qdefault->bindInt(':weight_class_from_id', $Qclasses->valueInt('weight_class_id'));
            $Qdefault->bindInt(':weight_class_to_id', $weight_class_id);
            $Qdefault->bindValue(':weight_class_rule', '1');
            $Qdefault->setLogging($_SESSION['module'], $weight_class_id);
            $Qdefault->execute();

            if ( $osC_Database->isError() ) {
              $error = true;
              break;
            }

            if ( $error === false ) {
              $Qnew = $osC_Database->query('insert into :table_weight_classes_rules (weight_class_from_id, weight_class_to_id, weight_class_rule) values (:weight_class_from_id, :weight_class_to_id, :weight_class_rule)');
              $Qnew->bindTable(':table_weight_classes_rules', TABLE_WEIGHT_CLASS_RULES);
              $Qnew->bindInt(':weight_class_from_id', $weight_class_id);
              $Qnew->bindInt(':weight_class_to_id', $Qclasses->valueInt('weight_class_id'));
              $Qnew->bindValue(':weight_class_rule', $data['rules'][$Qclasses->valueInt('weight_class_id')]);
              $Qnew->setLogging($_SESSION['module'], $weight_class_id);
              $Qnew->execute();

              if ( $osC_Database->isError() ) {
                $error = true;
                break;
              }
            }
          }
        }
      }

      if ( $error === false ) {
        if ( $default === true ) {
          $Qupdate = $osC_Database->query('update :table_configuration set configuration_value = :configuration_value where configuration_key = :configuration_key');
          $Qupdate->bindTable(':table_configuration', TABLE_CONFIGURATION);
          $Qupdate->bindInt(':configuration_value', $weight_class_id);
          $Qupdate->bindValue(':configuration_key', 'SHIPPING_WEIGHT_UNIT');
          $Qupdate->setLogging($_SESSION['module'], $weight_class_id);
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

      $osC_Database->rollbackTransaction();

      return false;
    }

    function delete($id) {
      global $osC_Database;

      $error = false;

      $osC_Database->startTransaction();

      $Qrules = $osC_Database->query('delete from :table_weight_classes_rules where weight_class_from_id = :weight_class_from_id or weight_class_to_id = :weight_class_to_id');
      $Qrules->bindTable(':table_weight_classes_rules', TABLE_WEIGHT_CLASS_RULES);
      $Qrules->bindInt(':weight_class_from_id', $id);
      $Qrules->bindInt(':weight_class_to_id', $id);
      $Qrules->setLogging($_SESSION['module'], $id);
      $Qrules->execute();

      if ( $osC_Database->isError() ) {
        $error = true;
      }

      if ( $error === false ) {
        $Qclasses = $osC_Database->query('delete from :table_weight_classes where weight_class_id = :weight_class_id');
        $Qclasses->bindTable(':table_weight_classes', TABLE_WEIGHT_CLASS);
        $Qclasses->bindInt(':weight_class_id', $id);
        $Qclasses->setLogging($_SESSION['module'], $id);
        $Qclasses->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
        }
      }

      if ( $error === false ) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('weight-classes');
        osC_Cache::clear('weight-rules');

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }
  }
?>
