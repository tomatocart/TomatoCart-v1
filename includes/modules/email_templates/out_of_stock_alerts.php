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

  class toC_Email_Template_out_of_stock_alerts extends toC_Email_Template {

/* Private variables */

    var $_template_name = 'out_of_stock_alerts',
        $_keywords = array( '%%products_name%%',
                            '%%products_variants%%',                    
                            '%%products_quantity%%');

/* Class constructor */
    function toC_Email_Template_out_of_stock_alerts() {
      parent::toC_Email_Template($this->_template_name);
    }
    
    /* Private methods */
    function setData($products_name, $products_quantity, $products_variants = '') {
      $this->products_quantity = $products_quantity;
      $this->products_name = $products_name;
      $this->products_variants = $products_variants;
      
      $this->addRecipient(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
    }

    function buildMessage() {
      $replaces = array($this->products_name, $this->products_variants, $this->products_quantity);

      $this->_title =  str_replace($this->_keywords, $replaces, $this->_title);
      $this->_email_text = str_replace($this->_keywords, $replaces, $this->_content);
    }
  }
?>
    
