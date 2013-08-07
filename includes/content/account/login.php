<?php
/*
  $Id: login.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/classes/account.php');

  class osC_Account_Login extends osC_Template {

/* Private variables */

    var $_module = 'login',
        $_group = 'account',
        $_page_title,
        $_page_contents = 'login.php',
        $_page_image = 'table_background_login.gif';

/* Class constructor */

    function osC_Account_Login() {
      global $osC_Language, $osC_Services, $breadcrumb;

// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled (or the session has not started)
      if (osc_empty(session_id())) {
        osc_redirect(osc_href_link(FILENAME_INFO, 'cookie', 'AUTO'));
      }

      $this->_page_title = $osC_Language->get('sign_in_heading');

      if ($osC_Services->isStarted('breadcrumb')) {
        $breadcrumb->add($osC_Language->get('breadcrumb_sign_in'), osc_href_link(FILENAME_ACCOUNT, $this->_module, 'SSL'));
      }

      if ($_GET[$this->_module] == 'process') {
        $this->_process();
      }
    }

/* Private methods */

    function _process() {
      global $osC_Database, $osC_Session, $osC_Language, $osC_ShoppingCart, $messageStack, $osC_Customer, $osC_NavigationHistory, $toC_Wishlist;

      if (osC_Account::checkEntry($_POST['email_address'])) {
        if (osC_Account::checkPassword($_POST['password'], $_POST['email_address'])) {
          if (osC_Account::checkStatus($_POST['email_address'])) {
            if (SERVICE_SESSION_REGENERATE_ID == '1') {
              $osC_Session->recreate();
            }
  
            $osC_Customer->setCustomerData(osC_Account::getID($_POST['email_address']));
  
            $Qupdate = $osC_Database->query('update :table_customers set date_last_logon = :date_last_logon, number_of_logons = number_of_logons+1 where customers_id = :customers_id');
            $Qupdate->bindTable(':table_customers', TABLE_CUSTOMERS);
            $Qupdate->bindRaw(':date_last_logon', 'now()');
            $Qupdate->bindInt(':customers_id', $osC_Customer->getID());
            $Qupdate->execute();
            
            if ( defined('SYNCHRONIZE_CART_WITH_DATABASE') && (SYNCHRONIZE_CART_WITH_DATABASE == '1') ) {
              $osC_ShoppingCart->synchronizeWithDatabase();
            }
            
            $toC_Wishlist->synchronizeWithDatabase();
  
            $osC_NavigationHistory->removeCurrentPage();
  
            if ($osC_NavigationHistory->hasSnapshot()) {
              $osC_NavigationHistory->redirectToSnapshot();
            } else {
              osc_redirect(osc_href_link(FILENAME_DEFAULT, null, 'AUTO'));
            }
          } else {
            $messageStack->add('login', $osC_Language->get('error_login_status_disabled'));
          }
        } else {
          $messageStack->add('login', $osC_Language->get('error_login_no_match'));
        }
      } else {
        $messageStack->add('login', $osC_Language->get('error_login_no_match'));
      }
    }
  }
?>
