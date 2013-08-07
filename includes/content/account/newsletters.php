<?php
/*
  $Id: newsletters.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Account_Newsletters extends osC_Template {

/* Private variables */

    var $_module = 'newsletters',
        $_group = 'account',
        $_page_title ,
        $_page_contents = 'account_newsletters.php',
        $_page_image = 'table_background_account.gif';

/* Class constructor */

    function osC_Account_Newsletters() {
      global $osC_Language, $osC_Services, $breadcrumb, $osC_Database, $osC_Customer, $Qnewsletter;

      $this->_page_title = $osC_Language->get('newsletters_heading');

      if ($osC_Services->isStarted('breadcrumb')) {
        $breadcrumb->add($osC_Language->get('breadcrumb_newsletters'), osc_href_link(FILENAME_ACCOUNT, $this->_module, 'SSL'));
      }

/////////////////////// HPDL /////// Should be moved to the customers class!
      $Qnewsletter = $osC_Database->query('select customers_newsletter from :table_customers where customers_id = :customers_id');
      $Qnewsletter->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qnewsletter->bindInt(':customers_id', $osC_Customer->getID());
      $Qnewsletter->execute();

      if ($_GET[$this->_module] == 'save') {
        $this->_process();
      }
    }

/* Private methods */

    function _process() {
      global $messageStack, $osC_Database, $osC_Language, $osC_Customer, $Qnewsletter;

      if (isset($_POST['newsletter_general']) && is_numeric($_POST['newsletter_general'])) {
        $newsletter_general = $_POST['newsletter_general'];
      } else {
        $newsletter_general = '0';
      }

      if ($newsletter_general != $Qnewsletter->valueInt('customers_newsletter')) {
        $newsletter_general = (($Qnewsletter->value('customers_newsletter') == '1') ? '0' : '1');

        $Qupdate = $osC_Database->query('update :table_customers set customers_newsletter = :customers_newsletter where customers_id = :customers_id');
        $Qupdate->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qupdate->bindInt(':customers_newsletter', $newsletter_general);
        $Qupdate->bindInt(':customers_id', $osC_Customer->getID());
        $Qupdate->execute();

        if ($Qupdate->affectedRows() === 1) {
          $messageStack->add_session('account', $osC_Language->get('success_newsletter_updated'), 'success');
        }
      }

      osc_redirect(osc_href_link(FILENAME_ACCOUNT, null, 'SSL'));
    }
  }
?>
