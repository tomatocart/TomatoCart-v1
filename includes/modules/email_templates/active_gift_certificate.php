<?php
/*
  $Id: active_gift_certificate.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require_once(realpath(dirname(__FILE__) . '/../../../'). '/includes/classes/email_template.php');

  class toC_Email_Template_active_gift_certificate extends toC_Email_Template {

/* Private variables */

    var $_template_name = 'active_gift_certificate',
        $_keywords = array('%%sender_name%%',
                           '%%sender_email%%',
                           '%%recipient_name%%',
                           '%%recipient_email%%',
                           '%%gift_certificate_amount%%',
                           '%%gift_certificate_code%%',
                           '%%gift_certificate_message%%',
                           '%%store_name%%',
                           '%%store_owner_email_address%%');

/* Class constructor */

    function toC_Email_Template_active_gift_certificate() {
      parent::toC_Email_Template($this->_template_name);
    }


/* Private methods */

  function setData($sender_name, $sender_email, $recipient_name, $recipient_email, $gift_certificate_amount, $gift_certificate_code, $gift_certificate_message){
      $this->_sender_name = $sender_name;
      $this->_sender_email = $sender_email;
      $this->_recipient_name = $recipient_name;
      $this->_recipient_email = $recipient_email;
      $this->_gift_certificate_amount = $gift_certificate_amount;
      $this->_gift_certificate_code = $gift_certificate_code;
      $this->_gift_certificate_message = $gift_certificate_message;

      $this->addRecipient($this->_recipient_name, $this->_recipient_email);
    }

    function buildMessage() {
      $replaces = array($this->_sender_name, $this->_sender_email, $this->_recipient_name, $this->_recipient_email, $this->_gift_certificate_amount, $this->_gift_certificate_code, $this->_gift_certificate_message, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

      $this->_title = str_replace($this->_keywords, $replaces, $this->_title);
      $this->_email_text = str_replace($this->_keywords, $replaces, $this->_content);
    }
  }
?>
