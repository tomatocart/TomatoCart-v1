<?php
/*
  $Id: admin_create_purchase_order.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require_once(realpath(dirname(__FILE__) . '/../../../'). '/includes/classes/email_template.php');

  class toC_Email_Template_admin_create_purchase_order extends toC_Email_Template{
    var $_template_name = 'admin_create_purchase_order',
        $_keywords = array( '%%vendors_name%%',
                            '%%purchase_order_products%%',
                            '%%order_number%%',
                            '%%total_amount%%',
                            '%%store_name%%',
                            '%%store_ower_email_address%%');

// class constructor
    function toC_Email_Template_admin_create_purchase_order() {
      parent::toC_Email_Template($this->_template_name);
    }

// class methods
    function setData($purchase_orders_no, $vendors_name, $vendors_email_address, $products, $total_amount){
      $this->_vendors_name = $vendors_name;
      $this->_vendors_email_address = $vendors_email_address;
      $this->_purchase_order_products = $products;
      $this->_total_amount = $total_amount;
      $this->_order_number = $purchase_orders_no;

      $this->addRecipient($vendors_name, $vendors_email_address);
    }

    function buildMessage() {
      global $osC_Language;

      $replaces = array($this->_vendors_name, $this->_purchase_order_products, $this->_order_number, $this->_total_amount, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
      $this->_title = str_replace($this->_keywords, $replaces, $this->_title);
      $this->_email_text = str_replace($this->_keywords, $replaces, $this->_content);
    }
  }
?>
