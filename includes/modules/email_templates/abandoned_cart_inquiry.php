<?php
/*
  $Id: abandoned_cart_inquiry.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require_once(realpath(dirname(__FILE__) . '/../../../'). '/includes/classes/email_template.php');

  class toC_Email_Template_abandoned_cart_inquiry extends toC_Email_Template {

/* Private variables */

    var $_template_name = 'abandoned_cart_inquiry',
        $_keywords = array( '%%greeting_text%%',
                            '%%customer_first_name%%',
                            '%%customer_last_name%%',
                            '%%shopping_cart_contents%%',
                            '%%comment%%',
                            '%%store_name%%',
                            '%%store_address%%',
                            '%%store_owner_email_address%%');

/* Class constructor */

    function toC_Email_Template_abandoned_cart_inquiry() {
      parent::toC_Email_Template($this->_template_name);
    }


/* Private methods */

  function setData($gender, $first_name, $last_name, $cart_contents, $comment, $to_email_address){
      $this->_gender = $gender;
      $this->_first_name = $first_name;
      $this->_last_name = $last_name;
      $this->_cart_contents = $cart_contents;
      $this->_comment = $comment;

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

      $shopping_cart_content = '';
      foreach($this->_cart_contents as $product){
        $shopping_cart_content .= $product['qty'] . ' x ' . $product['name'] . '<br />';
      }

      $replaces = array($greeting_text, $this->_first_name, $this->_last_name, $shopping_cart_content, $this->_comment, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
      $this->_title = str_replace($this->_keywords, $replaces, $this->_title);
      $this->_email_text = str_replace($this->_keywords, $replaces, $this->_content);
    }
  }
?>
