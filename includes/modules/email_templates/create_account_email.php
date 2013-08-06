<?php
/*
  $Id: create_account_email.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require_once(realpath(dirname(__FILE__) . '/../../../'). '/includes/classes/email_template.php');

  class toC_Email_Template_create_account_email extends toC_Email_Template{

/* Private variables */
  
    var $_template_name = 'create_account_email',
        $_keywords = array( '%%greeting_text%%',
                            '%%customer_first_name%%',
                            '%%customer_last_name%%',
                            '%%customer_email_address%%',
                            '%%customer_password%%',
                            '%%store_name%%',
                            '%%store_owner_email_address%%');

// class constructor
    function toC_Email_Template_Create_Account_Email() {
      parent::toC_Email_Template($this->_template_name);
    }

// class methods
    function setData($osC_Customer, $password){
      $this->_customer = $osC_Customer;
      $this->_password = $password;

      $this->addRecipient($osC_Customer->getName(), $osC_Customer->getEmailAddress());
    }

    function buildMessage() {
      global $osC_Language;

      $gender = $this->_customer->getGender();
      // build the message content
      if ((ACCOUNT_GENDER > -1) && isset($gender)) {
        if ($gender == 'm') {
          $greeting_text = sprintf($osC_Language->get('email_addressing_gender_male'), $this->_customer->getLastName()) . "<br /><br />";
        } else {
          $greeting_text = sprintf($osC_Language->get('email_addressing_gender_female'), $this->_customer->getLastName()) . "<br /><br />";
        }
      } else {
        $greeting_text = sprintf($osC_Language->get('email_addressing_gender_unknown'), $this->_customer->getName()) . "<br /><br />";
      }

      $replaces = array($greeting_text, $this->_customer->getFirstName(), $this->_customer->getLastName(), $this->_customer->getEmailAddress(), $this->_password, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
      
      $this->_title = str_replace($this->_keywords, $replaces, $this->_title);
      $this->_email_text = str_replace($this->_keywords, $replaces, $this->_content);
    }
  }
?>
