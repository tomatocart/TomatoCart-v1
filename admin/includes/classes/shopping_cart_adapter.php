<?php
/*
  $Id: shopping_cart_adapter.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  include_once('includes/classes/order.php');

  class toC_ShoppingCart_Adapter extends osC_Order {

// class constructor
    function toC_ShoppingCart_Adapter($order_id) {
      parent::osC_Order($order_id);
    }

    function hasContents() {
      return !empty($this->_contents);
    }
      
    function numberOfPhysicalItems() {
      $total = 0;

      if ($this->hasContents()) {
        foreach ($this->_contents as $product) {
          if(($product['type'] == PRODUCT_TYPE_SIMPLE) || ( ($product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) && ($product['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_PHYSICAL) )) {
            $total += $product['quantity'];
          }
        }
      }

      return $total;
    }
    
    function numberOfItems() {
      $total = 0;

      if ($this->hasContents()) {
        foreach (array_keys($this->_contents) as $products_id) {
          $total += $this->getQuantity($products_id);
        }
      }

      return $total;
    }

    function getQuantity($products_id) {
      if (isset($this->_contents[$products_id])) {
        return $this->_contents[$products_id]['quantity'];
      }

      return 0;
    }

    function exists($products_id) {
      return isset($this->_contents[$products_id]);
    }

    function getProducts() {
      return $this->_contents;
    }

    function getSubTotal() {
      return $this->_sub_total;
    }

    function getTotal() {
      return $this->_total;
    }

    function isTotalZero() {
      return ($this->_total == 0);
    }    
    
    function getWeight() {
      return $this->_weight;
    }

    function getContentType() {
      global $osC_Database;

      if ( $this->hasContents() ) {
        $products = array_values($this->_contents);
        
        foreach ($products as $product) {
          if (($product['type'] == PRODUCT_TYPE_SIMPLE) || ( ($product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) && ($product['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_PHYSICAL) )) {
            switch ($this->_content_type) {
              case 'virtual':
                $this->_content_type = 'mixed';
          
                return $this->_content_type;
                break;
              default:
                $this->_content_type = 'physical';
                break;
            }
          } else {
            switch ($this->_content_type) {
              case 'physical':
                $this->_content_type = 'mixed';
          
                return $this->_content_type;
                break;
              default:
                $this->_content_type = 'virtual';
                break;
            }
          }        
        }
      }

      return $this->_content_type;
    }

    function hasVariants($products_id) {
      return isset($this->_contents[$products_id]['variants']) && !empty($this->_contents[$products_id]['variants']);
    }

    function getVariants($products_id) {
      if (isset($this->_contents[$products_id]['variants']) && !empty($this->_contents[$products_id]['variants'])) {
        return $this->_contents[$products_id]['variants'];
      }
    }

    function isInStock($products_id) {
      global $osC_Database;

      $osC_Product = new osC_Product(osc_get_product_id($products_id));
      if (($osC_Product->getQuantity($products_id) - $this->_contents[$products_id]['quantity']) >= 0) {
        return true;
      } elseif ($this->_products_in_stock === true) {
        $this->_products_in_stock = false;
      }

      return false;
    }

    function hasStock() {
      return $this->_products_in_stock;
    }

    function hasShippingAddress() {
      return isset($this->_shipping_address) && isset($this->_shipping_address['id']);
    }

    function setShippingAddress($address_id) {
      global $osC_Database, $osC_Customer;

      $previous_address = false;

      if (isset($this->_shipping_address['id'])) {
        $previous_address = $this->getShippingAddress();
      }

      $Qaddress = $osC_Database->query('select ab.entry_firstname, ab.entry_lastname, ab.entry_company, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, ab.entry_telephone, z.zone_code, z.zone_name, ab.entry_country_id, c.countries_name, c.countries_iso_code_2, c.countries_iso_code_3, c.address_format, ab.entry_state from :table_address_book ab left join :table_zones z on (ab.entry_zone_id = z.zone_id) left join :table_countries c on (ab.entry_country_id = c.countries_id) where ab.customers_id = :customers_id and ab.address_book_id = :address_book_id');
      $Qaddress->bindTable(':table_address_book', TABLE_ADDRESS_BOOK);
      $Qaddress->bindTable(':table_zones', TABLE_ZONES);
      $Qaddress->bindTable(':table_countries', TABLE_COUNTRIES);
      $Qaddress->bindInt(':customers_id', $osC_Customer->getID());
      $Qaddress->bindInt(':address_book_id', $address_id);
      $Qaddress->execute();

      $this->_shipping_address = array('id' => $address_id,
                                       'firstname' => $Qaddress->valueProtected('entry_firstname'),
                                       'lastname' => $Qaddress->valueProtected('entry_lastname'),
                                       'company' => $Qaddress->valueProtected('entry_company'),
                                       'street_address' => $Qaddress->valueProtected('entry_street_address'),
                                       'suburb' => $Qaddress->valueProtected('entry_suburb'),
                                       'city' => $Qaddress->valueProtected('entry_city'),
                                       'postcode' => $Qaddress->valueProtected('entry_postcode'),
                                       'state' => (!osc_empty($Qaddress->valueProtected('entry_state'))) ? $Qaddress->valueProtected('entry_state') : $Qaddress->valueProtected('zone_name'),
                                       'zone_id' => $Qaddress->valueInt('entry_zone_id'),
                                       'zone_code' => $Qaddress->value('zone_code'),
                                       'country_id' => $Qaddress->valueInt('entry_country_id'),
                                       'country_title' => $Qaddress->value('countries_name'),
                                       'country_iso_code_2' => $Qaddress->value('countries_iso_code_2'),
                                       'country_iso_code_3' => $Qaddress->value('countries_iso_code_3'),
                                       'format' => $Qaddress->value('address_format'),
                                       'telephone_number' => $Qaddress->value('entry_telephone'));

      if ( is_array($previous_address) && ( ($previous_address['id'] != $this->_shipping_address['id']) || ($previous_address['country_id'] != $this->_shipping_address['country_id']) || ($previous_address['zone_id'] != $this->_shipping_address['zone_id']) || ($previous_address['state'] != $this->_shipping_address['state']) || ($previous_address['postcode'] != $this->_shipping_address['postcode']) ) ) {
        $this->_calculate();
      }
    }

    function getShippingAddress($key = '') {
      if (empty($key)) {
        return $this->_shipping_address;
      }

      return $this->_shipping_address[$key];
    }

    function resetShippingAddress() {
      global $osC_Customer;

      $this->_shipping_address = array('zone_id' => STORE_ZONE, 'country_id' => STORE_COUNTRY);

      if ($osC_Customer->isLoggedOn() && $osC_Customer->hasDefaultAddress()) {
        $this->setShippingAddress($osC_Customer->getDefaultAddressID());
      }
    }

    function setShippingMethod($shipping_array, $calculate_total = true) {
      $this->_shipping_method = $shipping_array;

      if ($calculate_total === true) {
        $this->_calculate(false);
      }
    }

    function getShippingMethod($key = '') {
      if (empty($key)) {
        return $this->_shipping_method;
      }

      return $this->_shipping_method[$key];
    }

    function resetShippingMethod() {
      $this->_shipping_method = array();
      
      unset($_SESSION['osC_ShoppingCart_data']['shipping_quotes']);
      
      $this->_calculate();
    }

    function hasShippingMethod() {
      return !empty($this->_shipping_method);
    }

    function hasBillingAddress() {
      return isset($this->_billing_address) && isset($this->_billing_address['id']);
    }

    function setBillingAddress($address_id) {
      global $osC_Database, $osC_Customer;

      $previous_address = false;

      if (isset($this->_billing_address['id'])) {
        $previous_address = $this->getBillingAddress();
      }

      $Qaddress = $osC_Database->query('select ab.entry_firstname, ab.entry_lastname, ab.entry_company, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, ab.entry_telephone, z.zone_code, z.zone_name, ab.entry_country_id, c.countries_name, c.countries_iso_code_2, c.countries_iso_code_3, c.address_format, ab.entry_state from :table_address_book ab left join :table_zones z on (ab.entry_zone_id = z.zone_id) left join :table_countries c on (ab.entry_country_id = c.countries_id) where ab.customers_id = :customers_id and ab.address_book_id = :address_book_id');
      $Qaddress->bindTable(':table_address_book', TABLE_ADDRESS_BOOK);
      $Qaddress->bindTable(':table_zones', TABLE_ZONES);
      $Qaddress->bindTable(':table_countries', TABLE_COUNTRIES);
      $Qaddress->bindInt(':customers_id', $osC_Customer->getID());
      $Qaddress->bindInt(':address_book_id', $address_id);
      $Qaddress->execute();

      $this->_billing_address = array('id' => $address_id,
                                      'firstname' => $Qaddress->valueProtected('entry_firstname'),
                                      'lastname' => $Qaddress->valueProtected('entry_lastname'),
                                      'company' => $Qaddress->valueProtected('entry_company'),
                                      'street_address' => $Qaddress->valueProtected('entry_street_address'),
                                      'suburb' => $Qaddress->valueProtected('entry_suburb'),
                                      'city' => $Qaddress->valueProtected('entry_city'),
                                      'postcode' => $Qaddress->valueProtected('entry_postcode'),
                                      'state' => (!osc_empty($Qaddress->valueProtected('entry_state'))) ? $Qaddress->valueProtected('entry_state') : $Qaddress->valueProtected('zone_name'),
                                      'zone_id' => $Qaddress->valueInt('entry_zone_id'),
                                      'zone_code' => $Qaddress->value('zone_code'),
                                      'country_id' => $Qaddress->valueInt('entry_country_id'),
                                      'country_title' => $Qaddress->value('countries_name'),
                                      'country_iso_code_2' => $Qaddress->value('countries_iso_code_2'),
                                      'country_iso_code_3' => $Qaddress->value('countries_iso_code_3'),
                                      'format' => $Qaddress->value('address_format'),
                                      'telephone_number' => $Qaddress->value('entry_telephone'));

      if ( is_array($previous_address) && ( ($previous_address['id'] != $this->_billing_address['id']) || ($previous_address['country_id'] != $this->_billing_address['country_id']) || ($previous_address['zone_id'] != $this->_billing_address['zone_id']) || ($previous_address['state'] != $this->_billing_address['state']) || ($previous_address['postcode'] != $this->_billing_address['postcode']) ) ) {
        $this->_calculate();
      }
    }

    function getBillingAddress($key = '') {
      if (empty($key)) {
        return $this->_billing_address;
      }

      return $this->_billing_address[$key];
    }

    function resetBillingAddress() {
      global $osC_Customer;

      $this->_billing_address = array('zone_id' => STORE_ZONE, 'country_id' => STORE_COUNTRY);

      if ($osC_Customer->isLoggedOn() && $osC_Customer->hasDefaultAddress()) {
        $this->setBillingAddress($osC_Customer->getDefaultAddressID());
      }
    }

    function setBillingMethod($billing_array) {
      $this->_billing_method = $billing_array;

      $this->_calculate();
    }

    function getBillingMethod($key = '') {
      if (empty($key)) {
        return $this->_billing_method;
      }

      return $this->_billing_method[$key];
    }

    function resetBillingMethod($calculate = true) {
      $this->_billing_method = array();

      if ($calculate == true) {
        $this->_calculate();
      }
    }

    function hasBillingMethod() {
      return !empty($this->_billing_method);
    }
    
    function getCartBillingMethods() {
      global $osC_Language;
      
      $payment_methods = array();
      
      if ($this->isUseStoreCredit()) {
        $payment_methods[] = $osC_Language->get('store_credit_title');
      }
      
      if ($this->hasBillingMethod()) {
        $payment_methods[] = $this->getBillingMethod('title');
      }  
      
      return $payment_methods;
    }

    function getCartBillingModules() {
      $payment_modules = array();
      
      if ($this->isUseStoreCredit()) {
        $payment_modules[] = 'store_credit';
      }
      
      if ($this->hasBillingMethod()) {
        $payment_modules[] = $this->getBillingMethod('id');
      }
      
      return $payment_modules;
    }

    function getTaxingAddress($id = '') {
      if ($this->getContentType() == 'virtual') {
        return $this->getBillingAddress($id);
      }

      return $this->getShippingAddress($id);
    }

    function addTaxAmount($amount) {
      $this->_tax += $amount;
    }

    function numberOfTaxGroups() {
      return sizeof($this->_tax_groups);
    }

    function addTaxGroup($group, $amount) {
      if (isset($this->_tax_groups[$group])) {
        $this->_tax_groups[$group] += $amount;
      } else {
        $this->_tax_groups[$group] = $amount;
      }
    }
    
    function resetGiftCertificates () {
      $this->_gift_certificate_codes = array();
      $this->_gift_certificate_redeem_amount = array();
    }

    function addGiftCertificateCode ($gift_certificate_code) {
      $this->_gift_certificate_codes[] = $gift_certificate_code;
      
      $this->_calculate();
      $this->updateOrderTotal();
      
      $this->_insertGiftCertificateRedeemHistory($gift_certificate_code);
    }
    
    function deleteGiftCertificate ($gift_certificate_code, $caculate = true) {
      foreach ($this->_gift_certificate_codes as $i => $code) {
        if($code == $gift_certificate_code) {
          unset($this->_gift_certificate_codes[$i]);
          unset($this->_gift_certificate_redeem_amount[$code]);
        }
      }

      if ($caculate == true) {
        $this->_calculate();
        $this->updateOrderTotal();
      }
      
      $this->_deleteGiftCertificateRedeemHistory($gift_certificate_code);
    }
    
    function getGiftCertificateCodes() {
      return $this->_gift_certificate_codes;
    }
    
    function hasGiftCertificate() {
      return !empty($this->_gift_certificate_codes);
    }
    
    function containsGiftCertifcate($gift_certificate_code) {
      return in_array($gift_certificate_code, $this->_gift_certificate_codes);
    }
    
    function setGiftCertificateRedeemAmount($gift_certificate_code, $amount) {
      $this->_gift_certificate_redeem_amount[$gift_certificate_code] = $amount;
    }
    
    function getGiftCertificateRedeemAmount($gift_certificate_code = '') {
      if ( !empty($gift_certificate_code) ) {
        return $this->_gift_certificate_redeem_amount[$gift_certificate_code];
      } 
      
      return $this->_gift_certificate_redeem_amount;
    }

    function setCouponCode($coupon_code) {
      $this->_coupon_code = $coupon_code;
      
      $this->_calculate();
      
      $this->updateOrderTotal();
      
      $this->_insertCouponRedeemHistory();
    }
      
    function getCouponCode() {
      return $this->_coupon_code;
    }
    
    function hasCoupon() {
      return !empty($this->_coupon_code);
    }
    
    function deleteCoupon() {
      $this->_deleteCouponRedeemHistory();
      $this->_coupon_code = null;
      
      $this->_calculate();
    }

    function setCouponAmount($amount) {
      $this->_coupon_amount = $amount;
    }

    function getCouponAmount() {
      return $this->_coupon_amount;
    }
    
    function isUseStoreCredit() {
      return $this->_use_customer_credit;
    }
    
    function setUseStoreCredit($use_store_credit) {
      $this->_use_customer_credit = $use_store_credit;
    }
      
    function setStoreCredit($store_credit) {
      $this->_customer_credit = $store_credit;
    }

    function getStoreCredit() {
      return $this->_customer_credit;
    }
    
    function addToTotal($amount) {
      $this->_total += $amount;
    }

    function getOrderTotals() {
      return $this->_order_totals;
    }

    function getShippingBoxesWeight() {
      return $this->_shipping_boxes_weight;
    }

    function numberOfShippingBoxes() {
      return $this->_shipping_boxes;
    }

    function updateOrderTotal(){
      global $osC_Database;

      $osC_Database->simpleQuery('delete from ' . TABLE_ORDERS_TOTAL . ' where orders_id = '. $this->_order_id);


      $modules = $this->getOrderTotals();
      foreach ($modules as $module) {
        $Qinsert = $osC_Database->query('insert into :table_orders_total (orders_id, title, text, value, class, sort_order) values (:orders_id, :title, :text, :value, :class, :sort_order)');
        $Qinsert->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
        $Qinsert->bindInt(':orders_id', $this->_order_id);
        $Qinsert->bindValue(':title', $module['title']);
        $Qinsert->bindValue(':text', $module['text']);
        $Qinsert->bindValue(':value', $module['value']);
        $Qinsert->bindValue(':class', $module['code']);
        $Qinsert->bindInt(':sort_order', $module['sort_order']);
        $Qinsert->setLogging($_SESSION['module'], $this->getOrderID());
        $Qinsert->execute();
      }
    }

    function _insertCouponRedeemHistory() {
      global $osC_Database;
      
      include_once('../includes/classes/coupon.php');
      $toC_Coupon = new toC_Coupon($this->getCouponCode());

      $Qcoupon = $osC_Database->query('insert into :table_coupons_redeem_history (coupons_id, customers_id, orders_id, redeem_amount, redeem_date, redeem_ip_address) values (:coupons_id, :customers_id, :orders_id, :redeem_amount, now(), :redeem_ip_address)');
      $Qcoupon->bindTable(':table_coupons_redeem_history', TABLE_COUPONS_REDEEM_HISTORY);
      $Qcoupon->bindInt(':coupons_id', $toC_Coupon->getID());
      $Qcoupon->bindInt(':customers_id', $this->_customer['customers_id']);
      $Qcoupon->bindInt(':orders_id', $this->getOrderID());
      $Qcoupon->bindValue(':redeem_amount', $this->_coupon_amount);
      $Qcoupon->bindValue(':redeem_ip_address', osc_get_ip_address());
      $Qcoupon->setLogging($_SESSION['module'], $this->getOrderID());
      $Qcoupon->execute();
      
      if (!$osC_Database->isError()) {
        return true;
      }
      
      return false;
    }
    
    function _deleteCouponRedeemHistory() {
      global $osC_Database;
      
      include_once('../includes/classes/coupon.php');
      $toC_Coupon = new toC_Coupon($this->_coupon_code);

      $Qdelete = $osC_Database->query('delete from :table_coupons_redeem_history where coupons_id = :coupons_id and orders_id = :orders_id');
      $Qdelete->bindTable(':table_coupons_redeem_history', TABLE_COUPONS_REDEEM_HISTORY);
      $Qdelete->bindInt(':coupons_id', $toC_Coupon->getID());
      $Qdelete->bindInt(':orders_id', $this->getOrderID());
      $Qdelete->setLogging($_SESSION['module'], $this->getOrderID());
      $Qdelete->execute();
      
      if (!$osC_Database->isError()) {
        return true;
      }
      
      return false;
    }
    
    function _insertGiftCertificateRedeemHistory($gift_certificate_code) {
      global $osC_Database;
      
      //get gift certificate id
      $Qcertificate = $osC_Database->query('select gift_certificates_id from :table_gift_certificates where gift_certificates_code = :gift_certificates_code');
      $Qcertificate->bindTable(':table_gift_certificates', TABLE_GIFT_CERTIFICATES);
      $Qcertificate->bindValue(':gift_certificates_code', $gift_certificate_code);
      $Qcertificate->execute();
      
      $Qinsert = $osC_Database->query('insert into :table_gift_certificates_redeem_history (gift_certificates_id, customers_id, orders_id, redeem_date, redeem_amount, redeem_ip_address) values (:gift_certificates_id, :customers_id, :orders_id, now(), :redeem_amount, :redeem_ip_address)');
      $Qinsert->bindTable(':table_gift_certificates_redeem_history', TABLE_GIFT_CERTIFICATES_REDEEM_HISTORY);
      $Qinsert->bindInt(':gift_certificates_id', $Qcertificate->valueInt('gift_certificates_id'));
      $Qinsert->bindInt(':customers_id', $this->_customer['customers_id']);
      $Qinsert->bindInt(':orders_id', $this->getOrderID());
      $Qinsert->bindValue(':redeem_amount', $this->_gift_certificate_redeem_amount[$gift_certificate_code]);
      $Qinsert->bindValue(':redeem_ip_address', osc_get_ip_address());
      $Qinsert->setLogging($_SESSION['module'], $this->getOrderID());
      $Qinsert->execute();
      
      if (!$osC_Database->isError()) {
        return true;
      }
      
      return false;
    }
    
    function _deleteGiftCertificateRedeemHistory($gift_certificate_code) {
      global $osC_Database;
        
      $Qdelete = $osC_Database->query('delete from :table_gift_certificates_redeem_history where orders_id = :orders_id and gift_certificates_id = (select gift_certificates_id from :table_gift_certificates where gift_certificates_code = :gift_certificates_code)');
      $Qdelete->bindTable(':table_gift_certificates_redeem_history', TABLE_GIFT_CERTIFICATES_REDEEM_HISTORY);
      $Qdelete->bindInt(':orders_id', $this->getOrderID());
      $Qdelete->bindValue(':gift_certificates_code', $gift_certificate_code);
      $Qdelete->setLogging($_SESSION['module'], $this->getOrderID());
      $Qdelete->execute();
      
      if (!$osC_Database->isError()) {
        return true;
      }
      
      return false;
    }

    function _calculate($set_shipping = true) {
      global $osC_Currencies, $osC_Tax, $osC_Weight, $osC_Shipping, $osC_OrderTotal, $osC_Customer;

//put the order currency code in the session for order total modules and shipping modules
      $_SESSION['currency'] = $this->getCurrency();
      
      $this->restoreStoreCredit();
      
      require_once('../includes/classes/customer.php');
      $osC_Customer = new osC_Customer();
      $osC_Customer->setCustomerData($this->getCustomersID());

      $this->_sub_total = 0;
      $this->_total = 0;
      $this->_weight = 0;
      $this->_tax = 0;
      $this->_tax_groups = array();
      $this->_shipping_boxes_weight = 0;
      $this->_shipping_boxes = 0;
      $this->_shipping_quotes = array();
      $this->_order_totals = array();

      if ($this->hasContents()) {
        foreach ($this->_contents as $products_id => $data) {
          $products_weight = $osC_Weight->convert($data['weight'], $data['weight_class_id'], SHIPPING_WEIGHT_UNIT);
          $this->_weight += $products_weight * $data['quantity'];

          $tax = $osC_Tax->getTaxRate($data['tax_class_id'], $this->getTaxingAddress('country_id'), $this->getTaxingAddress('zone_id'));
          $tax_description = $osC_Tax->getTaxRateDescription($data['tax_class_id'], $this->getTaxingAddress('country_id'), $this->getTaxingAddress('zone_id'));

          //update tax to database
          $this->_contents[$products_id]['tax'] = $tax;
          $this->_updateProductTax($this->_contents[$products_id]['orders_products_id'], $tax);
          
          $shown_price = $osC_Currencies->addTaxRateToPrice($data['final_price'], $tax, $data['quantity']);

          $this->_sub_total += $shown_price;
          $this->_total += $shown_price;

          if (DISPLAY_PRICE_WITH_TAX == '1') {
            $tax_amount = $shown_price - ($shown_price / (($tax < 10) ? '1.0' . str_replace('.', '', $tax) : '1.' . str_replace('.', '', $tax)));
          } else {
            $tax_amount = ($tax / 100) * $shown_price;

            //oscommerce 3 bug, no matter the tax is displayed or not, tax should not be add to total
            $this->_total += $tax_amount;
          }

          $this->_tax += $tax_amount;

          if (isset($this->_tax_groups[$tax_description])) {
            $this->_tax_groups[$tax_description] += $tax_amount;
          } else {
            $this->_tax_groups[$tax_description] = $tax_amount;
          }
        }

        $this->_shipping_boxes_weight = $this->_weight;
        $this->_shipping_boxes = 1;

        if (SHIPPING_BOX_WEIGHT >= ($this->_shipping_boxes_weight * SHIPPING_BOX_PADDING/100)) {
          $this->_shipping_boxes_weight = $this->_shipping_boxes_weight + SHIPPING_BOX_WEIGHT;
        } else {
          $this->_shipping_boxes_weight = $this->_shipping_boxes_weight + ($this->_shipping_boxes_weight * SHIPPING_BOX_PADDING/100);
        }

        if ($this->_shipping_boxes_weight > SHIPPING_MAX_WEIGHT) { // Split into many boxes
          $this->_shipping_boxes = ceil($this->_shipping_boxes_weight / SHIPPING_MAX_WEIGHT);
          $this->_shipping_boxes_weight = $this->_shipping_boxes_weight / $this->_shipping_boxes;
        }

        if ($set_shipping === true) {
          unset($_SESSION['osC_ShoppingCart_data']['shipping_quotes']);
          
          if (!class_exists('osC_Shipping')) {
            include('includes/classes/shipping.php');
          }

          //check if this order already have a delivery method
          if (!empty($this->_deliver_module)) {
            $osC_Shipping = new osC_Shipping($this->_deliver_module);
            $this->setShippingMethod($osC_Shipping->getQuote(), false);
          }else if (!$this->hasShippingMethod() || ($this->getShippingMethod('is_cheapest') === true)) {
            $osC_Shipping = new osC_Shipping();
            $this->setShippingMethod($osC_Shipping->getCheapestQuote(), false);
          }else{
            $osC_Shipping = new osC_Shipping($this->getShippingMethod('id'));
            $this->setShippingMethod($osC_Shipping->getQuote(), false);
          }
        }
      }
      if (!class_exists('osC_OrderTotal')) {
        include('includes/classes/order_total.php');
      }

      $osC_OrderTotal = new osC_OrderTotal();
      $this->_order_totals = $osC_OrderTotal->getResult();
      
      if ($this->isUseStoreCredit()) {
        $this->insertStoreCredit();
      }

      unset($_SESSION['currency']);
      unset($_SESSION['osC_Customer_data']);
    }
    
    function _updateProductTax($orders_products_id, $tax) {
      global $osC_Database;
      
      $Qupdate = $osC_Database->query('update :table_orders_products set products_tax = :products_tax where orders_id = :orders_id and orders_products_id = :orders_products_id');
      $Qupdate->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
      $Qupdate->bindInt(':orders_id', $this->_order_id);
      $Qupdate->bindInt(':orders_products_id', $orders_products_id);
      $Qupdate->bindValue(':products_tax', $tax);
      $Qupdate->execute();
      
      if (!$osC_Database->isError()) {
        return true;
      }

      return false;
    }

    function updateProductPrice($orders_products_id, $price) {
      global $osC_Database;
      
      $price = $price / $this->getCurrencyValue();
      
      $Qupdate = $osC_Database->query('update :table_orders_products set products_price = :products_price, final_price = :final_price where orders_id = :orders_id and orders_products_id = :orders_products_id');
      $Qupdate ->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
      $Qupdate->bindInt(':orders_id', $this->_order_id);
      $Qupdate->bindInt(':orders_products_id', $orders_products_id);
      $Qupdate->bindValue(':products_price', $price);
      $Qupdate->bindValue(':final_price', $price);
      $Qupdate->execute();
      
      if (!$osC_Database->isError()) {
        $this->_calculate();
        $this->updateOrderTotal();
        
        return true;
      }else{
        return false;
      }
    }
    
    function updateProductQuantity($orders_products_id, $quantity) {
      global $osC_Database;

      $error = false;
      
      if($quantity > 0) {
        //find products_id_string
        $products_id_string = '';
        foreach($this->_contents as $tmp_products_id_string => $tmp_product){
          if($orders_products_id == $this->_contents[$tmp_products_id_string]['orders_products_id'])
            $products_id_string = $tmp_products_id_string;
        }
      
        $Qupdate = $osC_Database->query('update :table_orders_products set products_quantity = :products_quantity where orders_products_id = :orders_products_id ');
        $Qupdate->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
        $Qupdate->bindInt(':products_quantity', $quantity);
        $Qupdate->bindInt(':orders_products_id', $orders_products_id);
        $Qupdate->setLogging($_SESSION['module'], $this->_order_id);
        $Qupdate->execute();

        if ($osC_Database->isError()){
          $error = true;
        }else{
          if ($quantity > $this->_contents[$products_id_string]['quantity']) {
            osC_Product::updateStock($this->_order_id, $orders_products_id, osc_get_product_id($products_id_string), ($quantity - $this->_contents[$products_id_string]['quantity']));
          }elseif ($quantity < $this->_contents[$products_id_string]['quantity']) {
            osC_Product::restock($this->_order_id, $orders_products_id, osc_get_product_id($products_id_string), ($this->_contents[$products_id_string]['quantity'] - $quantity));
          }

          $this->_contents[$products_id_string]['quantity'] = $quantity;

          $this->_calculate();
          $this->updateOrderTotal();
          
          return true;
        }
      }else{
        if($this->deleteProduct($orders_products_id)) {
          return true;
        }
      }
      
      return false;
    }
    
    function updateProducts($products){
      global $osC_Database;

      $error = false;

      foreach ($products as $orders_products_id => $product){
       //find products_id_string
       $products_id_string = '';
        foreach($this->_contents as $tmp_products_id_string => $tmp_product){
          if($orders_products_id == $this->_contents[$tmp_products_id_string]['orders_products_id'])
            $products_id_string = $tmp_products_id_string;
        }

        if($product['qty'] > 0 && ($error === false)) {
          $Qupdate = $osC_Database->query('update :table_orders_products set products_price =:products_price, final_price =:final_price, products_quantity = :products_quantity where orders_products_id = :orders_products_id ');
          $Qupdate->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
          $Qupdate->bindInt(':products_price', $product['price']);
          $Qupdate->bindInt(':final_price', $product['price']);
          $Qupdate->bindInt(':products_quantity', $product['qty']);
          $Qupdate->bindInt(':orders_products_id', $orders_products_id);
          $Qupdate->setLogging($_SESSION['module'], $this->_order_id);
          $Qupdate->execute();

          if ($osC_Database->isError()){
            $error = true;
          }else{
            if ($product['qty'] > $this->_contents[$products_id_string]['quantity']) {
              osC_Product::updateStock($this->_order_id, $orders_products_id, osc_get_product_id($products_id_string), ($product['qty'] - $this->_contents[$products_id_string]['quantity']));
            }elseif ($product['qty'] < $this->_contents[$products_id_string]['quantity']) {
              osC_Product::restock($this->_order_id, $orders_products_id, osc_get_product_id($products_id_string), ($this->_contents[$products_id_string]['quantity'] - $product['qty']));
            }

            $this->_contents[$products_id_string]['price'] = $product['price'];
            $this->_contents[$products_id_string]['final_price'] = $product['price'];
            $this->_contents[$products_id_string]['quantity'] = $product['qty'];

            $this->_calculate();
            $this->updateOrderTotal();
          }
        }else{
          if($this->deleteProduct($orders_products_id) === false){
            $error = true;
          }
        }
      }

      if($error === false)
        return true;

      return false;
    }

    function deleteProduct($orders_products_id) {
      global $osC_Database;

      $error = false;
      
      $osC_Database->startTransaction();
      
      //find products_id_string
      $products_id_string = '';
      foreach($this->_contents as $tmp_products_id_string => $tmp_product){
        if ($orders_products_id == $this->_contents[$tmp_products_id_string]['orders_products_id'])
          $products_id_string = $tmp_products_id_string;
      }
      
      $products_id = osc_get_product_id($products_id_string);
      $osC_Product = new osC_Product($products_id, $this->getCustomer('customers_id'));
      

      $restock = osC_Product::restock($this->_order_id, $orders_products_id, osc_get_product_id($products_id_string), $this->_contents[$products_id_string]['quantity']);

      if ($restock === true) {
        $QdeleteVariants = $osC_Database->query('delete from :table_orders_products_variants where orders_products_id = :orders_products_id ');
        $QdeleteVariants->bindTable(':table_orders_products_variants', TABLE_ORDERS_PRODUCTS_VARIANTS);
        $QdeleteVariants->bindInt(':orders_products_id', $orders_products_id);
        $QdeleteVariants->setLogging($_SESSION['module'], $this->_order_id);
        $QdeleteVariants->execute();

        if ($osC_Database->isError()) {
          $error = true;
        }else{
          $Qdelete = $osC_Database->query('delete from :table_orders_products where orders_products_id = :orders_products_id ');
          $Qdelete->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
          $Qdelete->bindInt(':orders_products_id', $orders_products_id);
          $Qdelete->setLogging($_SESSION['module'], $this->_order_id);
          $Qdelete->execute();
          
          if ($osC_Product->getProductType() == PRODUCT_TYPE_DOWNLOADABLE) {
            $Qopd = $osC_Database->query('delete from :table_orders_products_download where orders_products_id = :orders_products_id');
            $Qopd->bindTable(':table_orders_products_download', TABLE_ORDERS_PRODUCTS_DOWNLOAD);
            $Qopd->bindInt(':orders_products_id', $orders_products_id);
            $Qopd->setLogging($_SESSION['module'], $this->_order_id);
            $Qopd->execute();
          }
  
          if ($osC_Product->getProductType() == PRODUCT_TYPE_GIFT_CERTIFICATE) {
            $Qgc = $osC_Database->query('delete from :table_gift_certificates where orders_products_id = :orders_products_id');
            $Qgc->bindTable(':table_gift_certificates', TABLE_GIFT_CERTIFICATES);
            $Qgc->bindInt(':orders_products_id', $orders_products_id);
            $Qgc->setLogging($_SESSION['module'], $this->_order_id);
            $Qgc->execute();
          }

          if ($osC_Database->isError()) {
            $error = true;
          }else{
            unset($this->_contents[$products_id_string]);
          }
        }
      }

      if ($error === false) {
        $osC_Database->commitTransaction();

        $this->_calculate(true);
        $this->updateOrderTotal();
        return true;
      }

      $osC_Database->rollbackTransaction();
      return false;
    }

    /**
     * Add the products to this order.
     */
    function addProduct($products_id_string, $quantity, $gift_certificate_data = null) {
      global $osC_Database, $osC_Language, $osC_Tax;

      $error = false;

      $osC_Database->startTransaction();
      
      $products_id = osc_get_product_id($products_id_string);
      $variants = osc_parse_variants_from_id_string($products_id_string);
      $osC_Product = new osC_Product($products_id, $this->getCustomer('customers_id'));

      //If the products with the variants exists in the order, then increase the order quantity
      //Each gift certificate added to order will be considered as a new product
      //
      if (isset($this->_contents[$products_id_string])) {
        $orders_products_id = $this->_contents[$products_id_string]['orders_products_id'];
        $new_quantity = $this->_contents[$products_id_string]['quantity'] + $quantity;

        $Qupdate = $osC_Database->query('update :table_orders_products set products_quantity = :products_quantity, products_price = :products_price, final_price = :final_price, products_type = :products_type where orders_products_id = :orders_products_id ');
        $Qupdate->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
        $Qupdate->bindInt(':products_quantity', $new_quantity);
        $Qupdate->bindValue(':products_price', $osC_Product->getPrice($variants, $new_quantity));
        $Qupdate->bindValue(':final_price', $osC_Product->getPrice($variants, $new_quantity));
        $Qupdate->bindInt(':orders_products_id', $orders_products_id);
        $Qupdate->bindInt(':products_type', $osC_Product->getProductType());
        $Qupdate->setLogging($_SESSION['module'], $this->_order_id);
        $Qupdate->execute();

        if ($osC_Database->isError()) {
          $error = true;
        } else {
          $this->_contents[$products_id_string]['quantity'] = $new_quantity;
          $this->_contents[$products_id_string]['price'] = $osC_Product->getPrice($variants, $new_quantity);
          $this->_contents[$products_id_string]['final_price'] = $osC_Product->getPrice($variants, $new_quantity);
        }
      } else {
        $products_price = $osC_Product->getPrice($variants, $quantity);
        
        if ( $osC_Product->isEmailGiftCertificate() && $osC_Product->isOpenAmountGiftCertificate() ) {
          $products_price = $gift_certificate_data['price'];
        }
        
        $Qinsert = $osC_Database->query('insert into :table_orders_products (orders_id, products_id, products_sku, products_name, products_price, final_price, products_tax, products_quantity, products_type) values (:orders_id, :products_id, :products_sku, :products_name, :products_price, :final_price, :products_tax, :products_quantity, :products_type) ');
        $Qinsert->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
        $Qinsert->bindInt(':orders_id', $this->_order_id);
        $Qinsert->bindInt(':products_id', $osC_Product->getID());
        $Qinsert->bindValue(':products_sku', $osC_Product->getSKU());
        $Qinsert->bindValue(':products_name', $osC_Product->getTitle());
        $Qinsert->bindValue(':products_price', $products_price);
        $Qinsert->bindValue(':final_price', $products_price);
        $Qinsert->bindValue(':products_tax', $osC_Tax->getTaxRate($osC_Product->getTaxClassID(), $this->_shipping_address['country_id'], $this->_shipping_address['zone_id']));
        $Qinsert->bindInt(':products_quantity', $quantity);
        $Qinsert->bindInt(':products_type', $osC_Product->getProductType());
        $Qinsert->execute();
        
        if ($osC_Database->isError()){
          $error = true;
        } else {
          $orders_products_id = $osC_Database->nextID();
          
          if ( is_array($variants) && !empty($variants) ) {
            foreach ($variants as $groups_id => $values_id) {
              $Qvariants = $osC_Database->query('select pvg.products_variants_groups_name, pvv.products_variants_values_name from :table_products_variants pv, :table_products_variants_entries pve, :table_products_variants_groups pvg, :table_products_variants_values pvv where pv.products_id = :products_id and pv.products_variants_id = pve.products_variants_id and pve.products_variants_groups_id = :groups_id and pve.products_variants_values_id = :variants_values_id and pve.products_variants_groups_id = pvg.products_variants_groups_id and pve.products_variants_values_id = pvv.products_variants_values_id and pvg.language_id = :language_id and pvv.language_id = :language_id');
              $Qvariants->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
              $Qvariants->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
              $Qvariants->bindTable(':table_products_variants_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
              $Qvariants->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
              $Qvariants->bindInt(':products_id', $osC_Product->getID());
              $Qvariants->bindInt(':groups_id', $groups_id);
              $Qvariants->bindInt(':variants_values_id', $values_id);
              $Qvariants->bindInt(':language_id', $osC_Language->getID());
              $Qvariants->bindInt(':language_id', $osC_Language->getID());
              $Qvariants->execute();

              $Qinsert = $osC_Database->query('insert into :table_orders_products_variants (orders_id, orders_products_id, products_variants_groups_id, products_variants_groups, products_variants_values_id, products_variants_values) values (:orders_id, :orders_products_id, :products_variants_groups_id, :products_variants_groups, :products_variants_values_id, :products_variants_values) ');
              $Qinsert->bindTable(':table_orders_products_variants', TABLE_ORDERS_PRODUCTS_VARIANTS);
              $Qinsert->bindInt(':orders_id', $this->_order_id);
              $Qinsert->bindInt(':orders_products_id', $orders_products_id);
              $Qinsert->bindInt(':products_variants_groups_id', $groups_id);
              $Qinsert->bindValue(':products_variants_groups', $Qvariants->value('products_variants_groups_name'));
              $Qinsert->bindInt(':products_variants_values_id', $values_id);
              $Qinsert->bindValue(':products_variants_values', $Qvariants->value('products_variants_values_name'));
              $Qinsert->setLogging($_SESSION['module'], $this->_order_id);
              $Qinsert->execute();

              if ($osC_Database->isError()){
                $error = true;
                break;
              }
            }
          }
          
          if ($error === false) {
            if ($osC_Product->getProductType() == PRODUCT_TYPE_DOWNLOADABLE) {
              $Qdownloadable = $osC_Database->query('select * from :table_products_downloadables where products_id = :products_id');
              $Qdownloadable->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
              $Qdownloadable->bindInt(':products_id', $products_id);
              $Qdownloadable->execute();
              
              $Qopd = $osC_Database->query('insert into :table_orders_products_download (orders_id, orders_products_id, orders_products_filename, orders_products_cache_filename, download_maxdays, download_count) values (:orders_id, :orders_products_id, :orders_products_filename, :orders_products_cache_filename, :download_maxdays, :download_count)');
              $Qopd->bindTable(':table_orders_products_download', TABLE_ORDERS_PRODUCTS_DOWNLOAD);
              $Qopd->bindInt(':orders_id', $_REQUEST['orders_id']);
              $Qopd->bindInt(':orders_products_id', $orders_products_id);
              $Qopd->bindValue(':orders_products_filename', $Qdownloadable->value('filename'));
              $Qopd->bindValue(':orders_products_cache_filename', $Qdownloadable->value('cache_filename'));
              $Qopd->bindValue(':download_maxdays', $Qdownloadable->valueInt('number_of_accessible_days'));
              $Qopd->bindValue(':download_count', $Qdownloadable->valueInt('number_of_downloads'));
              $Qopd->setLogging($_SESSION['module'], $this->_order_id);
              $Qopd->execute();
              
              if ($osC_Database->isError()){
                $error = true;
              }
            }
          }
          
          if ($error === false) {
            if ($osC_Product->getProductType() == PRODUCT_TYPE_GIFT_CERTIFICATE) {
              require_once('../includes/classes/gift_certificates.php');
              
              $Qgc = $osC_Database->query('insert into :table_gift_certificates (orders_id, orders_products_id, gift_certificates_type, amount, gift_certificates_code, recipients_name, recipients_email, senders_name, senders_email, messages) values (:orders_id, :orders_products_id, :gift_certificates_type, :amount, :gift_certificates_code, :recipients_name, :recipients_email, :senders_name, :senders_email, :messages)');
              $Qgc->bindTable(':table_gift_certificates', TABLE_GIFT_CERTIFICATES);
              $Qgc->bindInt(':orders_id', $_REQUEST['orders_id']);
              $Qgc->bindInt(':gift_certificates_type', $gift_certificate_data['type']);
              $Qgc->bindInt(':orders_products_id', $orders_products_id);
              $Qgc->bindValue(':amount', $gift_certificate_data['price']);
              $Qgc->bindValue(':gift_certificates_code', toC_Gift_Certificates::createGiftCertificateCode());
              $Qgc->bindValue(':recipients_name', $gift_certificate_data['recipients_name']);
              $Qgc->bindValue(':recipients_email', $gift_certificate_data['recipients_email']);
              $Qgc->bindValue(':senders_name', $gift_certificate_data['senders_name']);
              $Qgc->bindValue(':senders_email', $gift_certificate_data['senders_email']);
              $Qgc->bindValue(':messages', $gift_certificate_data['message']);
              $Qgc->setLogging($_SESSION['module'], $this->_order_id);
              $Qgc->execute();
              
              if ($osC_Database->isError()){
                $error = true;
              }
            }
          }
        }

        if($error === false){
          $this->_contents[$products_id_string] = array('id' => $products_id,
                                                        'orders_products_id' => $orders_products_id,
                                                        'quantity' => $quantity,
                                                        'name' => $osC_Product->getTitle(),
                                                        'sku' => $osC_Product->getSKU($variants_array),
                                                        'tax' => $osC_Tax->getTaxRate($osC_Product->getTaxClassID(), $this->_shipping_address['country_id'], $this->_shipping_address['zone_id']),
                                                        'price' => $product_price,
                                                        'final_price' => $products_price,
                                                        'weight' => $osC_Product->getWeight($variants),
                                                        'tax_class_id' => $osC_Product->getTaxClassID(),
                                                        'weight_class_id' => $osC_Product->getWeightClass());
        }
      }

      osC_Product::updateStock($this->_order_id, $orders_products_id, $products_id, $quantity);
      
      if ($error === false) {
        $osC_Database->commitTransaction();

        $this->_calculate();
        $this->updateOrderTotal();

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }

    function getZoneID($country_id, $state){
      global $osC_Database;

      $zone_id = null;

      $Qcheck = $osC_Database->query('select zone_id from :table_zones where zone_country_id = :zone_country_id limit 1');
      $Qcheck->bindTable(':table_zones', TABLE_ZONES);
      $Qcheck->bindInt(':zone_country_id', $country_id);
      $Qcheck->execute();

      if ($Qcheck->numberOfRows() > 0) {
        $Qzone = $osC_Database->query('select zone_id from :table_zones where zone_country_id = :zone_country_id and zone_code like :zone_code');
        $Qzone->bindTable(':table_zones', TABLE_ZONES);
        $Qzone->bindInt(':zone_country_id', $country_id);
        $Qzone->bindValue(':zone_code', $state);
        $Qzone->execute();

        if ($Qzone->numberOfRows() === 1) {
          $zone_id = $Qzone->valueInt('zone_id');
        } else {
          $Qzone = $osC_Database->query('select zone_id from :table_zones where zone_country_id = :zone_country_id and zone_name like :zone_name');
          $Qzone->bindTable(':table_zones', TABLE_ZONES);
          $Qzone->bindInt(':zone_country_id', $country_id);
          $Qzone->bindValue(':zone_name', $state . '%');
          $Qzone->execute();

          if ($Qzone->numberOfRows() === 1) {
            $zone_id = $Qzone->valueInt('zone_id');
          }
        }
      }

      return $zone_id;
    }

    function getCountryInfo($country_id) {
      global $osC_Database;

      $Qcountry = $osC_Database->query('select countries_name, countries_iso_code_2, countries_iso_code_3, address_format from :table_countries where countries_id=:countries_id');
      $Qcountry->bindTable(':table_countries', TABLE_COUNTRIES);
      $Qcountry->bindInt(':countries_id', $country_id);
      $Qcountry->execute();

      if($Qcountry->numberOfRows() == 1){
        $data['country'] = $Qcountry->value('countries_name');
        $data['country_iso2'] = $Qcountry->value('countries_iso_code_2');
        $data['country_iso3'] = $Qcountry->value('countries_iso_code_3');
        $data['address_format'] = $Qcountry->value('address_format');
      }

      return $data;
    }

    function updateOrderInfo($data){
      global $osC_Database, $osC_Currencies;

      $country_info = $this->getCountryInfo($data['billing_country_id']);
      $data['billing_country_iso2'] = $country_info['country_iso2'];
      $data['billing_country_iso3'] = $country_info['country_iso3'];
      $data['billing_address_format'] = $country_info['address_format'];

      $country_info = $this->getCountryInfo($data['delivery_country_id']);
      $data['delivery_country_iso2'] = $country_info['country_iso2'];
      $data['delivery_country_iso3'] = $country_info['country_iso3'];
      $data['delivery_address_format'] = $country_info['address_format'];

      $fields = array();
      foreach($data as $field => $value){
        $fields[] = $field . '=:' . $field;
      }

      $Qupdate = $osC_Database->query('update :table_orders set ' . implode(',', $fields) . ' where orders_id=:orders_id');
      $Qupdate->bindTable(':table_orders', TABLE_ORDERS);

      foreach($data as $field => $value){
        $Qupdate->bindValue(':' . $field, $value);
      }

      $Qupdate->bindInt(':orders_id', $data['orders_id']);
      $Qupdate->setLogging($_SESSION['module'], $data['orders_id']);
      $Qupdate->execute();
      
      if ($osC_Database->isError()){
        return false;
      }else{

        $this->_getSummary($this->_order_id);
        $this->_calculate();
        $this->updateOrderTotal();
      }

      return true;
    }
    
    function restoreStoreCredit() {
      global $osC_Database;
      
      $error = false;
      
      $Qcheck = $osC_Database->query('select amount from :table_customers_credits_history where orders_id = :orders_id');
      $Qcheck->bindTable(':table_customers_credits_history', TABLE_CUSTOMERS_CREDITS_HISTORY);
      $Qcheck->bindInt(':orders_id', $this->_order_id);
      $Qcheck->execute();
      
      if ($Qcheck->numberOfRows() > 0) {
        $amount = $Qcheck->value('amount');
        
        $Qcredit = $osC_Database->query('update :table_customers set customers_credits = (customers_credits + :customers_credits) where customers_id = :customers_id');
        $Qcredit->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcredit->bindRaw(':customers_credits', $amount * (-1));
        $Qcredit->bindInt(':customers_id', $this->getCustomersID());
        $Qcredit->setLogging($_SESSION['module'], $this->_order_id);
        $Qcredit->execute();
        
        if ($osC_Database->isError()) {
          $error = true;
        }
        
        if ($error === false) {
          $Qdelete = $osC_Database->query('delete from :table_customers_credits_history where orders_id = :orders_id');
          $Qdelete->bindTable(':table_customers_credits_history', TABLE_CUSTOMERS_CREDITS_HISTORY);
          $Qdelete->bindInt(':orders_id', $this->_order_id);
          $Qdelete->setLogging($_SESSION['module'], $this->_order_id);
          $Qdelete->execute();
          
          if ($osC_Database->isError()) {
            $error = true;
          }
        }
      }
      
      if ($error === false) {
        return true;
      }
       
      return false;
    }
    
    function insertStoreCredit() {
      global $osC_Database;
      
      $error = false;
      
      $Qinsert = $osC_Database->query('insert into :table_customers_credits_history (customers_id, orders_id, action_type, date_added, amount, comments) values (:customers_id, :orders_id, :action_type, now(), :amount, :comments)');
      $Qinsert->bindTable(':table_customers_credits_history', TABLE_CUSTOMERS_CREDITS_HISTORY);
      $Qinsert->bindInt(':customers_id', $this->getCustomersID());
      $Qinsert->bindInt(':orders_id', $this->_order_id);
      $Qinsert->bindInt(':action_type', STORE_CREDIT_ACTION_TYPE_ORDER_PURCHASE);
      $Qinsert->bindValue(':amount', $this->getStoreCredit() * (-1));
      $Qinsert->bindValue(':comments', '');
      $Qinsert->setLogging($_SESSION['module'], $this->_order_id);
      $Qinsert->execute();
        
      if ($osC_Database->isError()) {
        $error = true;
      }
      
      if ($error === false) {
        $Qcredit = $osC_Database->query('update :table_customers set customers_credits = (customers_credits - :customers_credits) where customers_id = :customers_id');
        $Qcredit->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcredit->bindRaw(':customers_credits', $this->getStoreCredit());
        $Qcredit->bindInt(':customers_id', $this->getCustomersID());
        $Qcredit->setLogging($_SESSION['module'], $this->_order_id);
        $Qcredit->execute();
        
        if ($osC_Database->isError()) {
          $error = true;
        }
      }
      
      if ($error === false) {
        return true;
      }
      
      return false;
    }
    
    function updatePaymentMethod($payment_code, $pay_with_store_credit) {
      global $osC_Database;
      
      $payment_method = '';
      foreach (osC_Payment_Admin::getInstalledModules() as $key => $value) {
        if ($key == $payment_code) {
          $payment_method = $value;
        }
      }
      
      $this->setUseStoreCredit($pay_with_store_credit);
      $this->setBillingMethod(array('id' => $payment_code, 'title' => $payment_method));
      $this->updateOrderTotal();
      
      $Qupdate = $osC_Database->query('update :table_orders set payment_method = :payment_method, payment_module = :payment_module where orders_id = :orders_id');
      $Qupdate->bindTable(':table_orders', TABLE_ORDERS);
      $Qupdate->bindValue(':payment_method', implode(',', $this->getCartBillingMethods()));
      $Qupdate->bindValue(':payment_module', implode(',', $this->getCartBillingModules()));
      $Qupdate->bindInt(':orders_id', $this->_order_id);
      $Qupdate->setLogging($_SESSION['module'], $this->_order_id);
      $Qupdate->execute();

      if (!$osC_Database->isError()) {
        return true;
      }
      
      return false;
    }
  
    function setGiftWrapping($gift_wrapping, $wrapping_message) {
      global $osC_Database;
      
      $this->_is_gift_wrapping = $gift_wrapping;
      
      $this->_calculate();
      $this->updateOrderTotal();
      
      $Qupdate = $osC_Database->query('update :table_orders set gift_wrapping = :gift_wrapping, wrapping_message = :wrapping_message where orders_id = :orders_id');
      $Qupdate->bindTable(':table_orders', TABLE_ORDERS);
      $Qupdate->bindValue(':gift_wrapping', ($gift_wrapping == true ? 1 : 0));
      $Qupdate->bindValue(':wrapping_message', $wrapping_message);
      $Qupdate->bindInt(':orders_id', $this->_order_id);
      $Qupdate->setLogging($_SESSION['module'], $this->_order_id);
      $Qupdate->execute();
      
      if (!$osC_Database->isError()) {
        return true;
      }
      
      return false;
    }

    function isGiftWrapping() {
      return $this->_is_gift_wrapping;
    }
  }
?>
