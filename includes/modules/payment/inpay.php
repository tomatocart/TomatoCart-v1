<?php
/*
  $Id: inpay.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_inpay extends osC_Payment {
    var $_title,
        $_code = 'inpay',
        $_status = false,
        $_sort_order,
        $_order_id,
        $_ignore_order_totals = array('sub_total', 'tax', 'total'),
        $_transaction_response;

    //class constructor
    function osC_Payment_inpay() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;

      $this->_title = $osC_Language->get('payment_inpay_title');
      $this->_method_title = $osC_Language->get('payment_inpay_method_title');
      $this->_sort_order = MODULE_PAYMENT_INPAY_SORT_ORDER;
      $this->_status = ((MODULE_PAYMENT_INPAY_STATUS == '1') ? true : false);

      if (MODULE_PAYMENT_INPAY_GATEWAY_SERVER == 'Production') {
        $this->form_action_url = 'https://secure.inpay.com';
      } else {
        $this->form_action_url = 'https://test-secure.inpay.com';
      }

      if ($this->_status === true) {
        $this->order_status = MODULE_PAYMENT_INPAY_PREPARE_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_INPAY_PREPARE_ORDER_STATUS_ID : (int)ORDERS_STATUS_PAID;

        if ((int)MODULE_PAYMENT_INPAY_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_INPAY_ZONE);
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

      /* Ensure the http_build_query is defined */

      if (!function_exists('http_build_query')) {
          function http_build_query($data, $prefix='', $sep='', $key='') {
              $ret = array();
              foreach ((array)$data as $k => $v) {
                  if (is_int($k) && $prefix != null) {
                      $k = urlencode($prefix . $k);
                  }
                  if ((!empty($key)) || ($key === 0))  $k = $key.'['.urlencode($k).']';
                  if (is_array($v) || is_object($v)) {
                      array_push($ret, http_build_query($v, '', $sep, $k));
                  } else {
                      array_push($ret, $k.'='.urlencode($v));
                  }
              }
              if (empty($sep)) $sep = ini_get('arg_separator.output');
              return implode($sep, $ret);
          }// http_build_query
      }//if
    }

    function javascript_validation()
    {
        return false;
    }

    function pre_confirmation_check() {
      return false;
    }

    function selection() {
      return array('id' => $this->_code,
                   'module' => $this->_method_title);
    }

    function confirmation() {
      $this->_order_id = osC_Order::insert(ORDERS_STATUS_PREPARING);

      return false;
    }

    function process_button() {
      global $osC_Customer, $osC_Currencies, $osC_ShoppingCart, $osC_Tax, $osC_Language;

      $params = array('cmd' => '_xclick',
                      'item_name' => STORE_NAME,
                      'merchant_id' => MODULE_PAYMENT_INPAY_MERCHANT_ID,
                      'flow_layout' => MODULE_PAYMENT_INPAY_FLOW_LAYOUT,
                      'currency' => $osC_Currencies->getCode(),
                      'order_id' => $this->_order_id,
                      'custom' => $osC_Customer->getID(),
                      'no_note' => '1',
                      'notify_url' => osc_href_link(FILENAME_CHECKOUT, 'callback&module=' . $this->_code, 'SSL', false, false, true),
                      'return_url' => osc_href_link(FILENAME_CHECKOUT, 'process', 'SSL', null, null, true),
                      'cancel_url' => osc_href_link(FILENAME_CHECKOUT, 'process', 'SSL', null, null, true),
                      'bn' => 'Tomatocart_Default_ST',
                      'buyer_email' => $osC_ShoppingCart->getBillingAddress('email_address'),
                      'paymentaction' => 'Sale');

      if ($osC_ShoppingCart->hasShippingAddress()) {
        $address = '';
        $address .= $osC_ShoppingCart->getShippingAddress('street_address') . ' ' . $osC_ShoppingCart->getShippingAddress('city') . ' ' . $osC_ShoppingCart->getShippingAddress('zone_code') . ' ' . $osC_ShoppingCart->getShippingAddress('postcode') . ' ' . $osC_ShoppingCart->getShippingAddress('country_iso_code_2');
        
        $params['address_override'] = '1';
        $params['buyer_name'] = utf8_encode($osC_ShoppingCart->getShippingAddress('firstname') . ' ' . $osC_ShoppingCart->getShippingAddress('lastname'));
        $params['buyer_address'] = utf8_encode($address);
        $params['country'] = $osC_ShoppingCart->getShippingAddress('country_iso_code_2');
      } else {
        $address = '';
        $address .= $osC_ShoppingCart->getBillingAddress('street_address') . ' ' . $osC_ShoppingCart->getBillingAddress('city') . ' ' . $osC_ShoppingCart->getBillingAddress('zone_code') . ' ' . $osC_ShoppingCart->getBillingAddress('postcode') . ' ' . $osC_ShoppingCart->getBillingAddress('country_iso_code_2');
        
        $params['no_shipping'] = '1';
        $params['buyer_name'] = utf8_encode($osC_ShoppingCart->getBillingAddress('firstname') . ' ' . $osC_ShoppingCart->getBillingAddress('lastname'));
        $params['buyer_address'] = utf8_encode($address);
        $params['country'] = $osC_ShoppingCart->getBillingAddress('country_iso_code_2');
      }

      $shipping_tax = ($osC_ShoppingCart->getShippingMethod('cost')) * ($osC_Tax->getTaxRate($osC_ShoppingCart->getShippingMethod('tax_class_id'), $osC_ShoppingCart->getTaxingAddress('country_id'), $osC_ShoppingCart->getTaxingAddress('zone_id')) / 100);

      if (DISPLAY_PRICE_WITH_TAX == '1') {
        $shipping = $osC_ShoppingCart->getShippingMethod('cost');
      } else {
        $shipping = $osC_ShoppingCart->getShippingMethod('cost') + $shipping_tax;
      }
      $params['shipping'] = $osC_Currencies->formatRaw($shipping);

      $total_tax = $osC_ShoppingCart->getTax() - $shipping_tax;
      $params['tax'] = $osC_Currencies->formatRaw($total_tax);
      $params['amount'] = $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal() - $shipping - $total_tax);

    //product(s) info
      $products_info = '';
      if ($osC_ShoppingCart->hasContents()) {
        $products = $osC_ShoppingCart->getProducts();

        foreach($products as $product) {
          $products_info .= $product['name'];

          //gift certificate
          if ($product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
            $products_info .= "\n" . ' - ' . $osC_Language->get('senders_name') . ': ' . $product['gc_data']['senders_name'];

            if ($product['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
              $products_info .= "\n" . ' - ' . $osC_Language->get('senders_email')  . ': ' . $product['gc_data']['senders_email'];
            }

            $products_info .= "\n" . ' - ' . $osC_Language->get('recipients_name') . ': ' . $product['gc_data']['recipients_name'];

            if ($product['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
              $products_info .= "\n" . ' - ' . $osC_Language->get('recipients_email')  . ': ' . $product['gc_data']['recipients_email'];
            }

            $products_info .= "\n" . ' - ' . $osC_Language->get('message')  . ': ' . $product['gc_data']['message'];
          }

          if ($osC_ShoppingCart->hasVariants($product['id'])) {
            foreach ($osC_ShoppingCart->getVariants($product['id']) as $variant) {
              $products_info .= ' - ' . $variant['groups_name'] . ': ' . $variant['values_name'];
            }
          }

          $products_info .= ';';
        }
      }

      $params['order_text'] = utf8_encode($products_info);

      // calc Md5 sum
      $params['checksum'] = $this->calcInpayMd5Key($params);

      reset($params);
      $process_button_string = '';
      foreach ($params as $key => $value) {
        $process_button_string .= osc_draw_hidden_field($key, $value);
      }

      return $process_button_string;
    }

    function callback() {
      global $osC_Database, $osC_Currencies;

      $result = "VERIFIED";
      $check = true;

      // Validate request
      if ( !isset($_POST['order_id']) || !is_numeric($_POST['order_id']) || ($_POST['order_id'] <= 0) ) {
        $check = false;
        $result = 'bad order id';
      }

      if ($check) {
        if ( !isset($_POST['invoice_amount']) ) {
          $check = false;
          $result = 'bad amount';
        }
      }

      if ($check) {
        if ( !isset($_POST['invoice_currency']) ) {
          $check = false;
          $result = 'bad currency';
        }
      }

      if ($check) {
        if ( !isset($_POST['checksum']) || !isset($_POST['invoice_reference']) || !isset($_POST['invoice_created_at']) || !isset($_POST['invoice_status']) ) {
          $check = false;
          $result = 'missing vatiables';
        }
      }

      if ($check) {
        //calc checksum
        $sum = http_build_query(array(
          'order_id' => $_POST['order_id'],
          'invoice_reference' => $_POST['invoice_reference'],
          'invoice_amount' => $_POST['invoice_amount'],
          'invoice_currency' => $_POST['invoice_currency'],
          'invoice_created_at' => $_POST['invoice_created_at'],
          'invoice_status' => $_POST['invoice_status'],
          'secret_key' => MODULE_PAYMENT_INPAY_SECRET_KEY), '', "&");

        $md5v = md5($sum);

        if ($md5v != $_POST['checksum']) {
          $check = false;
          $result = 'bad checksum';
        }
      }

      if ($check) {
        if ( !osC_Order::exists($_POST['order_id'])) {
          $check = false;
          $result = 'order not found';
        }
      }

      if ($check) {
        $Qcheck = $osC_Database->query('select orders_status, currency, currency_value from :table_orders where orders_id = :orders_id');
        $Qcheck->bindTable(':table_orders', TABLE_ORDERS);
        $Qcheck->bindInt(':orders_id', $_POST['invoice']);
        $Qcheck->bindInt(':customers_id', $_POST['custom']);
        $Qcheck->execute();

        if ($Qcheck->numberOfRows() > 0) {
          $order = $Qcheck->toArray();

          $Qtotal = $osC_Database->query('select value from :table_orders_total where orders_id = :orders_id and class = "total" limit 1');
          $Qtotal->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
          $Qtotal->bindInt(':orders_id', $_POST['invoice']);
          $Qtotal->execute();

          $total = $Qtotal->toArray();

          if ( number_format($_POST['invoice_amount'], $osC_Currencies->getDecimalPlaces($order['currency'])) != number_format($total['value']*$order['currency_value'], $osC_Currencies->getDecimalPlaces($order['currency'])) ) {
            $check = false;
            $result = 'Inpay transaction value (' . osc_output_string_protected($_POST['invoice_amount']) . ') does not match order value (' . number_format($total['value'] * $order['currency_value'], $osC_Currencies->get_decimal_places($order['currency'])) . ')';
          }
        }
      }

      if ($check) {
       // check status
        $delivered_status = 7;
        if (($order['orders_status'] == MODULE_PAYMENT_INPAY_COMP_ORDER_STATUS_ID) || ($order['orders_status'] == $delivered_status)) {
          $check = false;
          $result = 'Status already in level' . $order['orders_status'];
        }
      }

      if ($check) {
        $invoice_status = $this->get_invoice_status($_POST);

        $check = false;

        if ((($invoice_status == "pending")||($invoice_status == "created"))&&(($_POST["invoice_status"] == "pending")||($POST["invoice_status"] == "created"))) {
            $check = true;
        } else if (($invoice_status == "approved") && ($_POST["invoice_status"] == "approved")) {
            $check = true;
        } else if (($invoice_status == "sum_too_low") && ($_POST["invoice_status"] == "sum_too_low")) {
            $check = true;
        }

        if (!$check) {
          $result = 'Bad invoice status:' . $invoice_status;
        }
      }

      // Validate request end
      if ($result == 'VERIFIED') {
        $invoice_approved = false;
        $invoice_created = false;
        $invoice_partial = false;

        switch ($_POST['invoice_status']) {
          case 'created':
          case 'pending':
            $msg = "customer has been asked to pay " . $_POST['invoice_amount'] . ' ' . $_POST['invoice_currency'] . ' with reference: ' . $_POST['invoice_reference'] . ' via his online bank';
            $order_status_id = MODULE_PAYMENT_INPAY_CREATE_ORDER_STATUS_ID;
            $invoice_created = true;
            break;

          case "approved":
            $msg = "Inpay has confirmed that the payment of " . $_POST['invoice_amount'] . " " . $_POST['invoice_currency'] . " has been received";
            $order_status_id = MODULE_PAYMENT_INPAY_COMP_ORDER_STATUS_ID;
            $invoice_approved = true;
            break;

          case "sum_too_low":
            $msg = "Partial payment received by inpay. Reference: " . $_POST['invoice_reference'];
            $order_status_id = MODULE_PAYMENT_INPAY_SUM_TOO_LOW_ORDER_STATUS_ID;
            $invoice_partial = true;
            break;
        }

        $comments = 'Inpay ' . ucfirst($_POST['invoice_status']) . '[' . $msg . ']';

        if ($invoice_approved || $invoice_created || $invoice_partial) {
          osC_Order::process($_POST['order_id'], $order_status_id, $comments);
          osC_Order::insertOrderStatusHistory($_POST['order_id'], $order_status_id, $comments);
        }else {
          if (defined('MODULE_PAYMENT_INPAY_DEBUG_EMAIL')) {
            $email_body = 'INPAY_DEBUG_POST_DATA:' . "\n\n";

            reset($_POST);
            foreach($_POST as $key=>$value) {
              $email_body .= $key . '=' . $value . "\n";
            }

            $email_body .= "\n" . 'INPAY_DEBUG_GET_DATA:' . "\n\n";
            reset($_GET);
            foreach($_GET as $key=>$value) {
              $email_body .= $key . '=' . $value . "\n";
            }

            osc_email('', MODULE_PAYMENT_INPAY_DEBUG_EMAIL, 'Inpay Invalid Process', $email_body, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
          }

          if (isset($_POST['order_id']) && is_numeric($_POST['order_id']) && $_POST['order_id'] > 0) {
          $Qcheck = $osC_Database->query('select orders_id from :table_orders where orders_id=:orders_id');
          $Qcheck->bindTable(':table_orders', TABLE_ORDERS);
          $Qcheck->bindInt('orders_id', $_POST['order_id']);
          $Qcheck->execute();

              if ($Qcheck->numberOfRows() > 0) {
                $comments = 'Inpay Invalid [' . $result . ']';

                osC_Order::insertOrderStatusHistory($_POST['order_id'], $order_status_id, $comments);
              }
          }
        }        
      }
    }

    function process() {
      unset($_SESSION['prepOrderID']);
    }

    function calcInpayMd5Key($order) {
      $sk = MODULE_PAYMENT_INPAY_SECRET_KEY;

      $q = http_build_query( array ("merchant_id"=>$order['merchant_id'],
      "order_id"=>$order['order_id'],
      "amount"=>$order['amount'],
      "currency"=>$order['currency'],
      "order_text"=>$order['order_text'],
      "flow_layout"=>$order['flow_layout'],
      "secret_key"=>$sk), "", "&");

      $md5v = md5($q);

      return $md5v;
    }

    function calc_inpay_invoice_status_md5key($pars) {
      $q = http_build_query(array("invoice_ref"=>$pars['invoice_reference'], "merchant_id"=>MODULE_PAYMENT_INPAY_MERCHANT_ID,
     "secret_key"=>MODULE_PAYMENT_INPAY_SECRET_KEY), "", "&");
      $md5v = md5($q);

      return $md5v;
    }

    function get_invoice_status($pars) {
        //
        // prepare parameters
        //
        $calc_md5 = $this->calc_inpay_invoice_status_md5key($pars);
        $q = http_build_query(array("merchant_id"=>MODULE_PAYMENT_INPAY_MERCHANT_ID, "invoice_ref"=>$pars['invoice_reference'], "checksum"=>$calc_md5), "", "&");
        //
        // communicate to inpay server
        //
        $fsocket = false;
        $curl = false;
        $result = false;
        $fp = false;
        $server = 'secure.inpay.com';
        if (MODULE_PAYMENT_INPAY_GATEWAY_SERVER != 'Production') {
            $server = 'test-secure.inpay.com';
        }

        if ((PHP_VERSION >= 4.3) && ($fp = @fsockopen('ssl://'.$server, 443, $errno, $errstr, 30))) {
            $fsocket = true;
        } elseif (function_exists('curl_exec')) {
            $curl = true;
        }
        if ($fsocket == true) {
            $header = 'POST /api/get_invoice_status HTTP/1.1'."\r\n".
            'Host: '.$server."\r\n".
            'Content-Type: application/x-www-form-urlencoded'."\r\n".
            'Content-Length: '.strlen($q)."\r\n".
            'Connection: close'."\r\n\r\n";
            @fputs($fp, $header.$q);
            $str = '';
            while (!@feof($fp)) {
                $res = @fgets($fp, 1024);
                $str .= (string)$res;
            }
            @fclose($fp);
            $result=$str;
            $result = preg_split('/^\r?$/m', $result, 2);
            $result = trim($result[1]);
        } elseif ($curl == true) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://'.$server.'/api/get_invoice_status');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $q);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);
            curl_close($ch);
        }

        return (string)$result;
    }
  }

?>