<?php
/*
  $Id: order.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require_once('../includes/classes/product.php');

  class osC_Order {
  
// private variables
    var $_valid_order = false,
        $_contents = array(),
        $_sub_total = 0,
        $_total = 0,
        $_weight = 0,
        $_tax = 0,
        $_tax_groups = array(),
        $_is_gift_wrapping = false,
        $_gift_wrapping_message = '',
        $_coupon_code = null,
        $_coupon_amount = 0,
        $_order_id = 0,
        $_customers_id = 0,
        $_invoice_number = null,
        $_invoice_date = null,
        $_payment_method = '',
        $_payment_module = '',
        $_deliver_method = '',
        $_deliver_module = '',
        $_gift_certificate_codes = array(),
        $_gift_certificate_redeem_amount = array(),
        $_content_type,
        $_customer_credit = 0,
        $_use_customer_credit = false,
        $_has_payment_method = true,
        $_products_in_stock = true;

// class constructor
    function osC_Order($order_id = '') {
      $this->_valid_order = false;

      if (is_numeric($order_id)) {
        $this->_getSummary($order_id);
        $this->_getProducts();
      }
    }

// private methods
    function _getSummary($order_id) {
      global $osC_Database;

      $Qorder = $osC_Database->query('select * from :table_orders where orders_id = :orders_id');
      $Qorder->bindTable(':table_orders', TABLE_ORDERS);
      $Qorder->bindInt(':orders_id', $order_id);
      $Qorder->execute();

      if ($Qorder->numberOfRows() === 1) {
        $this->_valid_order = true;
        $this->_order_id = $Qorder->valueInt('orders_id');
        $this->_customers_id = $Qorder->valueInt('customers_id');
        $this->_invoice_number = $Qorder->value('invoice_number');
        $this->_invoice_date = $Qorder->value('invoice_date');
        $this->_is_gift_wrapping = $Qorder->valueInt('gift_wrapping');
        
        $customers_name = explode(' ', $Qorder->valueProtected('customers_name'));
        $first_name = '';
        $last_name = '';
        if (!empty($customers_name)) {
          foreach($customers_name as $key => $name) {
            if ($key === 0) {
              $first_name .= $name;
            }else {
              $last_name .= ' ' . $name;
            }
          }
        }

        $this->_customer = array('firstname' => $first_name,
                                 'lastname' => $last_name,
                                 'name' => $Qorder->valueProtected('customers_name'),
                                 'customers_id' => $Qorder->valueProtected('customers_id'),
                                 'company' => $Qorder->valueProtected('customers_company'),
                                 'street_address' => $Qorder->valueProtected('customers_street_address'),
                                 'suburb' => $Qorder->valueProtected('customers_suburb'),
                                 'city' => $Qorder->valueProtected('customers_city'),
                                 'postcode' => $Qorder->valueProtected('customers_postcode'),
                                 'state' => $Qorder->valueProtected('customers_state'),
                                 'zone_code' => $Qorder->value('customers_state_code'),
                                 'country_title' => $Qorder->value('customers_country'),
                                 'country_iso2' => $Qorder->value('customers_country_iso2'),
                                 'country_iso3' => $Qorder->value('customers_country_iso3'),
                                 'format' => $Qorder->value('customers_address_format'),
                                 'gift_wrapping' => $Qorder->valueInt('gift_wrapping'),
                                 'wrapping_message' => $Qorder->value('wrapping_message'),
                                 'telephone' => $Qorder->valueProtected('customers_telephone'),
                                 'email_address' => $Qorder->valueProtected('customers_email_address'));
        
        $delivery_name = explode(' ', $Qorder->valueProtected('delivery_name'));
        $first_name = ( isset($delivery_name[0]) ? $delivery_name[0] : '');
        $last_name = ( isset($delivery_name[1]) ? $delivery_name[1] : '');

        $this->_shipping_address = array(
                                 'firstname' => $first_name,
                                 'lastname' => $last_name,
                                 'name' => $Qorder->valueProtected('delivery_name'),
                                 'company' => $Qorder->valueProtected('delivery_company'),
                                 'street_address' => $Qorder->valueProtected('delivery_street_address'),
                                 'suburb' => $Qorder->valueProtected('delivery_suburb'),
                                 'city' => $Qorder->valueProtected('delivery_city'),
                                 'postcode' => $Qorder->valueProtected('delivery_postcode'),
                                 'state' => $Qorder->valueProtected('delivery_state'),
                                 'zone_id' => $Qorder->valueProtected('delivery_zone_id'),
                                 'zone_code' => $Qorder->valueProtected('delivery_state_code'),
                                 'country_id' => $Qorder->valueProtected('delivery_country_id'),
                                 'country_title' => $Qorder->valueProtected('delivery_country'),
                                 'country_iso_code_2' => $Qorder->valueProtected('delivery_country_iso2'),
                                 'country_iso_code_3' => $Qorder->valueProtected('delivery_country_iso3'),
                                 'telephone_number' => $Qorder->valueProtected('delivery_telephone'),
                                 'format' => $Qorder->valueProtected('delivery_address_format'));

        $billing_name = explode(' ', $Qorder->valueProtected('billing_name'));
        $first_name = ( isset($billing_name[0]) ? $billing_name[0] : '');
        $last_name = ( isset($billing_name[1]) ? $billing_name[1] : '');

        $this->_billing_address = array(
                                'firstname' => $first_name,
                                'lastname' => $last_name,
                                'name' => $Qorder->valueProtected('billing_name'),
                                'company' => $Qorder->valueProtected('billing_company'),
                                'street_address' => $Qorder->valueProtected('billing_street_address'),
                                'suburb' => $Qorder->valueProtected('billing_suburb'),
                                'city' => $Qorder->valueProtected('billing_city'),
                                'postcode' => $Qorder->valueProtected('billing_postcode'),
                                'state' => $Qorder->valueProtected('billing_state'),
                                'zone_id' => $Qorder->valueProtected('billing_zone_id'),
                                'zone_code' => $Qorder->valueProtected('billing_state_code'),
                                'country_id' => $Qorder->valueProtected('billing_country_id'),
                                'country_title' => $Qorder->valueProtected('billing_country'),
                                'country_iso_code_2' => $Qorder->valueProtected('billing_country_iso2'),
                                'country_iso_code_3' => $Qorder->valueProtected('billing_country_iso3'),
                                'telephone_number' => $Qorder->valueProtected('billing_telephone'),
                                'format' => $Qorder->valueProtected('billing_address_format'));
        
        
        $payment_methods = $Qorder->valueProtected('payment_method');
        $payment_modules = $Qorder->valueProtected('payment_module');
        
        $payment_methods = explode(',', $payment_methods);
        $payment_modules = explode(',', $payment_modules);
        
        if (current($payment_modules) == 'store_credit') {
          $this->_use_customer_credit = true;
          
          if (sizeof($payment_modules) == 2) {
            $this->_payment_method = next($payment_methods);
            $this->_payment_module = next($payment_modules);
          } else {
            $this->_has_payment_method = false;
            $this->_payment_method = $Qorder->value('payment_method');
          }
        } else {
          $this->_payment_method = current($payment_methods);
          $this->_payment_module = current($payment_modules);
        }
        
        $this->_date_purchased = $Qorder->value('date_purchased');
        $this->_last_modified = $Qorder->value('last_modified');
        $this->_status_id = $Qorder->value('orders_status');
        $this->_customers_comment = ($Qorder->valueProtected('customers_comment') == null) ? '' : $Qorder->value('customers_comment');
        $this->_admin_comment = ($Qorder->valueProtected('admin_comment') == null) ? '' : $Qorder->value('admin_comment');
        
        $this->_currency = array('code' => $Qorder->value('currency'),
                                 'value' => $Qorder->value('currency_value'));

        $this->_getTotals();
      }
    }

    function _getStatus() {
      global $osC_Database, $osC_Language;

      $Qstatus = $osC_Database->query('select orders_status_name from :table_orders_status where orders_status_id = :orders_status_id and language_id = :language_id');
      $Qstatus->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
      $Qstatus->bindInt(':orders_status_id', $this->_status_id);

/* HPDL - DEFAULT_LANGUAGE is the language code, not the language id */
//        $Qstatus->bindInt(':language_id', (isset($_SESSION['languages_id']) ? $_SESSION['languages_id'] : DEFAULT_LANGUAGE));
      $Qstatus->bindInt(':language_id', $osC_Language->getID());
      $Qstatus->execute();

      if ($Qstatus->numberOfRows() === 1) {
        $this->_status = $Qstatus->value('orders_status_name');
      } else {
        $this->_status = $this->_status_id;
      }
    }

    function _getStatusHistory() {
      global $osC_Database, $osC_Language;

      $history_array = array();

      $Qhistory = $osC_Database->query('select osh.orders_status_history_id, osh.orders_status_id, osh.date_added, osh.customer_notified, osh.comments, os.orders_status_name from :table_orders_status_history osh left join :table_orders_status os on (osh.orders_status_id = os.orders_status_id and os.language_id = :language_id) where osh.orders_id = :orders_id order by osh.date_added');
      $Qhistory->bindTable(':table_orders_status_history', TABLE_ORDERS_STATUS_HISTORY);
      $Qhistory->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);

