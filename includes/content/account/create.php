<?php
/*
  $Id: create.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/classes/account.php');
  require_once("ext/securimage/securimage.php");

  class osC_Account_Create extends osC_Template {

/* Private variables */

    var $_module = 'create',
        $_group = 'account',
        $_page_title,
        $_page_contents = 'create.php',
        $_page_image = 'table_background_account.gif';

/* Class constructor */

    function osC_Account_Create() {
      global $osC_Language, $osC_Services, $breadcrumb;

      $this->_page_title = $osC_Language->get('create_account_heading');

      if ($_GET[$this->_module] == 'success') {
        if ($osC_Services->isStarted('breadcrumb')) {
          $breadcrumb->add($osC_Language->get('breadcrumb_create_account'));
        }

        $this->_page_title = $osC_Language->get('create_account_success_heading');
        $this->_page_contents = 'create_success.php';
      } else {
        if ($osC_Services->isStarted('breadcrumb')) {
          $breadcrumb->add($osC_Language->get('breadcrumb_create_account'), osc_href_link(FILENAME_ACCOUNT, $this->_module, 'SSL'));
        }
        
        $this->addStyleSheet('ext/multibox/multibox.css');
        $this->addJavascriptFilename('ext/multibox/Overlay.js');
        $this->addJavascriptFilename('ext/multibox/MultiBox.js');        

        $this->addJavascriptPhpFilename('includes/form_check.js.php');
      }

      if ($_GET[$this->_module] == 'save') {
        $this->_process();
      }
      
      if ($_GET[$this->_module] == 'show_captcha') {
        $this->_show_captcha();
      }
    }

/* Private methods */
    
    function _show_captcha() {
      $img = new securimage();
      
      $img->show();
    }

    function _process() {
      global $messageStack, $osC_Database, $osC_Language, $osC_Customer;

      $data = array();

      if (DISPLAY_PRIVACY_CONDITIONS == '1') {
        if ( (isset($_POST['privacy_conditions']) === false) || (isset($_POST['privacy_conditions']) && ($_POST['privacy_conditions'] != '1')) ) {
          $messageStack->add($this->_module, $osC_Language->get('error_privacy_statement_not_accepted'));
        }
      }

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
        $messageStack->add($this->_module, sprintf($osC_Language->get('field_customer_first_name_error'), ACCOUNT_FIRST_NAME));
      }

      if (isset($_POST['lastname']) && (strlen(trim($_POST['lastname'])) >= ACCOUNT_LAST_NAME)) {
        $data['lastname'] = $_POST['lastname'];
      } else {
        $messageStack->add($this->_module, sprintf($osC_Language->get('field_customer_last_name_error'), ACCOUNT_LAST_NAME));
      }
      
      $data['newsletter'] = ( isset($_POST['newsletter']) && ($_POST['newsletter'] == '1') ) ? 1 : 0;

      if (ACCOUNT_DATE_OF_BIRTH == '1') {
        if (isset($_POST['dob_days']) && isset($_POST['dob_months']) && isset($_POST['dob_years']) && checkdate($_POST['dob_months'], $_POST['dob_days'], $_POST['dob_years'])) {
          $data['dob'] = mktime(0, 0, 0, $_POST['dob_months'], $_POST['dob_days'], $_POST['dob_years']);
        } else {
          $messageStack->add($this->_module, $osC_Language->get('field_customer_date_of_birth_error'));
        }
      }

      if (isset($_POST['email_address']) && (strlen(trim($_POST['email_address'])) >= ACCOUNT_EMAIL_ADDRESS)) {
        if (osc_validate_email_address($_POST['email_address'])) {
          if (osC_Account::checkDuplicateEntry($_POST['email_address']) === false) {
            $data['email_address'] = $_POST['email_address'];
          } else {
            $messageStack->add($this->_module, $osC_Language->get('field_customer_email_address_exists_error'));
          }
        } else {
          $messageStack->add($this->_module, $osC_Language->get('field_customer_email_address_check_error'));
        }
      } else {
        $messageStack->add($this->_module, sprintf($osC_Language->get('field_customer_email_address_error'), ACCOUNT_EMAIL_ADDRESS));
      }

      if ( (isset($_POST['password']) === false) || (isset($_POST['password']) && (strlen(trim($_POST['password'])) < ACCOUNT_PASSWORD)) ) {
        $messageStack->add($this->_module, sprintf($osC_Language->get('field_customer_password_error'), ACCOUNT_PASSWORD));
      } elseif ( (isset($_POST['confirmation']) === false) || (isset($_POST['confirmation']) && (trim($_POST['password']) != trim($_POST['confirmation']))) ) {
        $messageStack->add($this->_module, $osC_Language->get('field_customer_password_mismatch_with_confirmation'));
      } else {
        $data['password'] = $_POST['password'];
      }
      
      if ( ACTIVATE_CAPTCHA == '1' ) {
        if (isset($_POST['captcha_code']) && !empty($_POST['captcha_code'])) {
          $securimage = new Securimage();
          
          if ($securimage->check($_POST['captcha_code']) == false) {
            $messageStack->add('create', $osC_Language->get('field_create_account_captcha_check_error'));
          }
        } else {
          $messageStack->add('create', $osC_Language->get('field_create_account_captcha_check_error'));
        }  
      }

      if ($messageStack->size($this->_module) === 0) {
        if (osC_Account::createEntry($data)) {
          $messageStack->add_session('create', $osC_Language->get('success_account_updated'), 'success');
        }

        osc_redirect(osc_href_link(FILENAME_ACCOUNT, 'create=success', 'SSL'));
      }
    }
  }
?>
