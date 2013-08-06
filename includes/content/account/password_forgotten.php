<?php
/*
  $Id: password_forgotten.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/classes/account.php');

  class osC_Account_Password_forgotten extends osC_Template {

/* Private variables */

    var $_module = 'password_forgotten',
        $_group = 'account',
        $_page_title,
        $_page_contents = 'password_forgotten.php',
        $_page_image = 'table_background_password_forgotten.gif';

/* Class constructor */

    function osC_Account_Password_forgotten() {
      global $osC_Language, $osC_Services, $breadcrumb;

      $this->_page_title = $osC_Language->get('password_forgotten_heading');

      $this->addJavascriptPhpFilename('includes/form_check.js.php');

      if ($osC_Services->isStarted('breadcrumb')) {
        $breadcrumb->add($osC_Language->get('breadcrumb_password_forgotten'), osc_href_link(FILENAME_ACCOUNT, $this->_module, 'SSL'));
      }

      if ($_GET[$this->_module] == 'process') {
        $this->_process();
      }
    }

/* Private methods */

    function _process() {
      global $messageStack, $osC_Database, $osC_Language;

      $Qcheck = $osC_Database->query('select customers_id, customers_firstname, customers_lastname, customers_gender, customers_email_address, customers_password from :table_customers where customers_email_address = :customers_email_address limit 1');
      $Qcheck->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qcheck->bindValue(':customers_email_address', $_POST['email_address']);
      $Qcheck->execute();

      if ($Qcheck->numberOfRows() === 1) {
        $password = osc_create_random_string(ACCOUNT_PASSWORD);

        if (osC_Account::savePassword($password, $Qcheck->valueInt('customers_id'))) {

          include('includes/classes/email_template.php');
          $email_template = toC_Email_Template::getEmailTemplate('password_forgotten');
          $email_template->setData($Qcheck->valueProtected('customers_firstname'), $Qcheck->valueProtected('customers_lastname'), getenv('REMOTE_ADDR'), $password, $Qcheck->valueProtected('customers_gender'), $Qcheck->valueProtected('customers_email_address'));
          $email_template->buildMessage();
          $email_template->sendEmail();

          $messageStack->add_session('login', $osC_Language->get('success_password_forgotten_sent'), 'success');
        }

        osc_redirect(osc_href_link(FILENAME_ACCOUNT, 'login', 'SSL'));
      } else {
        $messageStack->add('password_forgotten', $osC_Language->get('error_password_forgotten_no_email_address_found'));
      }
    }
  }
?>
