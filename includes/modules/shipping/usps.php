<?php
/*
  $Id: usps.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  class osC_Shipping_usps extends osC_Shipping {
    var $icon, $countries;

    var $_title,
        $_code = 'usps',
        $_status = false,
        $_error = false,
        $_error_messages = array(),
        $_shipping_methods = array(),
        $_response,
        $_sort_order;

    // class constructor
    function osC_Shipping_usps() {
      global $osC_Language;

      $this->icon = DIR_WS_IMAGES . 'icons/shipping_usps.jpg';

      $this->_title = $osC_Language->get('shipping_usps_title');
      $this->_description = $osC_Language->get('shipping_usps_description');
      $this->_status = (defined('MODULE_SHIPPING_USPS_STATUS') && (MODULE_SHIPPING_USPS_STATUS == 'Enabled') ? true : false);
      $this->_sort_order = (defined('MODULE_SHIPPING_USPS_SORT_ORDER') ? MODULE_SHIPPING_USPS_SORT_ORDER : null);
    }

    // class methods
    function initialize() {
      global $osC_Database, $osC_ShoppingCart;

      $this->tax_class = MODULE_SHIPPING_USPS_TAX_CLASS;

      if ( ($this->_status === true) && ((int)MODULE_SHIPPING_USPS_ZONE > 0) ) {
        $check_flag = false;

        $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
        $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
        $Qcheck->bindInt(':geo_zone_id', MODULE_SHIPPING_USPS_ZONE);
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
    }
  
    /**
     * Get the real-time shipping quote with USPS API
     *
     * @acess public
     * @return  array
     */
    function quote() {
      global $osC_Language, $osC_ShoppingCart, $osC_Weight;
      
      //verify whether the default shipping weight unit is pounds. Otherwise, it is necessary to convert it to pounds
      $weight = $osC_ShoppingCart->getWeight();
      if (SHIPPING_WEIGHT_UNIT != MODULE_SHIPPING_USPS_WEIGHT_CLASS_ID) {
        $weight = $osC_Weight->convert($osC_ShoppingCart->getWeight(), SHIPPING_WEIGHT_UNIT, MODULE_SHIPPING_USPS_WEIGHT_CLASS_ID);
      }
      
      //get the shipping address
      $shipping_address = $osC_ShoppingCart->getShippingAddress();
      
      //get the sub total
      $sub_total = $osC_ShoppingCart->getSubTotal();
      
      //build the api request
      $request = $this->_build_qoute_request($weight, $shipping_address, $sub_total);

      //send the request
      if ($request !== false) {
        $this->_send_quote_request($request);
      }
      
      //parse xml response to get the shipping rate
      $this->_parse_quote_response($shipping_address);
      
      //return the quotes
      $this->quotes = array('id' => $this->_code,
                            'module' => $this->_title,
                            'methods' => $this->_shipping_mothods,
                            'tax_class_id' => $this->tax_class);
      
      //verify whether the weight should be displayed
      if (MODULE_SHIPPING_USPS_DISPLAY_DELIVERY_WEIGHT == 'Yes') {
        $this->quotes['module'] .= ' (' . $osC_Language->get('shipping_usps_weight_text') . $osC_Weight->display($osC_ShoppingCart->getWeight(), SHIPPING_WEIGHT_UNIT) . ')';
      }
      
      if (!empty($this->icon)) $this->quotes['icon'] = osc_image($this->icon, $this->_title);
      
      if ($this->_error === true) {
        $this->_set_contact_message();
        
        $this->quotes['error'] = '<strong style="color:red;">' . implode('<br />', $this->_error_messages) . '</strong>';
      }

      return $this->quotes;
    }
    
    /**
     * Build the api request for getting the real-time quote
     *
     * @acess private
     * @param $weight
     * @param $shipping_address
     * @param $sub_total
     * @return  mixed
     */
    function _build_qoute_request($weight, $shipping_address, $sub_total) {
      $request = false;
      
      //get the destination post code, weight to pounds and ounces
      $postcode = str_replace(' ', '', $shipping_address['postcode']);
      $weight = ($weight < 0.1 ? 0.1 : $weight);
      $pounds = floor($weight);
      $ounces = round(16 * ($weight - $pounds), 2);
      
      //dimensions
      $width = MODULE_SHIPPING_USPS_DIMENSIONS_WIDTH;
      $height = MODULE_SHIPPING_USPS_DIMENSIONS_HEIGHT;
      $length = MODULE_SHIPPING_USPS_DIMENSIONS_LENGTH;
      
      //container
      $container = strtoupper(MODULE_SHIPPING_USPS_CONTAINER);
      
      //build domestic request
      if ($shipping_address['country_iso_code_2'] == 'US') {
        if (MODULE_SHIPPING_USPS_USERPASSWORD) {
          $xml  = '<RateV4Request USERID="' .  MODULE_SHIPPING_USPS_USERID . '" PASSWORD="' . MODULE_SHIPPING_USPS_USERPASSWORD . '">';
        }else {
          $xml  = '<RateV4Request USERID="' .  MODULE_SHIPPING_USPS_USERID . '">';
        }
        
        $xml .= ' <Package ID="1">';
        $xml .= '<Service>ALL</Service>';
        $xml .= '<ZipOrigination>' . substr(MODULE_SHIPPING_USPS_ZIPCODE, 0, 5) . '</ZipOrigination>';
        $xml .= '<ZipDestination>' . substr($postcode, 0, 5) . '</ZipDestination>';
        $xml .= '<Pounds>' . $pounds . '</Pounds>';
        $xml .= '<Ounces>' . $ounces . '</Ounces>';

        // Size cannot be Regular if Container is Rectangular
        if ($container == 'RECTANGULAR' && MODULE_SHIPPING_USPS_SIZE == 'REGULAR') {
          $container = 'VARIABLE';
        }

        $xml .= '<Container>' . $container . '</Container>';
        $xml .= '<Size>' . MODULE_SHIPPING_USPS_SIZE . '</Size>';
        $xml .= '<Width>' . $width . '</Width>';
        $xml .= '<Length>' . $length . '</Length>';
        $xml .= '<Height>' . $height . '</Height>';
      
        // Calculate girth based on usps calculation if it is not provided
        if (MODULE_SHIPPING_USPS_GIRTH){
          $xml .= '<Girth>' . MODULE_SHIPPING_USPS_GIRTH . '</Girth>';
        }
        else{
          $xml .= '<Girth>' . (round(((float)$width + (float)$length * 2 + (float)$height * 2), 1)) . '</Girth>';
        }
         
        $xml .= '<Machinable>' . (MODULE_SHIPPING_USPS_MACHINABLE == 'Yes' ? 'true' : 'false') . '</Machinable>';
        $xml .= '</Package>';
        $xml .= '</RateV4Request>';

        $request = 'API=RateV4&XML=' . urlencode($xml);

      //build international request
      }else {
        //load all countries and codes
        $countries = $this->_get_countries();
        
        if (isset($countries[$shipping_address['country_iso_code_2']])) {
          if (MODULE_SHIPPING_USPS_USERPASSWORD) {
            $xml  = '<IntlRateV2Request USERID="' .  MODULE_SHIPPING_USPS_USERID . '" PASSWORD="' . MODULE_SHIPPING_USPS_USERPASSWORD . '">';
          }else {
            $xml  = '<IntlRateV2Request USERID="' .  MODULE_SHIPPING_USPS_USERID . '">';
          }
          
          $xml .= ' <Package ID="1">';
          $xml .= '   <Pounds>' . $pounds . '</Pounds>';
          $xml .= '   <Ounces>' . $ounces . '</Ounces>';
          $xml .= '   <MailType>All</MailType>';
          $xml .= '   <GXG>';
          $xml .= '     <POBoxFlag>N</POBoxFlag>';
          $xml .= '     <GiftFlag>N</GiftFlag>';
          $xml .= '   </GXG>';
          $xml .= '   <ValueOfContents>' . $sub_total . '</ValueOfContents>';
          $xml .= '   <Country>' . $countries[$shipping_address['country_iso_code_2']] . '</Country>';

          // Intl only supports RECT and NONRECT
          if ($container == 'VARIABLE') {
            $container = 'NONRECTANGULAR';
          }

          $xml .= '   <Container>' . $container . '</Container>';
          $xml .= '   <Size>' . MODULE_SHIPPING_USPS_SIZE . '</Size>';
          $xml .= '   <Width>' . $width . '</Width>';
          $xml .= '   <Length>' . $length . '</Length>';
          $xml .= '   <Height>' . $height . '</Height>';
          $xml .= '   <Girth>' . MODULE_SHIPPING_USPS_GIRTH . '</Girth>';
          $xml .= '   <CommercialFlag>N</CommercialFlag>';
          $xml .= ' </Package>';
          $xml .= '</IntlRateV2Request>';

          $request = 'API=IntlRateV2&XML=' . urlencode($xml);
        }
      }
      
      return $request;
    }
    
    /**
     * Send the api request to get the real-time quote
     *
     * @acess private
     * @param $request
     * @return  void
     */
    function _send_quote_request($request) {
      $api_url = 'http://production.shippingapis.com/shippingapi.dll?' . $request;
      
      //verify whether the curl is supported
      if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $this->_response = curl_exec($ch);
        
        // Verify whether there is any curl error thrown
        if(curl_errno($ch)){
          $this->_error = true;
        }
    
        curl_close($ch);
        
      // Create and send http get request with file function
      }else if (function_exists('stream_context_create')) {
        //parse the service url
        $server = parse_url($api_url);
        //set the context options
        $options = array('http' => array('method' => 'get',
                                         'timeout' => 20,
                                         'header' => 'Host: ' . $server['host'], 
                                         'request_fulluri' => true));
        
        //create the context
        $context = stream_context_create($options);
        
        // Execute request to get the response
        $this->_response = file_get_contents($api_url, false, $context);
      
      //send the http get request with socket
      }else {
        //parse the service url
        $server = parse_url($api_url);
        
        $fp = fsockopen($server['host'], 80, $errno, $errstr, 30);
        
        if (!$fp) {
          $this->_error = true;
        }else {
          $request_line = "GET " . $server['path'] . "?" . $server['query'] . " HTTP/1.1\r\n";
          $request_header = "Host:" . $server['host'] . "\r\n";
          $request_header .= "Connection: Close\r\n\r\n";
          
          fwrite($fp, $request_line . $request_header);
          while(!feof($fp)) {
            $this->_response .= fgets($fp, 1024);
          }
          
          fclose($fp);
        }
      }
    }
    
    /**
     * parse quote response to get the real-time shipping quotes
     *
     * @acess private
     * @return  void
     */
    function _parse_quote_response($shipping_address) {
      global $osC_Currencies, $osC_Language;
      
      if (!empty($this->_response)) {
        //filter the xml entities
        $this->_response = preg_replace('/&amp;lt;sup&amp;gt;&amp;#\d+;&amp;lt;\/sup&amp;gt;/', '', $this->_response);
        $this->_response = str_replace(array('**', "\r\n", '\"'), array('', '', '"'), $this->_response);
        
        //load the xml
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXml($this->_response);
        
        $rate_response = $dom->getElementsByTagName('RateV4Response')->item(0);
        $intl_rate_response = $dom->getElementsByTagName('IntlRateV2Response')->item(0);
        $error = $dom->getElementsByTagName('Error')->item(0);
        
        if ($rate_response || $intl_rate_response) {
          //verify whether it is deomestic service
          if ($shipping_address['country_iso_code_2'] == 'US') {
            $allowed = array(0, 1, 2, 3, 4, 5, 6, 7, 12, 13, 16, 17, 18, 19, 22, 23, 25, 27, 28);
            
            //get the postage tags which including the rates
            $package = $rate_response->getElementsByTagName('Package')->item(0);
            $postages = $package->getElementsByTagName('Postage');
            
            //get the selected domestic services set in the admin panel
            $deomestic_services = array();
            if (MODULE_SHIPPING_USPS_DEOMESTIC_SERVICES) {
              $deomestic_services = explode(',', MODULE_SHIPPING_USPS_DEOMESTIC_SERVICES);
            }
            
            //get the separated postage
            if ($postages->length > 0) {
              foreach ($postages as $postage) {
                  $classid = $postage->getAttribute('CLASSID');
                  
                  if (in_array($classid, $allowed) && in_array($classid, $deomestic_services)) {
                    $cost = $this->_convert($postage->getElementsByTagName('Rate')->item(0)->nodeValue, 'USD', $osC_Currencies->getCode());
                    
                    $title = $postage->getElementsByTagName('MailService')->item(0)->nodeValue;
                    
                    $this->_shipping_mothods[] = array(
                      'id'           => $this->_code . $classid,
                      'title'        => $title,
                      'cost'         => $cost + MODULE_SHIPPING_USPS_HANDLING
                    );              
                  }
              }
            //record the error
            }else {
              $error = $package->getElementsByTagName('Error')->item(0);
              
              $this->_error = true;
              
              if (MODULE_SHIPPING_USPS_DEBUG_MODE == 'Enabled') {
                $this->_error_messages[] = $error->getElementsByTagName('Description')->item(0)->nodeValue;
              }
            }
            
          //it is international service
          }else {
            $allowed = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 21);

            $package = $intl_rate_response->getElementsByTagName('Package')->item(0);

            $services = $package->getElementsByTagName('Service');
            
            //get the selected international services set in the admin panel
            $international_services = array();
            if (MODULE_SHIPPING_USPS_INTERNATIONAL_SERVICES) {
              $international_services = explode(',', MODULE_SHIPPING_USPS_INTERNATIONAL_SERVICES);
            }
            
            //get rate for each service
            foreach ($services as $service) {
              $id = $service->getAttribute('ID');

              if (in_array($id, $allowed) && in_array($id, $international_services)) {
                $title = $service->getElementsByTagName('SvcDescription')->item(0)->nodeValue;
                
                if (MODULE_SHIPPING_USPS_DISPLAY_DELIVERY_TIME == 'Yes') {
                  $title .= ' (' . $osC_Language->get('shipping_usps_estimated_time') . ' ' . $service->getElementsByTagName('SvcCommitments')->item(0)->nodeValue . ')';
                }

                $cost = $this->_convert($service->getElementsByTagName('Postage')->item(0)->nodeValue, 'USD', $osC_Currencies->getCode());

                $this->_shipping_mothods[] = array(
                  'id'           => $this->_code . $id,
                  'title'        => $title,
                  'cost'         => $cost + MODULE_SHIPPING_USPS_HANDLING
                );
              }
            }
            
            
          }
          
        //record the error
        }else if ($error) {
          $this->_error = true;
          
          if (MODULE_SHIPPING_USPS_DEBUG_MODE == 'Enabled') {
            $this->_error_messages[] = $error->getElementsByTagName('Description')->item(0)->nodeValue;
          }
        }
      }
    }
    
    /**
     * Convert the usd to the default currency in the system
     *
     * @acess private
     * @param $value
     * @param $from
     * @param $to
     * @return int
     */
    function _convert($value, $from, $to) {
      global $osC_Currencies;
      
      $currencies = $osC_Currencies->getData();
      
      if ($osC_Currencies->exists($from)) {
        $from = $currencies[$from]['value'];
      } else {
        $from = 0;
      }
    
      if ($osC_Currencies->exists($to)) {
        $to = $currencies[$to]['value'];
      } else {
        $to = 0;
      }   
    
      return $value * ($to / $from);
    }
    
    /**
     * This is a help function to return all countries and codes
     *
     * @acess private
     * @return array
     */
    function _get_countries() {
      $countries = array(
        'AF' => 'Afghanistan',
        'AL' => 'Albania',
        'DZ' => 'Algeria',
        'AD' => 'Andorra',
        'AO' => 'Angola',
        'AI' => 'Anguilla',
        'AG' => 'Antigua and Barbuda',
        'AR' => 'Argentina',
        'AM' => 'Armenia',
        'AW' => 'Aruba',
        'AU' => 'Australia',
        'AT' => 'Austria',
        'AZ' => 'Azerbaijan',
        'BS' => 'Bahamas',
        'BH' => 'Bahrain',
        'BD' => 'Bangladesh',
        'BB' => 'Barbados',
        'BY' => 'Belarus',
        'BE' => 'Belgium',
        'BZ' => 'Belize',
        'BJ' => 'Benin',
        'BM' => 'Bermuda',
        'BT' => 'Bhutan',
        'BO' => 'Bolivia',
        'BA' => 'Bosnia-Herzegovina',
        'BW' => 'Botswana',
        'BR' => 'Brazil',
        'VG' => 'British Virgin Islands',
        'BN' => 'Brunei Darussalam',
        'BG' => 'Bulgaria',
        'BF' => 'Burkina Faso',
        'MM' => 'Burma',
        'BI' => 'Burundi',
        'KH' => 'Cambodia',
        'CM' => 'Cameroon',
        'CA' => 'Canada',
        'CV' => 'Cape Verde',
        'KY' => 'Cayman Islands',
        'CF' => 'Central African Republic',
        'TD' => 'Chad',
        'CL' => 'Chile',
        'CN' => 'China',
        'CX' => 'Christmas Island (Australia)',
        'CC' => 'Cocos Island (Australia)',
        'CO' => 'Colombia',
        'KM' => 'Comoros',
        'CG' => 'Congo (Brazzaville),Republic of the',
        'ZR' => 'Congo, Democratic Republic of the',
        'CK' => 'Cook Islands (New Zealand)',
        'CR' => 'Costa Rica',
        'CI' => 'Cote d\'Ivoire (Ivory Coast)',
        'HR' => 'Croatia',
        'CU' => 'Cuba',
        'CY' => 'Cyprus',
        'CZ' => 'Czech Republic',
        'DK' => 'Denmark',
        'DJ' => 'Djibouti',
        'DM' => 'Dominica',
        'DO' => 'Dominican Republic',
        'TP' => 'East Timor (Indonesia)',
        'EC' => 'Ecuador',
        'EG' => 'Egypt',
        'SV' => 'El Salvador',
        'GQ' => 'Equatorial Guinea',
        'ER' => 'Eritrea',
        'EE' => 'Estonia',
        'ET' => 'Ethiopia',
        'FK' => 'Falkland Islands',
        'FO' => 'Faroe Islands',
        'FJ' => 'Fiji',
        'FI' => 'Finland',
        'FR' => 'France',
        'GF' => 'French Guiana',
        'PF' => 'French Polynesia',
        'GA' => 'Gabon',
        'GM' => 'Gambia',
        'GE' => 'Georgia, Republic of',
        'DE' => 'Germany',
        'GH' => 'Ghana',
        'GI' => 'Gibraltar',
        'GB' => 'Great Britain and Northern Ireland',
        'GR' => 'Greece',
        'GL' => 'Greenland',
        'GD' => 'Grenada',
        'GP' => 'Guadeloupe',
        'GT' => 'Guatemala',
        'GN' => 'Guinea',
        'GW' => 'Guinea-Bissau',
        'GY' => 'Guyana',
        'HT' => 'Haiti',
        'HN' => 'Honduras',
        'HK' => 'Hong Kong',
        'HU' => 'Hungary',
        'IS' => 'Iceland',
        'IN' => 'India',
        'ID' => 'Indonesia',
        'IR' => 'Iran',
        'IQ' => 'Iraq',
        'IE' => 'Ireland',
        'IL' => 'Israel',
        'IT' => 'Italy',
        'JM' => 'Jamaica',
        'JP' => 'Japan',
        'JO' => 'Jordan',
        'KZ' => 'Kazakhstan',
        'KE' => 'Kenya',
        'KI' => 'Kiribati',
        'KW' => 'Kuwait',
        'KG' => 'Kyrgyzstan',
        'LA' => 'Laos',
        'LV' => 'Latvia',
        'LB' => 'Lebanon',
        'LS' => 'Lesotho',
        'LR' => 'Liberia',
        'LY' => 'Libya',
        'LI' => 'Liechtenstein',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'MO' => 'Macao',
        'MK' => 'Macedonia, Republic of',
        'MG' => 'Madagascar',
        'MW' => 'Malawi',
        'MY' => 'Malaysia',
        'MV' => 'Maldives',
        'ML' => 'Mali',
        'MT' => 'Malta',
        'MQ' => 'Martinique',
        'MR' => 'Mauritania',
        'MU' => 'Mauritius',
        'YT' => 'Mayotte (France)',
        'MX' => 'Mexico',
        'MD' => 'Moldova',
        'MC' => 'Monaco (France)',
        'MN' => 'Mongolia',
        'MS' => 'Montserrat',
        'MA' => 'Morocco',
        'MZ' => 'Mozambique',
        'NA' => 'Namibia',
        'NR' => 'Nauru',
        'NP' => 'Nepal',
        'NL' => 'Netherlands',
        'AN' => 'Netherlands Antilles',
        'NC' => 'New Caledonia',
        'NZ' => 'New Zealand',
        'NI' => 'Nicaragua',
        'NE' => 'Niger',
        'NG' => 'Nigeria',
        'KP' => 'North Korea (Korea, Democratic People\'s Republic of)',
        'NO' => 'Norway',
        'OM' => 'Oman',
        'PK' => 'Pakistan',
        'PA' => 'Panama',
        'PG' => 'Papua New Guinea',
        'PY' => 'Paraguay',
        'PE' => 'Peru',
        'PH' => 'Philippines',
        'PN' => 'Pitcairn Island',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'QA' => 'Qatar',
        'RE' => 'Reunion',
        'RO' => 'Romania',
        'RU' => 'Russia',
        'RW' => 'Rwanda',
        'SH' => 'Saint Helena',
        'KN' => 'Saint Kitts (St. Christopher and Nevis)',
        'LC' => 'Saint Lucia',
        'PM' => 'Saint Pierre and Miquelon',
        'VC' => 'Saint Vincent and the Grenadines',
        'SM' => 'San Marino',
        'ST' => 'Sao Tome and Principe',
        'SA' => 'Saudi Arabia',
        'SN' => 'Senegal',
        'YU' => 'Serbia-Montenegro',
        'SC' => 'Seychelles',
        'SL' => 'Sierra Leone',
        'SG' => 'Singapore',
        'SK' => 'Slovak Republic',
        'SI' => 'Slovenia',
        'SB' => 'Solomon Islands',
        'SO' => 'Somalia',
        'ZA' => 'South Africa',
        'GS' => 'South Georgia (Falkland Islands)',
        'KR' => 'South Korea (Korea, Republic of)',
        'ES' => 'Spain',
        'LK' => 'Sri Lanka',
        'SD' => 'Sudan',
        'SR' => 'Suriname',
        'SZ' => 'Swaziland',
        'SE' => 'Sweden',
        'CH' => 'Switzerland',
        'SY' => 'Syrian Arab Republic',
        'TW' => 'Taiwan',
        'TJ' => 'Tajikistan',
        'TZ' => 'Tanzania',
        'TH' => 'Thailand',
        'TG' => 'Togo',
        'TK' => 'Tokelau (Union) Group (Western Samoa)',
        'TO' => 'Tonga',
        'TT' => 'Trinidad and Tobago',
        'TN' => 'Tunisia',
        'TR' => 'Turkey',
        'TM' => 'Turkmenistan',
        'TC' => 'Turks and Caicos Islands',
        'TV' => 'Tuvalu',
        'UG' => 'Uganda',
        'UA' => 'Ukraine',
        'AE' => 'United Arab Emirates',
        'UY' => 'Uruguay',
        'UZ' => 'Uzbekistan',
        'VU' => 'Vanuatu',
        'VA' => 'Vatican City',
        'VE' => 'Venezuela',
        'VN' => 'Vietnam',
        'WF' => 'Wallis and Futuna Islands',
        'WS' => 'Western Samoa',
        'YE' => 'Yemen',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe');
      
      return $countries;
    }
    
    /**
     * set the contact message as error happened
     *
     * @acess private
     * @return void
     */
    function _set_contact_message() {
      global $osC_Language;
      
      $store_info = explode("\n", STORE_NAME_ADDRESS);
      $store_telephone = array_pop($store_info);
      $this->_error_messages[] = '<div style="border:2px dotted red;padding:0 10px;color: red;margin: 5px 0;">' . 
                                  '<p style="font-size:normal;font-weight:normal;">' . $osC_Language->get('shipping_usps_error') . '</p>' .
                                  '<p><strong>' . $store_telephone . '</strong></p>' .
                                  '<p><strong>Email: </strong>' . STORE_OWNER_EMAIL_ADDRESS . '</p>' .
                                '</div>';
    }
  }
?>
