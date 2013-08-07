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

  class toC_Email_Template_admin_password_forgotten extends toC_Email_Template {

/* Private variables */

    var $_template_name = 'admin_password_forgotten',
        $_keywords = array( '%%user_name%%',
                            '%%admin_ip_address%%',
                            '%%admin_password%%',
                            '%%store_name%%',
                            '%%store_owner_email_address%%');

/* Class constructor */

    function toC_Email_Template_admin_password_forgotten() {
      parent::toC_Email_Template($this->_template_name);
    }


/* Private methods */

  function setData($user_name, $ip_address, $password, $admin_email) {
      $this->_user_name = $user_name;
      $this->_ip_address = $ip_address;
      $this->_password = $password;

      $this->addRecipient($this->_user_name, $admin_email);
    }

    function buildMessage() {
      global $osC_Language;

      $replaces = array($this->_user_name, $this->_ip_address, $this->_password, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
      $this->_title = str_replace($this->_keywords, $replaces, $this->_title);
      $this->_email_text = str_replace($this->_keywords, $replaces, $this->_content);
    }
  }
?>
