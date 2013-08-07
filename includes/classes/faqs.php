<?php
/*
  $Id: faqs.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2004 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Faqs {

    function &getListing() {
      global $osC_Database, $osC_Language;

      $Qfaqs = $osC_Database->query('select f.faqs_id, fd.faqs_question, fd.faqs_answer from :table_faqs f, :table_faqs_description fd where f.faqs_status = 1 and f.faqs_id = fd.faqs_id and fd.language_id = :language_id order by f.faqs_order desc, fd.faqs_question');
      $Qfaqs->bindTable(':table_faqs', TABLE_FAQS);
      $Qfaqs->bindTable(':table_faqs_description', TABLE_FAQS_DESCRIPTION);
      $Qfaqs->bindInt(':language_id', $osC_Language->getID());
      $Qfaqs->execute();

      return $Qfaqs;
    }
  }
?>
