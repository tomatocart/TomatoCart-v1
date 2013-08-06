<?php
/*
  $Id: admin_create_order_credit_slip.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require_once(realpath(dirname(__FILE__) . '/../../../'). '/includes/classes/email_template.php');

  class toC_Email_Template_admin_create_order_credit_slip extends toC_Email_Template {

/* Private variables */

    var $_template_name = 'admin_create_order_credit_slip',
        $_keywords = array('%%customer_name%%',
                           '%%customer_email_address%%',
                           '%%returned_products%%',
                           '%%order_number%%',
                           '%%slip_number%%',
                           '%%total_amount%%',
                           '%%store_name%%',
                           '%%store_ower_email_address%%');

/* Class constructor */

    function toC_Email_Template_admin_create_order_credit_slip() {
      parent::toC_Email_Template($this->_template_name);
    }


/* Private methods */

    function setData($customer_name, $customer_email_address, $returned_products, $order_number, $slip_number, $total_amount) {
      $this->_customer_name = $customer_name;
      $this->_customer_email_address = $customer_email_address;
      $this->_returned_products = $returned_products;
      $this->_order_number = $order_number;
      $this->_slip_number = $slip_number;
      $this->_total_amount = $total_amount;

      $this->addRecipient($customer_name, $customer_email_address);
    }

    function buildMessage() {
      global $osC_Language;

      $replaces = array($this->_customer_name, $this->_customer_email_address, $this->_returned_products, $this->_order_number, $this->_slip_number, $this->_total_amount, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
      $this->_title = str_replace($this->_keywords, $replaces, $this->_title);
      $this->_email_text = str_replace($this->_keywords, $replaces, $this->_content);
    }
  }
?>
