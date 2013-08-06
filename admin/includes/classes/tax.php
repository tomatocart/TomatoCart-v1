<?php
/*
  $Id: tax.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('../includes/classes/tax.php');

  class osC_Tax_Admin extends osC_Tax {
    var $tax_rates;

// class constructor
    function osC_Tax_Admin() {
      $this->tax_rates = array();
    }

// class methods
    function getTaxRate($class_id, $country_id = null, $zone_id = null) {
      global $osC_Database;

      if (empty($country_id) && empty($zone_id)) {
        $country_id = STORE_COUNTRY;
        $zone_id = STORE_ZONE;
      }

      if (isset($this->tax_rates[$class_id][$country_id][$zone_id]['rate']) == false) {
        $Qtax = $osC_Database->query('select sum(tax_rate) as tax_rate from :table_tax_rates tr left join :table_zones_to_geo_zones za on (tr.tax_zone_id = za.geo_zone_id) left join :table_geo_zones tz on (tz.geo_zone_id = tr.tax_zone_id) where (za.zone_country_id is null or za.zone_country_id = 0 or za.zone_country_id = :zone_country_id) and (za.zone_id is null or za.zone_id = 0 or za.zone_id = :zone_id) and tr.tax_class_id = :tax_class_id group by tr.tax_priority');
        $Qtax->bindTable(':table_tax_rates', TABLE_TAX_RATES);
        $Qtax->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
        $Qtax->bindTable(':table_geo_zones', TABLE_GEO_ZONES);
        $Qtax->bindInt(':zone_country_id', $country_id);
        $Qtax->bindInt(':zone_id', $zone_id);
        $Qtax->bindInt(':tax_class_id', $class_id);
        $Qtax->execute();

        if ($Qtax->numberOfRows()) {
          $tax_multiplier = 1.0;
          while ($Qtax->next()) {
            $tax_multiplier *= 1.0 + ($Qtax->value('tax_rate') / 100);
          }

          $tax_rate = ($tax_multiplier - 1.0) * 100;
        } else {
          $tax_rate = 0;
        }

        $this->tax_rates[$class_id][$country_id][$zone_id]['rate'] = $tax_rate;
      }

      return $this->tax_rates[$class_id][$country_id][$zone_id]['rate'];
    }

    function getData($id, $key = null) {
      global $osC_Database;

      $Qclasses = $osC_Database->query('select * from :table_tax_class where tax_class_id = :tax_class_id');
      $Qclasses->bindTable(':table_tax_class', TABLE_TAX_CLASS);
      $Qclasses->bindInt(':tax_class_id', $id);
      $Qclasses->execute();

      $Qrates = $osC_Database->query('select count(*) as total_tax_rates from :table_tax_rates where tax_class_id = :tax_class_id');
      $Qrates->bindTable(':table_tax_rates', TABLE_TAX_RATES);
      $Qrates->bindInt(':tax_class_id', $id);
      $Qrates->execute();

      $data = array_merge($Qclasses->toArray(), $Qrates->toArray());

      $Qrates->freeResult();
      $Qclasses->freeResult();

      if ( empty($key) ) {
        return $data;
      } else {
        return $data[$key];
      }
    }

    function getEntryData($id) {
      global $osC_Database;

      $Qrates = $osC_Database->query('select r.*, tc.tax_class_title, z.geo_zone_id, z.geo_zone_name from :table_tax_rates r, :table_tax_class tc, :table_geo_zones z where r.tax_rates_id = :tax_rates_id and r.tax_class_id = tc.tax_class_id and r.tax_zone_id = z.geo_zone_id');
      $Qrates->bindTable(':table_tax_rates', TABLE_TAX_RATES);
      $Qrates->bindTable(':table_tax_class', TABLE_TAX_CLASS);
      $Qrates->bindTable(':table_geo_zones', TABLE_GEO_ZONES);
      $Qrates->bindInt(':tax_rates_id', $id);
      $Qrates->execute();

      $data = $Qrates->toArray();

      $Qrates->freeResult();

      return $data;
    }

    function save($id = null, $data) {
      global $osC_Database;

      if ( is_numeric($id) ) {
        $Qclass = $osC_Database->query('update :table_tax_class set tax_class_title = :tax_class_title, tax_class_description = :tax_class_description, last_modified = now() where tax_class_id = :tax_class_id');
        $Qclass->bindInt(':tax_class_id', $id);
      } else {
        $Qclass = $osC_Database->query('insert into :table_tax_class (tax_class_title, tax_class_description, date_added) values (:tax_class_title, :tax_class_description, now())');
      }

      $Qclass->bindTable(':table_tax_class', TABLE_TAX_CLASS);
      $Qclass->bindValue(':tax_class_title', $data['title']);
      $Qclass->bindValue(':tax_class_description', $data['description']);
      $Qclass->setLogging($_SESSION['module'], $id);
      $Qclass->execute();

      if ( !$osC_Database->isError() ) {
        return true;
      }

      return false;
    }

    function delete($id) {
      global $osC_Database;

      $error = false;

      $osC_Database->startTransaction();

      $Qrates = $osC_Database->query('delete from :table_tax_rates where tax_class_id = :tax_class_id');
      $Qrates->bindTable(':table_tax_rates', TABLE_TAX_RATES);
      $Qrates->bindInt(':tax_class_id', $id);
      $Qrates->setLogging($_SESSION['module'], $id);
      $Qrates->execute();

      if ( !$osC_Database->isError() ) {
        $Qclass = $osC_Database->query('delete from :table_tax_class where tax_class_id = :tax_class_id');
        $Qclass->bindTable(':table_tax_class', TABLE_TAX_CLASS);
        $Qclass->bindInt(':tax_class_id', $id);
        $Qclass->setLogging($_SESSION['module'], $id);
        $Qclass->execute();

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

    function saveEntry($id = null, $data) {
      global $osC_Database;

      if ( is_numeric($id) ) {
        $Qrate = $osC_Database->query('update :table_tax_rates set tax_zone_id = :tax_zone_id, tax_priority = :tax_priority, tax_rate = :tax_rate, tax_description = :tax_description, last_modified = now() where tax_rates_id = :tax_rates_id');
        $Qrate->bindInt(':tax_rates_id', $id);
      } else {
        $Qrate = $osC_Database->query('insert into :table_tax_rates (tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, date_added) values (:tax_zone_id, :tax_class_id, :tax_priority, :tax_rate, :tax_description, now())');
        $Qrate->bindInt(':tax_class_id', $data['tax_class_id']);
      }

      $Qrate->bindTable(':table_tax_rates', TABLE_TAX_RATES);
      $Qrate->bindInt(':tax_zone_id', $data['zone_id']);
      $Qrate->bindInt(':tax_priority', $data['priority']);
      $Qrate->bindValue(':tax_rate', $data['rate']);
      $Qrate->bindValue(':tax_description', $data['description']);
      $Qrate->setLogging($_SESSION['module'], $id);
      $Qrate->execute();

      if ( !$osC_Database->isError() ) {
        return true;
      }

      return false;
    }

    function deleteEntry($id) {
      global $osC_Database;

      $Qrate = $osC_Database->query('delete from :table_tax_rates where tax_rates_id = :tax_rates_id');
      $Qrate->bindTable(':table_tax_rates', TABLE_TAX_RATES);
      $Qrate->bindInt(':tax_rates_id', $id);
      $Qrate->setLogging($_SESSION['module'], $id);
      $Qrate->execute();

      if ( !$osC_Database->isError() ) {
        return true;
      }

      return false;
    }
  }
?>
