<?php
/*
  $Id: email_templates.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/


  class toC_Email_Templates_Admin {

    function getData($id) {
      global $osC_Database, $osC_Language;

      $Qtemplate = $osC_Database->query('select * from :table_email_templates e, :table_email_templates_description ed where e.email_templates_id = ed.email_templates_id and e.email_templates_id = :email_templates_id');

      $Qtemplate->bindTable(':table_email_templates', TABLE_EMAIL_TEMPLATES);
      $Qtemplate->bindTable(':table_email_templates_description', TABLE_EMAIL_TEMPLATES_DESCRIPTION);
      $Qtemplate->bindInt(':email_templates_id', $id);
      $Qtemplate->execute();

      $data = $Qtemplate->toArray();

      $Qtemplate->freeResult();

      return $data;
    }

    function setStatus($id, $flag) {
      global $osC_Database;

      $Qtemplate = $osC_Database->query('update :table_email_templates set email_templates_status= :email_templates_status where email_templates_id = :email_templates_id');
      $Qtemplate->bindTable(':table_email_templates', TABLE_EMAIL_TEMPLATES);
      $Qtemplate->bindInt(':email_templates_status', $flag);
      $Qtemplate->bindInt(':email_templates_id', $id);
      $Qtemplate->setLogging($_SESSION['module'], $id);
      $Qtemplate->execute();

      return true;
    }

    function save($id = null, $data) {
      global $osC_Database, $osC_Language;

      $error = false;

      $osC_Database->startTransaction();

      $Qtemplate = $osC_Database->query('update :table_email_templates set email_templates_status = :email_templates_status where email_templates_id = :email_templates_id');
      $Qtemplate->bindTable(':table_email_templates', TABLE_EMAIL_TEMPLATES);
      $Qtemplate->bindInt(':email_templates_id', $id);
      $Qtemplate->bindValue(':email_templates_status', $data['email_templates_status']);
      $Qtemplate->setLogging($_SESSION['module'], $id);
      $Qtemplate->execute();

      if ( !$osC_Database->isError() ) {

        foreach ($osC_Language->getAll() as $l) {
          $Qed = $osC_Database->query('update :table_email_templates_description  set email_title = :email_title , email_content = :email_content  where email_templates_id = :email_templates_id and language_id = :language_id ');
          $Qed->bindTable(':table_email_templates_description', TABLE_EMAIL_TEMPLATES_DESCRIPTION);
          $Qed->bindInt(':email_templates_id', $id);
          $Qed->bindInt(':language_id', $l['id']);
          $Qed->bindValue(':email_title', $data['email_title'][$l['id']]);
          $Qed->bindValue(':email_content', $data['email_content'][$l['id']]);
          $Qed->setLogging($_SESSION['module'], $id);
          $Qed->execute();

          if ( $osC_Database->isError() ) {
            $error = true;
            break;
          }
        }
      }

      if ( $error === false ) {
          $osC_Database->commitTransaction();

          return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }

    function getKeywords($email_templates_name) {
      include('../includes/modules/email_templates/' . $email_templates_name.'.php');

      $module = 'toC_Email_Template_' . $email_templates_name;
      $module = new $module();

      $keywords = $module->getKeywords();
      return $keywords;
    }

}
?>
