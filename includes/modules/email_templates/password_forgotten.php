<?php
/*
  $Id: password_forgotten.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require_once(realpath(dirname(__FILE__) . '/../../../'). '/includes/classes/email_template.php');

  class toC_Email_Template_password_forgotten extends toC_Email_Template {

/* Private variables */

    var $_template_name = 'password_forgotten',
        $_keywords = array( '%%greeting_text%%',
                            '%%customer_first_name%%',
                            '%%customer_last_name%%',
                            '%%customer_ip_address%%',
                            '%%customer_password%%',
                            '%%store_name%%',
                            '%%store_owner_email_address%%');

/* Class constructor */

    function toC_Email_Template_password_forgotten() {
      parent::toC_Email_Template($this->_template_name);
    }


/* Private methods */

  function setData($first_name, $last_name, $ip_address, $password, $gender, $customer_email){
      $this->_first_name = $first_name;
      $this->_last_name = $last_name;
      $this->_ip_address = $ip_address;
      $this->_password = $password;
      $this->_gender = $gender;

      $this->addRecipient($this->_first_name . ' ' . $this->_last_name, $customer_email);
    }

    function buildMessage() {
      global $osC_Language;

      // build the message content
      if ((ACCOUNT_GENDER > -1) && isset($this->_gender)) {
        if ($this->_gender == 'm') {
          $greeting_text = sprintf($osC_Language->get('email_addressing_gender_male'), $this->_last_name) . "<br /><br />";
        } else {
          $greeting_text = sprintf($osC_Language->get('email_addressing_gender_female'), $this->_first_name) . "<br /><br />";
        }
      } else {
        $greeting_text = sprintf($osC_Language->get('email_addressing_gender_unknown'), $this->_first_name . ' ' . $this->_last_name) . "<br /><br />";
      }

      $replaces = array($greeting_text, $this->_first_name, $this->_last_name, $this->_ip_address, $this->_password, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
      $this->_title = str_replace($this->_keywords, $replaces, $this->_title);
      $this->_email_text = str_replace($this->_keywords, $replaces, $this->_content);
    }
  }
?>
