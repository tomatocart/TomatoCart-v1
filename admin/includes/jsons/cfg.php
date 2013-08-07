<?php
/*
  $Id: cfg.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
  class toC_Json_Cfg {
    
    function getCountries(){
      global $toC_Json;
      
      foreach (osC_Address::getCountries() as $country) {
        $countries_array[] = array('id' => $country['id'],
                                   'text' => $country['name']);
      }    
    
      $response = array(EXT_JSON_READER_ROOT => $countries_array);      
      echo $toC_Json->encode($response);
    }
    
      
    function getZones(){
      global $toC_Json;
      foreach (osC_Address::getZones() as $zone) {
        if($zone['country_id'] == STORE_COUNTRY) {
	        $zones_array[] = array('id' => $zone['id'],
	                             'text' => $zone['name'],
	                             'group' => $zone['country_name']);
        }
      }    

      $response = array(EXT_JSON_READER_ROOT => $zones_array);                        
      echo $toC_Json->encode($response);
    }
  }
?>
