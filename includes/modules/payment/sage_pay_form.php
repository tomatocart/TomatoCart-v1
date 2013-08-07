<?php
/*
  $Id: sage_pay_form.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_sage_pay_form extends osC_Payment {
    var $_title,
        $_code = 'sage_pay_form',
        $_status = false,
        $_sort_order,
        $_order_id,
        $_transaction_response;

    // class constructor
    function osC_Payment_sage_pay_form() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;
      
      $this->_title = $osC_Language->get('payment_sage_pay_form_title');
      $this->_method_title = $osC_Language->get('payment_sage_pay_form_method_title');
      $this->_sort_order = MODULE_PAYMENT_SAGE_PAY_FORM_SORT_ORDER;
      $this->_status = ((MODULE_PAYMENT_SAGE_PAY_FORM_STATUS == '1') ? true : false);

      switch (MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_SERVER) {
        case 'Live':
          $this->form_action_url = 'https://live.sagepay.com/gateway/service/vspform-register.vsp';
          break;

        case 'Test':
          $this->form_action_url = 'https://test.sagepay.com/gateway/service/vspform-register.vsp';
          break;

        default:
          $this->form_action_url = 'https://test.sagepay.com/Simulator/VSPFormGateway.asp';
          break;
      }

      if ($this->_status === true) {
        $this->order_status = MODULE_PAYMENT_SAGE_PAY_FORM_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_SAGE_PAY_FORM_ORDER_STATUS_ID : (int)ORDERS_STATUS_PAID;

        if ((int)MODULE_PAYMENT_SAGE_PAY_FORM_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_SAGE_PAY_FORM_ZONE);
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
      return false;
    }

    function process_button() {
      global $osC_Customer, $osC_Currencies, $osC_ShoppingCart, $osC_Tax, $osC_Session, $osC_Language;
      
      $process_button_string = '';
      
      $params = array('VPSProtocol' => '2.23',
                      'ReferrerID' => 'C74D7B82-E9EB-4FBD-93DB-76F0F551C802',
                      'Vendor' => substr(MODULE_PAYMENT_SAGE_PAY_FORM_VENDOR_LOGIN_NAME, 0, 15));
      
      if ( MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_METHOD == 'Payment' ) {
        $params['TxType'] = 'PAYMENT';
      } elseif ( MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_METHOD == 'Deferred' ) {
        $params['TxType'] = 'DEFERRED';
      } else {
        $params['TxType'] = 'AUTHENTICATE';
      }
      
      $crypt = array('VendorTxCode' => substr(date('YmdHis') . '-' . $osC_Customer->getID(). '-' . $osC_ShoppingCart->getCartID(), 0, 40),
                     'Amount' => $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal()), 
                     'Currency' => $osC_Currencies->getCode(), 
                     'Description' => substr(STORE_NAME, 0, 100), 
                     'SuccessURL' => osc_href_link(FILENAME_CHECKOUT, 'process', 'SSL', null, null, true),
                     'FailureURL' => osc_href_link(FILENAME_CHECKOUT, 'checkout', 'SSL', null, null, true), 
                     'CustomerName' => substr($osC_ShoppingCart->getBillingAddress('firstname') . ' ' . $osC_ShoppingCart->getBillingAddress('lastname'), 0, 100), 
                     'CustomerEMail' => substr($osC_Customer->getEmailAddress(), 0, 255), 
                     'BillingSurname' => substr($osC_ShoppingCart->getBillingAddress('lastname'), 0, 20), 
                     'BillingFirstnames' => substr($osC_ShoppingCart->getBillingAddress('firstname'), 0, 20), 
                     'BillingAddress1' => substr($osC_ShoppingCart->getBillingAddress('street_address'), 0, 100), 
                     'BillingCity' => substr($osC_ShoppingCart->getBillingAddress('city'), 0, 40), 
                     'BillingPostCode' => substr($osC_ShoppingCart->getBillingAddress('postcode'), 0, 10), 
                     'BillingCountry' => $osC_ShoppingCart->getBillingAddress('country_iso_code_2'));
      
      if ($crypt['BillingCountry'] == 'US') {
        $crypt['BillingState'] =$osC_ShoppingCart->getBillingAddress('zone_code');
      }
      
      $crypt['BillingPhone'] = substr($osC_ShoppingCart->getBillingAddress('telephone_number'), 0, 20);
      $crypt['DeliverySurname'] = substr($osC_ShoppingCart->getShippingAddress('lastname'), 0, 20);
      $crypt['DeliveryFirstnames'] = substr($osC_ShoppingCart->getShippingAddress('firstname'), 0, 20);
      $crypt['DeliveryAddress1'] = substr($osC_ShoppingCart->getShippingAddress('street_address'), 0, 100);
      $crypt['DeliveryCity'] = substr($osC_ShoppingCart->getShippingAddress('city'), 0, 40);
      $crypt['DeliveryPostCode'] = substr($osC_ShoppingCart->getShippingAddress('postcode'), 0, 10);
      $crypt['DeliveryCountry'] = $osC_ShoppingCart->getShippingAddress('country_iso_code_2');
      
      if ($crypt['DeliveryCountry'] == 'US') {
        $crypt['DeliveryState'] = $osC_ShoppingCart->getShippingAddress('zone_code');
      }
      
      if (osc_not_null(MODULE_PAYMENT_SAGE_PAY_FORM_VENDOR_EMAIL)) {
        $crypt['VendorEMail'] = substr(MODULE_PAYMENT_SAGE_PAY_FORM_VENDOR_EMAIL, 0, 255);
      }
      
      switch (MODULE_PAYMENT_SAGE_PAY_FORM_SEND_EMAIL) {
        case 'No One':
          $crypt['SendEMail'] = 0;
          break;

        case 'Customer and Vendor':
          $crypt['SendEMail'] = 1;
          break;

        case 'Vendor Only':
          $crypt['SendEMail'] = 2;
          break;
      }
      
      if (osc_not_null(MODULE_PAYMENT_SAGE_PAY_FORM_CUSTOMER_EMAIL_MESSAGE)) {
        $crypt['eMailMessage'] = substr(MODULE_PAYMENT_SAGE_PAY_FORM_CUSTOMER_EMAIL_MESSAGE, 0, 7500);
      }
      
      //products
      $contents = array();
      if ($osC_ShoppingCart->hasContents()) {
        $products = $osC_ShoppingCart->getProducts();
        foreach($products as $product) {
          $product_name = $product['name'];

          //gift certificate
          if ($product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
            $product_name .= "\n" . ' - ' . $osC_Language->get('senders_name') . ': ' . $product['gc_data']['senders_name'];

            if ($product['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
              $product_name .= "\n" . ' - ' . $osC_Language->get('senders_email')  . ': ' . $product['gc_data']['senders_email'];
            }

            $product_name .= "\n" . ' - ' . $osC_Language->get('recipients_name') . ': ' . $product['gc_data']['recipients_name'];

            if ($product['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
              $product_name .= "\n" . ' - ' . $osC_Language->get('recipients_email')  . ': ' . $product['gc_data']['recipients_email'];
            }

            $product_name .= "\n" . ' - ' . $osC_Language->get('message')  . ': ' . $product['gc_data']['message'];
          }

          if ($osC_ShoppingCart->hasVariants($product['id'])) {
            foreach ($osC_ShoppingCart->getVariants($product['id']) as $variant) {
              $product_name .= ' - ' . $variant['groups_name'] . ': ' . $variant['values_name'];
            }
          }
          
          $tax = $osC_Tax->getTaxRate($product['tax_class_id'], $osC_ShoppingCart->getTaxingAddress('country_id'), $osC_ShoppingCart->getTaxingAddress('zone_id'));
          
          $contents[] = str_replace(array(':', "\n", "\r", '&'), '', $product_name) . ':' . $product['quantity'] . ':' . $osC_Currencies->formatRaw($product['final_price']) . ':' . $osC_Currencies->formatRaw(($product['tax'] / 100) * $product['final_price']) . ':' . $osC_Currencies->formatRaw((($tax / 100) * $product['final_price']) + $product['final_price']) . ':' . $osC_Currencies->formatRaw(((($tax / 100) * $product['final_price']) + $product['final_price']) * $product['quantity']);
        }
      }
      
      //order totals
      foreach ($osC_ShoppingCart->getOrderTotals() as $total) {
        $contents[] = str_replace(array(':', "\n", "\r", '&'), '', strip_tags($total['title'])) . ':---:---:---:---:' . $osC_Currencies->formatRaw($total['value']);
      }
      
      $crypt['Basket'] = substr(sizeof($contents) . ':' . implode(':', $contents), 0, 7500);
      $crypt['Apply3DSecure'] = '0';
      $crypt_string = '';
      
      foreach ($crypt as $key => $value) {
        $crypt_string .= $key . '=' . trim($value) . '&';
      }
      
      $crypt_string = substr($crypt_string, 0, -1);
      
      $params['Crypt'] = base64_encode($this->simpleXor($crypt_string, MODULE_PAYMENT_SAGE_PAY_FORM_ENCRYPTION_PASSWORD));

      $process_button_string = '';

      foreach ($params as $key => $value) {
        $process_button_string .= osc_draw_hidden_field($key, $value);
      }

      return $process_button_string;
    }

    function process() {
      global $osC_Currencies, $osC_ShoppingCart, $osC_Customer, $osC_Language, $messageStack;
      
      if (isset($_GET['crypt']) && osc_not_null($_GET['crypt'])) {
        $transaction_response = $this->simpleXor($this->base64Decode($_GET['crypt']), MODULE_PAYMENT_SAGE_PAY_FORM_ENCRYPTION_PASSWORD);
        
        $string_array = explode('&', $transaction_response);
        $return = array('Status' => null);
        
        foreach ($string_array as $string) {
          if (strpos($string, '=') != false) {
            $parts = explode('=', $string, 2);
            $return[trim($parts[0])] = trim($parts[1]);
          }
        }
        
        if ( ($return['Status'] != 'OK') && ($return['Status'] != 'AUTHENTICATED') && ($return['Status'] != 'REGISTERED') ) {
	        $error = $this->getErrorMessageNumber($return['StatusDetail']);
	
          osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'checkout' . (osc_not_null($error)) ? '&error=' . $error : '', 'SSL'));
        }
        
        if ( isset($return['VPSTxId']) ) {
          $orders_id = osC_Order::insert();
            
          $sagepay_comments = 'Sage Pay Reference ID: ' . $return['VPSTxId'] . (osc_not_null($_SESSION['comments']) ? "\n\n" . $_SESSION['comments'] : '');
          osC_Order::process($orders_id, $this->order_status, $sagepay_comments);
        }
      }else {
        osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'checkout', 'SSL'));
      }
    }
    
    function get_error() {
      global $osC_Language;
      
      $message = $osC_Language->get('payment_sage_pay_form_error_general');
      
      if ( isset($_GET['error']) && is_numeric($_GET['error']) && $this->errorMessageNumberExists($_GET['error']) ) {
        $message = $this->getErrorMessage($_GET['error']) . ' ' . $message;
      }else if ( isset($_GET['crypt']) && osc_not_null($_GET['crypt']) ) {
        $transaction_response = $this->simpleXor($this->base64Decode($_GET['crypt']), MODULE_PAYMENT_SAGE_PAY_FORM_ENCRYPTION_PASSWORD);
        
        $string_array = explode('&', $transaction_response);
        $return = array('Status' => null);
        
        foreach ($string_array as $string) {
          if (strpos($string, '=') != false) {
            $parts = explode('=', $string, 2);
            $return[trim($parts[0])] = trim($parts[1]);
          }
        }
        
        $error_number = $this->getErrorMessageNumber($return['StatusDetail']);
        
        if ( is_numeric($error_number) && $this->errorMessageNumberExists($error_number) ) {
          $message = $this->getErrorMessage($error_number) . ' ' . $message;
        }
      }
      
      $error = array('title' => $osC_Language->get('payment_sage_pay_form_error_title'),
                     'error' => $message);

      return $error;
      
    }
    
/*  From the Sage Pay Form PHP Kit:
**  The SimpleXor encryption algorithm                                                                                **
**  NOTE: This is a placeholder really.  Future releases of VSP Form will use AES or TwoFish.  Proper encryption      **
**  This simple function and the Base64 will deter script kiddies and prevent the "View Source" type tampering        **
**  It won't stop a half decent hacker though, but the most they could do is change the amount field to something     **
**  else, so provided the vendor checks the reports and compares amounts, there is no harm done.  It's still          **
**  more secure than the other PSPs who don't both encrypting their forms at all                                      */

    function simpleXor($InString, $Key) {
			// Initialise key array
      $KeyList = array();
      
      $output = "";
			
			// Convert $Key into array of ASCII values
      for ($i = 0; $i < strlen($Key); $i++) {
        $KeyList[$i] = ord(substr($Key, $i, 1));
      }
			
			// Step through string a character at a time
      for ($i= 0; $i < strlen($InString); $i++) {
        $output .= @chr(ord(substr($InString, $i, 1)) ^ ($KeyList[$i % strlen($Key)]));
      }
			
			// Return the result
      return $output;
    }
    
