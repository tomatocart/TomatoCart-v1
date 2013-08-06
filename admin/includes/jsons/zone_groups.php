<?php
/*
  $Id: zone_groups.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/zone_groups.php');

  class toC_Json_Zone_Groups {

    function listZoneGroups() {
      global $toC_Json, $osC_Database;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
    
      $Qzones = $osC_Database->query('select * from :table_geo_zones order by geo_zone_name');
      $Qzones->bindTable(':table_geo_zones', TABLE_GEO_ZONES);
      $Qzones->setExtBatchLimit($start, $limit);
      $Qzones->execute();
      
      $records = array();
      while ($Qzones->next()) {
        $Qentries = $osC_Database->query('select count(*) geo_zone_entries from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id');
        $Qentries->bindTable(':table_zones_to_geo_zones',TABLE_ZONES_TO_GEO_ZONES);
        $Qentries->bindInt(':geo_zone_id', $Qzones->value('geo_zone_id'));
        $Qentries->execute();
        
        $records[] = array( 'geo_zone_id' => $Qzones->value('geo_zone_id'),
                            'geo_zone_name' => $Qzones->value('geo_zone_name'),
                            'geo_zone_entries' => $Qentries->value('geo_zone_entries'));         
      }
      
      $response = array(EXT_JSON_READER_TOTAL => $Qzones->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records); 
      
      echo $toC_Json->encode($response);
    }
      
    function listZoneEntries(){
      global $toC_Json, $osC_Database, $osC_Language;
          
      $Qentries = $osC_Database->query('select z2gz.association_id, z2gz.zone_country_id countries_id, c.countries_name, z2gz.zone_id, z2gz.geo_zone_id, z2gz.last_modified, z2gz.date_added, z.zone_name from :table_zones_to_geo_zones z2gz left join :table_countries c on (z2gz.zone_country_id = c.countries_id) left join :table_zones z on (z2gz.zone_id = z.zone_id) where z2gz.geo_zone_id = :geo_zone_id order by c.countries_name, z.zone_name');
      $Qentries->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
      $Qentries->bindTable(':table_zones', TABLE_ZONES);
      $Qentries->bindTable(':table_countries', TABLE_COUNTRIES);
      $Qentries->bindInt(':geo_zone_id', $_REQUEST['geo_zone_id']);
      $Qentries->execute();  
      
      $records = array();
      while ($Qentries->next()) {       
        $records[] = array(
          'geo_zone_entry_id' => $Qentries->value('association_id'),
          'countries_id' => $Qentries->value('countries_id'),
          'zone_id' => $Qentries->value('zone_id'),
          'countries_name' => (($Qentries->value('countries_id') > 0) ? $Qentries->value('countries_name') : $osC_Language->get('all_countries')),
          'zone_name' => (($Qentries->value('zone_id') > 0) ? $Qentries->value('zone_name') : $osC_Language->get('all_zones')));
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records); 
                  
      echo $toC_Json->encode($response);
    }
        
    function loadZoneGroup() {
      global $toC_Json;
      
      $data = osC_ZoneGroups_Admin::getData($_REQUEST['geo_zone_id']);
      
      $response = array('success' => true, 'data' => $data); 
      
      echo $toC_Json->encode($response);
    }
  
    function saveZoneGroup() {
      global $toC_Json, $osC_Language;
      
      $data = array('zone_name' => $_REQUEST['geo_zone_name'], 
                    'zone_description' => $_REQUEST['geo_zone_description']);
    
      if ( osC_ZoneGroups_Admin::save((isset($_REQUEST['geo_zone_id']) && is_numeric($_REQUEST['geo_zone_id']) && $_REQUEST['geo_zone_id'] != -1 ? $_REQUEST['geo_zone_id'] : null), $data) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
      
      echo $toC_Json->encode($response);
    }
   
    function deleteZoneGroup() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $error = false;
      $feedback = array();
      
      $Qcheck = $osC_Database->query('select tax_zone_id from :table_tax_rates where tax_zone_id = :tax_zone_id limit 1');
      $Qcheck->bindTable(':table_tax_rates', TABLE_TAX_RATES);
      $Qcheck->bindInt(':tax_zone_id', $_REQUEST['geo_zone_id']);
      $Qcheck->execute();
      
      if ( $Qcheck->numberOfRows() > 0 ) {
        $error = true;
        $feedback[] = sprintf($osC_Language->get('delete_warning_group_in_use_tax_rate'), $Qcheck->numberOfRows());
      }

      if ($error === false) {
        if (osC_ZoneGroups_Admin::delete($_REQUEST['geo_zone_id'])) {
          $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }        
        
      echo $toC_Json->encode($response);
    }
    
    function loadZoneEntry() {
      global $toC_Json, $osC_Language;
      
      $data = osC_ZoneGroups_Admin::getEntryData($_REQUEST['geo_zone_entry_id']);
      
      $response = array('success' => true, 'data' => $data); 
      
      echo $toC_Json->encode($response);
    }
    
    
    function listCountries() {
      global $toC_Json, $osC_Database, $osC_Language;
     
      $Qentries = $osC_Database->query('select countries_name,countries_id from :table_countries');
      $Qentries->bindTable(':table_countries',TABLE_COUNTRIES);
      $Qentries->execute(); 
  
      $records = array(array('countries_id' => '0',
                             'countries_name' => $osC_Language->get('all_countries')));
      while ($Qentries->next()) {
        $records[] = array('countries_id' => $Qentries->value('countries_id'),
                           'countries_name' => $Qentries->value('countries_name'));
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records); 
      
      echo $toC_Json->encode($response); 
    }
    
    
    function listZones() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      if(isset($_REQUEST['countries_id'])){
        $Qentries = $osC_Database->query('select zone_id,zone_name from :table_zone where zone_country_id=:country_id');
        $Qentries->bindTable(':table_zone',TABLE_ZONES);
        $Qentries->bindInt(':country_id', $_REQUEST['countries_id']);
        $Qentries->execute(); 
  
        $records = array(array('zone_id' => '0',
                               'zone_name' => $osC_Language->get('all_zones')));
        while ($Qentries->next()) {
          $records[] = array('zone_id' => $Qentries->value('zone_id'),
                             'zone_name' => $Qentries->value('zone_name'));
        }
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records); 
                  
      echo $toC_Json->encode($response); 
    }
    
    function saveZoneEntry() {
      global $toC_Json, $osC_Language;
      
      $data = array('group_id' => $_REQUEST['geo_zone_id'],
                    'country_id' => $_REQUEST['countries_id'],
                    'zone_id' => $_REQUEST['zone_id']);
      
      if ( osC_ZoneGroups_Admin::saveEntry((isset($_REQUEST['geo_zone_entry_id']) && is_numeric($_REQUEST['geo_zone_entry_id']) ? $_REQUEST['geo_zone_entry_id'] : null), $data) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteZoneEntry() {
      global $toC_Json, $osC_Language;
     
      if ( osC_ZoneGroups_Admin::deleteEntry($_REQUEST['geo_zone_entry_id']) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
      
      echo $toC_Json->encode($response);               
    }  
    
    function deleteZoneEntries() {
      global $toC_Json, $osC_Language;
     
      $error = false;
      
      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if (!osC_ZoneGroups_Admin::deleteEntry($id)) {
          $error = true;
          break;
        }
      }
      
      if ($error === false) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false ,'feedback' => $osC_Language->get('ms_error_action_not_performed'));               
      }
      
      echo $toC_Json->encode($response);               
    }  
  }
?>
