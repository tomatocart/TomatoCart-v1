<?php
/*
  $Id: admin_order_status_updated.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require_once(realpath(dirname(__FILE__) . '/../../../'). '/includes/classes/email_template.php');

  class toC_Email_Template_admin_order_status_updated extends toC_Email_Template {

/* Private variables */

    var $_template_name = 'admin_order_status_updated',
        $_keywords = array( '%%order_number%%',
                            '%%invoice_link%%',
                            '%%date_ordered%%',
                            '%%order_comment%%',
                            '%%new_order_status%%',
                            '%%customer_name%%',
                            '%%store_name%%',
                            '%%store_owner_email_address%%');

/* Class constructor */

    function toC_Email_Template_admin_order_status_updated() {
      parent::toC_Email_Template($this->_template_name);
    }


/* Private methods */

    function setData($order_number, $invoice_link, $date_ordered, $append_comment, $order_comment, $new_order_status, $customer_name, $customers_email_address){
      $this->_order_number = $order_number;
      $this->_invoice_link = $invoice_link;
      $this->_date_ordered = $date_ordered;
      $this->_order_comment = $order_comment;
      $this->_new_order_status = $new_order_status;
      $this->_append_comment = $append_comment;
      $this->_customer_name = $customer_name;

      $this->addRecipient($customer_name, $customers_email_address);
    }

    function buildMessage() {
      if ( $this->_append_comment === false ) {
        $this->_order_comment = '';
      }

      $replaces = array($this->_order_number, $this->_invoice_link, $this->_date_ordered, $this->_order_comment, $this->_new_order_status, $this->_customer_name, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

      $this->_title = str_replace($this->_keywords, $replaces, $this->_title);
      $this->_email_text = str_replace($this->_keywords, $replaces, $this->_content);
    }
  }
?>
