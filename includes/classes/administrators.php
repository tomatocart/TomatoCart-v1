<?php
/*
  $Id: administrators.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Administrators {
  
    function login($user_name, $user_password) {
      global $osC_Database;
      
      $response = array();
      if ( !empty($user_name) && !empty($user_password) ) {
        $Qadmin = $osC_Database->query('select id, user_name, user_password from :table_administrators where user_name = :user_name');
        $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
        $Qadmin->bindValue(':user_name', $user_name);
        $Qadmin->execute();
        
        if ( $Qadmin->numberOfRows() > 0) {
          if ( osc_validate_password($user_password, $Qadmin->value('user_password')) ) {
            $_SESSION['admin'] = array('id' => $Qadmin->valueInt('id'),
                                       'username' => $Qadmin->value('user_name'));

            return true;
          }
        } 
      }
      
      return false;
    }
  }
?>
