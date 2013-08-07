<?php
/*
  $Id: email_template.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Email_Template {
    var $_keywords = array(),
        $_template_name = '',
        $_status,
        $_title,
        $_content,
        $_email_text,
        $_attachments = array(),
        $_recipients = array();

// class constructor
    function toC_Email_Template($template_name) {
      global $osC_Database, $osC_Language;

      $Qtemplate = $osC_Database->query('select et.email_templates_status, etd.email_title, etd.email_content from :table_email_templates et, :table_email_templates_description etd where et.email_templates_id = etd.email_templates_id and et.email_templates_name = :email_templates_name and etd.language_id = :language_id');

      $Qtemplate->bindValue(':email_templates_name', $template_name);
      $Qtemplate->bindInt(':language_id', $osC_Language->getID());
      $Qtemplate->bindTable(':table_email_templates', TABLE_EMAIL_TEMPLATES);
      $Qtemplate->bindTable(':table_email_templates_description', TABLE_EMAIL_TEMPLATES_DESCRIPTION);
      $Qtemplate->execute();

      $this->_status = $Qtemplate->valueInt('email_templates_status');
      $this->_title = $Qtemplate->value('email_title');
      $this->_content = $Qtemplate->value('email_content');
    }

    function getEmailTemplate($template_name){
      $file_path = realpath(dirname(__FILE__) . '/../') . '/modules/email_templates/' . $template_name . '.php';
      if(file_exists($file_path)){
        include_once($file_path);

        $email_template_class = 'toC_Email_Template_' . $template_name;
        return new $email_template_class();
      }else{
        return null;
      }
    }

// class methods
    function getKeywords(){
      return $this->_keywords;
    }

    function addRecipient($name, $email_address){
      $this->_recipients[] = array('name' => $name, 'email' => $email_address);
    }
    
    function addAttachment($file, $is_uploaded = false) {
    	$this->_attachments[] = array($file, $is_uploaded);
    }
    
    function resetRecipients() {
      $this->_recipients = array();
    }
    
    function hasAttachment() {
    	if (count($this->_attachments) != 0) {
    		return true;
    	} else {
    		return false;
    	}
    }

    function sendEmail(){
      if($this->_status == '1'){
        foreach($this->_recipients as $recipient) {
        	if (SEND_EMAILS == '-1') {
          	return false;
        	}
    
          $osC_Mail = new osC_Mail($recipient['name'], $recipient['email'], STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $this->_title);
          $osC_Mail->setBodyHTML($this->_email_text);
          
          if ($this->hasAttachment()) {
          	foreach ($this->_attachments as $attachment) {
	          	$osC_Mail->addAttachment($attachment[0] , $attachment[1]);	
          	}
          }
          
          $osC_Mail->send();
        }
      }
    }

  }
?>
