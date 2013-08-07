<?php
/*
  $Id: active_downloadable_product.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require_once(realpath(dirname(__FILE__) . '/../../../'). '/includes/classes/email_template.php');

  class toC_Email_Template_active_downloadable_product extends toC_Email_Template {

/* Private variables */

    var $_template_name = 'active_downloadable_product',
        $_keywords = array('%%customer_name%%',
                           '%%customer_email_address%%',
                           '%%downloadable_products%%',
                           '%%download_link%%',
                           '%%store_name%%',
                           '%%store_owner_email_address%%');

/* Class constructor */

    function toC_Email_Template_active_downloadable_product() {
      parent::toC_Email_Template($this->_template_name);
    }


/* Private methods */

    function setData($customer_name, $email_address, $download_products) {
      $this->_customer_name = $customer_name;
      $this->_email_address = $email_address;
      $this->_downloadable_products = $download_products;

      $this->addRecipient($customer_name, $email_address);
    }

    function buildMessage() {
      $replaces = array($this->_customer_name, $this->_email_address, $this->_downloadable_products, HTTP_SERVER . DIR_WS_CATALOG . FILENAME_ACCOUNT . '?' . 'orders', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

      $this->_title = str_replace($this->_keywords, $replaces, $this->_title);
      $this->_email_text = str_replace($this->_keywords, $replaces, $this->_content);
    }
  }
?>
