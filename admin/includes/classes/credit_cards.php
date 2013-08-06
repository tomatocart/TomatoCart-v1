<?php
/*
  $Id: credit_cards.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_CreditCards_Admin {
    function getData($id) {
      global $osC_Database;

      $Qcc = $osC_Database->query('select * from :table_credit_cards where id = :id');
      $Qcc->bindTable(':table_credit_cards', TABLE_CREDIT_CARDS);
      $Qcc->bindInt(':id', $id);
      $Qcc->execute();

      $result = $Qcc->toArray();

      $Qcc->freeResult();

      return $result;
    }

    function save($id = null, $data) {
      global $osC_Database;

      if ( is_numeric($id) ) {
        $Qcc = $osC_Database->query('update :table_credit_cards set credit_card_name = :credit_card_name, pattern = :pattern, credit_card_status = :credit_card_status, sort_order = :sort_order where id = :id');
        $Qcc->bindInt(':id', $id);
      } else {
        $Qcc = $osC_Database->query('insert into :table_credit_cards (credit_card_name, pattern, credit_card_status, sort_order) values (:credit_card_name, :pattern, :credit_card_status, :sort_order)');
      }

      $Qcc->bindTable(':table_credit_cards', TABLE_CREDIT_CARDS);
      $Qcc->bindValue(':credit_card_name', $data['credit_card_name']);
      $Qcc->bindValue(':pattern', $data['pattern']);
      $Qcc->bindInt(':credit_card_status', $data['credit_card_status']);
      $Qcc->bindInt(':sort_order', $data['sort_order']);
      $Qcc->setLogging($_SESSION['module'], $id);
      $Qcc->execute();

      if ( $Qcc->affectedRows() ) {
        osC_Cache::clear('credit-cards');

        return true;
      }

      return false;
    }

    function delete($id) {
      global $osC_Database;

      $Qdel = $osC_Database->query('delete from :table_credit_cards where id = :id');
      $Qdel->bindTable(':table_credit_cards', TABLE_CREDIT_CARDS);
      $Qdel->bindInt(':id', $id);
      $Qdel->setLogging($_SESSION['module'], $id);
      $Qdel->execute();

      if ( $Qdel->affectedRows() ) {
        osC_Cache::clear('credit-cards');

        return true;
      }

      return false;
    }

    function setStatus($id, $status) {
      global $osC_Database;

      $Qcc = $osC_Database->query('update :table_credit_cards set credit_card_status = :credit_card_status where id = :id');
      $Qcc->bindTable(':table_credit_cards', TABLE_CREDIT_CARDS);
      $Qcc->bindInt(':credit_card_status', ($status === true) ? 1 : 0);
      $Qcc->bindInt(':id', $id);
      $Qcc->setLogging($_SESSION['module'], $id);
      $Qcc->execute();

      if ( $Qcc->affectedRows() ) {
        osC_Cache::clear('credit-cards');

        return true;
      }
    }
  }
?>
