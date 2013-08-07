<?php
/*
  $Id: address.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Address {
    function format($address, $new_line = "\n") {
      global $osC_Database;

      $address_format = '';

      if (is_numeric($address)) {
        $Qaddress = $osC_Database->query('select ab.entry_firstname as firstname, ab.entry_lastname as lastname, ab.entry_company as company, ab.entry_street_address as street_address, ab.entry_suburb as suburb, ab.entry_city as city, ab.entry_postcode as postcode, ab.entry_state as state, ab.entry_zone_id as zone_id, ab.entry_country_id as country_id, ab.entry_telephone as telephone_number, z.zone_code as zone_code, c.countries_name as country_title from :table_address_book ab left join :table_zones z on (ab.entry_zone_id = z.zone_id), :table_countries c where ab.address_book_id = :address_book_id and ab.entry_country_id = c.countries_id');
        $Qaddress->bindTable(':table_address_book', TABLE_ADDRESS_BOOK);
        $Qaddress->bindTable(':table_zones', TABLE_ZONES);
        $Qaddress->bindTable(':table_countries', TABLE_COUNTRIES);
        $Qaddress->bindInt(':address_book_id', $address);
        $Qaddress->execute();

        $address = $Qaddress->toArray();
      }

      $firstname = $lastname = '';

      if (isset($address['firstname']) && !empty($address['firstname'])) {
        $firstname = $address['firstname'];
        $lastname = $address['lastname'];
      } elseif (isset($address['name']) && !empty($address['name'])) {
        $firstname = $address['name'];
      }

      $state = $address['state'];
      $state_code = $address['zone_code'];

      if (isset($address['zone_id']) && is_numeric($address['zone_id']) && ($address['zone_id'] > 0)) {
        $state = osC_Address::getZoneName($address['zone_id']);
        $state_code = osC_Address::getZoneCode($address['zone_id']);
      }

      $country = $address['country_title'];

      if (empty($country) && isset($address['country_id']) && is_numeric($address['country_id']) && ($address['country_id'] > 0)) {
        $country = osC_Address::getCountryName($address['country_id']);
      }

      if (isset($address['format'])) {
        $address_format = $address['format'];
      } elseif (isset($address['country_id']) && is_numeric($address['country_id']) && ($address['country_id'] > 0)) {
        $address_format = osC_Address::getFormat($address['country_id']);
      }

      if (empty($address_format)) {
        $address_format = ":name\n:street_address\n:postcode :city\n:country";
      }
      
      if ( defined('DISPLAY_TELEPHONE_NUMBER') && ((int)DISPLAY_TELEPHONE_NUMBER == 1) ) {
        if (strpos($address_format, 'telephone_number') == false) {
          $address_format .= "\n:telephone_number";
        }
      }
      
      $find_array = array('/\:name\b/',
                          '/\:street_address\b/',
                          '/\:suburb\b/',
                          '/\:city\b/',
                          '/\:postcode\b/',
                          '/\:state\b/',
                          '/\:state_code\b/',
                          '/\:country\b/', 
                          '/\:telephone_number\b/');
      
      $replace_array = array(osc_output_string_protected($firstname . ' ' . $lastname),
                             osc_output_string_protected($address['street_address']),
                             osc_output_string_protected($address['suburb']),
                             osc_output_string_protected($address['city']),
                             osc_output_string_protected($address['postcode']),
                             osc_output_string_protected($state),
                             osc_output_string_protected($state_code),
                             osc_output_string_protected($country), 
                             osc_output_string_protected($address['telephone_number']));
                             
      $formated = preg_replace($find_array, $replace_array, $address_format);
      
      if ( (ACCOUNT_COMPANY > -1) && !empty($address['company']) ) {
        $company = osc_output_string_protected($address['company']);

        $formated = $company . $new_line . $formated;
      }

      if ($new_line != "\n") {
        $formated = str_replace("\n", $new_line, $formated);
      }
      
      return $formated;
    }

    function getCountries() {
      global $osC_Database;

      static $_countries;

      if (!isset($_countries)) {
        $_countries = array();

        $Qcountries = $osC_Database->query('select * from :table_countries order by countries_name');
        $Qcountries->bindTable(':table_countries', TABLE_COUNTRIES);
        $Qcountries->execute();

        while ($Qcountries->next()) {
          $_countries[] = array('id' => $Qcountries->valueInt('countries_id'),
                                'name' => $Qcountries->value('countries_name'),
                                'iso_2' => $Qcountries->value('countries_iso_code_2'),
                                'iso_3' => $Qcountries->value('countries_iso_code_3'),
                                'format' => $Qcountries->value('address_format'));
        }

        $Qcountries->freeResult();
      }

      return $_countries;
    }

    function getCountryName($id) {
      global $osC_Database;

      $Qcountry = $osC_Database->query('select countries_name from :table_countries where countries_id = :countries_id');
      $Qcountry->bindTable(':table_countries', TABLE_COUNTRIES);
      $Qcountry->bindInt(':countries_id', $id);
      $Qcountry->execute();

      return $Qcountry->value('countries_name');
    }

    function getCountryIsoCode2($id) {
      global $osC_Database;

      $Qcountry = $osC_Database->query('select countries_iso_code_2 from :table_countries where countries_id = :countries_id');
      $Qcountry->bindTable(':table_countries', TABLE_COUNTRIES);
      $Qcountry->bindInt(':countries_id', $id);
      $Qcountry->execute();

      return $Qcountry->value('countries_iso_code_2');
    }

    function getCountryIsoCode3($id) {
      global $osC_Database;

      $Qcountry = $osC_Database->query('select countries_iso_code_3 from :table_countries where countries_id = :countries_id');
      $Qcountry->bindTable(':table_countries', TABLE_COUNTRIES);
      $Qcountry->bindInt(':countries_id', $id);
      $Qcountry->execute();

      return $Qcountry->value('countries_iso_code_3');
    }

    function getFormat($id) {
      global $osC_Database;

      $Qcountry = $osC_Database->query('select address_format from :table_countries where countries_id = :countries_id');
      $Qcountry->bindTable(':table_countries', TABLE_COUNTRIES);
      $Qcountry->bindInt(':countries_id', $id);
      $Qcountry->execute();

      return $Qcountry->value('address_format');
    }

    function getZoneName($id) {
      global $osC_Database;

      $Qzone = $osC_Database->query('select zone_name from :table_zones where zone_id = :zone_id');
      $Qzone->bindTable(':table_zones', TABLE_ZONES);
      $Qzone->bindInt(':zone_id', $id);
      $Qzone->execute();

      return $Qzone->value('zone_name');
    }

    function getZoneCode($id) {
      global $osC_Database;

      $Qzone = $osC_Database->query('select zone_code from :table_zones where zone_id = :zone_id');
      $Qzone->bindTable(':table_zones', TABLE_ZONES);
      $Qzone->bindInt(':zone_id', $id);
      $Qzone->execute();

      return $Qzone->value('zone_code');
    }

    function getZones($id = null) {
      global $osC_Database;

      $zones_array = array();

      $Qzones = $osC_Database->query('select z.zone_code, z.zone_id, z.zone_country_id, z.zone_name, c.countries_name from :table_zones z, :table_countries c where');

      if (!empty($id)) {
        $Qzones->appendQuery('z.zone_country_id = :zone_country_id and');
        $Qzones->bindInt(':zone_country_id', $id);
      }

      $Qzones->appendQuery('z.zone_country_id = c.countries_id order by c.countries_name, z.zone_name');
      $Qzones->bindTable(':table_countries', TABLE_COUNTRIES);
      $Qzones->bindTable(':table_zones', TABLE_ZONES);
      $Qzones->execute();

      while ($Qzones->next()) {
        $zones_array[] = array('id' => $Qzones->valueInt('zone_id'),
                               'code' => $Qzones->value('zone_code'),
                               'name' => $Qzones->value('zone_name'),
                               'country_id' => $Qzones->valueInt('zone_country_id'),
                               'country_name' => $Qzones->value('countries_name'));
      }

      return $zones_array;
    }
  }
?>
