<?php
/*
  $Id: configuration.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/configuration.php');
  
  class toC_Json_Configuration {

    function listConfigurations() {
      global $toC_Json, $osC_Database;
      
      $Qcfg = $osC_Database->query('select configuration_id, configuration_key, configuration_title, configuration_description, configuration_value, use_function, set_function from :table_configuration where configuration_group_id = :configuration_group_id order by sort_order');
      $Qcfg->bindTable(':table_configuration', TABLE_CONFIGURATION);
      $Qcfg->bindInt(':configuration_group_id', $_REQUEST['gID']);
      $Qcfg->execute();
            
      $keys = array();
      while ($Qcfg->next()) {
        $cfgValue = $Qcfg->value('configuration_value');
        if ( !osc_empty($Qcfg->value('use_function')) ) {
          $cfgValue = osc_call_user_func($Qcfg->value('use_function'), $Qcfg->value('configuration_value'));
        }
        
        $control = array();
        if ( !osc_empty($Qcfg->value('set_function')) ) {
          $control = osc_call_user_func($Qcfg->value('set_function'), $Qcfg->value('configuration_value'), $Qcfg->value('configuration_key'));
        } else {
          $control['type'] = 'textfield';
          $control['name'] = $Qcfg->value('configuration_key');
        }
        $control['id'] = $Qcfg->value('configuration_id');
        $control['title'] = $Qcfg->value('configuration_title');
        $control['value'] = $cfgValue;
        
        $keys[] = $control;
      }

      echo $toC_Json->encode($keys);    
    
    }
    
    function saveConfigurations(){
      global $toC_Json, $osC_Language;
      
      $response['success'] = osC_Configuration_Admin::save($_REQUEST['cID'], $_REQUEST['configuration_value']);
      if ($response['success']) {
        $response['feedback'] = $osC_Language->get('ms_success_action_performed');
      } else {
        $response['feedback'] = $osC_Language->get('ms_error_action_not_performed');
      }   
          
      echo $toC_Json->encode($response);
    }
  }
?>
