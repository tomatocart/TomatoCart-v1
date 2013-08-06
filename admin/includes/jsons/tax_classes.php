<?php
/*
  $Id: tax_classes.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/tax.php');

  class toC_Json_Tax_Classes {
        
    function listTaxClasses() {
      global $toC_Json, $osC_Database;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qclasses = $osC_Database->query('select tax_class_id, tax_class_title, tax_class_description, last_modified, date_added from :table_tax_class order by tax_class_title');
      $Qclasses->bindTable(':table_tax_class', TABLE_TAX_CLASS);
      $Qclasses->setExtBatchLimit($start, $limit);
      $Qclasses->execute();
            
      $records = array();     
      while ( $Qclasses->next() ) {
        $Qrates = $osC_Database->query('select count(*) as total_rates from :table_tax_rates where tax_class_id = :tax_class_id');
        $Qrates->bindTable(':table_tax_rates', TABLE_TAX_RATES);
        $Qrates->bindInt(':tax_class_id', $Qclasses->valueInt('tax_class_id'));
        $Qrates->execute();
           
        $records[] = array(
          'tax_class_id' => $Qclasses->value('tax_class_id'),
          'tax_class_title' => $Qclasses->value('tax_class_title'),
          'tax_total_rates' => $Qrates->valueInt('total_rates')
        );
        $Qrates->freeResult();    
      }

      $response = array(EXT_JSON_READER_TOTAL => $Qclasses->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }          
    
    function listTaxRates() {
      global $toC_Json, $osC_Database;
      
      $Qrates = $osC_Database->query('select r.tax_rates_id, r.tax_priority, r.tax_rate, r.tax_description, r.date_added, r.last_modified, z.geo_zone_id, z.geo_zone_name from :table_tax_rates r, :table_geo_zones z where r.tax_class_id = :tax_class_id and r.tax_zone_id = z.geo_zone_id order by r.tax_priority, z.geo_zone_name');
      $Qrates->bindTable(':table_tax_rates', TABLE_TAX_RATES);
      $Qrates->bindTable(':table_geo_zones', TABLE_GEO_ZONES);
      $Qrates->bindInt(':tax_class_id', $_REQUEST['tax_class_id']);
      $Qrates->execute();    
      
      $records = array();
      while ( $Qrates->next() ) {
        $records[] = array(
          'tax_rates_id' => $Qrates->value('tax_rates_id'),
          'tax_priority' => $Qrates->value('tax_priority'),
          'tax_rate' => $Qrates->value('tax_rate'),
          'geo_zone_name' => $Qrates->value('geo_zone_name')
        );     
      }
      $Qrates->freeResult(); 
      
      $response = array(EXT_JSON_READER_TOTAL => $Qrates->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
                          
      echo $toC_Json->encode($response);     
    }
    
    function listGeoZones() {
      global $toC_Json, $osC_Database;
      
      $Qzones = $osC_Database->query('select geo_zone_id,geo_zone_name from :table_geo_zones');
      $Qzones->bindTable(':table_geo_zones',TABLE_GEO_ZONES);
      $Qzones->execute();
      
      $records = array();
      while ($Qzones->next()) {
        $records[] = array(
          'geo_zone_id' => $Qzones->value('geo_zone_id'),
          'geo_zone_name' => $Qzones->value('geo_zone_name')
        );                     
      }        
      $Qzones->freeResult();
        
      $response = array(EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response);
    }

    function loadTaxClass() {
      global $toC_Json;
     
      $data = osC_Tax_Admin::getData($_REQUEST['tax_class_id']);       
      
      $response = array('success' => true, 'data' => $data);
    
      echo $toC_Json->encode($response);   
    }
   
    function loadTaxRate() {
      global $toC_Json;
     
      $data = osC_Tax_Admin::getEntryData($_REQUEST['tax_rates_id']);
      
      $response = array('success' => true, 'data' => $data);
      
      echo $toC_Json->encode($response);
    }

    function saveTaxClass() {
      global $toC_Json, $osC_Language;
     
      $data = array('title' => $_REQUEST['tax_class_title'], 
                    'description' => $_REQUEST['tax_class_description']);
     
      if ( osC_Tax_Admin::save( ( isset($_REQUEST['tax_class_id'] ) && is_numeric( $_REQUEST['tax_class_id'] ) ) ? $_REQUEST['tax_class_id'] : null, $data) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));      
      }
     
      echo $toC_Json->encode($response);
    }
   
    function saveTaxRate() {
      global $toC_Json, $osC_Language;

      $data = array('zone_id' => $_REQUEST['geo_zone_id'], 
                    'tax_class_id' => $_REQUEST['tax_class_id'], 
                    'rate' => $_REQUEST['tax_rate'], 
                    'description' => $_REQUEST['tax_description'], 
                    'priority' => $_REQUEST['tax_priority']);
       
      if ( osC_Tax_Admin::saveEntry(( isset($_REQUEST['tax_rates_id']) && is_numeric($_REQUEST['tax_rates_id']) ) ? $_REQUEST['tax_rates_id'] : null, $data) ) {      
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      } 
        
      echo $toC_Json->encode($response);     
    }    
  
    function deleteTaxClass() {
      global $toC_Json, $osC_Language, $osC_Database;
     
      $error = false;
      $feedback = array();
      
      $Qcheck = $osC_Database->query('select products_id from :table_products where products_tax_class_id = :products_tax_class_id limit 1');
      $Qcheck->bindTable(':table_products', TABLE_PRODUCTS);
      $Qcheck->bindInt(':products_tax_class_id', $_REQUEST['tax_class_id']);
      $Qcheck->execute();
    
      if ( $Qcheck->numberOfRows() > 0 ) {
        $error = true;
        $feedback[] = sprintf($osC_Language->get('delete_warning_tax_class_in_use'), $Qcheck->numberOfRows());
      } 
      
      if ($error === false) {
        if (osC_Tax_Admin::delete($_REQUEST['tax_class_id'])) {
          $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }        
      
      echo $toC_Json->encode($response);                            
    }
    
    function deleteTaxRate() {
      global $toC_Json, $osC_Language;
     
      if ( osC_Tax_Admin::deleteEntry($_REQUEST['rateId']) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
       
      echo $toC_Json->encode($response);               
    }       
    
    function deleteTaxRates() {
      global $toC_Json, $osC_Language;
     
      $error = false;
     
      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if (!osC_Tax_Admin::deleteEntry($id)) {
          $error = true;
          break;
        }
      }

      if ($error === false) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
       
      echo $toC_Json->encode($response);               
    }       
  }
?>
