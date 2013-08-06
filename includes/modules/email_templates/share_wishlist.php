<?php
/*
  $Id: share_wishlist.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require_once(realpath(dirname(__FILE__) . '/../../../'). '/includes/classes/email_template.php');

  class toC_Email_Template_share_wishlist extends toC_Email_Template {

/* Private variables */

    var $_template_name = 'share_wishlist',
        $_keywords = array( '%%from_name%%',
                            '%%from_email_address%%',
                            '%%to_email_address%%',
                            '%%message%%',
                            '%%wishlist_url%%',
                            '%%store_name%%',
                            '%%store_address%%',
                            '%%store_owner_email_address%%');

/* Class constructor */

    function toC_Email_Template_share_wishlist() {
      parent::toC_Email_Template($this->_template_name);
    }


/* Private methods */

    function setData($from_name, $from_email_address, $to_email_address, $message, $wishlist_url){
      $this->_from_name = $from_name;
      $this->_from_email_address = $from_email_address;
      $this->_to_email_address = $to_email_address;
      $this->_message = $message;
      $this->_wishlist_url = $wishlist_url;
      
      $emails = explode(',', $this->_to_email_address);
      foreach ($emails as $email) {
        if (osc_validate_email_address($email)) {
          $this->addRecipient('', $email);
        }
      }
    }

    function buildMessage() {
      $replaces = array($this->_from_name, $this->_from_email_address, $this->_to_email_address, $this->_message, $this->_wishlist_url, STORE_NAME, HTTP_SERVER . DIR_WS_CATALOG, STORE_OWNER_EMAIL_ADDRESS);

      $this->_title = str_replace($this->_keywords, $replaces, $this->_title);
      $this->_email_text = str_replace($this->_keywords, $replaces, $this->_content);
    }
  }
?>