<?php
/*
  $Id: moneybookers.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_moneybookers extends osC_Payment {
    var $_title,
        $_code = 'moneybookers',
        $_status = false,
        $_sort_order,
        $_order_id,
        $_transaction_response;

    function osC_Payment_moneybookers() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;

      $this->_title = $osC_Language->get('payment_moneybookers_title');
      $this->_method_title = $osC_Language->get('payment_moneybookers_method_title');
      $this->_status = (MODULE_PAYMENT_MONEYBOOKERS_STATUS == '1') ? true : false;
      $this->_sort_order = MODULE_PAYMENT_MONEYBOOKERS_SORT_ORDER;

      $this->form_action_url = 'https://www.moneybookers.com/app/payment.pl';

      if ($this->_status === true) {
        if ((int)MODULE_PAYMENT_MONEYBOOKERS_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_MONEYBOOKERS_ORDER_STATUS_ID;
        }

        if ((int)MODULE_PAYMENT_MONEYBOOKERS_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_MONEYBOOKERS_ZONE);
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
      global $osC_Customer, $osC_Currencies, $osC_ShoppingCart, $osC_Language;

      //products
      $products = array();
      if ($osC_ShoppingCart->hasContents()) {

        $products = array();
        foreach($osC_ShoppingCart->getProducts() as $product) {
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
          
          //variants
          if ($osC_ShoppingCart->hasVariants($product['id'])) {
            foreach ($osC_ShoppingCart->getVariants($product['id']) as $variant) {
              $product_name .= ' - ' . $variant['groups_name'] . ': ' . $variant['values_name'];
            }
          }
          
          $products[] = $product['quantity'] . ' x ' . $product_name;
        }
      }

      $params = array('pay_to_email' => MODULE_PAYMENT_MONEYBOOKERS_ACCOUNT_EMAIL,
                      'description' => STORE_NAME,
                      'transaction_id' => $this->_order_id,
                      'return_url' => osc_href_link(FILENAME_CHECKOUT, 'process=dummy', 'SSL', null, null, true),
                      'cancel_url' => osc_href_link(FILENAME_CHECKOUT, 'checkout&view=paymentInformationForm', 'SSL', null, null, true),      
                      'status_url' => osc_href_link(FILENAME_CHECKOUT, 'callback&module=' . $this->_code, 'SSL', null, null, true),
                      'language' => $osC_Language->getCode(),
                      'detail1_text' => implode(',', $products),
                      'detail1_description' => implode(',', $products),
                      'currency' => 'USD',
                      //'amount' => '0.1',
                      'amount' => $osC_ShoppingCart->getTotal(),
                      'confirmation_note' => 'Thans for shopping at ' . STORE_NAME . '!');

      $process_button_string = '';
      foreach ($params as $key => $value) {
        $key = trim($key);
        $value = trim($value);
        $process_button_string .= osc_draw_hidden_field($key, $value);
      }

      return $process_button_string;
    }
    
    function callback() {

      $md5sign = strtoupper(md5($_POST['merchant_id'] . $_POST['transaction_id'] . strtoupper(md5(MODULE_PAYMENT_MONEYBOOKERS_SECRET_WORD)) . $_POST['mb_amount'] . $_POST['mb_currency'] . $_POST['status']));
      $comment = 'Moneybookers Verified: ' . $_POST['status'];

      
      if($md5sign == $_POST['md5sig'] && intval($_POST['status']) == 2) {
        osC_Order::process($_POST['transaction_id'], $this->order_status, $comment);
      } 
    }
    
  }
?>
