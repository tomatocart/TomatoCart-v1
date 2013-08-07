<?php
/*
  $Id: email_templates.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/email_templates.php');

  class toC_Json_Email_Templates {
        
    function listEmailTemplates() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $Qtemplates = $osC_Database->query('select * from :table_email_templates e, :table_email_templates_description ed where e.email_templates_id = ed.email_templates_id and ed.language_id = :language_id order by e.email_templates_name ');
      $Qtemplates->bindInt(':language_id', $osC_Language->getID());
      $Qtemplates->bindTable(':table_email_templates', TABLE_EMAIL_TEMPLATES);
      $Qtemplates->bindTable(':table_email_templates_description', TABLE_EMAIL_TEMPLATES_DESCRIPTION);
      $Qtemplates->execute();
        
      $records = array();     
      while ( $Qtemplates->next() ) {
        $records[] = array(
          'email_templates_id' => $Qtemplates->valueInt('email_templates_id'),
          'email_templates_name' => $Qtemplates->value('email_templates_name'),
          'email_title' => $Qtemplates->value('email_title'),
          'email_templates_status' => $Qtemplates->value('email_templates_status')
        );           
      }
      $Qtemplates->freeResult();         
       
      $response = array(EXT_JSON_READER_TOTAL => sizeof($records),
                        EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }          
    
    function setStatus() {
      global $toC_Json, $osC_Language;
        
      if ( toC_Email_Templates_Admin::setStatus($_REQUEST['email_templates_id'], ( isset($_REQUEST['flag']) ? $_REQUEST['flag'] : null) ) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed') );
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function loadEmailTemplate() {
      global $toC_Json, $osC_Database;
     
      $email_templates_id = ( isset($_REQUEST['email_templates_id']) && is_numeric($_REQUEST['email_templates_id']) ) ? $_REQUEST['email_templates_id'] : null;
      
      $data = toC_Email_Templates_Admin::getData($email_templates_id);
      
      $Qtemplate = $osC_Database->query('select * from :table_email_templates_description where email_templates_id= :email_templates_id');
      $Qtemplate->bindTable(':table_email_templates_description', TABLE_EMAIL_TEMPLATES_DESCRIPTION);
      $Qtemplate->bindInt(':email_templates_id', $email_templates_id);
      $Qtemplate->execute();
      
      while ($Qtemplate->next()) {
        $data["email_title[" . $Qtemplate->valueInt('language_id') . "]"] = $Qtemplate->value('email_title');
        $data["email_content[" . $Qtemplate->valueInt('language_id') . "]"] = $Qtemplate->value('email_content');
      }
      
      $response = array('success' => true, 'data' => $data);
       
      echo $toC_Json->encode($response);    
    }
   
    function getVariables() {
      global $toC_Json;

      $keywords = toC_Email_Templates_Admin:: getKeywords($_REQUEST['email_templates_name']);
      
      $records = array();
      foreach ($keywords as $key => $value) {
        $records[] = array('id' => $key, 'value' => $value);
      }

      $response = array(EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response);
    }
    
    function saveEmailTemplate() {
      global $toC_Json, $osC_Language;
      
      $email_templates_id = ( isset($_REQUEST['email_templates_id']) && is_numeric($_REQUEST['email_templates_id']) )? $_REQUEST['email_templates_id'] : null;
      
      $data = array('email_templates_status' => $_REQUEST['email_templates_status'],
                    'email_title' => $_REQUEST['email_title'],
                    'email_content' => $_REQUEST['email_content']);
                         
      $error = false;
      $feedback = array();

      foreach ( $data['email_title'] as $key => $value ) {
        if ( empty($value) ) {
          $feedback[] = $osC_Language->get('ms_error_email_title_empty').'('.$osC_Language->getData($key, 'name').')';
          $error = true;
        }
      }
      
     foreach ( $data['email_content'] as $key => $value ) {
       if ( empty($value) ) {
         $feedback[] = $osC_Language->get('ms_error_email_content_empty').'('.$osC_Language->getData($key, 'name').')';
         $error = true;
       }
     }
     
     if ( $error === false ) {
       if ( toC_Email_Templates_Admin::save($email_templates_id, $data) ) {
         $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
       } else {
         $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
       }
     } else {
       $response['success'] = false;
       $response['feedback'] = $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback);
     }
     
     echo $toC_Json->encode($response);
    }
  }
?>
