<?php
/*
  $Id: change_shipping_method.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Countries_Admin {
    function getData($id) {
      global $osC_Database;

      $Qcountries = $osC_Database->query('select * from :table_countries where countries_id = :countries_id');
      $Qcountries->bindTable(':table_countries', TABLE_COUNTRIES);
      $Qcountries->bindInt(':countries_id', $id);
      $Qcountries->execute();

      $Qzones = $osC_Database->query('select count(*) as total_zones from :table_zones where zone_country_id = :zone_country_id');
      $Qzones->bindTable(':table_zones', TABLE_ZONES);
      $Qzones->bindInt(':zone_country_id', $id);
      $Qzones->execute();

      $data = array_merge($Qcountries->toArray(), $Qzones->toArray());

      $Qzones->freeResult();
      $Qcountries->freeResult();

      return $data;
    }

    function getZoneData($id) {
      global $osC_Database;

      $Qzones = $osC_Database->query('select * from :table_zones where zone_id = :zone_id');
      $Qzones->bindTable(':table_zones', TABLE_ZONES);
      $Qzones->bindInt(':zone_id', $id);
      $Qzones->execute();

      $data = $Qzones->toArray();

      $Qzones->freeResult();

      return $data;
    }

    function save($id = null, $data) {
      global $osC_Database;

      if ( is_numeric($id) && $id>0 ) {
        $Qcountry = $osC_Database->query('update :table_countries set countries_name = :countries_name, countries_iso_code_2 = :countries_iso_code_2, countries_iso_code_3 = :countries_iso_code_3, address_format = :address_format where countries_id = :countries_id');
        $Qcountry->bindInt(':countries_id', $id);
      } else {
        $Qcountry = $osC_Database->query('insert into :table_countries (countries_name, countries_iso_code_2, countries_iso_code_3, address_format) values (:countries_name, :countries_iso_code_2, :countries_iso_code_3, :address_format)');
      }

      $Qcountry->bindTable(':table_countries', TABLE_COUNTRIES);
      $Qcountry->bindValue(':countries_name', $data['name']);
      $Qcountry->bindValue(':countries_iso_code_2', $data['iso_code_2']);
      $Qcountry->bindValue(':countries_iso_code_3', $data['iso_code_3']);
      $Qcountry->bindValue(':address_format', $data['address_format']);
      $Qcountry->setLogging($_SESSION['module'], $id);
      $Qcountry->execute();

      if ( !$osC_Database->isError() ) {
        return true;
      }

      return false;
    }

    function delete($id) {
      global $osC_Database;

      $error = false;

      $osC_Database->startTransaction();

      $Qzones = $osC_Database->query('delete from :table_zones where zone_country_id = :zone_country_id');
      $Qzones->bindTable(':table_zones', TABLE_ZONES);
      $Qzones->bindInt(':zone_country_id', $id);
      $Qzones->setLogging($_SESSION['module'], $id);
      $Qzones->execute();

      if ( !$osC_Database->isError() ) {
        $Qcountry = $osC_Database->query('delete from :table_countries where countries_id = :countries_id');
        $Qcountry->bindTable(':table_countries', TABLE_COUNTRIES);
        $Qcountry->bindInt(':countries_id', $id);
        $Qcountry->setLogging($_SESSION['module'], $id);
        $Qcountry->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
        }
      } else {
        $error = true;
      }

      if ( $error === false ) {
        $osC_Database->commitTransaction();

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }

    function saveZone($id = null, $data) {
      global $osC_Database;

      if ( is_numeric($id) && $id>0 ) {
        $Qzone = $osC_Database->query('update :table_zones set zone_name = :zone_name, zone_code = :zone_code, zone_country_id = :zone_country_id where zone_id = :zone_id');
        $Qzone->bindInt(':zone_id', $id);
      } else {
        $Qzone = $osC_Database->query('insert into :table_zones (zone_name, zone_code, zone_country_id) values (:zone_name, :zone_code, :zone_country_id)');
      }
      $Qzone->bindTable(':table_zones', TABLE_ZONES);
      $Qzone->bindValue(':zone_name', $data['name']);
      $Qzone->bindValue(':zone_code', $data['code']);
      $Qzone->bindInt(':zone_country_id', $data['country_id']);
      $Qzone->setLogging($_SESSION['module'], $id);
      $Qzone->execute();

      if ( !$osC_Database->isError() ) {
        return true;
      }

      return false;
    }

    function deleteZone($id) {
      global $osC_Database;

      $Qzone = $osC_Database->query('delete from :table_zones where zone_id = :zone_id');
      $Qzone->bindTable(':table_zones', TABLE_ZONES);
      $Qzone->bindInt(':zone_id', $id);
      $Qzone->setLogging($_SESSION['module'], $id);
      $Qzone->execute();

      if ( !$osC_Database->isError() ) {
        return true;
      }

      return false;
     }
  }
?>
