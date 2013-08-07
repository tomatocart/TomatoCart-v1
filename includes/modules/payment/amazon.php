<?php
/*
  $Id: amazon.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_amazon extends osC_Payment {
    
    var $_title,
        $_code = 'amazon',
        $_status = false,
        $_sort_order,
        $_order_id;
          
    static $public_key_cache = array();    
  
    function osC_Payment_amazon() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;
  
      $this->_title = $osC_Language->get('payment_amazon_title');
      $this->_method_title = $osC_Language->get('payment_amazon_method_title');
      $this->_status = (MODULE_PAYMENT_AMAZON_STATUS == '1') ? true : false;
      $this->_sort_order = MODULE_PAYMENT_AMAZON_SORT_ORDER;
  
      switch (MODULE_PAYMENT_AMAZON_SERVER) {
        case 'Production':
          $this->form_action_url = 'https://authorize.payments.amazon.com/pba/paypipeline';
          break;
  
        default:
          $this->form_action_url = 'https://authorize.payments-sandbox.amazon.com/pba/paypipeline';
          break;
      }      
        
      if ($this->_status === true) {
        if ((int)MODULE_PAYMENT_AMAZON_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_AMAZON_ORDER_STATUS_ID;
        }
  
        if ((int)MODULE_PAYMENT_AMAZON_ZONE > 0) {
          $check_flag = false;
  
          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_AMAZON_ZONE);
          $Qcheck->bindInt(':zone_country_id', $osC_ShoppingCart->getBillingAddress('country_id'));
          $Qcheck->execute();
  
          while ($Qcheck->next()) {
            if ($Qcheck->valueInt('zone_id') < 1) {
              $check_flag = true;
              break;
            } elseif ($Qcheck->valueInt('zone_id') == $osC_ShoppingCart->getBillingAddress('zone_id')) {
              $check_flag = true;
              break;
            }
          }
  
          if ($check_flag == false) {
            $this->_status = false;
          }
        }
      }
    }
  
    function selection() {
      return array('id' => $this->_code,
                  'module' => $this->_method_title);
    }
      
    function confirmation() {
      $this->_order_id = osC_Order::insert(ORDERS_STATUS_PREPARING);
    }
  
    function process_button() {
      global $osC_ShoppingCart;  
    
      $params = array('accessKey' => MODULE_PAYMENT_AMAZON_ACCESS_KEY,
                      'amount' => $osC_ShoppingCart->getTotal(),
                      'description' => STORE_NAME,
                      'signatureMethod' => 'HmacSHA256',
                      'referenceId' => $this->_order_id,
                      'immediateReturn' => '0',
                      'returnUrl' => osc_href_link(FILENAME_CHECKOUT, 'process', 'SSL', null, null, true),
                      'abandonUrl' => osc_href_link(FILENAME_CHECKOUT, 'checkout&view=paymentInformationForm', 'SSL', null, null, true), 
                      'processImmediate' => '1',
                      'ipnUrl' => osc_href_link(FILENAME_CHECKOUT, 'callback&module=' . $this->_code, 'SSL', null, null, true),
                      'cobrandingStyle' => 'logo',
                      'signatureVersion' => '2');
      
      $site = parse_url($this->form_action_url);
      $params['signature'] = self::_sign_params($params, MODULE_PAYMENT_AMAZON_SECRET_KEY, 'post', $site['host'], $site['path'], 'HmacSHA256');
      
      $process_button_string = '';
      foreach ($params as $key => $value) {
        $key = trim($key);
        $value = trim($value);
        $process_button_string .= osc_draw_hidden_field($key, $value);
        $process_button_string .= "\n";
      }

      return $process_button_string;
    }
    
    function _sign_params(array $parameters, $key, $http_method, $host, $request_url,$algorithm) {
      $string_to_sign = self::_calculate_string_to_sign_v2($parameters, $http_method, $host, $request_url);
      
      return self::_sign($string_to_sign, $key, $algorithm);
    }
    
    function _calculate_string_to_sign_v2(array $parameters, $http_method, $host_header, $request_url) {
      if ($http_method == null) {
        throw new Exception("HttpMethod cannot be null");
      }
      
      $data = $http_method;
      $data .= "\n";
        
      if ($host_header == null) {
        $host_header = "";
      } 
      $data .= $host_header;
      $data .= "\n";
        
      if (!isset ($request_url)) {
        $request_url = "/";
      }
      $uri_encoded = implode("/", array_map(array("osC_Payment_amazon", "_urlencode"), explode("/", $request_url)));
      $data .= $uri_encoded;
      $data .= "\n";
        
      uksort($parameters, 'strcmp');
      $data .= self::_get_parameters_as_string($parameters);
      return $data;
    }

    function _get_host_header($end_point) {
      $url = parse_url($end_point);
      $host = $url['host'];
      $scheme = strtoupper($url['scheme']);
      if (isset($url['port'])) {
        $port = $url['port'];
        if (("HTTPS" == $scheme && $port != 443) ||  ("HTTP" == $scheme && $port != 80)) {
          return strtolower($host) . ":" . $port;
        }
      }
      return strtolower($host);
    }
    
    function _get_request_url($end_point) {
      $url = parse_url($end_point);
      $request_url = $url['path'];
      if ($request_url == null || $request_url == "") {
        $request_url = "/";
      } else {
        $request_url = rawurldecode($request_url);
      }
      return $request_url;
    }
    
    function get_signature_algorithm($signatureMethod) {
      if ("RSA-SHA1" == $signatureMethod) {
        return OPENSSL_ALGO_SHA1;
      }
      
      return null;
    }
    
    function _urlencode($value) {
      return str_replace('%7E', '~', rawurlencode($value));
    }
    
    function _get_parameters_as_string(array $parameters) {
      $params = array();
      foreach ($parameters as $key => $value) {
        $params[] = $key . '=' . self::_urlencode($value);
      }
      return implode('&', $params);
    }

    function _sign($data, $key, $algorithm) {
      if ($algorithm === 'HmacSHA1') {
        $hash = 'sha1';
      } else if ($algorithm === 'HmacSHA256') {
        $hash = 'sha256';
      } else {
        throw new Exception ("Non-supported signing method specified");
      }
      
      return base64_encode( hash_hmac($hash, $data, $key, true) );
    }
    
    function get_public_key($certificate_url) {       
      if (isset(self::$public_key_cache[$certificate_url])) {
        return self::$public_key_cache[$certificate_url];
      }
    
      //fetch the certificate and cache it
      $options = array(
        CURLOPT_SSL_VERIFYHOST => 1,
        CURLOPT_SSL_VERIFYPEER => true, //verify the certificate
        CURLOPT_CAINFO => MODULE_PAYMENT_AMAZON_X509_CERTIFICATE,
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_FOLLOWLOCATION => false,     // do not follow redirects
      );

      $ch = curl_init($certificate_url);

      $seterror = curl_setopt_array( $ch, $options);
            
      $content = curl_exec($ch);

      $err = curl_errno( $ch );
      $errmsg  = curl_error( $ch );
      $header  = curl_getinfo( $ch );      
      
      $header['errno']   = $err;
      $header['errmsg']  = $errmsg;
      $header['content'] = $content;

      $public_key = openssl_get_publickey($content);

      curl_close($ch);

      self::$public_key_cache[$certificate_url] = $public_key;
      return $public_key;
    } 

    function log($content){
      $fh = fopen('includes/logs/amazon.log', 'a+');
      fwrite($fh, $content);
      fclose($fh);
    }
    
    function _validate_request($parameters, $url_end_point, $http_method)  {
      //Input validation
      $signature = $parameters['signature'];
      if (!isset($signature)) {
        self::log( "'signature' is missing from the parameters. \n");
      }
    
      $signature_version = $parameters['signatureVersion'];
      if (!isset($signature_version)) {
        self::log( "'signatureVersion' is missing from the parameters. \n");
      }
    
      $signature_method = $parameters['signatureMethod'];
      if (!isset($signature_method)) {
        self::log( "'signatureMethod' is missing from the parameters. \n");
      }
    
      $signature_algorithm = self::get_signature_algorithm($signature_method);
      if (!isset($signature_algorithm)) {
        self::log( "'signatureMethod' is missing from the parameters. \n");
      }
    
      $certificate_url = $parameters['certificateUrl'];
      if (!isset($certificate_url)) {
        self::log( "'certificateUrl' is missing from the parameters. \n");
      }
    
      $publicKey = self::get_public_key($certificate_url);
      if (!isset($publicKey)) {
        self::log("public key certificate could not fetched from url: " . $certificate_url. '\n');
      }

      //Calculate string to sign
      $host_header = self::_get_host_header($url_end_point);
      $request_url = self::_get_request_url($url_end_point);
    
      //We should not include signature while calculating string to sign.
      unset($parameters['signature']); 
      $string_to_sign = self::_calculate_string_to_sign_v2($parameters, $http_method, $host_header, $request_url);
      //We should include signature back to array after calculating string to sign.
      $parameters['signature'] = $signature;

      // Verification of signature          
      $decoded_signature = base64_decode($signature);
    
      return openssl_verify($string_to_sign, $decoded_signature, $publicKey);
    }    

    function callback(){

      $order_id = $_POST['referenceId'];
      $url_end_point = osc_href_link(FILENAME_CHECKOUT, 'callback&module=' . $this->_code, 'SSL', null, null, true);

      $params = $_POST;
      $params['callback'] = '';
      $params['module'] = 'amazon';
      $error = self::_validate_request($params, $url_end_point, "POST");   

      
      if($error == 1 && $_POST['status'] == 'PS') {
        $comments = 'Amazon IPN Verified.';
        
        osC_Order::process($order_id, $this->order_status, $comments);
      }    
    }
    function process() {
      if (is_numeric($_GET['referenceId'])) {
        osC_Order::process($_GET['referenceId'], ORDERS_STATUS_PENDING);
      }
    }
  

  }
?>
