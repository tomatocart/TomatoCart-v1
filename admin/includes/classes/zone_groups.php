<?php
/*
  $Id: zone_groups.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_ZoneGroups_Admin {
    function getData($id, $key = null) {
      global $osC_Database;

      $Qzones = $osC_Database->query('select * from :table_geo_zones where geo_zone_id = :geo_zone_id');
      $Qzones->bindTable(':table_geo_zones', TABLE_GEO_ZONES);
      $Qzones->bindInt(':geo_zone_id', $id);
      $Qzones->execute();

      $Qentries = $osC_Database->query('select count(*) as total_entries from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id');
      $Qentries->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
      $Qentries->bindInt(':geo_zone_id', $id);
      $Qentries->execute();

      $data = array_merge($Qzones->toArray(), $Qentries->toArray());

      $Qentries->freeResult();
      $Qzones->freeResult();

      if ( empty($key) ) {
        return $data;
      } else {
        return $data[$key];
      }
    }

    function save($id = null, $data) {
      global $osC_Database;

      if ( is_numeric($id) ) {
        $Qzone = $osC_Database->query('update :table_geo_zones set geo_zone_name = :geo_zone_name, geo_zone_description = :geo_zone_description, last_modified = now() where geo_zone_id = :geo_zone_id');
        $Qzone->bindInt(':geo_zone_id', $id);
      } else {
        $Qzone = $osC_Database->query('insert into :table_geo_zones (geo_zone_name, geo_zone_description, date_added) values (:geo_zone_name, :geo_zone_description, now())');
      }

      $Qzone->bindTable(':table_geo_zones', TABLE_GEO_ZONES);
      $Qzone->bindValue(':geo_zone_name', $data['zone_name']);
      $Qzone->bindValue(':geo_zone_description', $data['zone_description']);
      $Qzone->setLogging($_SESSION['module'], $id);
      $Qzone->execute();

      if ( !$osC_Database->isError() ) {
        return true;
      }

      return false;
    }

    function delete($id) {
      global $osC_Database;

      $error = false;

      $osC_Database->startTransaction();

      $Qentry = $osC_Database->query('delete from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id');
      $Qentry->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
      $Qentry->bindInt(':geo_zone_id', $id);
      $Qentry->setLogging($_SESSION['module'], $id);
      $Qentry->execute();

      if ( !$osC_Database->isError() ) {
        $Qzone = $osC_Database->query('delete from :table_geo_zones where geo_zone_id = :geo_zone_id');
        $Qzone->bindTable(':table_geo_zones', TABLE_GEO_ZONES);
        $Qzone->bindInt(':geo_zone_id', $id);
        $Qzone->setLogging($_SESSION['module'], $id);
        $Qzone->execute();

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

    function getEntryData($id) {
      global $osC_Database, $osC_Language;

      $Qentries = $osC_Database->query('select z2gz.*, c.countries_name, z.zone_name from :table_zones_to_geo_zones z2gz left join :table_countries c on (z2gz.zone_country_id = c.countries_id) left join :table_zones z on (z2gz.zone_id = z.zone_id) where z2gz.association_id = :association_id');
      $Qentries->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
      $Qentries->bindTable(':table_zones', TABLE_ZONES);
      $Qentries->bindTable(':table_countries', TABLE_COUNTRIES);
      $Qentries->bindInt(':association_id', $id);
      $Qentries->execute();

      $data = $Qentries->toArray();

      if ( empty($data['countries_name']) ) {
        $data['countries_name'] = $osC_Language->get('all_countries');
      }

      if ( empty($data['zone_name']) ) {
        $data['zone_name'] = $osC_Language->get('all_zones');
      }

      $Qentries->freeResult();

      return $data;
    }

    function saveEntry($id = null, $data) {
      global $osC_Database;

      if ( is_numeric($id) ) {
        $Qentry = $osC_Database->query('update :table_zones_to_geo_zones set zone_country_id = :zone_country_id, zone_id = :zone_id, last_modified = now() where association_id = :association_id');
        $Qentry->bindInt(':association_id', $id);
      } else {
        $Qentry = $osC_Database->query('insert into :table_zones_to_geo_zones (zone_country_id, zone_id, geo_zone_id, date_added) values (:zone_country_id, :zone_id, :geo_zone_id, now())');
        $Qentry->bindInt(':geo_zone_id', $data['group_id']);
      }
      $Qentry->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
      $Qentry->bindInt(':zone_country_id', $data['country_id']);
      $Qentry->bindInt(':zone_id', $data['zone_id']);
      $Qentry->setLogging($_SESSION['module'], $id);
      $Qentry->execute();

      if ( !$osC_Database->isError() ) {
        return true;
      }

      return false;
    }

    function deleteEntry($id) {
      global $osC_Database;

      $Qentry = $osC_Database->query('delete from :table_zones_to_geo_zones where association_id = :association_id');
      $Qentry->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
      $Qentry->bindInt(':association_id', $id);
      $Qentry->setLogging($_SESSION['module'], $id);
      $Qentry->execute();

      if ( !$osC_Database->isError() ) {
        return true;
      }

      return false;
    }
  }
?>
