<?php
/*
  $Id: send_coupon.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require_once(realpath(dirname(__FILE__) . '/../../../'). '/includes/classes/email_template.php');

  class toC_Email_Template_send_coupon extends toC_Email_Template {

/* Private variables */

    var $_template_name = 'send_coupon',
        $_keywords = array( '%%greeting_text%%',
                            '%%customer_first_name%%',
                            '%%customer_last_name%%',
                            '%%coupon_code%%',
                            '%%addtional_message%%',
                            '%%store_name%%',
                            '%%store_address%%',
                            '%%store_owner_email_address%%');

/* Class constructor */

    function toC_Email_Template_send_coupon() {
      parent::toC_Email_Template($this->_template_name);
    }


/* Private methods */

  function setData($gender, $first_name, $last_name, $coupon_code, $addtional_message, $to_email_address){
      $this->_gender = $gender;
      $this->_first_name = $first_name;
      $this->_last_name = $last_name;
      $this->_coupon_code = $coupon_code;
      $this->_addtional_message = $addtional_message;

      $this->addRecipient($first_name . ' ' . $last_name, $to_email_address);
    }

    function buildMessage() {
      global $osC_Language;

      // build the message content
          if ((ACCOUNT_GENDER > -1) && isset($this->_gender)) {
        if ($this->_gender == 'm') {
          $greeting_text = sprintf($osC_Language->get('email_greet_mr'), $this->_last_name) . "<br /><br />";
        } else {
          $greeting_text = sprintf($osC_Language->get('email_greet_ms'), $this->_last_name) . "<br /><br />";
        }
      } else {
        $greeting_text = sprintf($osC_Language->get('email_greet_general'), $this->first_name . ' ' . $this->_last_name) . "<br /><br />";
      }


      $replaces = array($greeting_text, $this->_first_name, $this->_last_name, $this->_coupon_code, $this->_addtional_message, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
      $this->_title = str_replace($this->_keywords, $replaces, $this->_title);
      $this->_email_text = str_replace($this->_keywords, $replaces, $this->_content);
    }
  }
?>
