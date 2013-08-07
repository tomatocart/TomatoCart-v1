<?php
/*
  $Id: administrators_log.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_AdministratorsLog {
    function getData($id) {
      global $osC_Database;

      $Qlog = $osC_Database->query('select al.id, al.module, al.module_action, al.module_id, al.action, a.user_name, unix_timestamp(al.datestamp) as datestamp from :table_administrators_log al, :table_administrators a where al.id = :id and al.administrators_id = a.id limit 1');
      $Qlog->bindTable(':table_administrators_log', TABLE_ADMINISTRATORS_LOG);
      $Qlog->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
      $Qlog->bindInt(':id', $id);
      $Qlog->execute();

      $data = $Qlog->toArray();

      $Qlog->freeResult();

      return $data;
    }

    function insert($module, $module_action, $module_id, $action, $log, $transaction_id) {
      global $osC_Database;

      if ( is_numeric($transaction_id) ) {
        $log_id = $transaction_id;
      } else {
        $Qlog = $osC_Database->query('select max(id) as id from :table_administrators_log');
        $Qlog->bindTable(':table_administrators_log', TABLE_ADMINISTRATORS_LOG);
        $Qlog->execute();

        $log_id = $Qlog->valueInt('id') + 1;

        if ( $transaction_id === true ) {
          $osC_Database->logging_transaction = $log_id;
        }
      }

      foreach ( $log as $entry ) {
        $Qlog = $osC_Database->query('insert into :table_administrators_log (id, module, module_action, module_id, field_key, old_value, new_value, action, administrators_id, datestamp) values (:id, :module, :module_action, :module_id, :field_key, :old_value, :new_value, :action, :administrators_id, now())');
        $Qlog->bindTable(':table_administrators_log', TABLE_ADMINISTRATORS_LOG);
        $Qlog->bindInt(':id', $log_id);
        $Qlog->bindValue(':module', $module);
        $Qlog->bindValue(':module_action', $module_action);
        $Qlog->bindInt(':module_id', $module_id);
        $Qlog->bindValue(':field_key', $entry['key']);
        $Qlog->bindValue(':old_value', $entry['old']);
        $Qlog->bindValue(':new_value', $entry['new']);
        $Qlog->bindValue(':action', $action);
        $Qlog->bindInt(':administrators_id', $_SESSION['admin']['id']);
        $Qlog->execute();
      }
    }

    function delete($id) {
      global $osC_Database;

      $Qlog = $osC_Database->query('delete from :table_administrators_log where id = :id');
      $Qlog->bindTable(':table_administrators_log', TABLE_ADMINISTRATORS_LOG);
      $Qlog->bindInt(':id', $id);
      $Qlog->execute();

      if ( !$osC_Database->isError() ) {
        return true;
      }

      return false;
    }
  }
?>
