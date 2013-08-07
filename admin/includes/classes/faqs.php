<?php
/*
  $Id: faqs.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/


class toC_Faqs_Admin {

  function getData($id) {
    global $osC_Database, $osC_Language;

    $Qfaqs = $osC_Database->query('select f.*, fd.* from :table_faqs f, :table_faqs_description fd where f.faqs_id = :faqs_id and f.faqs_id =fd.faqs_id and fd.language_id = :language_id');

    $Qfaqs->bindTable(':table_faqs', TABLE_FAQS);
    $Qfaqs->bindTable(':table_faqs_description', TABLE_FAQS_DESCRIPTION);
    $Qfaqs->bindInt(':faqs_id', $id);
    $Qfaqs->bindInt(':language_id', $osC_Language->getID());
    $Qfaqs->execute();

    $data = $Qfaqs->toArray();

    $Qfaqs->freeResult();

    return $data;
  }

    function setStatus($id, $flag){
    global $osC_Database;
    $Qstatus = $osC_Database->query('update :table_faqs set faqs_status= :faqs_status, faqs_last_modified = now() where faqs_id = :faqs_id');
    $Qstatus->bindInt(':faqs_status', $flag);
    $Qstatus->bindInt(':faqs_id', $id);
    $Qstatus->bindTable(':table_faqs', TABLE_FAQS);
    $Qstatus->setLogging($_SESSION['module'], $id);
    $Qstatus->execute();

    osC_Cache::clear('sefu-faqs');
    osC_Cache::clear('box-faqs');
    return true;
    }

  function save($id = null, $data) {
    global $osC_Database, $osC_Language;

    $error = false;

    $osC_Database->startTransaction();

    if ( is_numeric($id) ) {
      $Qfaqs = $osC_Database->query('update :table_faqs set faqs_status = :faqs_status, faqs_order = :faqs_order, faqs_last_modified = now() where faqs_id = :faqs_id');
      $Qfaqs->bindInt(':faqs_id', $id);
    } else {
      $Qfaqs = $osC_Database->query('insert into :table_faqs (faqs_status,faqs_order,faqs_date_added) values (:faqs_status,:faqs_order, :faqs_date_added)');
      $Qfaqs->bindRaw(':faqs_date_added', 'now()');
    }

    $Qfaqs->bindTable(':table_faqs', TABLE_FAQS);
    $Qfaqs->bindValue(':faqs_status', $data['faqs_status']);
    $Qfaqs->bindValue(':faqs_order', $data['faqs_order']);
    $Qfaqs->setLogging($_SESSION['module'], $id);
    $Qfaqs->execute();

    if ( $osC_Database->isError() ) {
      $error = true;
    } else {
      if ( is_numeric($id) ) {
        $faqs_id = $id;
      } else {
        $faqs_id = $osC_Database->nextID();
      }
    }

    //Process Languages
    //
    if ( $error === false ) {
      foreach ($osC_Language->getAll() as $l) {
        if ( is_numeric($id) ) {
          $Qfd = $osC_Database->query('update :table_faqs_description set faqs_question = :faqs_question, faqs_url = :faqs_url, faqs_answer = :faqs_answer  where faqs_id = :faqs_id and language_id = :language_id');
        } else {
          $Qfd = $osC_Database->query('insert into :table_faqs_description (faqs_id, language_id, faqs_question, faqs_url, faqs_answer) values (:faqs_id, :language_id, :faqs_question, :faqs_url, :faqs_answer)');
        }

        $Qfd->bindTable(':table_faqs_description', TABLE_FAQS_DESCRIPTION);
        $Qfd->bindInt(':faqs_id', $faqs_id);
        $Qfd->bindInt(':language_id', $l['id']);
        $Qfd->bindValue(':faqs_question', $data['faqs_question'][$l['id']]);
        $Qfd->bindValue(':faqs_url', $data['faqs_url'][$l['id']]);
        $Qfd->bindValue(':faqs_answer', $data['faqs_answer'][$l['id']]);
        $Qfd->setLogging($_SESSION['module'], $faqs_id);
        $Qfd->execute();
        
        if ( $osC_Database->isError() ) {
          $error = true;
          break;
        }
      }
    }

    if ( $error === false ) {
      $osC_Database->commitTransaction();

      osC_Cache::clear('sefu-faqs');
      osC_Cache::clear('box-faqs');
      return true;
    }
    $osC_Database->rollbackTransaction();

    return false;
  }


  function delete($id) {
    global $osC_Database;
    $error = false;

    $osC_Database->startTransaction();

    $Qfd = $osC_Database->query('delete from :table_faqs_description where faqs_id = :faqs_id');
    $Qfd->bindTable(':table_faqs_description', TABLE_FAQS_DESCRIPTION);
    $Qfd->bindInt(':faqs_id', $id);
    $Qfd->setLogging($_SESSION['module'], $id);
    $Qfd->execute();

    if ( $osC_Database->isError() ) {
      $error = true;
    }

    if ( $error === false ) {
      $Qfaqs = $osC_Database->query('delete from :table_faqs where faqs_id = :faqs_id');
      $Qfaqs->bindTable(':table_faqs', TABLE_FAQS);
      $Qfaqs->bindInt(':faqs_id', $id);
      $Qfaqs->setLogging($_SESSION['module'], $id);
      $Qfaqs->execute();

      if ( $osC_Database->isError() ) {
        $error = true;
      }

      if ( $error === false ) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('sefu-faqs');
        osC_Cache::clear('box-faqs');
        return true;
      }
    }
    $osC_Database->rollbackTransaction();
    return false;
  }
}
?>
