<?php
/*
  $Id: edit.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/classes/account.php');

  class osC_Account_Edit extends osC_Template {

/* Private variables */

    var $_module = 'edit',
        $_group = 'account',
        $_page_title,
        $_page_contents = 'account_edit.php',
        $_page_image = 'table_background_account.gif';

/* Class constructor */

    function osC_Account_Edit() {
      global $osC_Language, $osC_Services, $breadcrumb;

      $this->_page_title = $osC_Language->get('account_edit_heading');

      $this->addJavascriptPhpFilename('includes/form_check.js.php');

      if ($osC_Services->isStarted('breadcrumb')) {
        $breadcrumb->add($osC_Language->get('breadcrumb_edit_account'), osc_href_link(FILENAME_ACCOUNT, $this->_module, 'SSL'));
      }

      if ($_GET[$this->_module] == 'save') {
        $this->_process();
      }
    }

/* Private methods */

    function _process() {
      global $messageStack, $osC_Database, $osC_Language, $osC_Customer;

      $data = array();

      if (ACCOUNT_GENDER == '1') {
        if (isset($_POST['gender']) && (($_POST['gender'] == 'm') || ($_POST['gender'] == 'f'))) {
          $data['gender'] = $_POST['gender'];
        } else {
          $messageStack->add($this->_module, $osC_Language->get('field_customer_gender_error'));
        }
      } else {
        $data['gender'] = isset($_POST['gender']) ? $_POST['gender'] : '';
      }

      if (isset($_POST['firstname']) && (strlen(trim($_POST['firstname'])) >= ACCOUNT_FIRST_NAME)) {
        $data['firstname'] = $_POST['firstname'];
      } else {
        $messageStack->add('account_edit', sprintf($osC_Language->get('field_customer_first_name_error'), ACCOUNT_FIRST_NAME));
      }

      if (isset($_POST['lastname']) && (strlen(trim($_POST['lastname'])) >= ACCOUNT_LAST_NAME)) {
        $data['lastname'] = $_POST['lastname'];
      } else {
        $messageStack->add('account_edit', sprintf($osC_Language->get('field_customer_last_name_error'), ACCOUNT_LAST_NAME));
      }

      if (ACCOUNT_DATE_OF_BIRTH == '1') {
        if (isset($_POST['dob_days']) && isset($_POST['dob_months']) && isset($_POST['dob_years']) && checkdate($_POST['dob_months'], $_POST['dob_days'], $_POST['dob_years'])) {
          $data['dob'] = mktime(0, 0, 0, $_POST['dob_months'], $_POST['dob_days'], $_POST['dob_years']);
        } else {
          $messageStack->add('account_edit', $osC_Language->get('field_customer_date_of_birth_error'));
        }
      }

      if (isset($_POST['email_address']) && (strlen(trim($_POST['email_address'])) >= ACCOUNT_EMAIL_ADDRESS)) {
        if (osc_validate_email_address($_POST['email_address'])) {
          if (osC_Account::checkDuplicateEntry($_POST['email_address']) === false) {
            $data['email_address'] = $_POST['email_address'];
          } else {
            $messageStack->add('account_edit', $osC_Language->get('field_customer_email_address_exists_error'));
          }
        } else {
          $messageStack->add('account_edit', $osC_Language->get('field_customer_email_address_check_error'));
        }
      } else {
        $messageStack->add('account_edit', sprintf($osC_Language->get('field_customer_email_address_error'), ACCOUNT_EMAIL_ADDRESS));
      }

      if ($messageStack->size('account_edit') === 0) {
        if (osC_Account::saveEntry($data)) {
// reset the session variables
          if (ACCOUNT_GENDER > -1) {
            $osC_Customer->setGender($data['gender']);
          }
          $osC_Customer->setFirstName(trim($data['firstname']));
          $osC_Customer->setLastName(trim($data['lastname']));
          $osC_Customer->setEmailAddress($data['email_address']);

          $messageStack->add_session('account', $osC_Language->get('success_account_updated'), 'success');
        }

        osc_redirect(osc_href_link(FILENAME_ACCOUNT, null, 'SSL'));
      }
    }
  }
?>
