<?php
/*
  $Id: newsletters.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Newsletters_Admin {
    function getData($id) {
      global $osC_Database;

      $Qnewsletter = $osC_Database->query('select * from :table_newsletters where newsletters_id = :newsletters_id');
      $Qnewsletter->bindTable(':table_newsletters', TABLE_NEWSLETTERS);
      $Qnewsletter->bindInt(':newsletters_id', $id);
      $Qnewsletter->execute();

      $data = $Qnewsletter->toArray();

      $Qnewsletter->freeResult();

      return $data;
    }

    function save($id = null, $data) {
      global $osC_Database;

      if ( is_numeric($id) ) {
        $Qemail = $osC_Database->query('update :table_newsletters set title = :title, content = :content, module = :module where newsletters_id = :newsletters_id');
        $Qemail->bindInt(':newsletters_id', $id);
      } else {
        $Qemail = $osC_Database->query('insert into :table_newsletters (title, content, module, date_added, status) values (:title, :content, :module, now(), 0)');
      }

      $Qemail->bindTable(':table_newsletters', TABLE_NEWSLETTERS);
      $Qemail->bindValue(':title', $data['title']);
      $Qemail->bindValue(':content', $data['content']);
      $Qemail->bindValue(':module', $data['module']);
      $Qemail->setLogging($_SESSION['module'], $id);
      $Qemail->execute();

      if ( !$osC_Database->isError() ) {
        return true;
      }

      return false;
    }

    function delete($id) {
      global $osC_Database;

      $error = false;

      $osC_Database->startTransaction();

      $Qdelete = $osC_Database->query('delete from :table_newsletters_log where newsletters_id = :newsletters_id');
      $Qdelete->bindTable(':table_newsletters_log', TABLE_NEWSLETTERS_LOG);
      $Qdelete->bindInt(':newsletters_id', $id);
      $Qdelete->execute();

      if ( $osC_Database->isError() ) {
        $error = true;
      }

      if ( $error === false ) {
        $Qdelete = $osC_Database->query('delete from :table_newsletters where newsletters_id = :newsletters_id');
        $Qdelete->bindTable(':table_newsletters', TABLE_NEWSLETTERS);
        $Qdelete->bindInt(':newsletters_id', $id);
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

    function getTestEmailsData() {
      global $osC_Database;

      $Qcfg = $osC_Database->query('select * from :table_configuration where configuration_key = :configuration_key');
      $Qcfg->bindTable(':table_configuration', TABLE_CONFIGURATION);
      $Qcfg->bindValue(':configuration_key', 'TEST_EMAILS');
      $Qcfg->execute();

      $result = $Qcfg->toArray();

      $Qcfg->freeResult();

      return $result;
    }
  }
?>
