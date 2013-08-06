<?php
/*
  $Id: configuration_wizard.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/desktop_settings.php');
  
  class toC_Json_Configuration_Wizard {
    
    function loadCardsInformation() {
      global $toC_Json;
      
      $data = array('STORE_NAME' => STORE_NAME,
                    'STORE_OWNER' => STORE_OWNER,
                    'STORE_COUNTRY' => STORE_COUNTRY,
                    'STORE_COUNTRY_NAME' => osC_Address::getCountryName(STORE_COUNTRY),
                    'STORE_ZONE' => STORE_ZONE,
                    'STORE_ZONE_NAME' => osC_Address::getZoneName(STORE_ZONE),
                    'TAX_DECIMAL_PLACES' => TAX_DECIMAL_PLACES,
                    'INVOICE_START_NUMBER' => INVOICE_START_NUMBER,
                    'STORE_NAME_ADDRESS' => STORE_NAME_ADDRESS,
                    'STORE_OWNER_EMAIL_ADDRESS' => STORE_OWNER_EMAIL_ADDRESS,
                    'EMAIL_FROM' => EMAIL_FROM,
                    'SEND_EXTRA_ORDER_EMAILS_TO' => SEND_EXTRA_ORDER_EMAILS_TO,
                    'EMAIL_TRANSPORT' => EMAIL_TRANSPORT,
                    'SMTP_HOST' => SMTP_HOST,
                    'SMTP_PORT' => SMTP_PORT,
                    'SMTP_USERNAME' => SMTP_USERNAME,
                    'SMTP_PASSWORD' => SMTP_PASSWORD,
                    'SEND_EMAILS' => SEND_EMAILS,
                    'SHIPPING_ORIGIN_COUNTRY' => SHIPPING_ORIGIN_COUNTRY,
                    'SHIPPING_ORIGIN_ZIP' => SHIPPING_ORIGIN_ZIP,
                    'SHIPPING_MAX_WEIGHT' => SHIPPING_MAX_WEIGHT,
                    'SHIPPING_BOX_WEIGHT' => SHIPPING_BOX_WEIGHT,
                    'SHIPPING_BOX_PADDING' => SHIPPING_BOX_PADDING,
                    'SHIPPING_WEIGHT_UNIT' => SHIPPING_WEIGHT_UNIT);
		  
      $response = array('success' => true ,'data' => $data);
      
      echo $toC_Json->encode($response);
    }
    
    function saveWizard() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $error = false;
      
      $configurations = $toC_Json->decode($_REQUEST['data']);
      
      foreach($configurations as $index => $configurations){
        $configuration =  get_object_vars($configurations);
        foreach($configuration as $key => $value){
          $Qupdate = $osC_Database->query('update :table_configuration set configuration_value = :configuration_value, last_modified = now() where configuration_key = :configuration_key');
          $Qupdate->bindTable(':table_configuration', TABLE_CONFIGURATION);
          $Qupdate->bindValue(':configuration_value', $value);
          $Qupdate->bindValue(':configuration_key', $key);
          $Qupdate->execute();
          if ($osC_Database->isError()) {
            $error = true;
           
            break;
          }
        }
      }
  
      if ($error === false) {
        require_once('includes/classes/desktop_settings.php');
        $toC_Desktop_Settings = new toC_Desktop_Settings();
        $toC_Desktop_Settings->setWizardComplete(); 
  
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        
        osC_Cache::clear('configuration');
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
      echo $toC_Json->encode($response);
    }
    
    function getCountries(){
      global $toC_Json;
      
      foreach (osC_Address::getCountries() as $country) {
        $countries_array[] = array('id' => $country['id'],
                                   'text' => $country['name']);
      }    
    
      $response = array(EXT_JSON_READER_ROOT => $countries_array);      
      
      echo $toC_Json->encode($response);
    }
    
    function getZones() {
      global $toC_Json;
      
      foreach (osC_Address::getZones($_REQUEST['countries_id']) as $zone) {
        $zones_array[] = array('id' => $zone['id'],
                                         'text' => $zone['name']);
      }
      
      $response = array(EXT_JSON_READER_ROOT => $zones_array);                        

      echo $toC_Json->encode($response);
    }
    
    function getWeightClasses() {
      global $toC_Json;
     
      foreach (osC_Weight::getClasses() as $class) {
        $class_name[] = array('id' => $class['id'],
                              'text' => $class['title']);
      }
      
      $response = array(EXT_JSON_READER_ROOT => $class_name); 
                        
      echo $toC_Json->encode($response);
      
    }
    
    function saveDesktop() {
      
      $data = array('wizardcomplete' => empty($_REQUEST['wizardcomplete']) ? null : $_REQUEST['wizardcomplete']);

      $toC_Desktop_Settings = new toC_Desktop_Settings();
      $toC_Desktop_Settings->save($data);
      
      echo '{success: true}';
      
    }
  }
?>
