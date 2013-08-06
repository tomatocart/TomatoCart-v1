<?php
/*
  $Id: eway_au.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  define('REAL_TIME', 'Real-Time');
  define('REAL_TIME_CVN', 'Real-Time CVN');
  define('GEO_IP_ANTI_FRAUD', 'Geo-IP Anti Fraud');
  define('REAL_TIME_HOSTED', 'Real-Time Hosted');
  define('REAL_TIME_CVN_HOSTED', 'Real-Time CVN Hosted');

  define('EWAY_PAYMENT_LIVE_REAL_TIME', 'https://www.eway.com.au/gateway/xmlpayment.asp');
  define('EWAY_PAYMENT_LIVE_REAL_TIME_TESTING_MODE', 'https://www.eway.com.au/gateway/xmltest/testpage.asp');
  define('EWAY_PAYMENT_LIVE_REAL_TIME_CVN', 'https://www.eway.com.au/gateway_cvn/xmlpayment.asp');
  define('EWAY_PAYMENT_LIVE_REAL_TIME_CVN_TESTING_MODE', 'https://www.eway.com.au/gateway_cvn/xmltest/testpage.asp');
  define('EWAY_PAYMENT_LIVE_GEO_IP_ANTI_FRAUD', 'http://www.eway.com.au/gateway_cvn/xmlbeagle.asp');
  define('EWAY_PAYMENT_LIVE_GEO_IP_ANTI_FRAUD_TESTING_MODE', 'https://www.eway.com.au/gateway_beagle/test/xmlbeagle_test.asp'); 
  define('EWAY_PAYMENT_HOSTED_REAL_TIME', 'https://www.eway.com.au/gateway/payment.asp');
  define('EWAY_PAYMENT_HOSTED_REAL_TIME_TESTING_MODE', 'https://www.eway.com.au/gateway/payment.asp');
  define('EWAY_PAYMENT_HOSTED_REAL_TIME_CVN', 'https://www.eway.com.au/gateway_cvn/payment.asp');
  define('EWAY_PAYMENT_HOSTED_REAL_TIME_CVN_TESTING_MODE', 'https://www.eway.com.au/gateway_cvn/payment.asp');   

  class osC_Payment_eway_au extends osC_Payment {
    var $_title,
        $_code = 'eway_au',
        $_status = false,
        $_sort_order,
        $_order_id;

    function osC_Payment_eway_au() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;

      $this->_title = $osC_Language->get('payment_eway_au_title');
      $this->_method_title = $osC_Language->get('payment_eway_au_method_title');
      $this->_status = (MODULE_PAYMENT_EWAY_AU_STATUS == '1') ? true : false;
      $this->_sort_order = MODULE_PAYMENT_EWAY_AU_SORT_ORDER;
      
      if ($this->_status === true) {
        if ((int)MODULE_PAYMENT_EWAY_AU_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_EWAY_AU_ORDER_STATUS_ID;
        }

        if ((int)MODULE_PAYMENT_EWAY_AU_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_EWAY_AU_ZONE);
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
      global $osC_Database, $osC_Language, $osC_ShoppingCart;
      
      $selection = array('id' => $this->_code,
                         'module' => $this->_method_title);
      
      if(MODULE_PAYMENT_EWAYPAYMENT_PROCESSING_METHOD == REAL_TIME || MODULE_PAYMENT_EWAYPAYMENT_PROCESSING_METHOD == REAL_TIME_CVN || MODULE_PAYMENT_EWAYPAYMENT_PROCESSING_METHOD == GEO_IP_ANTI_FRAUD){
      
	      for ($i = 1; $i < 13; $i++) {
	        $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B', mktime(0, 0, 0, $i, 1)));
	      }
	
	      $year = date('Y');
	      for ($i = $year; $i < $year + 10; $i++) {
	        $expires_year[] = array('id' => $i, 'text' => strftime('%Y',mktime(0, 0, 0, 1, 1, $i)));
	      }
	
        $selection['fields'] = array(array('title' => $osC_Language->get('payment_eway_au_credit_card_owner'),
                                           'field' => osc_draw_input_field('eway_au_cc_owner', $osC_ShoppingCart->getBillingAddress('firstname') . ' ' . $osC_ShoppingCart->getBillingAddress('lastname'))),
                                     array('title' => $osC_Language->get('payment_eway_au_credit_card_number'),
                                           'field' => osc_draw_input_field('eway_au_cc_number')),
                                     array('title' => $osC_Language->get('payment_eway_au_credit_card_expiry_date'),
                                           'field' => osc_draw_pull_down_menu('eway_au_cc_expires_month', $expires_month) . '&nbsp;' . osc_draw_pull_down_menu('eway_au_cc_expires_year', $expires_year)),
                                     array('title' => $osC_Language->get('payment_eway_au_credit_card_cvv'),
                                           'field' => osc_draw_input_field('eway_au_cc_cvv')));
      }

      return $selection;
      
    }    

    function pre_confirmation_check() {
    }
    
    function _doPayment() {
      global $messageStack;
      
      if(MODULE_PAYMENT_EWAY_AU_SSL_VERIFIER > 0 && ($request_type == 'NONSSL')) {
        echo 'TRANSACTION ERROR: INSECURE (solutions: make it https:// or change "SSL Verifier" to FALSE from "eWay Payment" module)';
        exit;
      }
      
      //live payment or hosted payment
      if(MODULE_PAYMENT_EWAYPAYMENT_PROCESSING_METHOD == REAL_TIME || MODULE_PAYMENT_EWAYPAYMENT_PROCESSING_METHOD == REAL_TIME_CVN || MODULE_PAYMENT_EWAYPAYMENT_PROCESSING_METHOD == GEO_IP_ANTI_FRAUD){
        require_once('ext/eway/eway_payment_live.php');
        $eway = new EwayPaymentLive(MODULE_PAYMENT_EWAY_AU_CUSTOMER_ID, MODULE_PAYMENT_EWAYPAYMENT_PROCESSING_METHOD, (MODULE_PAYMENT_EWAYPAYMENT_GATEWAY_MODE == 'Live gateway') ? true : false);
        
//        $eway->setTransactionData("TotalAmount", 1);
        $eway->setTransactionData("TotalAmount", $_POST['my_totalamount']); //mandatory field
        $eway->setTransactionData("CustomerFirstName", $_POST['my_firstname']);
        $eway->setTransactionData("CustomerLastName", $_POST['my_lastname']);
        $eway->setTransactionData("CustomerEmail", $_POST['my_email']);
        $eway->setTransactionData("CustomerAddress", $_POST['my_address']);
        $eway->setTransactionData("CustomerPostcode", $_POST['my_postcode']);
        $eway->setTransactionData("CustomerInvoiceDescription", $_POST['my_invoice_description']);
        $eway->setTransactionData("CustomerInvoiceRef", $_POST['my_invoice_ref']);
        $eway->setTransactionData("CardHoldersName", $_POST['my_card_name']); //mandatory field
        $eway->setTransactionData("CardNumber", $_POST['my_card_number']); //mandatory field
        $eway->setTransactionData("CardExpiryMonth", $_POST['my_card_exp_month']); //mandatory field
        $eway->setTransactionData("CardExpiryYear", $_POST['my_card_exp_year']); //mandatory field
        $eway->setTransactionData("TrxnNumber", "");
        $eway->setTransactionData("Option1", $_POST['my_ewayOption1']);
        $eway->setTransactionData("Option2", "");
        $eway->setTransactionData("Option3", "");
        
        //for REAL_TIME_CVN
        $eway->setTransactionData("CVN", $_POST['my_eway_cvn']);
        
        if(MODULE_PAYMENT_EWAYPAYMENT_PROCESSING_METHOD == GEO_IP_ANTI_FRAUD) {
          //for GEO_IP_ANTI_FRAUD     
          $eway->setTransactionData("CustomerIPAddress", $eway->getVisitorIP()); //mandatory field when using Geo-IP Anti-Fraud
          $eway->setTransactionData("CustomerBillingCountry", $_POST['my_country_code']); //mandatory field when using Geo-IP Anti-Fraud
        }
        
        //$eway->setCurlPreferences(CURLOPT_CAINFO, "/usr/share/ssl/certs/my.cert.crt"); //Pass a filename of a file holding one or more certificates to verify the peer with. This only makes sense when used in combination with the CURLOPT_SSL_VERIFYPEER option. 
        //$eway->setCurlPreferences(CURLOPT_CAPATH, "/usr/share/ssl/certs/my.cert.path");
        if(MODULE_PAYMENT_EWAY_AU_SSL_VERIFIER < 0)
          $eway->setCurlPreferences(CURLOPT_SSL_VERIFYPEER, 0);  //pass a long that is set to a zero value to stop curl from verifying the peer's certificate 
        
            
        if(MODULE_PAYMENT_EWAY_AU_CURL_PROXY != ""){
          $eway->setCurlPreferences(CURLOPT_PROXYTYPE, CURLPROXY_HTTP); //use CURL proxy, for example godaddy.com hosting requires it
          $eway->setCurlPreferences(CURLOPT_PROXY, MODULE_PAYMENT_EWAY_AU_CURL_PROXY); //use CURL proxy, for example godaddy.com hosting requires it
        }
        
        $response = $eway->doPayment();
        
        if ($response["EWAYTRXNSTATUS"] == "False") {
          $messageStack->add_session('checkout', $response[EWAYTRXNERROR], 'error');
          
          osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'checkout&view=paymentInformationForm', 'SSL', null, null, true));
        } else if ($response["EWAYTRXNSTATUS"] == "True") {
          $comment = 'eWay ' . MODULE_PAYMENT_EWAYPAYMENT_PROCESSING_METHOD . ' Success[' . $response[EWAYTRXNERROR] . ']';
          
          $this->_order_id = osC_Order::insert();
      
          osC_Order::process($this->_order_id, $this->order_status, $comment);
        }
      } else {
        //hosted payment
        require_once('ext/eway/eway_payment_hosted.php');
        
        $eway = new EwayPaymentHosted(MODULE_PAYMENT_EWAY_AU_CUSTOMER_ID, MODULE_PAYMENT_EWAYPAYMENT_PROCESSING_METHOD, (MODULE_PAYMENT_EWAYPAYMENT_GATEWAY_MODE == 'Live gateway') ? true : false);    
        
        $orders_id = 0;
        if (isset($_SESSION['prepOrderID'])) {
          $_prep = explode('-', $_SESSION['prepOrderID']);
          $orders_id = $_prep[1];
        }
        
        $eway->setTransactionData("TotalAmount", $_POST['my_totalamount']); //mandatory field
//        $eway->setTransactionData("TotalAmount", 1);
        $eway->setTransactionData("CustomerFirstName", $_POST['my_firstname']);
        $eway->setTransactionData("CustomerLastName", $_POST['my_lastname']);
        $eway->setTransactionData("CustomerEmail", $_POST['my_email']);
        $eway->setTransactionData("CustomerAddress", $_POST['my_address']);
        $eway->setTransactionData("CustomerPostcode", $_POST['my_postcode']);
        $eway->setTransactionData("CustomerInvoiceDescription", $_POST['my_invoice_description']);
        $eway->setTransactionData("CustomerInvoiceRef", $_POST['my_invoice_ref']);
        $eway->setTransactionData("URL", osc_href_link(FILENAME_CHECKOUT, 'callback&module=' . $this->_code . '&return=yes&orders_id=' . $orders_id, 'SSL', null, null, true)); //the script that will receive the results: http://www.mywebsite.com.au/testewayhosted.php?return=yes
        $eway->setTransactionData("SiteTitle", STORE_NAME);
        $eway->setTransactionData("TrxnNumber", "");
        $eway->setTransactionData("Option1", $_POST['my_ewayOption1']);
        $eway->setTransactionData("Option2", "");
        $eway->setTransactionData("Option3", "");
                
        $eway->doPayment();
        exit;
      }
    }
    
    function process() {
      $this->_doPayment();
    }
    
    function confirmation(){
      if ((MODULE_PAYMENT_EWAYPAYMENT_PROCESSING_METHOD == REAL_TIME_HOSTED) || (MODULE_PAYMENT_EWAYPAYMENT_PROCESSING_METHOD == REAL_TIME_CVN_HOSTED)) {
        $this->_order_id = osC_Order::insert(ORDERS_STATUS_PREPARING);
      }
    }
    
    function process_button() {
      global $osC_ShoppingCart, $osC_Customer, $osC_Session;
      
      $params = array('my_invoice_ref' => $osC_Customer->getID(). '-' . date('Ymdhis'),
                      'my_totalamount' => $osC_ShoppingCart->getTotal(),
                      'my_firstname' => $osC_ShoppingCart->getBillingAddress('firstname'),
                      'my_lastname' => $osC_ShoppingCart->getBillingAddress('lastname'),
                      'my_address' => $osC_ShoppingCart->getBillingAddress('street_address'),
                      'my_postcode' => $osC_ShoppingCart->getBillingAddress('postcode'),
                      'eWAYURL' => osc_href_link(FILENAME_CHECKOUT, 'process', 'SSL', null, null, true),
                      'eWAYAutoRedirect' => '1',
                      'my_email' => $osC_Customer->getEmailAddress(),
                      'my_country_code' => $osC_ShoppingCart->getBillingAddress('country_id'),
                      'my_card_name' => $_POST['eway_au_cc_owner'],
                      'my_card_number' => $_POST['eway_au_cc_number'],
                      'my_card_exp_month' => $_POST['eway_au_cc_expires_month'],
                      'my_card_exp_year' => $_POST['eway_au_cc_expires_year'],
                      'my_eway_cvn' => $_POST['eway_au_cc_cvv'],
                      'my_ewayOption1' => $osC_Session->getID(),
                      'my_invoice_description' => $osC_Session->getID(),
                      'my_order_id' => $this->_order_id);
      
      $process_button_string = '';
      foreach ($params as $key => $value) {
        $key = trim($key);
        $value = trim($value);
        $process_button_string .= osc_draw_hidden_field($key, $value);
      }

      return $process_button_string;
    }
    
    function getJavascriptBlock() {
      global $osC_Language, $osC_CreditCard;

      $osC_CreditCard = new osC_CreditCard();
      $js = '';
      
      if(MODULE_PAYMENT_EWAYPAYMENT_PROCESSING_METHOD == REAL_TIME || MODULE_PAYMENT_EWAYPAYMENT_PROCESSING_METHOD == REAL_TIME_CVN || MODULE_PAYMENT_EWAYPAYMENT_PROCESSING_METHOD == GEO_IP_ANTI_FRAUD){
	      $js .= '  if (payment_value == "' . $this->_code . '") {' . "\n" .
	            '    var eway_au_cc_owner = document.checkout_payment.eway_au_cc_owner.value;' . "\n" .
	            '    var eway_au_cc_number = document.checkout_payment.eway_au_cc_number.value;' . "\n" .
	            '    eway_au_cc_number = eway_au_cc_number.replace(/[^\d]/gi, "");' . "\n" . 
	            '    var eway_au_cc_cvv = document.checkout_payment.eway_au_cc_cvv.value;'. "\n";
	
	      if (CFG_CREDIT_CARDS_VERIFY_WITH_JS == '1') {
	        $js .= '    var eway_au_cc_type_match = false;' . "\n";
	      }
	
	      $js .= '    if (eway_au_cc_owner == "" || eway_au_cc_owner.length < ' . (int)CC_OWNER_MIN_LENGTH . ') {' . "\n" .
	             '      error_message = error_message + "' . sprintf($osC_Language->get('payment_eway_eu_js_credit_card_owner'), CC_OWNER_MIN_LENGTH) . '\n";' . "\n" .
	             '      error = 1;' . "\n" .
	             '    }' . "\n";
	      
        $js .= '    if (eway_au_cc_number == "" || eway_au_cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
             '      error_message = error_message + "' . sprintf($osC_Language->get('payment_eway_eu_js_credit_card_number'), CC_NUMBER_MIN_LENGTH) . '\n";' . "\n" .
             '      error = 1;' . "\n" .
             '    }' . "\n";
        
        $js .= '    if (eway_au_cc_cvv == "" || eway_au_cc_cvv.length < 3) {' . "\n" .
               '      error_message = error_message + "' . sprintf($osC_Language->get('payment_eway_eu_cc_js_credit_card_cvc'), 3) . '\n";' . "\n" .
               '      error = 1;' . "\n" .
               '    }' . "\n";
	
	      $js .= '  }' . "\n";
      }
      
      return $js;
    }    
    
    function callback(){
      global $osC_ShoppingCart;
      
      //process return results from eway 
      if ($_GET["return"] == "yes") {
        if ($_POST["ewayTrxnStatus"] == "False") {
          $messageStack->add_session('checkout', $_POST[eWAYresponseText], 'error');
          
          osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'checkout&view=paymentInformationForm', 'SSL', null, null, true));
        }else if ($_POST["ewayTrxnStatus"] == "True") {
          $comment = 'eWay ' . MODULE_PAYMENT_EWAYPAYMENT_PROCESSING_METHOD . ' Success[eWAYresponseText' . $_POST['eWAYresponseText'] . ']';
          
          osC_Order::process($_GET['orders_id'], $this->order_status, $comment);
          
          $osC_ShoppingCart->reset(true);
                    
          // unregister session variables used during checkout
          unset($_SESSION['comments']);
          
          osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'success', 'SSL'));
        }
      }    
    }
  }
?>