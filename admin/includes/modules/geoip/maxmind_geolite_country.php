<?php
/*
  $Id: maxmind_geolite_country.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_GeoIP_maxmind_geolite_country extends osC_GeoIP_Admin {

    var $_title;
    var $_description;
    var $_code = 'maxmind_geolite_country';
    var $_author_name = 'osCommerce';
    var $_author_www = 'http://www.oscommerce.com';
    var $_handler;

    function osC_GeoIP_maxmind_geolite_country() {
      global $osC_Language;

      $this->_title = $osC_Language->get('geoip_maxmind_geolite_country_title');
      $this->_description = $osC_Language->get('geoip_maxmind_geolite_country_description');
      $this->_status = (defined('MODULE_DEFAULT_GEOIP') && (MODULE_DEFAULT_GEOIP == $this->_code));
    }

    function activate() {
      include('external/maxmind/geoip/geoip.php');

      $this->_handler = geoip_open('external/maxmind/geoip/geoip.dat', GEOIP_MEMORY_CACHE);
      $this->_active = true;
    }

    function deactivate() {
      geoip_close($this->_handler);
      unset($this->_handler);
      $this->_active = false;
    }

    function isValid($ip_address) {
      return (geoip_country_id_by_addr($this->_handler, $ip_address) !== false);
    }

    function getCountryISOCode2($ip_address) {
      return strtolower(geoip_country_code_by_addr($this->_handler, $ip_address));
    }

    function getCountryName($ip_address) {
      return geoip_country_name_by_addr($this->_handler, $ip_address);
    }

    function getData($ip_address) {
      return array(osc_image('../images/worldflags/' . $this->getCountryISOCode2($ip_address) . '.png', $this->getCountryName($ip_address) . ', ' . $ip_address, 18, 12) . '&nbsp;' . $this->getCountryName($ip_address));
    }
  }
?>
