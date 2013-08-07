<?php
/*
  $Id: pm2checkout.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_pm2checkout extends osC_Payment {
    var $_title,
        $_code = 'pm2checkout',
        $_status = false,
        $_sort_order,
        $_order_id;

    // class constructor
    function osC_Payment_pm2checkout() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;

      $this->_title = $osC_Language->get('payment_pm2checkout_title');
      $this->_method_title = $osC_Language->get('payment_pm2checkout_method_title');
      $this->_sort_order = MODULE_PAYMENT_PM2CHECKOUT_SORT_ORDER;
      $this->_status = ((MODULE_PAYMENT_PM2CHECKOUT_STATUS == '1') ? true : false);

      $this->form_action_url = 'https://www.2checkout.com/checkout/spurchase';

      if ($this->_status === true) {
        $this->order_status = (int)MODULE_PAYMENT_PM2CHECKOUT_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_PM2CHECKOUT_ORDER_STATUS_ID : (int)ORDERS_STATUS_PAID;

        if ((int)MODULE_PAYMENT_PM2CHECKOUT_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_PM2CHECKOUT_ZONE);
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
      global $osC_Language;
       
      return array('id' => $this->_code,
                   'module' => $this->_method_title . (strlen($osC_Language->get('payment_pm2checkout_description')) > 0 ? ' (' . $osC_Language->get('payment_pm2checkout_description') . ')' : ''));
    }
    
    function confirmation() {
      $this->_order_id = osC_Order::insert();
    }

    function process_button() {
      global $osC_Customer, $osC_Currencies, $osC_ShoppingCart, $osC_Tax, $osC_Language;
      
      switch ($osC_ShoppingCart->getBillingAddress('country_iso_code_3')) {
        case 'USA':
        case 'CAN':
          $state_code = $osC_ShoppingCart->getBillingAddress('state');
          break;

        default:
          $state_code = 'XX';
          break;
      }
      
      if (MODULE_PAYMENT_PM2CHECKOUT_DEMO_MODE == '1') {
        $demo = 'Y';
      }else {
        $demo = 'N';
      }

      $process_button_string = '';
      $params = array('sid' => MODULE_PAYMENT_PM2CHECKOUT_SELLER_ID,
                      'total' => $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal()),
                      'cart_order_id' => $this->_order_id,
                      'x_receipt_link_url' => osc_href_link(FILENAME_CHECKOUT, 'process', 'SSL', null, null, true),
                      'fixed' => 'Y',
                      'first_name' => $osC_ShoppingCart->getBillingAddress('firstname'),
                      'last_name' => $osC_ShoppingCart->getBillingAddress('lastname'),
                      'street_address' => $osC_ShoppingCart->getBillingAddress('street_address'),
                      'city' => $osC_ShoppingCart->getBillingAddress('city'),
                      'state' => $state_code,
                      'zip' => $osC_ShoppingCart->getBillingAddress('postcode'),
                      'country' => $osC_ShoppingCart->getShippingAddress('country_iso_code_2'),
                      'email' => $osC_Customer->getEmailAddress(),
                      'phone' => $osC_ShoppingCart->getBillingAddress('telephone_number'),
                      'ship_name' => $osC_ShoppingCart->getShippingAddress('firstname') . ' ' . $osC_ShoppingCart->getShippingAddress('lastname'),
                      'ship_street_address' => $osC_ShoppingCart->getShippingAddress('street_address'),
                      'ship_city' => $osC_ShoppingCart->getShippingAddress('city'),
                      'ship_state' => $osC_ShoppingCart->getShippingAddress('zone_code'),
                      'ship_zip' => $osC_ShoppingCart->getShippingAddress('postcode'),
                      'demo' => $demo,
                      'id_type' => '1',
                      'ship_country' => $osC_ShoppingCart->getShippingAddress('country_iso_code_2'), 
                      'customer_id' => $osC_Customer->getID());
      
      if ($osC_ShoppingCart->hasContents()) {
        $i = 1;
        
        foreach($osC_ShoppingCart->getProducts() as $product) {
          $tax = $osC_Tax->getTaxRate($product['tax_class_id'], $osC_ShoppingCart->getTaxingAddress('country_id'), $osC_ShoppingCart->getTaxingAddress('zone_id'));
          $price = $osC_Currencies->addTaxRateToPrice($product['final_price'], $tax);
            
          $params['c_prod_' . $i] = (int)$product['id'] . ',' . (int)$product['quantity'];
          $params['c_name_' . $i] =  $product['name'];
          $params['c_description_' . $i] = $product['name'];
          $params['c_price_' . $i] = $osC_Currencies->formatRaw($price);
        }
      }
      
      foreach ($params as $key => $value) {
        $process_button_string .= osc_draw_hidden_field($key, $value);
      }
      
      return $process_button_string;
    }

    function process() {
      global $osC_Database, $osC_Currencies, $osC_ShoppingCart, $messageStack, $osC_Language;
      
      if (MODULE_PAYMENT_PM2CHECKOUT_DEMO_MODE == 1) {
        $order_number = 1;
      } else {
        $order_number = $_GET['order_number'];
      }

      $check_hash = strtoupper(md5(MODULE_PAYMENT_PM2CHECKOUT_SECRET_WORD . MODULE_PAYMENT_PM2CHECKOUT_SELLER_ID . $order_number . $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal())));

      if ($check_hash == $_GET['key']) {
        if (isset($_GET['cart_order_id']) && is_numeric($_GET['cart_order_id']) && ($_GET['cart_order_id'] > 0)) {
          $Qcheck = $osC_Database->query('select orders_status, currency, currency_value from :table_orders where orders_id = :orders_id and customers_id = :customers_id');
          $Qcheck->bindTable(':table_orders', TABLE_ORDERS);
          $Qcheck->bindInt(':orders_id', $_GET['cart_order_id']);
          $Qcheck->bindInt(':customers_id', $_GET['customer_id']);
          $Qcheck->execute();

          if ($Qcheck->numberOfRows() > 0) {
            $Qtotal = $osC_Database->query('select value from :table_orders_total where orders_id = :orders_id and class = "total" limit 1');
            $Qtotal->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
            $Qtotal->bindInt(':orders_id', $_GET['cart_order_id']);
            $Qtotal->execute();

            $comments = '2Checkout Order Successful [' . $_GET['order_number'] . '; ' . $osC_Currencies->format($_GET['total']) . ')]';

            osC_Order::process($_GET['cart_order_id'], $this->order_status, $comments);
          }
        }
      } else {
        $comments =  "MD5 HASH MISMATCH, PLEASE CONTACT THE SELLER";
        
        $messageStack->add_session('checkout', $comments);
        
        osC_Order::insertOrderStatusHistory($_GET['cart_order_id'], ORDERS_STATUS_PENDING, $comments);
        
        osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'checkout&view=paymentInformationForm', 'SSL'));
      }
    }
  }
?>