/*  From the Sage Pay Form PHP Kit:
** Base 64 decoding function **
** PHP does it natively but just for consistency and ease of maintenance, let's declare our own function **/
    function base64Decode($scrambled) {
      // Initialise output variable
      $output = '';

      // Fix plus to space conversion issue
      $scrambled = str_replace(' ', '+', $scrambled);

      // Do encoding
      $output = base64_decode($scrambled);
      
      // Return the result
      return $output;
    }
    
    function loadErrorMessages() {
      $this->_error_messages = array(  '400' => 'The syntax of the request was not understood by the server.',
                                       '401' => 'The request needs user authentication.',
                                       '403' => 'The server has refused to fulfill the request.',
                                       '404' => 'The document/file requested by the client was not found.',
                                       '405' => 'The method specified in the Request-Line is not allowed for the specified resource.',
                                       '408' => 'The client failed to send a request in the time allowed by the server.',
                                       '414' => 'The request was unsuccessful because the URI specified is longer than the server is willing to process.',
                                       '500' => 'The request was unsuccessful due to an unexpected condition encountered by the server.',
                                       '501' => 'The request was unsuccessful because the server can not support the functionality needed to fulfill the request.',
                                       '503' => 'The request was unsuccessful to the server being down or overloaded.',
                                       '2000' => 'The Authorisation was Declined by the bank.',
                                       '2001' => 'The Authorisation was Rejected by the vendor rule-base.',
                                       '2002' => 'The Authorisation timed out.',
                                       '2003' => 'An ERROR has occurred on the Protx System.',
                                       '2008' => 'The Transaction timed-out.',
                                       '2009' => 'The network connection to the bank is currently unavailable.',
                                       '2015' => 'The server encountered an unexpected condition which prevented it from fulfilling the request.',
                                       '3002' => 'The VPSTxId is invalid.',
                                       '3003' => 'The Currency is invalid.',
                                       '3004' => 'The Amount is invalid.',
                                       '3005' => 'The Amount is outside the mininum and maximum limits.',
                                       '3006' => 'The fractional part of the Amount is invalid for the specified currency.',
                                       '3007' => 'The RelatedSecurityKey format invalid.',
                                       '3008' => 'The Vendor or Vendorname format is invalid.',
                                       '3009' => 'The VendorTxCode is missing.',
                                       '3010' => 'The RelatedVPSTxId is invalid.',
                                       '3011' => 'The NotificationURL format is invalid.',
                                       '3012' => 'The RelatedVendorTxCode format invalid.',
                                       '3013' => 'The Description is missing.',
                                       '3014' => 'The TxType or PaymentType is invalid.',
                                       '3015' => 'The BillingAddress value is too long.',
                                       '3016' => 'The BillingPostCode value is too long.',
                                       '3017' => 'The RelatedTxAuthNo format is invalid.',
                                       '3018' => 'The GiftAid flag is invalid. If a value is supplied, should contain either 0 or 1.',
                                       '3019' => 'The ApplyAVSCV2 flag is invalid. The value, if supplied, should contain either 0, 1, 2 or 3.',
                                       '3020' => 'The Apply3DSecure flag is invalid. The value, if supplied, should contain either 0, 1, 2 or 3.',
                                       '3021' => 'The Basket format is invalid.',
                                       '3022' => 'The CustomerEMail is too long.',
                                       '3023' => 'The ContactFax is too long.',
                                       '3024' => 'The ContactNumber is too long.',
                                       '3025' => 'The DeliveryPostCode is too long.',
                                       '3026' => 'The DeliveryAddress is too long.',
                                       '3027' => 'The BillingPostCode is too long.',
                                       '3028' => 'The BillingAddress is too long.',
                                       '3029' => 'The FailureURL is missing.',
                                       '3030' => 'The SuccessURL is missing.',
                                       '3031' => 'The Amount value is required.',
                                       '3032' => 'The Amount format is invalid.',
                                       '3033' => 'The RelatedSecurityKey is required.',
                                       '3034' => 'The Vendor or VendorName value is required.',
                                       '3035' => 'The VendorTxCode format is invalid.',
                                       '3036' => 'The Description format is invalid.',
                                       '3037' => 'The NotificationURL is too long.',
                                       '3038' => 'The RelatedVendorTxCode is required.',
                                       '3039' => 'The TxType or PaymentType is missing.',
                                       '3040' => 'The RelatedTxAuth number is required.',
                                       '3041' => 'The Basket field is too long.',
                                       '3042' => 'The CustomerName field is too long.',
                                       '3043' => 'The eMailMessage field is too long.',
                                       '3044' => 'The VendorEMail is too long.',
                                       '3045' => 'The Currency field is missing.',
                                       '3046' => 'The VPSTxId field is missing.',
                                       '3047' => 'Invalid VPSTxId format.',
                                       '3048' => 'The CardNumber length is invalid.',
                                       '3049' => 'The StartDate format is invalid.',
                                       '3050' => 'The ExpiryDate format is invalid.',
                                       '3051' => 'The CardNumber field is required.',
                                       '3052' => 'The ExpiryDate field is required.',
                                       '3053' => 'The IssueNumber format is invalid.',
                                       '3054' => 'The CardType length is invalid.',
                                       '3055' => 'The CardType field is required.',
                                       '3056' => 'Invalid Amount field format. The Amount value contains a decimal point.',
                                       '3057' => 'The CV2 format is invalid.',
                                       '3058' => 'The CardHolder field is required.',
                                       '3059' => 'The CardHolder value is too long.',
                                       '3060' => 'The GiftAid format is invalid.',
                                       '3061' => 'The AuthCode format invalid.',
                                       '3062' => 'The CardNumber field should only contain numbers. No spaces, hyphens or other characters or separators.',
                                       '3063' => 'The 3DStatus value is too long.',
                                       '3064' => 'The ECI format is invalid.',
                                       '3065' => 'The XID format is invalid.',
                                       '3066' => 'The CAVV format is invalid.',
                                       '3067' => 'The ClientIPAddress is too long.',
                                       '3068' => 'The PaymentSystem invalid.',
                                       '3069' => 'The PaymentSystem is not supported on the account.',
                                       '3070' => 'The RelatedVPSTxId is required.',
                                       '3071' => 'The RelatedVPSTxId format is invalid.',
                                       '3072' => 'The TxAuthNo field is missing.',
                                       '3073' => 'The TxAuthNo format is invalid.',
                                       '3074' => 'The SecurityKey is missing.',
                                       '3075' => 'The SecurityKey format is invalid.',
                                       '3076' => 'The NotificationURL is required.',
                                       '3077' => 'The CustomerName is required.',
                                       '3078' => 'The CustomerEMail format is invalid.',
                                       '3079' => 'The ClientIPAddress format is invalid. Should not include leading zero\'s, and only include values in the range of 0 to 255.',
                                       '3080' => 'The VendorTxCode value is too long.',
                                       '3081' => 'The RelatedVendorTxCode value is too long.',
                                       '3082' => 'The Description value is too long.',
                                       '3083' => 'The RelatedTxAuthNo value is too long.',
                                       '3084' => 'The FailureURL value is too long.',
                                       '3085' => 'The FailureURL format is invalid.',
                                       '3086' => 'The SuccessURL value is too long.',
                                       '3087' => 'The SuccessURL format is invalid.',
                                       '3088' => 'The VendorEMail format is invalid.',
                                       '3089' => 'The BillingAddress is required.',
                                       '3090' => 'The BillingPostCode is required.',
                                       '3091' => 'The BillingAddress and BillingPostCode are required.',
                                       '3092' => 'The DeliveryAddress and DeliveryPostcode are required.',
                                       '3093' => 'The DeliveryAddress is required.',
                                       '3094' => 'The DeliveryPostcode is required.',
                                       '3095' => 'The VPSProtocol value is invalid.',
                                       '3096' => 'The VPSProtocol value is required.',
                                       '3097' => 'The VPSProtocol value is outside the valid range. Should be between 2.00 and 2.22.',
                                       '3098' => 'The VPSProtocol value is not supported by the system in use.',
                                       '3099' => 'The AccountType is not setup on this account.',
                                       '3100' => 'The AccountType value is invalid.',
                                       '3101' => 'The PaymentSystem does not support direct refund.',
                                       '3102' => 'The ReleaseAmount invalid.',
                                       '4000' => 'The VendorName is invalid or the account is not active.',
                                       '4001' => 'The VendorTxCode has been used before. All VendorTxCodes should be unique.',
                                       '4002' => 'An active transaction with this VendorTxCode has been found but the Amount is different.',
                                       '4003' => 'An active transaction with this VendorTxCode has been found but the Currency is different.',
                                       '4004' => 'An active transaction with this VendorTxCode has been found but the TxType is different.',
                                       '4005' => 'An active transaction with this VendorTxCode has been found but the some data fields are different.',
                                       '4006' => 'The TxType requested is not supported on this account.',
                                       '4007' => 'The TxType requested is not active on this account.',
                                       '4008' => 'The Currency is not supported on this account.',
                                       '4009' => 'The Amount is outside the allowed range.',
                                       '4020' => 'Information received from an Invalid IP address.',
                                       '4021' => 'The Card Range not supported by the system.',
                                       '4022' => 'The Card Type selected does not match card number.',
                                       '4023' => 'The Card Issue Number length is invalid.',
                                       '4024' => 'The Card Issue Number is required.',
                                       '4025' => 'The Card Issue Number is invalid.',
                                       '4026' => '3D-Authentication failed. This vendor\'s rules require a successful 3D-Authentication.',
                                       '4027' => '3D-Authentication failed. Cannot authorise this card.',
                                       '4028' => 'The RelatedVPSTxId cannot be found.',
                                       '4029' => 'The RelatedVendorTxCode does not match the original transaction.',
                                       '4030' => 'The RelatedTxAuthNo does not match the original transaction.',
                                       '4031' => 'The RelatedSecurityKey does not match the original transaction.',
                                       '4032' => 'The original transaction was carried out by a different Vendor.',
                                       '4033' => 'The Currency does not match the original transaction.',
                                       '4034' => 'The Transaction has already been Refunded.',
                                       '4035' => 'This Refund would exceed the amount of the original transaction.',
                                       '4036' => 'The Transaction has already been Voided.',
                                       '4037' => 'The Related transaction is not a DEFFERED payment.',
                                       '4038' => 'The Transaction has already been Released.',
                                       '4039' => 'The Tranaction is not in a DEFERRED state.',
                                       '4040' => 'The Transaction has been Aborted.',
                                       '4041' => 'The Transaction type does not support the requested operation.',
                                       '4042' => 'The VendorTxCode has been used before for another transaction. All VendorTxCodes must be unique.',
                                       '4043' => 'The Vendor Rule Bases disallow this card range.',
                                       '4044' => 'This Authorise would exceed 115% of the value of the original transaction.',
                                       '4045' => 'The Related transaction is not an AUTHENTICATE.',
                                       '4046' => '3D-Authentication required. Cannot authorise this card.',
                                       '4047' => 'The vendor account is closed.',
                                       '4048' => 'The Card Number length is invalid.',
                                       '4049' => 'The ReleaseAmount larger the original amount.',
                                       '5001' => 'The required service is not available or invalid.',
                                       '5002' => 'Invalid request.',
                                       '5003' => 'Internal server error.',
                                       '5004' => 'The Transaction state is invalid.',
                                       '5005' => 'The Vendor configuration is missing or invalid.',
                                       '5006' => 'Unable to redirect to Vendor\'s web site. The Vendor failed to provide a RedirectionURL.',
                                       '5007' => 'Invalid request. A required parameter is missing.',
                                       '5008' => 'Missing Custom vendor template.',
                                       '5009' => 'The Encryption password is missing.',
                                       '5010' => 'The CardNumber is required.',
                                       '5011' => 'The check digit invalid. Card failed the LUHN check. Check the card number and resubmit.',
                                       '5012' => 'The CardHolder name is required.',
                                       '5013' => 'The card has expired.',
                                       '5014' => 'The card expiry date is required.',
                                       '5015' => 'Card validation failure.',
                                       '5016' => 'The StartDate is in the future. The card is not yet valid.',
                                       '5017' => 'The Security Code is required.',
                                       '5018' => 'The Security Code length is invalid.',
                                       '5019' => 'The Security Code is not a number.',
                                       '5020' => 'The Card Address is required.',
                                       '5021' => 'The Card Address is too long.',
                                       '5022' => 'The Post Code value is required.',
                                       '5023' => 'The Post Code value is too long.',
                                       '5024' => 'The CardHolder value is too long.',
                                       '5025' => 'The number of authorisation attempts exceeds the limit.',
                                       '5026' => 'The Card Number is not numeric.',
                                       '5027' => 'The Card Start Date is invalid.',
                                       '5028' => 'The Card Expiry Date is invalid.',
                                       '5029' => '3D-Authentication failed. This vendor\'s rules require a successful 3D-Authentication.',
                                       '5030' => 'Unable to decrypt the request message. This might be caused by an incorrect password or invalid encoding.',
                                       '5994' => 'The Authorisation process failed, due to an internal server error.',
                                       '5995' => 'The AVS/CV2 checks failed.',
                                       '5996' => 'The Authorisation process timed-out. The bank did not respond within an acceptable time limit.',
                                       '5997' => 'A communication related error occured.',
                                       '5998' => 'Duplicate vendor notification attempt.',
                                       '5999' => 'The Session is invalid or has expired.',
                                       '6000' => 'Data Access Error.');
    }
    
    function errorMessageNumberExists($number) {
      if (!isset($this->_error_messages)) {
        $this->loadErrorMessages();
      }

      return (is_numeric($number) && isset($this->_error_messages[$number]));
    }
    
    function getErrorMessageNumber($string) {
      if (!isset($this->_error_messages)) {
        $this->loadErrorMessages();
      }
    
      $error = explode(' ', $string, 2);

      if (is_numeric($error[0]) && $this->errorMessageNumberExists($error[0])) {
        return $error[0];
      }

      return false;
    }
    
    function getErrorMessage($number) {
      if (!isset($this->_error_messages)) {
        $this->loadErrorMessages();
      }

      if (is_numeric($number) && $this->errorMessageNumberExists($number)) {
        return $this->_error_messages[$number];
      }

      return false;
    }
  }
?>
