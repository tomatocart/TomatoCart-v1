<?php
/*
  $Id: ups.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require_once('includes/classes/http_client.php');

  class osC_Shipping_ups extends osC_Shipping {
    var $icon, $countries;

    var $_title,
        $_code = 'ups',
        $_status = false,
        $_sort_order;

// class constructor
    function osC_Shipping_ups() {
      global $osC_Language;

      $this->icon = DIR_WS_IMAGES . 'icons/shipping_ups.gif';

      $this->_title = $osC_Language->get('shipping_ups_title');
      $this->_description = $osC_Language->get('shipping_ups_description');
      $this->_status = (defined('MODULE_SHIPPING_UPS_STATUS') && (MODULE_SHIPPING_UPS_STATUS == 'True') ? true : false);
      $this->_sort_order = (defined('MODULE_SHIPPING_UPS_SORT_ORDER') ? MODULE_SHIPPING_UPS_SORT_ORDER : null);
    }

// class methods
    function initialize() {
      global $osC_Database, $osC_ShoppingCart;

      $this->tax_class = MODULE_SHIPPING_UPS_TAX_CLASS;

      if ( ($this->_status === true) && ((int)MODULE_SHIPPING_UPS_ZONE > 0) ) {
        $check_flag = false;

        $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
        $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
        $Qcheck->bindInt(':geo_zone_id', MODULE_SHIPPING_UPS_ZONE);
        $Qcheck->bindInt(':zone_country_id', $osC_ShoppingCart->getShippingAddress('country_id'));
        $Qcheck->execute();

        while ($Qcheck->next()) {
          if ($Qcheck->valueInt('zone_id') < 1) {
            $check_flag = true;
            break;
          } elseif ($Qcheck->valueInt('zone_id') == $osC_ShoppingCart->getShippingAddress('zone_id')) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->_status = false;
        }
      }

      $this->types = array('1DM' => 'Next Day Air Early AM',
                           '1DML' => 'Next Day Air Early AM Letter',
                           '1DA' => 'Next Day Air',
                           '1DAL' => 'Next Day Air Letter',
                           '1DAPI' => 'Next Day Air Intra (Puerto Rico)',
                           '1DP' => 'Next Day Air Saver',
                           '1DPL' => 'Next Day Air Saver Letter',
                           '2DM' => '2nd Day Air AM',
                           '2DML' => '2nd Day Air AM Letter',
                           '2DA' => '2nd Day Air',
                           '2DAL' => '2nd Day Air Letter',
                           '3DS' => '3 Day Select',
                           'GND' => 'Ground',
                           'GNDCOM' => 'Ground Commercial',
                           'GNDRES' => 'Ground Residential',
                           'STD' => 'Canada Standard',
                           'XPR' => 'Worldwide Express',
                           'XPRL' => 'worldwide Express Letter',
                           'XDM' => 'Worldwide Express Plus',
                           'XDML' => 'Worldwide Express Plus Letter',
                           'XPD' => 'Worldwide Expedited');
    }

    function quote($method = '') {
      global $osC_Language, $osC_ShoppingCart;
      
      if ( !empty($method) && (isset($this->types[$method])) ) {
        $prod = $method;
      }else if ($osC_ShoppingCart->getShippingAddress('country_iso_code_2') == 'CA') {
        $prod = 'STD';
      }else {
        $prod = 'GNDRES';
      }
      
      if ($method) $this->_upsAction('3'); // return a single quote
      
      $this->_upsProduct($prod);
      
      $this->_upsOrigin(SHIPPING_ORIGIN_ZIP, osC_Address::getCountryIsoCode2(SHIPPING_ORIGIN_COUNTRY));
      $this->_upsDest($osC_ShoppingCart->getShippingAddress('postcode'), $osC_ShoppingCart->getShippingAddress('country_iso_code_2'));
      $this->_upsRate(MODULE_SHIPPING_UPS_PICKUP);
      $this->_upsContainer(MODULE_SHIPPING_UPS_PACKAGE);
      $this->_upsWeight($osC_ShoppingCart->getWeight());
      $this->_upsRescom(MODULE_SHIPPING_UPS_RES);
      $upsQuote = $this->_upsGetQuote();

      if ( (is_array($upsQuote)) && (sizeof($upsQuote) > 0) ) {
        $this->quotes = array('id' => $this->_code,
                              'module' => $this->_title . ' (' . $osC_ShoppingCart->numberOfShippingBoxes() . ' x ' . $osC_ShoppingCart->getWeight() . 'lbs)');

        $methods = array();
        $allowed_methods = explode(",", MODULE_SHIPPING_UPS_TYPES);
        $std_rcd = false;
        $qsize = sizeof($upsQuote);
        
        for ($i=0; $i<$qsize; $i++) {
          list($type, $cost) = each($upsQuote[$i]);
          
          if ($type=='STD') {
            if ($std_rcd) continue;
            else $std_rcd = true;
          };
          
          if (!in_array($type, $allowed_methods)) continue;
          
          $methods[] = array('id' => $type,
                             'title' => $this->types[$type],
                             'cost' => ($cost + MODULE_SHIPPING_UPS_HANDLING) * $osC_ShoppingCart->numberOfShippingBoxes());
        }

        $this->quotes['methods'] = $methods;
        if ($this->tax_class > 0) {
          $this->quotes['tax_class_id'] = $this->tax_class;
        }
      } else {
        $this->quotes = array('module' => $this->_title,
                              'error' => 'We are unable to obtain a rate quote for UPS shipping.<br>Please contact the store if no other alternative is shown.');
      }

      if (!empty($this->icon)) $this->quotes['icon'] = osc_image($this->icon, $this->_title);

      return $this->quotes;
    }
    
    function _upsRescom($foo) {
      switch ($foo) {
        case 'RES': // Residential Address
          $this->_upsResComCode = '1';
          break;
        case 'COM': // Commercial Address
          $this->_upsResComCode = '0';
          break;
      }
    }
    
    function _upsWeight($foo) {
      $this->_upsPackageWeight = $foo;
    }
    
    function _upsRate($foo) {
      switch ($foo) {
        case 'RDP':
          $this->_upsRateCode = 'Regular+Daily+Pickup';
          break;
        case 'OCA':
          $this->_upsRateCode = 'On+Call+Air';
          break;
        case 'OTP':
          $this->_upsRateCode = 'One+Time+Pickup';
          break;
        case 'LC':
          $this->_upsRateCode = 'Letter+Center';
          break;
        case 'CC':
          $this->_upsRateCode = 'Customer+Counter';
          break;
      }
    }

    function _upsContainer($foo) {
      switch ($foo) {
        case 'CP': // Customer Packaging
          $this->_upsContainerCode = '00';
          break;
        case 'ULE': // UPS Letter Envelope
          $this->_upsContainerCode = '01';
          break;
        case 'UT': // UPS Tube
          $this->_upsContainerCode = '03';
          break;
        case 'UEB': // UPS Express Box
          $this->_upsContainerCode = '21';
          break;
        case 'UW25': // UPS Worldwide 25 kilo
          $this->_upsContainerCode = '24';
          break;
        case 'UW10': // UPS Worldwide 10 kilo
          $this->_upsContainerCode = '25';
          break;
      }
    }
    
    function _upsDest($postal, $country){
      $postal = str_replace(' ', '', $postal);

      if ($country == 'US') {
        $this->_upsDestPostalCode = substr($postal, 0, 5);
      } else {
        $this->_upsDestPostalCode = $postal;
      }

      $this->_upsDestCountryCode = $country;
    }
    
    function _upsOrigin($postal, $country){
      $this->_upsOriginPostalCode = $postal;
      $this->_upsOriginCountryCode = $country;
    }
    
    function _upsAction($action) {
      /* 3 - Single Quote
         4 - All Available Quotes */

      $this->_upsActionCode = $action;
    }
    
    function _upsProduct($prod){
      $this->_upsProductCode = $prod;
    }
    
    function _upsGetQuote() {
      if (!isset($this->_upsActionCode)) $this->_upsActionCode = '4';

      $request = join('&', array('accept_UPS_license_agreement=yes',
                                 '10_action=' . $this->_upsActionCode,
                                 '13_product=' . $this->_upsProductCode,
                                 '14_origCountry=' . $this->_upsOriginCountryCode,
                                 '15_origPostal=' . $this->_upsOriginPostalCode,
                                 '19_destPostal=' . $this->_upsDestPostalCode,
                                 '22_destCountry=' . $this->_upsDestCountryCode,
                                 '23_weight=' . $this->_upsPackageWeight,
                                 '47_rate_chart=' . $this->_upsRateCode,
                                 '48_container=' . $this->_upsContainerCode,
                                 '49_residential=' . $this->_upsResComCode));
      $http = new httpClient();
      if ($http->Connect('www.ups.com', 80)) {
        $http->addHeader('Host', 'www.ups.com');
        $http->addHeader('User-Agent', 'tomatocart');
        $http->addHeader('Connection', 'Close');

        if ($http->Get('/using/services/rave/qcostcgi.cgi?' . $request)) $body = $http->getBody();

        $http->Disconnect();
      } else {
        return 'error';
      }
/*
    mail('you@yourdomain.com','UPS response',$body,'From: <you@yourdomain.com>');
*/
      $body_array = explode("\n", $body);

      $returnval = array();
      $errorret = 'error'; // only return error if NO rates returned

      $n = sizeof($body_array);
      for ($i = 0; $i < $n; $i++) {
        $result = explode('%', $body_array[$i]);
        $errcode = substr($result[0], -1);
        switch ($errcode) {
          case 3:
            if (is_array($returnval)) $returnval[] = array($result[1] => $result[8]);
            break;
          case 4:
            if (is_array($returnval)) $returnval[] = array($result[1] => $result[8]);
            break;
          case 5:
            $errorret = $result[1];
            break;
          case 6:
            if (is_array($returnval)) $returnval[] = array($result[3] => $result[10]);
            break;
        }
      }
      
      if (empty($returnval)) $returnval = $errorret;

      return $returnval;
    }
  }
?>
