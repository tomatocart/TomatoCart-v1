<?php
/*
  $Id: countries.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com
  
  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd
  
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/countries.php');
  
  class toC_Json_Countries {
  
    function listCountries() {
      global $toC_Json, $osC_Database;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qcountries = $osC_Database->query('select countries_id,countries_name,countries_iso_code_2,countries_iso_code_3,address_format from :table_countries');
      $Qcountries->bindTable(':table_countries', TABLE_COUNTRIES);
      $Qcountries->setExtBatchLimit($start, $limit);
      $Qcountries->execute();
      
      $records = array();
      while ($Qcountries->next()) {
        $Qzone = $osC_Database->query('select count(*) as totalZones from :table_zones where zone_country_id = :zone_country_id');
        $Qzone->bindTable(':table_zones',TABLE_ZONES);
        $Qzone->bindInt(':zone_country_id',$Qcountries->valueInt('countries_id'));
        $Qzone->execute();
        
        $records[] = array(
          'countries_id' => $Qcountries->value('countries_id'),
          'countries_name' => $Qcountries->value('countries_name'),
          'countries_iso_code' => osc_image('../images/worldflags/' . strtolower($Qcountries->value('countries_iso_code_2')) . '.png') . '&nbsp;&nbsp;' . $Qcountries->value('countries_iso_code_2') . '&nbsp;&nbsp; ' . $Qcountries->value('countries_iso_code_3'),
          'total_zones' => $Qzone->valueInt('totalZones'));
        
        $Qzone->freeResult();
      }
  
      $response = array(EXT_JSON_READER_TOTAL => $Qcountries->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);    
      
                       
      echo $toC_Json->encode($response);
    }
  
    function listZones() {
      global $toC_Json, $osC_Database;
      
      $Qzones = $osC_Database->query('select zone_id,zone_code,zone_name from :table_zones where zone_country_id = :zone_country_id');
      $Qzones->bindTable(':table_zones',TABLE_ZONES);
      $Qzones->bindInt(':zone_country_id',$_REQUEST['countries_id']);
      $Qzones->execute();
      
      $records = array();
      while ($Qzones->next()) {
        $records[] = array('zone_id' => $Qzones->value('zone_id'),
                           'zone_code' => $Qzones->value('zone_code'),
                           'zone_name' => $Qzones->value('zone_name'));
      }
      $Qzones->freeResult();
      
      $response = array(EXT_JSON_READER_ROOT => $records); 
      
      echo $toC_Json->encode($response);
    }
  
    function loadCountry() {
      global $toC_Json;

      $data = osC_Countries_Admin::getData($_REQUEST['countries_id']);
      
      $response = array('success' => true, 'data' => $data);
      
      echo $toC_Json->encode($response);
    }
  
    function saveCountry() {
      global $toC_Json, $osC_Language;
    
      $data = array('name' => $_REQUEST['countries_name'],
                    'iso_code_2' => $_REQUEST['countries_iso_code_2'],
                    'iso_code_3' => $_REQUEST['countries_iso_code_3'],
                    'address_format' => $_REQUEST['address_format'],);
      
      if (osC_Countries_Admin::save(( isset($_REQUEST['countries_id']) && is_numeric($_REQUEST['countries_id']) ) ? $_REQUEST['countries_id'] : null, $data)) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function loadZone() {
      global $toC_Json;
      
      $data = osC_Countries_Admin::getZoneData($_REQUEST['zone_id']);
      
      $response = array('success' => true, 'data' => $data);
      
      echo $toC_Json->encode($response);
    }
      
    function saveZone() {
      global $osC_Language, $toC_Json;
      
      $data = array('name' => $_REQUEST['zone_name'],
                    'code' => $_REQUEST['zone_code'],
                    'country_id' => $_REQUEST['countries_id']);
      
      if ( osC_Countries_Admin::saveZone(( isset($_REQUEST['zone_id']) && is_numeric($_REQUEST['zone_id']) ) ? $_REQUEST['zone_id'] : null, $data) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
  
    function deleteCountry() {
      global $osC_Language, $toC_Json, $osC_Database;
      
      $error = false;
      $feedback = array();
      
      $Qcheck = $osC_Database->query('select count(*) as total from :table_address_book where entry_country_id = :entry_country_id');
      $Qcheck->bindTable(':table_address_book', TABLE_ADDRESS_BOOK);
      $Qcheck->bindInt(':entry_country_id', $_REQUEST['countries_id']);
      $Qcheck->execute();      
    
      if ( $Qcheck->valueInt('total') > 0 ) {
        $error = true;
        $feedback[] = sprintf($osC_Language->get('delete_warning_country_in_use_address_book'), $Qcheck->valueInt('total'));
      }      
      
      $Qcheck = $osC_Database->query('select count(*) as total from :table_zones_to_geo_zones where zone_country_id = :zone_country_id');
      $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
      $Qcheck->bindInt(':zone_country_id', $_REQUEST['countries_id']);
      $Qcheck->execute();      
    
      if ( $Qcheck->valueInt('total') > 0 ) {
        $error = true;
        $feedback[] = sprintf($osC_Language->get('delete_warning_country_in_use_tax_zone'), $Qcheck->valueInt('total'));
      }      
      
      if ($error === false) {
        if (osC_Countries_Admin::delete($_REQUEST['countries_id'])) {
          $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }      
      
      echo $toC_Json->encode($response);
    }    
    
    function deleteZone(){
      global $osC_Language, $toC_Json, $osC_Database;
      
      $error = false;
      $feedback = array();
      
      $Qcheck = $osC_Database->query('select count(*) as total from :table_address_book where entry_zone_id = :entry_zone_id');
      $Qcheck->bindTable(':table_address_book', TABLE_ADDRESS_BOOK);
      $Qcheck->bindInt(':entry_zone_id', $_REQUEST['zone_id']);
      $Qcheck->execute();      
  
      if ( $Qcheck->valueInt('total') > 0 ) {
        $error = true;
        $feedback[] = sprintf($osC_Language->get('delete_warning_zone_in_use_address_book'), $Qcheck->valueInt('total'));
      }
      
      $Qcheck = $osC_Database->query('select count(*) as total from :table_zones_to_geo_zones where zone_id = :zone_id');
      $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
      $Qcheck->bindInt(':zone_id', $_REQUEST['zone_id']);
      $Qcheck->execute();

      if ( $Qcheck->valueInt('total') > 0 ) {
        $error = true;
        $feedback[] = sprintf($osC_Language->get('delete_warning_zone_in_use_tax_zone'), $Qcheck->valueInt('total'));
      }      
      
      if ($error === false) {
        if (osC_Countries_Admin::deleteZone($_REQUEST['zone_id'])) {
          $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }       
      
      echo $toC_Json->encode($response);
    }
    
    function deleteZones(){
      global $osC_Language, $toC_Json, $osC_Database;
      
      $error = false;
      $feedback = array();
      $check_tax_zones_flag = array();
      $check_address_book_flag = array();
            
      $batch = explode(',', $_REQUEST['batch']);
      
      $Qzones = $osC_Database->query('select zone_id, zone_name, zone_code from :table_zones where zone_id in (":zone_id") order by zone_name');
      $Qzones->bindTable(':table_zones', TABLE_ZONES);
      $Qzones->bindRaw(':zone_id', implode('", "', array_unique(array_filter(array_slice($batch, 0, MAX_DISPLAY_SEARCH_RESULTS), 'is_numeric'))));
      $Qzones->execute();
          
      while ($Qzones->next()) {
        $Qcheck = $osC_Database->query('select address_book_id from :table_address_book where entry_zone_id = :entry_zone_id limit 1');
        $Qcheck->bindTable(':table_address_book', TABLE_ADDRESS_BOOK);
        $Qcheck->bindInt(':entry_zone_id', $Qzones->valueInt('zone_id'));
        $Qcheck->execute();
    
        if ( $Qcheck->numberOfRows() === 1 ) {
          $error = true;
          $check_address_book_flag[] = $Qzones->value('zone_name');
        }
        
        $Qcheck = $osC_Database->query('select association_id from :table_zones_to_geo_zones where zone_id = :zone_id limit 1');
        $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
        $Qcheck->bindInt(':zone_id', $Qzones->valueInt('zone_id'));
        $Qcheck->execute();
      
        if ( $Qcheck->numberOfRows() === 1 ) {
          $error = true;
          $check_tax_zones_flag[] = $Qzones->value('zone_name');
        }
      }

      if ( !empty($check_address_book_flag) ) {
        $feedback[] = $osC_Language->get('batch_delete_warning_zone_in_use_address_book') . '<p>' . implode(', ', $check_address_book_flag) . '</p>';
      }
      
      if ( !empty($check_tax_zones_flag) ) {
        $feedback[] = $osC_Language->get('batch_delete_warning_zone_in_use_tax_zone') . '<p>' . implode(', ', $check_tax_zones_flag) . '</p>';
      }      
      
      if ($error === false) {
        foreach ($batch as $id) {
          if ( !osC_Countries_Admin::deleteZone($id) ) {
            $error = true;
            break;
          }
        }
      
        if ($error === false) {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }
      
      echo $toC_Json->encode($response);
    }
  }
?>
