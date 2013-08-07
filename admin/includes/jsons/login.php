<?php
/*
  $Id: articles.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/administrators.php');
  
  class toC_Json_Login {
    function login() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $Qcheck_session = $osC_Database->query('select count(*) from :table_sessions');
      $Qcheck_session->bindTable(':table_sessions', TABLE_SESSIONS);
      $Qcheck_session->execute();
      
      if ($osC_Database->isError() || $Qcheck_session->numberOfRows() < 1) {
        $Qrepaire = $osC_Database->query('repair table :table_sessions');
        $Qrepaire->bindTable(':table_sessions', TABLE_SESSIONS);
        $Qrepaire->execute();
        
        $Qrepaire->freeResult();
      }
      
      $Qcheck_session->freeResult();
      
      $response = array();
      if ( !empty($_REQUEST['user_name']) && !empty($_REQUEST['user_password']) ) {
        $Qadmin = $osC_Database->query('select id, user_name, user_password from :table_administrators where user_name = :user_name');
        $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
        $Qadmin->bindValue(':user_name', $_REQUEST['user_name']);
        $Qadmin->execute();
        
        if ( $Qadmin->numberOfRows() > 0) {
          while($Qadmin->next()) {
            if ( osc_validate_password($_REQUEST['user_password'], $Qadmin->value('user_password')) ) {
              $_SESSION['admin'] = array('id' => $Qadmin->valueInt('id'),
                                         'username' => $Qadmin->value('user_name'),
                                         'access' => osC_Access::getUserLevels($Qadmin->valueInt('id')));
              
              $response['success'] = true;
              echo $toC_Json->encode($response);
              exit;
            }
          }
        } 
      }
      
      $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_login_invalid'));
      echo $toC_Json->encode($response);
    }
    
    function logoff() {
      global $toC_Json, $osC_Language;

      unset($_SESSION['admin']);

      $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_logged_out'));  
                
      echo $toC_Json->encode($response);
    }

    function getPassword() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $error = false;
      $feedback = '';
      
      $email = $_REQUEST['email_address'];
      
      if (!osc_validate_email_address($email)) {
        $error = true;
        $feedback = $osC_Language->get('ms_error_wrong_email_address');
      } else if(!osC_Administrators_Admin::checkEmail($email)) {
        $error = true;
        $feedback = $osC_Language->get('ms_error_email_not_exist');
      }
      
      if ($error === false) {
        if( !osC_Administrators_Admin::generatePassword($email) ) {
          $error = true;
          $feedback = $osC_Language->get('ms_error_email_send_failure');
        }
      }
      
      if($error == false) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $feedback);             
      }
      
      echo $toC_Json->encode($response);        
    }
  }
?>