/* HPDL - DEFAULT_LANGUAGE is the language code, not the language id */
//        $Qstatus->bindInt(':language_id', (isset($_SESSION['languages_id']) ? $_SESSION['languages_id'] : DEFAULT_LANGUAGE));
      $Qhistory->bindInt(':language_id', $osC_Language->getID());

      $Qhistory->bindInt(':orders_id', $this->_order_id);
      $Qhistory->execute();

      while ($Qhistory->next()) {
        $history_array[] = array('status_id' => $Qhistory->valueInt('orders_status_id'),
                                 'orders_status_history_id' => $Qhistory->valueInt('orders_status_history_id'),
                                 'status' => $Qhistory->value('orders_status_name'),
                                 'date_added' => $Qhistory->value('date_added'),
                                 'customer_notified' => $Qhistory->valueInt('customer_notified'),
                                 'comment' => $Qhistory->valueProtected('comments'));
      }

      $this->_status_history = $history_array;
    }

    function _getTransactionHistory() {
      global $osC_Database, $osC_Language;

      $this->_transaction_history = array();

      $Qhistory = $osC_Database->query('select oth.transaction_code, oth.transaction_return_value, oth.transaction_return_status, oth.date_added, ots.status_name from :table_orders_transactions_history oth left join :table_orders_transactions_status ots on (oth.transaction_code = ots.id and ots.language_id = :language_id) where oth.orders_id = :orders_id order by oth.date_added');
      $Qhistory->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
      $Qhistory->bindTable(':table_orders_transactions_status', TABLE_ORDERS_TRANSACTIONS_STATUS);
      $Qhistory->bindInt(':language_id', $osC_Language->getID());
      $Qhistory->bindInt(':orders_id', $this->_order_id);
      $Qhistory->execute();

      while ($Qhistory->next()) {
        $this->_transaction_history[] = array('status_id' => $Qhistory->valueInt('transaction_code'),
                                              'status' => $Qhistory->value('status_name'),
                                              'return_value' => $Qhistory->valueProtected('transaction_return_value'),
                                              'return_status' => $Qhistory->valueInt('transaction_return_status'),
                                              'date_added' => $Qhistory->value('date_added'));
      }
    }

    function _getPostTransactionActions() {
      global $osC_Database, $osC_Language;

      $this->_post_transaction_actions = array();

      if (file_exists('includes/modules/payment/' . $this->_payment_module . '.php')) {
        include_once('includes/classes/payment.php');
        include_once('includes/modules/payment/' . $this->_payment_module . '.php');

        if (call_user_func(array('osC_Payment_' . $this->_payment_module, 'isInstalled')) === true) {
          $trans_array = array();

          foreach ($this->getTransactionHistory() as $history) {
            if ($history['return_status'] === 1) {
              $trans_array[] = $history['status_id'];
            }
          }

          $transactions = call_user_func(array('osC_Payment_' . $this->_payment_module, 'getPostTransactionActions'), $trans_array);

          if (is_array($transactions) && (empty($transactions) === false)) {
            $Qactions = $osC_Database->query('select id, status_name from :table_orders_transactions_status where language_id = :language_id and id in :id order by status_name');
            $Qactions->bindTable(':table_orders_transactions_status', TABLE_ORDERS_TRANSACTIONS_STATUS);
            $Qactions->bindInt(':language_id', $osC_Language->getID());
            $Qactions->bindRaw(':id', '(' . implode(', ', array_keys($transactions)) . ')');
            $Qactions->execute();

            $trans_code_array = array();

            while ($Qactions->next()) {
              $this->_post_transaction_actions[] = array('id' => $transactions[$Qactions->valueInt('id')],
                                                         'text' => $Qactions->value('status_name'));
            }
          }
        }
      }
    }

    function _getProducts() {
      global $osC_Database;

      $Qproducts = $osC_Database->query('select op.orders_products_id, op.products_id, op.products_type, op.products_name, op.products_sku, op.products_price, op.products_tax, op.products_quantity, op.products_return_quantity, op.final_price, p.products_weight, p.products_weight_class, p.products_tax_class_id from :table_orders_products op, :table_products p where p.products_id = op.products_id and orders_id = :orders_id');
      $Qproducts->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindInt(':orders_id', $this->_order_id);
      $Qproducts->execute();
      
      while ($Qproducts->next()) {
        $product = array('id' => $Qproducts->valueInt('products_id'),
                         'orders_products_id' => $Qproducts->valueInt('orders_products_id'),
                         'type' => $Qproducts->valueInt('products_type'),
                         'quantity' => $Qproducts->valueInt('products_quantity'),
                         'return_quantity' => $Qproducts->valueInt('products_return_quantity'),
                         'name' => $Qproducts->value('products_name'),
                         'sku' => $Qproducts->value('products_sku'),
                         'tax' => $Qproducts->value('products_tax'),
                         'tax_class_id' => $Qproducts->value('products_tax_class_id'),
                         'price' => $Qproducts->value('products_price'),
                         'final_price' => $Qproducts->value('final_price'),
                         'weight' => $Qproducts->value('products_weight'),
                         'tax_class_id' => $Qproducts->value('products_tax_class_id'),
                         'weight_class_id' => $Qproducts->value('products_weight_class'));
        
        if ($product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
          $Qcertificate = $osC_Database->query('select gift_certificates_type, gift_certificates_code, senders_name, senders_email, recipients_name, recipients_email, messages from :table_gift_certificates where orders_id = :orders_id and orders_products_id = :orders_products_id');
          $Qcertificate->bindTable(':table_gift_certificates', TABLE_GIFT_CERTIFICATES);
          $Qcertificate->bindInt(':orders_id', $this->_order_id);
          $Qcertificate->bindInt(':orders_products_id', $Qproducts->valueInt('orders_products_id'));
          $Qcertificate->execute();
          
          if ($Qcertificate->numberOfRows() > 0) {
            $product['gift_certificates_type'] = $Qcertificate->valueInt('gift_certificates_type');
            $product['gift_certificates_code'] = $Qcertificate->value('gift_certificates_code');
            $product['senders_name'] = $Qcertificate->value('senders_name');
            $product['senders_email'] = $Qcertificate->value('senders_email');
            $product['recipients_name'] = $Qcertificate->value('recipients_name');
            $product['recipients_email'] = $Qcertificate->value('recipients_email');
            $product['messages'] = $Qcertificate->value('messages');
          }
          
          $Qcertificate->freeResult();        
        }

        $Qvariants = $osC_Database->query('select products_variants_groups_id as groups_id, products_variants_groups as groups_name, products_variants_values_id as values_id, products_variants_values as values_name from :table_orders_products_variants where orders_id = :orders_id and orders_products_id = :orders_products_id');
        $Qvariants->bindTable(':table_orders_products_variants', TABLE_ORDERS_PRODUCTS_VARIANTS);
        $Qvariants->bindInt(':orders_id', $this->_order_id);
        $Qvariants->bindInt(':orders_products_id', $Qproducts->valueInt('orders_products_id'));
        $Qvariants->execute();

        $variants = array();
        if ($Qvariants->numberOfRows() > 0) {
          while ($Qvariants->next()) {
            $product['variants'][] = array('groups_id' => $Qvariants->valueInt('groups_id'),
                                           'values_id' => $Qvariants->valueInt('values_id'),
                                           'groups_name' => $Qvariants->value('groups_name'),
                                           'values_name' => $Qvariants->value('values_name'));

            $variants[$Qvariants->valueInt('groups_id')] = $Qvariants->valueInt('values_id');
          }
        }
        
        $Qcustomizations = $osC_Database->query('select orders_products_customizations_id, quantity from :table_orders_products_customizations where orders_id = :orders_id and orders_products_id = :orders_products_id');
        $Qcustomizations->bindTable(':table_orders_products_customizations', TABLE_ORDERS_PRODUCTS_CUSTOMIZATIONS);
        $Qcustomizations->bindInt(':orders_id', $this->_order_id);
        $Qcustomizations->bindInt(':orders_products_id', $Qproducts->valueInt('orders_products_id'));
        $Qcustomizations->execute();
        
        $customizations = null;
        while ( $Qcustomizations->next() ) {
          $Qfields = $osC_Database->query('select * from :table_orders_products_customizations_values where orders_products_customizations_id = :orders_products_customizations_id');
          $Qfields->bindTable(':table_orders_products_customizations_values', TABLE_ORDERS_PRODUCTS_CUSTOMIZATIONS_VALUES);
          $Qfields->bindInt(':orders_products_customizations_id', $Qcustomizations->valueInt('orders_products_customizations_id'));
          $Qfields->execute();
          
          $fields = array();
          while( $Qfields->next() ) {
            $fields[$Qfields->valueInt('orders_products_customizations_values_id')] = 
              array('customization_fields_id' => $Qfields->valueInt('customization_fields_id'),
                    'customization_fields_name' => $Qfields->value('customization_fields_name'),
                    'customization_type' => $Qfields->valueInt('customization_fields_type'),
                    'customization_value' => $Qfields->value('customization_fields_value'),
                    'cache_filename' => $Qfields->value('cache_file_name'));
          }
          $customizations[] = array('qty' => $Qcustomizations->valueInt('quantity'), 'fields' => $fields);
        }
        
        if ($customizations != null) {
          $product['customizations'] = $customizations;
        }
        
        if ($product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
          $products_id_string = $Qproducts->valueInt('products_id') . '#' . $product['orders_products_id'];
        } else {
          $products_id_string = osc_get_product_id_string($Qproducts->valueInt('products_id'), $variants);
        }
        
        $this->_contents[$products_id_string] = $product;
      }
    }

    function _getTotals() {
      global $osC_Database;

      $Qtotals = $osC_Database->query('select title, text, value, class from :table_orders_total where orders_id = :orders_id order by sort_order');
      $Qtotals->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
      $Qtotals->bindInt(':orders_id', $this->_order_id);
      $Qtotals->execute();

      $totals_array = array();
      while ($Qtotals->next()) {
        $totals_array[] = array('title' => $Qtotals->value('title'),
                                'text' => $Qtotals->value('text'),
                                'value' => $Qtotals->value('value'),
                                'class' => $Qtotals->value('class'));

        $class = $Qtotals->value('class');
        
        //shipping
        if (strpos($Qtotals->value('class'), 'shipping') !== false) {
          list($shipping, $module) = explode('-', $Qtotals->value('class'));
          
          $tmp = explode(':', $Qtotals->value('title'));
          $this->_deliver_method = $tmp[0];
          $this->_deliver_module = $module;
        } 
        //coupon
        else if ($class == 'coupon') {
          $this->_coupon_code = substr(strstr($Qtotals->value('title'), '('), 1, -3);
          $this->_coupon_amount = $Qtotals->value('value');
        } 
        //gift certificate
        else if ($class == 'gift_certificate') {
          $this->_gift_certificate_codes = array();
          $this->_gift_certificate_redeem_amount = array();
        
          $codes = explode(",", substr(strstr($Qtotals->value('title'), '('), 1, -3));
          foreach ($codes as $code) {
            $gift_certificate = explode("[", trim($code));
            $this->_gift_certificate_codes[] = trim($gift_certificate[0]);
            $this->_gift_certificate_redeem_amount[] = substr(trim($gift_certificate[1]), 0, -1);
          }
        } 
        //sub total
        else if ($class == 'sub_total') {
          $this->_sub_total = $Qtotals->value('value');
        } 
        //tax
        else if ($class == 'tax') {
          $this->_tax = $Qtotals->value('value');
        } 
        //total
        else if ($class == 'total') {
          $this->_total = $Qtotals->value('value');
        } 
      }

      $this->_order_totals = $totals_array;
    }

