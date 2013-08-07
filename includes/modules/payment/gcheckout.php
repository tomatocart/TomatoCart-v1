<?php
/*
  $Id: gcheckout.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_gcheckout extends osC_Payment {
    
    var $_title,
        $_code = 'gcheckout',
        $_status = false,
        $_sort_order,
        $_order_id,
        $_ignore_order_totals = array('sub_total', 'tax', 'total');
  
    function osC_Payment_gcheckout() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;
  
      $this->_title = $osC_Language->get('payment_gcheckout_title');
      $this->_method_title = $osC_Language->get('payment_gcheckout_method_title');
      $this->_status = (MODULE_PAYMENT_GCHECKOUT_STATUS == '1') ? true : false;
      $this->_sort_order = MODULE_PAYMENT_GCHECKOUT_SORT_ORDER;
  
      switch (MODULE_PAYMENT_GCHECKOUT_SERVER) {
        case 'Production':
          $this->form_action_url = 'https://checkout.google.com/api/checkout/v2/checkout/Merchant/'. MODULE_PAYMENT_GCHECKOUT_MERCHANT_ID;
          break;
  
        default:
          $this->form_action_url = 'https://sandbox.google.com/checkout/api/checkout/v2/checkout/Merchant/'. MODULE_PAYMENT_GCHECKOUT_MERCHANT_ID;
          break;
      }      
        
      if ($this->_status === true) {
        if ((int)MODULE_PAYMENT_GCHECKOUT_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_GCHECKOUT_ORDER_STATUS_ID;
        }
  
        if ((int)MODULE_PAYMENT_GCHECKOUT_ZONE > 0) {
          $check_flag = false;
  
          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_GCHECKOUT_ZONE);
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
      global $osC_ShoppingCart, $osC_Tax, $osC_Language, $osC_Currencies, $osC_Session;  

      require_once('includes/classes/product.php');
      require_once('ext/googlecheckout/googlecart.php');
      require_once('ext/googlecheckout/googleitem.php');
      require_once('ext/googlecheckout/googleshipping.php');
      
      $cart = new GoogleCart(MODULE_PAYMENT_GCHECKOUT_MERCHANT_ID, 
                             MODULE_PAYMENT_GCHECKOUT_MERCHANT_KEY, 
                             MODULE_PAYMENT_GCHECKOUT_SERVER, 
                             MODULE_PAYMENT_GCHECKOUT_CURRENCY);

      //transfer the whole cart
      if (MODULE_PAYMENT_GCHECKOUT_TRANSFER_CART == '1') {
        //products
        $products = $osC_ShoppingCart->getProducts();
        foreach($products as $product) {
          $name = $product['name'];
          
          //gift certificate
          if ($product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
            $name .= "\n" . ' - ' . $osC_Language->get('senders_name') . ': ' . $product['gc_data']['senders_name'];
            
            if ($product['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
              $name .= "\n" . ' - ' . $osC_Language->get('senders_email')  . ': ' . $product['gc_data']['senders_email'];
            }
            
            $name .= "\n" . ' - ' . $osC_Language->get('recipients_name') . ': ' . $product['gc_data']['recipients_name'];
            
            if ($product['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
              $name .= "\n" . ' - ' . $osC_Language->get('recipients_email')  . ': ' . $product['gc_data']['recipients_email'];
            }
            
            $name .= "\n" . ' - ' . $osC_Language->get('message')  . ': ' . $product['gc_data']['message'];
          }
          
          //variants
          $variants_array = array();
          if ($osC_ShoppingCart->hasVariants($product['id'])) {
            foreach ($osC_ShoppingCart->getVariants($product['id']) as $variants) {
              $variants_array[$variants['groups_id']] = $variants['variants_values_id'];
    
              $name .= "\n" . ' - ' . $variants['groups_name'] . ': ' . $variants['values_name'];
            }
          }
          
          //get tax
          $tax = $osC_Tax->getTaxRate($product['tax_class_id'], $osC_ShoppingCart->getTaxingAddress('country_id'), $osC_ShoppingCart->getTaxingAddress('zone_id'));
          if (DISPLAY_PRICE_WITH_TAX == '1') {
            $price = $osC_Currencies->addTaxRateToPrice($product['final_price'], $tax);
          }else {
            $price = $product['final_price'] + osc_round($product['final_price'] * ($tax / 100), $osC_Currencies->currencies[DEFAULT_CURRENCY]['decimal_places']);
          }
  
          $osC_Product = new osC_Product($product['id']);
          $gitem = new GoogleItem($name, $osC_Product->getDescription(), intval($product['quantity']), $price);
          
          $gitem->SetMerchantPrivateItemData(new MerchantPrivateItemData(array('item' => base64_encode(serialize($product)))));
          $gitem->SetMerchantItemId($product['id']);
          
          $cart->AddItem($gitem);
        }
        
        //add order totals modules into gcheckout cart as item such as: coupon, gift certificate, low order fee
        //exclude modules: sub_total, tax, total and shipping module
        $shipping_cost = 0;
        foreach ($osC_ShoppingCart->getOrderTotals() as $total) {
          if((!in_array($total['code'], $this->_ignore_order_totals)) && (strstr($total['code'], 'shipping') === FALSE)){
            $gitem = new GoogleItem($total['title'], '', '1', $total['value'] + $total['tax']);
            $gitem->SetMerchantPrivateItemData(new MerchantPrivateItemData(array('order_total' => base64_encode(serialize($total)))));
                               
            $cart->AddItem($gitem);
          } else if (strstr($total['code'], 'shipping') !== FALSE) {
            $shipping_cost = $total['value'] + $total['tax'];
          }
        }
        
        //shipping method
        $cart->AddShipping(new GooglePickUp($osC_ShoppingCart->getShippingMethod('title'), $shipping_cost));
      } else {
        $gitem = new GoogleItem(STORE_NAME, '', 1, $osC_ShoppingCart->getTotal());
        $gitem->SetMerchantPrivateItemData(new MerchantPrivateItemData(array('item' => base64_encode(serialize(STORE_NAME)))));
        
        $cart->AddItem($gitem);
      }

      //continue shopping url
      $cart->SetContinueShoppingUrl(osc_href_link(FILENAME_CHECKOUT, 'process', 'NOSSL', null, null, true));
      
      //edit cart url
      $cart->SetEditCartUrl(osc_href_link(FILENAME_CHECKOUT, '', 'NOSSL', null, null, true));
      
      // Request buyer's phone number
      $cart->SetRequestBuyerPhone(false);
      
      $private_data = $osC_Session->getID() . ';' . $osC_Session->getName();
      $cart->SetMerchantPrivateData(new MerchantPrivateData(array('orders_id' => $this->_order_id, 'session-data' => $private_data)));
            
      // Display Google Checkout button
      return $cart->CheckoutButtonCode();      
    }
    
    function process() {
      if (isset($_POST['invoice']) && is_numeric($_POST['invoice']) && isset($_POST['receiver_email']) && ($_POST['receiver_email'] == MODULE_PAYMENT_PAYPAL_IPN_ID) && isset($_POST['verify_sign']) && (empty($_POST['verify_sign']) === false) && isset($_POST['txn_id']) && (empty($_POST['txn_id']) === false)) {
        unset($_SESSION['prepOrderID']);
      }
    }
    
    function callback(){
      global $osC_Database, $osC_ShoppingCart;  
  
      require_once('ext/googlecheckout/googleresponse.php');
      require_once('ext/googlecheckout/googlemerchantcalculations.php');
      require_once('ext/googlecheckout/googleresult.php');
      require_once('ext/googlecheckout/googlerequest.php');
      
      $fh = fopen('log.txt', 'a+');
  
      $merchant_id = MODULE_PAYMENT_GCHECKOUT_MERCHANT_ID;  
      $merchant_key = MODULE_PAYMENT_GCHECKOUT_MERCHANT_KEY;
      $server_type = MODULE_PAYMENT_GCHECKOUT_SERVER;
      $currency = MODULE_PAYMENT_GCHECKOUT_CURRENCY;
             
      $Gresponse = new GoogleResponse($merchant_id, $merchant_key);

      $Gresponse->SetLogFiles('includes/logs/gerror.log', 'includes/logs/gmessage.log', L_ALL);     
  
      // Retrieve the XML sent in the HTTP POST request to the ResponseHandler
      $xml_response = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : file_get_contents("php://input");

      if (get_magic_quotes_gpc()) {
        $xml_response = stripslashes($xml_response);
      }
      
      list($root, $data) = $Gresponse->GetParsedXML($xml_response);
      fwrite($fh, var_export($data, true)); 
      fclose($fh);

      $Gresponse->SetMerchantAuthentication($merchant_id, $merchant_key);
       
      $status = $Gresponse->HttpAuthentication();
      
  
      if(!$status) {
        exit;
      }
          
      switch ($root) {
        case "request-received": break;
        case "error": break;
        case "diagnosis": break;
        case "checkout-redirect": break;
        case "merchant-calculation-callback": break;
        case "new-order-notification":{
          $serial_number = $data['new-order-notification']['serial-number'];
  
          $Gresponse->setSerialNumber($serial_number);

          $orders_id = $data['new-order-notification']['shopping-cart']['merchant-private-data']['orders_id']['VALUE'];
  
          $google_order_number = $data['new-order-notification']['google-order-number']['VALUE'];
  
          $osC_Database->simpleQuery("insert into " . TABLE_ORDER_GOOGLE . " (orders_id, google_order_number) values ('". $orders_id. "','". $google_order_number. "')");
  
          $Gresponse->SendAck();                
          break;
        }
        case "order-state-change-notification": {
			    $serial_number = $data['order-state-change-notification']['serial-number'];
			  
			    $Gresponse->setSerialNumber($serial_number);
			    $Gresponse->SendAck(); 
			    break;
        }
        case "charge-amount-notification": {
          $google_order_number = $data['charge-amount-notification']['google-order-number']['VALUE'];
          $totalcharge = $data['charge-amount-notification']['total-charge-amount']['VALUE'];           
          $currency = $data['charge-amount-notification']['total-charge-amount']['currency'];
          
          $Qorder = $osC_Database->query('select orders_id from :table_order_google where google_order_number = :google_order_number');
          $Qorder->bindTable(':table_order_google', TABLE_ORDER_GOOGLE);
          $Qorder->bindValue(':google_order_number', $google_order_number);
          $Qorder->execute();
  
          $order_id = 0;
          while ( $Qorder->next() ) {          
            $order_id = $Qorder->valueInt('orders_id');             
          }
  
          $Qorder->freeResult();

          $Qtotal = $osC_Database->query('select value, text from :table_orders_total where orders_id = :orders_id and class = "total" limit 1');
          $Qtotal->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
          $Qtotal->bindInt(':orders_id', $order_id );
          $Qtotal->execute();
          
          $total = $Qtotal->toArray();

          $neddtopay = true;
          $comment = 'Google Order Number:<strong> ' . $google_order_number . '</strong>';
          if (abs($totalcharge - $total['value']) < 0.001 ) {
            $comment .= '';
            $neddtopay = false;
          }else{
            $topay = $total['value'] - $totalcharge;
            $comment .= 'You have paid '. $totalcharge. ' '. $currency. ', and you still have to pay '. $topay. ' '. $currency;
          }
          $comments = 'Google Checkout Verified [' . $comment . ']';

          if($order_id != 0){
            if($neddtopay){
              osC_Order::process($order_id, ORDERS_STATUS_PARTLY_PAID, $comments);
            }else{
              osC_Order::process($order_id, $this->order_status, $comments);
            }
          }

			    $serial_number = $data['charge-amount-notification']['serial-number'];
			  
			    $Gresponse->setSerialNumber($serial_number);
			    $Gresponse->SendAck();
          break; 
	      }
	      case "chargeback-amount-notification": {
			    $serial_number = $data['risk-information-notification']['serial-number'];
			  
			    $Gresponse->setSerialNumber($serial_number);
			    $Gresponse->SendAck(); 
			    break; 
	      }
	      case "refund-amount-notification": {
			    $serial_number = $data['refund-amount-notification']['serial-number'];
			  
			    $Gresponse->setSerialNumber($serial_number);
			    $Gresponse->SendAck();
			    break;  
	      }
	      case "risk-information-notification": {
			    $serial_number = $data['risk-information-notification']['serial-number'];
			  
			    $Gresponse->setSerialNumber($serial_number);
			    $Gresponse->SendAck();
			    break;    
	      }
	      default:{
	        $Gresponse->SendBadRequestStatus("Invalid or not supported Message");
	        break;
	      }
      }  
    }
      
  }
?>
