<?php
/*
  $Id: contact.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require_once("includes/classes/departments.php");
  require_once("ext/securimage/securimage.php");
  
  class osC_Info_Contact extends osC_Template {

/* Private variables */

    var $_module = 'contact',
        $_group = 'info',
        $_page_title,
        $_page_contents = 'info_contact.php',
        $_page_image = 'table_background_contact_us.gif';

/* Class constructor */

    function osC_Info_Contact() {
      global $osC_Services, $osC_Language, $breadcrumb;
      
      $this->_page_title = $osC_Language->get('info_contact_heading');

      if ($osC_Services->isStarted('breadcrumb')) {
        $breadcrumb->add($osC_Language->get('breadcrumb_contact'), osc_href_link(FILENAME_INFO, $this->_module));
      }

      if ($_GET[$this->_module] == 'process') {
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
      global $osC_Language, $messageStack;
      
      if (isset($_POST['department_email']) && !empty($_POST['department_email'])) {
        $department_email = osc_sanitize_string($_POST['department_email']);
        
        if (!osc_validate_email_address($department_email)) {
          $messageStack->add('contact', $osC_Language->get('field_departments_email_error'));
        }
      } else {
        $department_email = STORE_OWNER_EMAIL_ADDRESS;
      }
      
      if (isset($_POST['name']) && !empty($_POST['name'])) {
        $name = osc_sanitize_string($_POST['name']);
      } else {
        $messageStack->add('contact', $osC_Language->get('field_customer_name_error'));
      }
            
      if (isset($_POST['email']) && !empty($_POST['email'])) {
        $email_address = osc_sanitize_string($_POST['email']);
        
        if (!osc_validate_email_address($email_address)) {
          $messageStack->add('contact', $osC_Language->get('field_customer_concat_email_error'));
        }
      } else {
        $messageStack->add('contact', $osC_Language->get('field_customer_concat_email_error'));
      }

      if (isset($_POST['telephone']) && !empty($_POST['telephone'])) {
        $telephone = osc_sanitize_string($_POST['telephone']);
      }
      
      if (isset($_POST['enquiry']) && !empty($_POST['enquiry'])) {
        $enquiry = osc_sanitize_string($_POST['enquiry']);
      } else {
        $messageStack->add('contact', $osC_Language->get('field_enquiry_error'));
      }
      
      if ( ACTIVATE_CAPTCHA == '1' ) {
        if (isset($_POST['captcha_code']) && !empty($_POST['captcha_code'])) {
          $securimage = new Securimage();
                  
          if ($securimage->check($_POST['captcha_code']) == false) {
            $messageStack->add('contact', $osC_Language->get('field_concat_captcha_check_error'));
          }
        } else {
          $messageStack->add('contact', $osC_Language->get('field_concat_captcha_check_error'));
        }  
      }
      
      if ( $messageStack->size('contact') === 0 ) {
        osc_email(STORE_OWNER, $department_email, $osC_Language->get('contact_email_subject'), $enquiry . '<br /><br /><br />' . $osC_Language->get('contact_telephone_title') . $telephone, $name, $email_address);

        osc_redirect(osc_href_link(FILENAME_INFO, 'contact=success', 'AUTO', true, false));    
      } 
    }
  }
?>