// public methods
    function isValid() {
      if ($this->_valid_order === true) {
        return true;
      } else {
        return false;
      }
    }

    function getOrderID() {
      return $this->_order_id;
    }
    
    function getCustomersID() {
      return $this->_customers_id;
    }
      
    function getInvoiceNumber() {
      return $this->_invoice_number;
    }
    
    function getCustomer($id = '') {
      if (empty($id)) {
        return $this->_customer;
      } elseif (isset($this->_customer[$id])) {
        return $this->_customer[$id];
      }

      return false;
    }

    function getDelivery($id = '') {
      if (empty($id)) {
        return $this->_shipping_address;
      } elseif (isset($this->_shipping_address[$id])) {
        return $this->_shipping_address[$id];
      }

      return false;
    }

    function getBilling($id = '') {
      if (empty($id)) {
        return $this->_billing_address;
      } elseif (isset($this->_billing_address[$id])) {
        return $this->_billing_address[$id];
      }

      return false;
    }

    function getPaymentMethod() {
      return $this->_payment_method;
    }

    function getPaymentModule() {
      return $this->_payment_module;
    }

    function isUseStoreCredit() {
      return $this->_use_customer_credit;
    }

    function hasPaymentMethod() {
      return $this->_has_payment_method;
    }
    
    function getDeliverMethod(){
      return $this->_deliver_method;
    }

    function getCreditCardDetails($id = '') {
      if (empty($id)) {
        return $this->_credit_card;
      } elseif (isset($this->_credit_card[$id])) {
        return $this->_credit_card[$id];
      }

      return false;
    }

    function isValidCreditCard() {
      if (!empty($this->_credit_card['owner']) && !empty($this->_credit_card['number']) && !empty($this->_credit_card['expires'])) {
        return true;
      }

      return false;
    }

    function getCurrency($id = 'code') {
      if (isset($this->_currency[$id])) {
        return $this->_currency[$id];
      }

      return false;
    }

    function getCurrencyValue() {
      return $this->getCurrency('value');
    }

    function getDateCreated() {
      return $this->_date_purchased;
    }
    
    function getInvoiceDate() {
      return $this->_invoice_date;
    }

    function getDateLastModified() {
      return $this->_last_modified;
    }

    function getStatusID() {
      return $this->_status_id;
    }

    function getStatus() {
      if (!isset($this->_status)) {
        $this->_getStatus();
      }

      return $this->_status;
    }

    function getNumberOfComments() {
      $number_of_comments = 0;

      if (!isset($this->_status_history)) {
        $this->_getStatusHistory();
      }

      foreach ($this->_status_history as $status_history) {
        if (!empty($status_history['comment'])) {
          $number_of_comments++;
        }
      }

      return $number_of_comments;
    }
    
    function getCustomersComment() {
      return $this->_customers_comment;
    }
    
    function getAdminComment() {
      return $this->_admin_comment;
    }

    function getProducts() {
      if (!isset($this->_contents)) {
        $this->_getProducts();
      }

      return $this->_contents;
    }

    function getNumberOfProducts() {
      if (!isset($this->_contents)) {
        $this->_getProducts();
      }

      return sizeof($this->_contents);
    }

    function getNumberOfItems() {
      $number_of_items = 0;

      if (!isset($this->_contents)) {
        $this->_getProducts();
      }

      foreach ($this->_contents as $product) {
        $number_of_items += $product['quantity'];
      }

      return $number_of_items;
    }

    function getTotal($id = 'total') {
      if (!isset($this->_order_totals)) {
        $this->_getTotals();
      }

      foreach ($this->_order_totals as $total) {
        if ($total['class'] == $id) {
          return strip_tags($total['text']);
        }
      }

      return false;
    }

    function getTotals() {
      if (!isset($this->_order_totals)) {
        $this->_getTotals();
      }

      return $this->_order_totals;
    }

    function getStatusHistory() {
      if (!isset($this->_status_history)) {
        $this->_getStatusHistory();
      }

      return $this->_status_history;
    }

    function getTransactionHistory() {
      if (!isset($this->_transaction_history)) {
        $this->_getTransactionHistory();
      }

      return $this->_transaction_history;
    }

    function getPostTransactionActions() {
      if (!isset($this->_post_transaction_actions)) {
        $this->_getPostTransactionActions();
      }

      return $this->_post_transaction_actions;
    }

    function hasPostTransactionActions() {
      if (!isset($this->_post_transaction_actions)) {
        $this->_getPostTransactionActions();
      }

      return !empty($this->_post_transaction_actions);
    }

    function hasShippingAddress(){
      return !empty($this->_shipping_address['name']) || !empty($this->_shipping_address['street_address']);
    }
    
    function createInvoice($id) {
      global $osC_Database;
      
      $Qcheck = $osC_Database->query('select max(invoice_number) as invoice_number from :table_orders');
      $Qcheck->bindTable(':table_orders', TABLE_ORDERS);
      $Qcheck->execute();
      
      $invoice_number = $Qcheck->value('invoice_number') + 1;
      $invoice_number = ($invoice_number > INVOICE_START_NUMBER) ? $invoice_number : INVOICE_START_NUMBER;
      
      $Qupdate = $osC_Database->query('update :table_orders set invoice_number = :invoice_number, invoice_date = now() where orders_id = :orders_id');
      $Qupdate->bindTable(':table_orders', TABLE_ORDERS);
      $Qupdate->bindInt(':orders_id', $id);
      $Qupdate->bindInt(':invoice_number', $invoice_number);
      $Qupdate->setLogging($_SESSION['module'], $id);
      $Qupdate->execute();
            
      if ( !$osC_Database->isError() ) {
        self::activeDownloadables($id);
        self::activeGiftCertificates($id);
        
        return true;
      }
      
      return false;
    }

    function activeDownloadables($orders_id) {
      global $osC_Database;
      
      //create email template object
      require_once('../includes/classes/email_template.php');
      $email = toC_Email_Template::getEmailTemplate('active_downloadable_product');
      
      //retrieve order information
      $Qorder = $osC_Database->query('select * from :table_orders where orders_id = :orders_id');
      $Qorder->bindTable(':table_orders', TABLE_ORDERS);
      $Qorder->bindInt(':orders_id', $orders_id);
      $Qorder->execute();
      
      $customers_name = $Qorder->value('customers_name');
      $customers_email_address = $Qorder->value('customers_email_address');
      $Qorder->freeResult();
      
      //retrieve downloable products
      $Qproducts = $osC_Database->query('select opd.orders_products_download_id, opd.status, op.products_name from :table_orders_products_download opd, :table_orders_products op where opd.orders_products_id = op.orders_products_id and opd.orders_id = :orders_id');
      $Qproducts->bindTable(':table_orders_products_download', TABLE_ORDERS_PRODUCTS_DOWNLOAD);
      $Qproducts->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
      $Qproducts->bindInt(':orders_id', $orders_id);
      $Qproducts->execute();
      
      while($Qproducts->next()) {
        if ( $Qproducts->valueInt('status') == 0 ) {
          //update downloadables product status
          $Qupdate = $osC_Database->query('update :table_orders_products_download set status = :status where orders_products_download_id = :orders_products_download_id');
          $Qupdate->bindTable(':table_orders_products_download', TABLE_ORDERS_PRODUCTS_DOWNLOAD);
          $Qupdate->bindInt(':status', 1);
          $Qupdate->bindInt(':orders_products_download_id', $Qproducts->valueInt('orders_products_download_id'));
          $Qupdate->setLogging($_SESSION['module'], $orders_id);
          $Qupdate->execute();      
            
          //send notification email
          $email->setData($customers_name, $customers_email_address, $Qproducts->value('products_name'));
          $email->buildMessage();
          $email->sendEmail();
        }
      }
    }
    
    function activeGiftCertificates($orders_id) {
      global $osC_Database;
      
      require_once('includes/classes/currencies.php');  
      $osC_Currencies = new osC_Currencies_Admin();

      //create email template object
      require_once('../includes/classes/email_template.php');
      $email = toC_Email_Template::getEmailTemplate('active_gift_certificate');
      
      //retrieve gift certifcates
      $Qcertificates = $osC_Database->query('select * from :table_gift_certificates where orders_id = :orders_id');
      $Qcertificates->bindTable(':table_gift_certificates', TABLE_GIFT_CERTIFICATES);
      $Qcertificates->bindInt(':orders_id', $orders_id);
      $Qcertificates->execute();
      
      while ($Qcertificates->next()) {
        if ( $Qcertificates->valueInt('status') == 0 ) {
          //update gift certificate status
          $Qupdate = $osC_Database->query('update :table_gift_certificates set status = :status where gift_certificates_id = :gift_certificates_id');
          $Qupdate->bindTable(':table_gift_certificates', TABLE_GIFT_CERTIFICATES);
          $Qupdate->bindInt(':status', 1);
          $Qupdate->bindInt(':gift_certificates_id', $Qcertificates->valueInt('gift_certificates_id'));
          $Qupdate->setLogging($_SESSION['module'], $orders_id);
          $Qupdate->execute();  
          
          //send notification email
          if ($Qcertificates->valueInt('type') == GIFT_CERTIFICATE_TYPE_EMAIL) {
            $email->resetRecipients();
            $email->setData($Qcertificates->value('senders_name'), $Qcertificates->value('senders_email'), $Qcertificates->value('recipients_name'), $Qcertificates->value('recipients_email'), $osC_Currencies->format($Qcertificates->value('amount')), $Qcertificates->value('gift_certificates_code'), $Qcertificates->value('messages'));
            $email->buildMessage();
            $email->sendEmail();
          }
        }
      }
    }
    
    function updateAdminComment($orders_id, $comment) {
      global $osC_Database;
      
      $Qupdate = $osC_Database->query('update :table_orders set admin_comment = :admin_comment where orders_id = :orders_id');
      $Qupdate->bindTable(':table_orders', TABLE_ORDERS);
      $Qupdate->bindValue(':admin_comment', $_REQUEST['admin_comment']);
      $Qupdate->bindInt(':orders_id', $_REQUEST['orders_id']);
      $Qupdate->setLogging($_SESSION['module'], $orders_id);
      $Qupdate->execute();

      if ( !$osC_Database->isError() ) {
        return true;
      }
      
      return false;
    }
    
    function updateCurrency($orders_id, $currency, $currency_value) {
      global $osC_Database;
      
      $Qupdate = $osC_Database->query('update :table_orders set currency = :currency, currency_value = :currency_value where orders_id = :orders_id');
      $Qupdate->bindTable(':table_orders', TABLE_ORDERS);
      $Qupdate->bindInt(':orders_id', $orders_id);
      $Qupdate->bindValue(':currency', $currency);
      $Qupdate->bindValue(':currency_value', $currency_value);
      $Qupdate->setLogging($_SESSION['module'], $orders_id);
      $Qupdate->execute();
      
      if (!$osC_Database->isError()) {
        return true;
      }
      
      return false;
    }
    
    function updateProductSKU($orders_id, $orders_products_id, $sku) {
      global $osC_Database;
      
      $Qsku = $osC_Database->query('update :table_orders_products set products_sku = :products_sku where orders_id = :orders_id and orders_products_id = :orders_products_id');
      $Qsku->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
      $Qsku->bindInt(':orders_id', $orders_id);
      $Qsku->bindInt(':orders_products_id', $orders_products_id);
      $Qsku->bindValue(':products_sku', $sku);
      $Qsku->setLogging($_SESSION['module'], $orders_id);
      $Qsku->execute();
      
      if ($osC_Database->isError()) {
        return false;
      }
      
      return true;
    }
    
    function delete($id, $restock = false) {
      global $osC_Database;

      $error = false;

      $osC_Database->startTransaction();

      if ($restock === true) {
        $Qproducts = $osC_Database->query('select orders_products_id, products_id, products_type, products_quantity from :table_orders_products where orders_id = :orders_id');
        $Qproducts->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
        $Qproducts->bindInt(':orders_id', $id);
        $Qproducts->execute();

        while ($Qproducts->next()) {
          $result = osC_Product::restock($id, $Qproducts->valueInt('orders_products_id'), $Qproducts->valueInt('products_id'), $Qproducts->valueInt('products_quantity'));

          if ($result == false) {
            $error = true;

            break;
          }
        }
      }
      
      if ($error === false) {
        $Qproducts = $osC_Database->query('delete from :table_orders_refunds_products where orders_refunds_id = (select orders_refunds_id from :table_orders_refunds where orders_id = :orders_id) ');
        $Qproducts->bindTable(':table_orders_refunds_products', TABLE_ORDERS_REFUNDS_PRODUCTS);
        $Qproducts->bindTable(':table_orders_refunds', TABLE_ORDERS_REFUNDS);
        $Qproducts->bindInt(':orders_id', $id);
        $Qproducts->setLogging($_SESSION['module'], $id);
        $Qproducts->execute();

        if ($osC_Database->isError() === true) {
          $error = true;
        }
      }
      
      if ($error === false) {
        $Qrefunds = $osC_Database->query('delete from :table_orders_refunds where orders_id = :orders_id');
        $Qrefunds->bindTable(':table_orders_refunds', TABLE_ORDERS_REFUNDS);
        $Qrefunds->bindInt(':orders_id', $id);
        $Qrefunds->setLogging($_SESSION['module'], $id);
        $Qrefunds->execute();

        if ($osC_Database->isError() === true) {
          $error = true;
        }
      }
      
      if ($error === false) {
        $Qproducts = $osC_Database->query('delete from :table_orders_returns_products where orders_returns_id = (select orders_returns_id from :table_orders_returns where orders_id = :orders_id) ');
        $Qproducts->bindTable(':table_orders_returns_products', TABLE_ORDERS_RETURNS_PRODUCTS);
        $Qproducts->bindTable(':table_orders_returns', TABLE_ORDERS_RETURNS);
        $Qproducts->bindInt(':orders_id', $id);
        $Qproducts->setLogging($_SESSION['module'], $id);
        $Qproducts->execute();

        if ($osC_Database->isError() === true) {
          $error = true;
        }
      }
      
      if ($error === false) {
        $Qreturns = $osC_Database->query('delete from :table_orders_returns where orders_id = :orders_id');
        $Qreturns->bindTable(':table_orders_returns', TABLE_ORDERS_RETURNS);
        $Qreturns->bindInt(':orders_id', $id);
        $Qreturns->setLogging($_SESSION['module'], $id);
        $Qreturns->execute();

        if ($osC_Database->isError() === true) {
          $error = true;
        }
      }
      
      if ($error === false) {
        $Qopd = $osC_Database->query('delete from :table_orders_products_download where orders_id = :orders_id');
        $Qopd->bindTable(':table_orders_products_download', TABLE_ORDERS_PRODUCTS_DOWNLOAD);
        $Qopd->bindInt(':orders_id', $id);
        $Qopd->setLogging($_SESSION['module'], $id);
        $Qopd->execute();

        if ($osC_Database->isError() === true) {
          $error = true;
        }
      }

      if ($error === false) {
        $Qgc = $osC_Database->query('delete from :table_gift_certificates where orders_id = :orders_id');
        $Qgc->bindTable(':table_gift_certificates', TABLE_GIFT_CERTIFICATES);
        $Qgc->bindInt(':orders_id', $id);
        $Qgc->setLogging($_SESSION['module'], $id);
        $Qgc->execute();

        if ($osC_Database->isError() === true) {
          $error = true;
        }
      }
      
      if ($error === false) {
        $Qopa = $osC_Database->query('delete from :table_orders_products_variants where orders_id = :orders_id');
        $Qopa->bindTable(':table_orders_products_variants', TABLE_ORDERS_PRODUCTS_VARIANTS);
        $Qopa->bindInt(':orders_id', $id);
        $Qopa->setLogging($_SESSION['module'], $id);
        $Qopa->execute();

        if ($osC_Database->isError() === true) {
          $error = true;
        }
      }

      if ($error === false) {
        $Qop = $osC_Database->query('delete from :table_orders_products where orders_id = :orders_id');
        $Qop->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
        $Qop->bindInt(':orders_id', $id);
        $Qop->setLogging($_SESSION['module'], $id);
        $Qop->execute();

        if ($osC_Database->isError() === true) {
          $error = true;
        }
      }

      if ($error === false) {
        $Qosh = $osC_Database->query('delete from :table_orders_transactions_history where orders_id = :orders_id');
        $Qosh->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
        $Qosh->bindInt(':orders_id', $id);
        $Qosh->setLogging($_SESSION['module'], $id);
        $Qosh->execute();

        if ($osC_Database->isError() === true) {
          $error = true;
        }
      }

      if ($error === false) {
        $Qosh = $osC_Database->query('delete from :table_orders_status_history where orders_id = :orders_id');
        $Qosh->bindTable(':table_orders_status_history', TABLE_ORDERS_STATUS_HISTORY);
        $Qosh->bindInt(':orders_id', $id);
        $Qosh->setLogging($_SESSION['module'], $id);
        $Qosh->execute();

        if ($osC_Database->isError() === true) {
          $error = true;
        }
      }

      if ($error === false) {
        $Qot = $osC_Database->query('delete from :table_orders_total where orders_id = :orders_id');
        $Qot->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
        $Qot->bindInt(':orders_id', $id);
        $Qot->setLogging($_SESSION['module'], $id);
        $Qot->execute();

        if ($osC_Database->isError() === true) {
          $error = true;
        }
      }

      if ($error === false) {
        $Qo = $osC_Database->query('delete from :table_orders where orders_id = :orders_id');
        $Qo->bindTable(':table_orders', TABLE_ORDERS);
        $Qo->bindInt(':orders_id', $id);
        $Qo->setLogging($_SESSION['module'], $id);
        $Qo->execute();

        if ($osC_Database->isError() === true) {
          $error = true;
        }
      }

      if ($error === false) {
        $osC_Database->commitTransaction();

        return true;
      } else {
        $osC_Database->rollbackTransaction();

        return false;
      }
    }
    
  function createOrder($data){
      global $osC_Database, $osC_Currencies;

      $error = false;

      $osC_Database->startTransaction();

      $Qcustomer = $osC_Database->query('select c.customers_default_address_id, a.entry_company as company, a.entry_firstname as firstname, a.entry_lastname as lastname, a.entry_street_address as street_address, a.entry_suburb as suburb, a.entry_postcode as postcode, a.entry_city as city, a.entry_state as state, a.entry_country_id as country_id, a.entry_zone_id as zone_id, co.countries_name as country, co.countries_iso_code_2 as country_iso2, co.countries_iso_code_3 as country_iso3, co.address_format as address_format from :table_customers c left join :table_address_book a on c.customers_default_address_id = a.address_book_id left join :table_countries co on a.entry_country_id = co.countries_id where c.customers_id = :customers_id');
      $Qcustomer->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qcustomer->bindTable(':table_address_book', TABLE_ADDRESS_BOOK);
      $Qcustomer->bindTable(':table_countries', TABLE_COUNTRIES);
      $Qcustomer->bindInt(':customers_id', $data['customers_id']);
      $Qcustomer->execute();

      $customer_data = $Qcustomer->toArray();
      $data = array_merge($data, $customer_data);

      $Qorder = $osC_Database->query('insert into :table_orders (customers_id, customers_name, customers_company, customers_street_address, customers_suburb, customers_city, customers_postcode, customers_state, customers_state_code, customers_country, customers_country_iso2, customers_country_iso3, customers_telephone, customers_email_address, customers_address_format, customers_ip_address, delivery_name, delivery_company, delivery_street_address, delivery_suburb, delivery_city, delivery_postcode, delivery_state, delivery_zone_id, delivery_state_code, delivery_country_id, delivery_country, delivery_country_iso2, delivery_country_iso3, delivery_address_format, billing_name, billing_company, billing_street_address, billing_suburb, billing_city, billing_postcode, billing_state, billing_zone_id, billing_state_code, billing_country_id, billing_country, billing_country_iso2, billing_country_iso3, billing_address_format, payment_method, payment_module, date_purchased, orders_status, currency, currency_value) values (:customers_id, :customers_name, :customers_company, :customers_street_address, :customers_suburb, :customers_city, :customers_postcode, :customers_state, :customers_state_code, :customers_country, :customers_country_iso2, :customers_country_iso3, :customers_telephone, :customers_email_address, :customers_address_format, :customers_ip_address, :delivery_name, :delivery_company, :delivery_street_address, :delivery_suburb, :delivery_city, :delivery_postcode, :delivery_state, :delivery_zone_id, :delivery_state_code, :delivery_country_id, :delivery_country, :delivery_country_iso2, :delivery_country_iso3, :delivery_address_format, :billing_name, :billing_company, :billing_street_address, :billing_suburb, :billing_city, :billing_postcode, :billing_state, :billing_zone_id, :billing_state_code, :billing_country_id, :billing_country, :billing_country_iso2, :billing_country_iso3, :billing_address_format, :payment_method, :payment_module, now(), :orders_status, :currency, :currency_value)');
      $Qorder->bindTable(':table_orders', TABLE_ORDERS);
      $Qorder->bindInt(':customers_id', $data['customers_id']);
      $Qorder->bindValue(':customers_name', $data['customers_name']);
      $Qorder->bindValue(':customers_company', '' /*$order->customer['company']*/);
      $Qorder->bindValue(':customers_street_address', '' /*$order->customer['street_address']*/);
      $Qorder->bindValue(':customers_suburb', '' /*$order->customer['suburb']*/);
      $Qorder->bindValue(':customers_city', '' /*$order->customer['city']*/);
      $Qorder->bindValue(':customers_postcode', '' /*$order->customer['postcode']*/);
      $Qorder->bindValue(':customers_state', '' /*$order->customer['state']*/);
      $Qorder->bindValue(':customers_state_code', '');
      $Qorder->bindValue(':customers_country', '' /*$order->customer['country']['title']*/);
      $Qorder->bindValue(':customers_country_iso2', '');
      $Qorder->bindValue(':customers_country_iso3', '');
      $Qorder->bindValue(':customers_telephone', '' /*$order->customer['telephone']*/);
      $Qorder->bindValue(':customers_email_address', $data['customers_email_address']);
      $Qorder->bindValue(':customers_address_format', '');
      $Qorder->bindValue(':customers_ip_address', '');
      $Qorder->bindValue(':delivery_name', $data['firstname'] . ',' . $data['lastname']);
      $Qorder->bindValue(':delivery_company', $data['company']);
      $Qorder->bindValue(':delivery_street_address', $data['street_address']);
      $Qorder->bindValue(':delivery_suburb', $data['suburb']);
      $Qorder->bindValue(':delivery_city', $data['city']);
      $Qorder->bindValue(':delivery_postcode', $data['postcode']);
      $Qorder->bindValue(':delivery_state', $data['state']);
      $Qorder->bindValue(':delivery_zone_id', $data['zone_id']);
      $Qorder->bindValue(':delivery_state_code', '');
      $Qorder->bindValue(':delivery_country_id', $data['country_id']);
      $Qorder->bindValue(':delivery_country', $data['country']);
      $Qorder->bindValue(':delivery_country_iso2', $data['country_iso2']);
      $Qorder->bindValue(':delivery_country_iso3', $data['country_iso3']);
      $Qorder->bindValue(':delivery_address_format', $data['address_format']);
      $Qorder->bindValue(':billing_name', $data['firstname'] . ',' . $data['lastname']);
      $Qorder->bindValue(':billing_company', $data['company']);
      $Qorder->bindValue(':billing_street_address', $data['street_address']);
      $Qorder->bindValue(':billing_suburb', $data['suburb']);
      $Qorder->bindValue(':billing_city', $data['city']);
      $Qorder->bindValue(':billing_postcode', $data['postcode']);
      $Qorder->bindValue(':billing_state', $data['state']);
      $Qorder->bindValue(':billing_zone_id', $data['zone_id']);
      $Qorder->bindValue(':billing_state_code', '');
      $Qorder->bindValue(':billing_country_id', $data['country_id']);
      $Qorder->bindValue(':billing_country', $data['country']);
      $Qorder->bindValue(':billing_country_iso2', $data['country_iso2']);
      $Qorder->bindValue(':billing_country_iso3', $data['country_iso3']);
      $Qorder->bindValue(':billing_address_format', $data['address_format']);
      $Qorder->bindValue(':payment_method', '');
      $Qorder->bindValue(':payment_module', '');
      $Qorder->bindInt(':orders_status', 1);
      $Qorder->bindValue(':currency', DEFAULT_CURRENCY);
      $Qorder->bindValue(':currency_value', $osC_Currencies->value(DEFAULT_CURRENCY));
      $Qorder->setLogging($_SESSION['module']);
      $Qorder->execute();

      if ( $osC_Database->isError() ) {
        $error = true;
      } else {
        $orders_id = $osC_Database->nextID();

        $Qstatus = $osC_Database->query('insert into :table_orders_status_history (orders_id, orders_status_id, date_added, customer_notified, comments) values (:orders_id, :orders_status_id, now(), :customer_notified, :comments)');
        $Qstatus->bindTable(':table_orders_status_history', TABLE_ORDERS_STATUS_HISTORY);
        $Qstatus->bindInt(':orders_id', $orders_id);
        $Qstatus->bindInt(':orders_status_id', DEFAULT_ORDERS_STATUS_ID);
        $Qstatus->bindInt(':customer_notified', '0');
        $Qstatus->bindValue(':comments', '');
        $Qstatus->setLogging($_SESSION['module'], $orders_id);
        $Qstatus->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
        }else{
          $Qtotal = $osC_Database->query('insert into :table_orders_total (orders_id, title, text, value, class, sort_order) values (:orders_id, :title, :text, :value, :class, :sort_order)');
          $Qtotal->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
          $Qtotal->bindInt(':orders_id', $orders_id);
          $Qtotal->bindValue(':title', 'Total:');
          $Qtotal->bindValue(':text', $osC_Currencies->format(0));
          $Qtotal->bindValue(':value', '0');
          $Qtotal->bindValue(':class', 'total');
          $Qtotal->bindValue(':sort_order', '100');
          $Qtotal->setLogging($_SESSION['module'], $orders_id);
          $Qtotal->execute();

          if ( $osC_Database->isError() ) {
            $error = true;
          }
        }
      }

      if ( $error === false ) {
        $osC_Database->commitTransaction();

        return $orders_id;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }
  }
?>
