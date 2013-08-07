<?php
/*
  $Id: search_terms.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Search_Terms_Admin {
    function getData($id) {
      global $osC_Database;
      
      $Qterm = $osC_Database->query('select * from :table_search_terms where search_terms_id = :search_terms_id');
      $Qterm->bindTable(':table_search_terms', TABLE_SEARCH_TERMS);
      $Qterm->bindInt(':search_terms_id', $id);
      $Qterm->execute();

      $data = $Qterm->toArray();
      
      $Qterm->freeResult();

      return $data;
    }

    function save($id, $data) {
      global $osC_Database;

      $Qupdate = $osC_Database->query('update :table_search_terms set text = :text, products_count = :products_count, search_count = :search_count , synonym = :synonym, show_in_terms = :show_in_terms where search_terms_id = :search_terms_id');
      $Qupdate->bindTable(':table_search_terms', TABLE_SEARCH_TERMS);
      $Qupdate->bindValue(':text', $data['text']);
      $Qupdate->bindInt(':products_count', $data['products_count']);
      $Qupdate->bindInt(':search_count', $data['search_count']);
      $Qupdate->bindValue(':synonym', $data['synonym']);
      $Qupdate->bindInt(':show_in_terms', $data['show_in_terms']);
      $Qupdate->bindInt(':search_terms_id', $id);
      $Qupdate->execute();

      if ($osC_Database->isError()) {
        return false;
      }

      return true;
    }

    function setStatus($id, $status) {
      global $osC_Database;

      $Qstatus = $osC_Database->query('update :table_search_terms set show_in_terms = :show_in_terms where search_terms_id = :search_terms_id');
      $Qstatus->bindTable(':table_search_terms', TABLE_SEARCH_TERMS);
      $Qstatus->bindInt(':show_in_terms', ($status == 1) ? 1 : 0);
      $Qstatus->bindInt(':search_terms_id', $id);
      $Qstatus->setLogging($_SESSION['module'], $id);
      $Qstatus->execute();

      if ($osC_Database->isError()) {
        return false;
      }

      return true;
    }
  }
?>
