<?php
/*
  $Id: order.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
  class osC_Order {
    var $info, $totals, $products, $customer, $delivery, $content_type;

/* Private variables */

    var $_id;

/* Class constructor */

    function osC_Order($order_id = '') {
      if (is_numeric($order_id)) {
        $this->_id = $order_id;
      }

      $this->info = array();
      $this->totals = array();
      $this->products = array();
      $this->customer = array();
      $this->delivery = array();

      if (!empty($order_id)) {
        $this->query($order_id);
      } else {
        $this->cart();
      }
    }

/* Public methods */
    
    function getID() {
      if (is_numeric($this->_id)) {
        return $this->_id;
      }
      
      return false;
    }

    function getStatusID($id) {
      global $osC_Database;

      $Qorder = $osC_Database->query('select orders_status from :table_orders where orders_id = :orders_id');
      $Qorder->bindTable(':table_orders', TABLE_ORDERS);
      $Qorder->bindInt(':orders_id', $id);
      $Qorder->execute();

      if ($Qorder->numberOfRows()) {
        return $Qorder->valueInt('orders_status');
      }

      return false;
    }

    function remove($id) {
      global $osC_Database;

      $Qcheck = $osC_Database->query('select orders_status from :table_orders where orders_id = :orders_id');
      $Qcheck->bindTable(':table_orders', TABLE_ORDERS);
      $Qcheck->bindInt(':orders_id', $id);
      $Qcheck->execute();

      if ($Qcheck->valueInt('orders_status') === ORDERS_STATUS_PREPARING) {
        $Qdel = $osC_Database->query('delete from :table_orders_products_download where orders_id = :orders_id');
        $Qdel->bindTable(':table_orders_products_download', TABLE_ORDERS_PRODUCTS_DOWNLOAD);
        $Qdel->bindInt(':orders_id', $id);
        $Qdel->execute();
        
        $Qdel = $osC_Database->query('delete from :table_gift_certificates where orders_id = :orders_id');
        $Qdel->bindTable(':table_gift_certificates', TABLE_GIFT_CERTIFICATES);
        $Qdel->bindInt(':orders_id', $id);
        $Qdel->execute();

        $Qdel = $osC_Database->query('delete from :table_orders_products_variants where orders_id = :orders_id');
        $Qdel->bindTable(':table_orders_products_variants', TABLE_ORDERS_PRODUCTS_VARIANTS);
        $Qdel->bindInt(':orders_id', $id);
        $Qdel->execute();

        $Qdel = $osC_Database->query('delete from :table_orders_products where orders_id = :orders_id');
        $Qdel->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
        $Qdel->bindInt(':orders_id', $id);
        $Qdel->execute();

        $Qdel = $osC_Database->query('delete from :table_orders_status_history where orders_id = :orders_id');
        $Qdel->bindTable(':table_orders_status_history', TABLE_ORDERS_STATUS_HISTORY);
        $Qdel->bindInt(':orders_id', $id);
        $Qdel->execute();

        $Qdel = $osC_Database->query('delete from :table_orders_total where orders_id = :orders_id');
        $Qdel->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
        $Qdel->bindInt(':orders_id', $id);
        $Qdel->execute();

        $Qdel = $osC_Database->query('delete from :table_orders where orders_id = :orders_id');
        $Qdel->bindTable(':table_orders', TABLE_ORDERS);
        $Qdel->bindInt(':orders_id', $id);
        $Qdel->execute();
      }

      if (isset($_SESSION['prepOrderID'])) {
        unset($_SESSION['prepOrderID']);
      }
    }
    
    function createCustomer() {
      global $osC_ShoppingCart, $osC_Customer;
      
      $address = $osC_ShoppingCart->getBillingAddress();
      $data = array('firstname' => $address['firstname'], 
                    'lastname' => $address['lastname'], 
                    'password' => $address['password'], 
                    'gender' => $address['gender'], 
                    'email_address' => $address['email_address']);
      
      if (osC_Account::createEntry($data, false)) {
        //insert billing address
        osC_Account::createNewAddress($osC_Customer->getID(), $address);
        
        //insert shipping address
        if (isset($address['ship_to_this_address']) && $address['ship_to_this_address'] == '0') {
          $shipping_address = $osC_ShoppingCart->getShippingAddress();
          
          osC_Account::createNewAddress($osC_Customer->getID(), $shipping_address);
        }
        
        return true;
      } else {
        return false;
      }
    }

    function insert($order_status = DEFAULT_ORDERS_STATUS_ID) {
      global $osC_Database, $osC_Customer, $osC_Language, $osC_Currencies, $osC_ShoppingCart, $osC_Tax, $toC_Wishlist;

      if (isset($_SESSION['prepOrderID'])) {
        $_prep = explode('-', $_SESSION['prepOrderID']);

        if ($_prep[0] == $osC_ShoppingCart->getCartID()) {
          return $_prep[1]; // order_id
        } else {
          if (osC_Order::getStatusID($_prep[1]) === ORDERS_STATUS_PREPARING) {
            osC_Order::remove($_prep[1]);
          }
        }
      }
                
      if (!class_exists(osC_Account)) {
        require_once('includes/classes/account.php');                    
      }
      
      if (!$osC_Customer->isLoggedOn()) {
        osC_Order::createCustomer();
      } else {
        //insert billing address
        $billing_address = $osC_ShoppingCart->getBillingAddress();
        
        if (isset($billing_address['id']) && ($billing_address['id'] == '-1')) {
          osC_Account::createNewAddress($osC_Customer->getID(), $billing_address);
        }
        
        //insert shipping address
        if (!isset($billing_address['ship_to_this_address']) || (isset($billing_address['ship_to_this_address']) && empty($billing_address['ship_to_this_address']))) {
          $shipping_address = $osC_ShoppingCart->getShippingAddress();
          
          if (isset($shipping_address['id']) && ($shipping_address['id'] == '-1')) {
            osC_Account::createNewAddress($osC_Customer->getID(), $shipping_address);
          }
        }
      }
                     
      $Qorder = $osC_Database->query('insert into :table_orders (customers_id, customers_name, customers_company, customers_street_address, customers_suburb, customers_city, customers_postcode, customers_state, customers_comment, customers_state_code, customers_country, customers_country_iso2, customers_country_iso3, customers_telephone, customers_email_address, customers_address_format, customers_ip_address, delivery_name, delivery_company, delivery_street_address, delivery_suburb, delivery_city, delivery_postcode, delivery_state, delivery_zone_id, delivery_state_code, delivery_country_id, delivery_country, delivery_country_iso2, delivery_country_iso3, delivery_address_format, delivery_telephone, billing_name, billing_company, billing_street_address, billing_suburb, billing_city, billing_postcode, billing_state, billing_zone_id, billing_state_code, billing_country_id, billing_country, billing_country_iso2, billing_country_iso3, billing_address_format, billing_telephone, payment_method, payment_module, uses_store_credit, store_credit_amount, date_purchased, orders_status, currency, currency_value, gift_wrapping, wrapping_message) values (:customers_id, :customers_name, :customers_company, :customers_street_address, :customers_suburb, :customers_city, :customers_postcode, :customers_state, :customers_comment, :customers_state_code, :customers_country, :customers_country_iso2, :customers_country_iso3, :customers_telephone, :customers_email_address, :customers_address_format, :customers_ip_address, :delivery_name, :delivery_company, :delivery_street_address, :delivery_suburb, :delivery_city, :delivery_postcode, :delivery_state, :delivery_zone_id, :delivery_state_code, :delivery_country_id, :delivery_country, :delivery_country_iso2, :delivery_country_iso3, :delivery_address_format, :delivery_telephone, :billing_name, :billing_company, :billing_street_address, :billing_suburb, :billing_city, :billing_postcode, :billing_state, :billing_zone_id, :billing_state_code, :billing_country_id, :billing_country, :billing_country_iso2, :billing_country_iso3, :billing_address_format, :billing_telephone, :payment_method, :payment_module, :uses_store_credit, :store_credit_amount, now(), :orders_status, :currency, :currency_value, :gift_wrapping, :wrapping_message)');
      $Qorder->bindTable(':table_orders', TABLE_ORDERS);
      $Qorder->bindInt(':customers_id', $osC_Customer->getID());
      $Qorder->bindValue(':customers_name', $osC_Customer->getName());
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
      $Qorder->bindValue(':customers_email_address', $osC_Customer->getEmailAddress());
      $Qorder->bindValue(':customers_comment', $_SESSION['comments']);
      $Qorder->bindValue(':customers_address_format', '');
      $Qorder->bindValue(':customers_ip_address', osc_get_ip_address());
      $Qorder->bindValue(':delivery_name', $osC_ShoppingCart->getShippingAddress('firstname') . ' ' . $osC_ShoppingCart->getShippingAddress('lastname'));
      $Qorder->bindValue(':delivery_company', $osC_ShoppingCart->getShippingAddress('company'));
      $Qorder->bindValue(':delivery_street_address', $osC_ShoppingCart->getShippingAddress('street_address'));
      $Qorder->bindValue(':delivery_suburb', $osC_ShoppingCart->getShippingAddress('suburb'));
      $Qorder->bindValue(':delivery_city', $osC_ShoppingCart->getShippingAddress('city'));
      $Qorder->bindValue(':delivery_postcode', $osC_ShoppingCart->getShippingAddress('postcode'));
      $Qorder->bindValue(':delivery_state', $osC_ShoppingCart->getShippingAddress('state'));
      $Qorder->bindValue(':delivery_zone_id', $osC_ShoppingCart->getShippingAddress('zone_id'));
      $Qorder->bindValue(':delivery_state_code', $osC_ShoppingCart->getShippingAddress('zone_code'));
      $Qorder->bindValue(':delivery_country_id', $osC_ShoppingCart->getShippingAddress('country_id'));
      $Qorder->bindValue(':delivery_country', $osC_ShoppingCart->getShippingAddress('country_title'));
      $Qorder->bindValue(':delivery_country_iso2', $osC_ShoppingCart->getShippingAddress('country_iso_code_2'));
      $Qorder->bindValue(':delivery_country_iso3', $osC_ShoppingCart->getShippingAddress('country_iso_code_3'));
      $Qorder->bindValue(':delivery_address_format', $osC_ShoppingCart->getShippingAddress('format'));
      $Qorder->bindValue(':delivery_telephone', $osC_ShoppingCart->getShippingAddress('telephone_number'));
      $Qorder->bindValue(':billing_name', $osC_ShoppingCart->getBillingAddress('firstname') . ' ' . $osC_ShoppingCart->getBillingAddress('lastname'));
      $Qorder->bindValue(':billing_company', $osC_ShoppingCart->getBillingAddress('company'));
      $Qorder->bindValue(':billing_street_address', $osC_ShoppingCart->getBillingAddress('street_address'));
      $Qorder->bindValue(':billing_suburb', $osC_ShoppingCart->getBillingAddress('suburb'));
      $Qorder->bindValue(':billing_city', $osC_ShoppingCart->getBillingAddress('city'));
      $Qorder->bindValue(':billing_postcode', $osC_ShoppingCart->getBillingAddress('postcode'));
      $Qorder->bindValue(':billing_state', $osC_ShoppingCart->getBillingAddress('state'));
      $Qorder->bindValue(':billing_zone_id', $osC_ShoppingCart->getBillingAddress('zone_id'));
      $Qorder->bindValue(':billing_state_code', $osC_ShoppingCart->getBillingAddress('zone_code'));
      $Qorder->bindValue(':billing_country_id', $osC_ShoppingCart->getBillingAddress('country_id'));
      $Qorder->bindValue(':billing_country', $osC_ShoppingCart->getBillingAddress('country_title'));
      $Qorder->bindValue(':billing_country_iso2', $osC_ShoppingCart->getBillingAddress('country_iso_code_2'));
      $Qorder->bindValue(':billing_country_iso3', $osC_ShoppingCart->getBillingAddress('country_iso_code_3'));
      $Qorder->bindValue(':billing_address_format', $osC_ShoppingCart->getBillingAddress('format'));
      $Qorder->bindValue(':billing_telephone', $osC_ShoppingCart->getBillingAddress('telephone_number'));
      $Qorder->bindValue(':payment_method', implode(',', $osC_ShoppingCart->getCartBillingMethods()));
      $Qorder->bindValue(':payment_module', implode(',', $osC_ShoppingCart->getCartBillingModules()));
      $Qorder->bindInt(':uses_store_credit', $osC_ShoppingCart->isUseStoreCredit());
      $Qorder->bindValue(':store_credit_amount', ($osC_ShoppingCart->isUseStoreCredit() ? $osC_ShoppingCart->getStoreCredit() : '0'));
      $Qorder->bindInt(':orders_status', $order_status);
      $Qorder->bindValue(':currency', $osC_Currencies->getCode());
      $Qorder->bindValue(':currency_value', $osC_Currencies->value($osC_Currencies->getCode()));
      $Qorder->bindInt(':gift_wrapping', ($osC_ShoppingCart->isGiftWrapping() ? '1' : '0'));
      $Qorder->bindValue(':wrapping_message', (isset($_SESSION['gift_wrapping_comments']) ? $_SESSION['gift_wrapping_comments'] : ''));
      $Qorder->execute();

      $insert_id = $osC_Database->nextID();

      foreach ($osC_ShoppingCart->getOrderTotals() as $module) {
        $Qtotals = $osC_Database->query('insert into :table_orders_total (orders_id, title, text, value, class, sort_order) values (:orders_id, :title, :text, :value, :class, :sort_order)');
        $Qtotals->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
        $Qtotals->bindInt(':orders_id', $insert_id);
        $Qtotals->bindValue(':title', $module['title']);
        $Qtotals->bindValue(':text', $module['text']);
        $Qtotals->bindValue(':value', $module['value']);
        $Qtotals->bindValue(':class', $module['code']);
        $Qtotals->bindInt(':sort_order', $module['sort_order']);
        $Qtotals->execute();
      }

      $Qstatus = $osC_Database->query('insert into :table_orders_status_history (orders_id, orders_status_id, date_added, customer_notified, comments) values (:orders_id, :orders_status_id, now(), :customer_notified, :comments)');
      $Qstatus->bindTable(':table_orders_status_history', TABLE_ORDERS_STATUS_HISTORY);
      $Qstatus->bindInt(':orders_id', $insert_id);
      $Qstatus->bindInt(':orders_status_id', $order_status);
      $Qstatus->bindInt(':customer_notified', '0');
      $Qstatus->bindValue(':comments', (isset($_SESSION['comments']) ? $_SESSION['comments'] : ''));
      $Qstatus->execute();

      foreach ($osC_ShoppingCart->getProducts() as $products) {
        $Qproducts = $osC_Database->query('insert into :table_orders_products (orders_id, products_id, products_type, products_sku, products_name, products_price, final_price, products_tax, products_quantity) values (:orders_id, :products_id, :products_type, :products_sku, :products_name, :products_price, :final_price, :products_tax, :products_quantity)');
        $Qproducts->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
        $Qproducts->bindInt(':orders_id', $insert_id);
        $Qproducts->bindInt(':products_id', osc_get_product_id($products['id']));
        $Qproducts->bindValue(':products_type', $products['type']);
        $Qproducts->bindValue(':products_sku', $products['sku']);
        $Qproducts->bindValue(':products_name', $products['name']);
        $Qproducts->bindValue(':products_price', $products['price']);
        $Qproducts->bindValue(':final_price', $products['final_price']);
        $Qproducts->bindValue(':products_tax', $osC_Tax->getTaxRate($products['tax_class_id'], $osC_ShoppingCart->getTaxingAddress('country_id'), $osC_ShoppingCart->getTaxingAddress('zone_id')));
        $Qproducts->bindInt(':products_quantity', $products['quantity']);
        $Qproducts->execute();

        $order_products_id = $osC_Database->nextID();
        
        if (!empty($products['customizations'])) {
          foreach ($products['customizations'] as $customization) {
            $Qcustomization = $osC_Database->query('insert into :table_orders_products_customizations (orders_id, orders_products_id, quantity) values (:orders_id, :orders_products_id, :quantity)');
            $Qcustomization->bindTable(':table_orders_products_customizations', TABLE_ORDERS_PRODUCTS_CUSTOMIZATIONS);
            $Qcustomization->bindInt(':orders_id', $insert_id);
            $Qcustomization->bindInt(':orders_products_id', $order_products_id);
            $Qcustomization->bindInt(':quantity', $customization['qty']);
            $Qcustomization->execute();
            
            $orders_products_customizations_id = $osC_Database->nextID();
            
            foreach ($customization['fields'] as $field) {
              $Qfield = $osC_Database->query('insert into :table_orders_products_customizations_values (orders_products_customizations_id , customization_fields_id, customization_fields_name, customization_fields_type, customization_fields_value, cache_file_name) values (:orders_products_customizations_id, :customization_fields_id, :customization_fields_name, :customization_fields_type, :customization_fields_value, :cache_file_name)');
              $Qfield->bindTable(':table_orders_products_customizations_values', TABLE_ORDERS_PRODUCTS_CUSTOMIZATIONS_VALUES);
              $Qfield->bindInt(':orders_products_customizations_id', $orders_products_customizations_id);
              $Qfield->bindInt(':customization_fields_id', $field['customization_fields_id']);
              $Qfield->bindValue(':customization_fields_name', $field['customization_fields_name']);
              $Qfield->bindInt(':customization_fields_type', $field['customization_type']);
              $Qfield->bindValue(':customization_fields_value', $field['customization_value']);
              $Qfield->bindValue(':cache_file_name', $field['cache_filename']);
              $Qfield->execute();
              
              if ($osC_Database->isError() === false) {
                @copy(DIR_FS_CACHE . 'products_customizations/' . $field['cache_filename'], DIR_FS_CACHE . 'orders_customizations/' . $field['cache_filename']);
              }
            }
          }
        }

        if ($osC_ShoppingCart->hasVariants($products['id'])) {
          foreach ($osC_ShoppingCart->getVariants($products['id']) as $variants_id => $variants) {
            $Qvariants = $osC_Database->query('select pvg.products_variants_groups_name, pvv.products_variants_values_name from :table_products_variants pv, :table_products_variants_entries pve, :table_products_variants_groups pvg, :table_products_variants_values pvv where pv.products_id = :products_id and pv.products_variants_id = pve.products_variants_id and pve.products_variants_groups_id = :groups_id and pve.products_variants_values_id = :variants_values_id and pve.products_variants_groups_id = pvg.products_variants_groups_id and pve.products_variants_values_id = pvv.products_variants_values_id and pvg.language_id = :pvg_language_id and pvv.language_id = :pvv_language_id');
            $Qvariants->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
            $Qvariants->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
            $Qvariants->bindTable(':table_products_variants_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
            $Qvariants->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
            $Qvariants->bindInt(':products_id', $products['id']);
            $Qvariants->bindInt(':groups_id', $variants['groups_id']);
            $Qvariants->bindInt(':variants_values_id', $variants['variants_values_id']);
            $Qvariants->bindInt(':pvg_language_id', $osC_Language->getID());
            $Qvariants->bindInt(':pvv_language_id', $osC_Language->getID());
            $Qvariants->execute();

            $Qopv = $osC_Database->query('insert into :table_orders_products_variants (orders_id, orders_products_id, products_variants_groups_id, products_variants_groups, products_variants_values_id, products_variants_values) values (:orders_id, :orders_products_id, :products_variants_groups_id, :products_variants_groups, :products_variants_values_id, :products_variants_values)');
            $Qopv->bindTable(':table_orders_products_variants', TABLE_ORDERS_PRODUCTS_VARIANTS);
            $Qopv->bindInt(':orders_id', $insert_id);
            $Qopv->bindInt(':orders_products_id', $order_products_id);
            $Qopv->bindInt(':products_variants_groups_id', $variants['groups_id']);
            $Qopv->bindValue(':products_variants_groups', $Qvariants->value('products_variants_groups_name'));
            $Qopv->bindInt(':products_variants_values_id', $variants['variants_values_id']);
            $Qopv->bindValue(':products_variants_values', $Qvariants->value('products_variants_values_name'));
            $Qopv->execute();
          }
        }
      
        if ($products['type'] == PRODUCT_TYPE_DOWNLOADABLE) {
          $Qdownloadable = $osC_Database->query('select * from :table_products_downloadables where products_id = :products_id');
          $Qdownloadable->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
          $Qdownloadable->bindInt(':products_id', osc_get_product_id($products['id']));
          $Qdownloadable->execute();
            
          if ($osC_ShoppingCart->hasVariants($products['id'])) {
            $variants_filename = $products['variant_filename'];
            $variants_cache_filename = $products['variant_cache_filename'];
          } else {
            $variants_filename = $Qdownloadable->value('filename');
            $variants_cache_filename = $Qdownloadable->value('cache_filename');
          }
          
          $Qopd = $osC_Database->query('insert into :table_orders_products_download (orders_id, orders_products_id, orders_products_filename, orders_products_cache_filename, download_maxdays, download_count) values (:orders_id, :orders_products_id, :orders_products_filename, :orders_products_cache_filename, :download_maxdays, :download_count)');
          $Qopd->bindTable(':table_orders_products_download', TABLE_ORDERS_PRODUCTS_DOWNLOAD);
          $Qopd->bindInt(':orders_id', $insert_id);
          $Qopd->bindInt(':orders_products_id', $order_products_id);
          $Qopd->bindValue(':orders_products_filename', $variants_filename);
          $Qopd->bindValue(':orders_products_cache_filename', $variants_cache_filename);
          $Qopd->bindValue(':download_maxdays', $Qdownloadable->valueInt('number_of_accessible_days'));
          $Qopd->bindValue(':download_count', $Qdownloadable->valueInt('number_of_downloads'));
          $Qopd->execute();
        }

        if ($products['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
          require_once('gift_certificates.php');
          
          $Qgc = $osC_Database->query('insert into :table_gift_certificates (orders_id, orders_products_id, gift_certificates_type, amount, gift_certificates_code, recipients_name, recipients_email, senders_name, senders_email, messages) values (:orders_id, :orders_products_id, :gift_certificates_type, :amount, :gift_certificates_code, :recipients_name, :recipients_email, :senders_name, :senders_email, :messages)');
          $Qgc->bindTable(':table_gift_certificates', TABLE_GIFT_CERTIFICATES);
          $Qgc->bindInt(':orders_id', $insert_id);
          $Qgc->bindInt(':gift_certificates_type', $products['gc_data']['type']);
          $Qgc->bindInt(':orders_products_id', $order_products_id);
          $Qgc->bindValue(':amount', $products['price']);
          $Qgc->bindValue(':gift_certificates_code', toC_Gift_Certificates::createGiftCertificateCode());
          $Qgc->bindValue(':recipients_name', $products['gc_data']['recipients_name']);
          $Qgc->bindValue(':recipients_email', (($products['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) ? $products['gc_data']['recipients_email'] : ''));
          $Qgc->bindValue(':senders_name', $products['gc_data']['senders_name']);
          $Qgc->bindValue(':senders_email', (($products['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) ? $products['gc_data']['senders_email'] : ''));
          $Qgc->bindValue(':messages', $products['gc_data']['message']);
          $Qgc->execute();
        }    
      }
    
      if ($osC_ShoppingCart->isUseStoreCredit()) {
        $Qhistory = $osC_Database->query('insert into :table_customers_credits_history (customers_id, action_type, date_added, amount, comments) values (:customers_id, :action_type, now(), :amount, :comments)');
        $Qhistory->bindTable(':table_customers_credits_history', TABLE_CUSTOMERS_CREDITS_HISTORY);
        $Qhistory->bindInt(':customers_id', $osC_Customer->getID());
        $Qhistory->bindInt(':action_type', STORE_CREDIT_ACTION_TYPE_ORDER_PURCHASE);
        $Qhistory->bindValue(':amount', $osC_ShoppingCart->getStoreCredit() * (-1));
        $Qhistory->bindValue(':comments', sprintf($osC_Language->get('store_credit_order_number'), $insert_id));
        $Qhistory->execute();
        
        $Qcustomer = $osC_Database->query('update :table_customers set customers_credits = (customers_credits + :customers_credits) where customers_id = :customers_id');
        $Qcustomer->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcustomer->bindRaw(':customers_credits', $osC_ShoppingCart->getStoreCredit() * (-1));
        $Qcustomer->bindInt(':customers_id', $osC_Customer->getID());
        $Qcustomer->execute();
        
        $Qcredit = $osC_Database->query('select customers_credits from :table_customers where customers_id = :customers_id');
        $Qcredit->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcredit->bindInt(':customers_id', $osC_Customer->getID());
        $Qcredit->execute();
        
        $osC_Customer->setStoreCredit($Qcredit->value('customers_credits'));
      }
    
      if($osC_ShoppingCart->hasCoupon()){
        include_once('includes/classes/coupon.php');
        $toC_Coupon = new toC_Coupon($osC_ShoppingCart->getCouponCode());

        $Qcoupon = $osC_Database->query('insert into :table_coupons_redeem_history (coupons_id, customers_id, orders_id, redeem_amount, redeem_date, redeem_ip_address) values (:coupons_id, :customers_id, :orders_id, :redeem_amount, now(), :redeem_ip_address)');
        $Qcoupon->bindTable(':table_coupons_redeem_history', TABLE_COUPONS_REDEEM_HISTORY);
        $Qcoupon->bindInt(':coupons_id', $toC_Coupon->getID());
        $Qcoupon->bindInt(':customers_id', $osC_Customer->getID());
        $Qcoupon->bindInt(':orders_id', $insert_id);
        $Qcoupon->bindValue(':redeem_amount', $osC_ShoppingCart->getCouponAmount());
        $Qcoupon->bindValue(':redeem_ip_address', osc_get_ip_address());
        $Qcoupon->execute();
      }
      
      if($osC_ShoppingCart->hasGiftCertificate()){
        $gift_certificate_codes = $osC_ShoppingCart->getGiftCertificateRedeemAmount();
        
        foreach($gift_certificate_codes as $gift_certificate_code => $amount) {
          $Qcertificate = $osC_Database->query('select gift_certificates_id from :table_gift_certificates where gift_certificates_code = :gift_certificates_code');
          $Qcertificate->bindTable(':table_gift_certificates', TABLE_GIFT_CERTIFICATES);
          $Qcertificate->bindValue(':gift_certificates_code', $gift_certificate_code);
          $Qcertificate->execute();
          
          $Qinsert = $osC_Database->query('insert into :table_gift_certificates_redeem_history (gift_certificates_id, customers_id, orders_id, redeem_date, redeem_amount, redeem_ip_address) values (:gift_certificates_id, :customers_id, :orders_id, now(), :redeem_amount, :redeem_ip_address)');
          $Qinsert->bindTable(':table_gift_certificates_redeem_history', TABLE_GIFT_CERTIFICATES_REDEEM_HISTORY);
          $Qinsert->bindInt(':gift_certificates_id', $Qcertificate->valueInt(gift_certificates_id));
          $Qinsert->bindInt(':customers_id', $osC_Customer->getID());
          $Qinsert->bindInt(':orders_id', $insert_id);
          $Qinsert->bindValue(':redeem_amount', $amount);
          $Qinsert->bindValue(':redeem_ip_address', osc_get_ip_address());
          $Qinsert->execute();
        }
      }

      $_SESSION['prepOrderID'] = $osC_ShoppingCart->getCartID() . '-' . $insert_id;

      return $insert_id;
    }

    function activeDownloadables($orders_id) {
      global $osC_Database;
      
      //create email template object
      require_once('includes/classes/email_template.php');
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
          $Qupdate->execute();      
            
          //send notification email
          $email->setData($customers_name, $customers_email_address, $Qproducts->value('products_name'));
          $email->buildMessage();
          $email->sendEmail();
        }
      }
    }
    
    function activeGiftCertificates($orders_id) {
      global $osC_Database, $osC_Currencies;
      
      //create email template object
      require_once('includes/classes/email_template.php');
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
          $Qupdate->execute();  
          
          //send notification email
          if ($Qcertificates->valueInt('type') == GIFT_CERTIFICATE_TYPE_EMAIL) {
            $email->setData($Qcertificates->value('senders_name'), $Qcertificates->value('senders_email'), $Qcertificates->value('recipients_name'), $Qcertificates->value('recipients_email'), $osC_Currencies->format($Qcertificates->value('amount')), $Qcertificates->value('gift_certificates_code'), $Qcertificates->value('messages'));
            $email->buildMessage();
            $email->sendEmail();
          }
        }
      }
    }

    function getOrderStatusData($id) {
      global $osC_Database, $osC_Language;

      $Qstatus = $osC_Database->query('select * from :table_orders_status where orders_status_id = :orders_status_id and language_id = :language_id');
      $Qstatus->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
      $Qstatus->bindInt(':orders_status_id', $id);
      $Qstatus->bindInt(':language_id', $osC_Language->getID());
      $Qstatus->execute();

      $data = $Qstatus->toArray();

      $Qstatus->freeResult();

      return $data;
    }
    
    function process($order_id, $status_id = '', $comments = '') {
      global $osC_Database;

      if (empty($status_id) || (is_numeric($status_id) === false)) {
        $status_id = DEFAULT_ORDERS_STATUS_ID;
      }

      $Qstatus = $osC_Database->query('insert into :table_orders_status_history (orders_id, orders_status_id, date_added, customer_notified, comments) values (:orders_id, :orders_status_id, now(), :customer_notified, :comments)');
      $Qstatus->bindTable(':table_orders_status_history', TABLE_ORDERS_STATUS_HISTORY);
      $Qstatus->bindInt(':orders_id', $order_id);
      $Qstatus->bindInt(':orders_status_id', $status_id);
      $Qstatus->bindInt(':customer_notified', (SEND_EMAILS == '1') ? '1' : '0');
      $Qstatus->bindValue(':comments', $comments);
      $Qstatus->execute();

      $Qupdate = $osC_Database->query('update :table_orders set orders_status = :orders_status where orders_id = :orders_id');
      $Qupdate->bindTable(':table_orders', TABLE_ORDERS);
      $Qupdate->bindInt(':orders_status', $status_id);
      $Qupdate->bindInt(':orders_id', $order_id);
      $Qupdate->execute();

      $Qproducts = $osC_Database->query('select orders_products_id, products_id, products_quantity from :table_orders_products where orders_id = :orders_id');
      $Qproducts->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
      $Qproducts->bindInt(':orders_id', $order_id);
      $Qproducts->execute();

      while ($Qproducts->next()) {
        osC_Product::updateStock($order_id, $Qproducts->valueInt('orders_products_id'), $Qproducts->valueInt('products_id'), $Qproducts->valueInt('products_quantity'));
      }

      $order_status = self::getOrderStatusData($status_id);
      
      if ($order_status['downloads_flag'] == 1) {
        self::activeDownloadables($order_id);
      }
      
      if ($order_status['gift_certificates_flag'] == 1) {
        self::activeGiftCertificates($order_id);
      }
      
      osC_Order::sendEmail($order_id);

      unset($_SESSION['prepOrderID']);
    }

    function sendEmail($id) {
      require_once('email_template.php');
      $email_template = toC_Email_Template::getEmailTemplate('new_order_created');
      $email_template->setData($id);
      if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
        $extra_emials = explode(',', SEND_EXTRA_ORDER_EMAILS_TO);
        
        if (is_array($extra_emials) && !osc_empty($extra_emials)) {
          foreach($extra_emials as $email) {
            $email_template->addRecipient('', trim($email));
          }
        }
      }
      $email_template->buildMessage();
      $email_template->sendEmail();
    }

    function &getListing($limit = null, $page_keyword = 'page') {
      global $osC_Database, $osC_Customer, $osC_Language;

      $Qorders = $osC_Database->query('select o.orders_id, o.date_purchased, o.delivery_name, o.delivery_country, o.billing_name, o.billing_country, ot.text as order_total, o.orders_status, s.orders_status_name, s.returns_flag from :table_orders o, :table_orders_total ot, :table_orders_status s where o.customers_id = :customers_id and o.orders_id = ot.orders_id and ot.class = "total" and o.orders_status = s.orders_status_id and s.language_id = :language_id order by orders_id desc');
      $Qorders->bindTable(':table_orders', TABLE_ORDERS);
      $Qorders->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
      $Qorders->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
      $Qorders->bindInt(':customers_id', $osC_Customer->getID());
      $Qorders->bindInt(':language_id', $osC_Language->getID());

      if (is_numeric($limit)) {
        $Qorders->setBatchLimit(isset($_GET[$page_keyword]) && is_numeric($_GET[$page_keyword]) ? $_GET[$page_keyword] : 1, $limit);
      }

      $Qorders->execute();

      return $Qorders;
    }
    
    function getLastPublicStatus($orders_id) {
      global $osC_Database, $osC_Language;

      if ( ($id === null) && isset($this) ) {
        $orders_id = $this->_id;
      }

      $Qstatus = $osC_Database->query('select os.orders_status_name from :table_orders_status os, :table_orders_status_history osh where osh.orders_id = :orders_id and osh.orders_status_id = os.orders_status_id and os.language_id = :language_id and os.public_flag = 1 order by osh.orders_status_history_id desc limit 1');
      $Qstatus->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
      $Qstatus->bindTable(':table_orders_status_history', TABLE_ORDERS_STATUS_HISTORY);
      $Qstatus->bindInt(':orders_id', $orders_id);
      $Qstatus->bindInt(':language_id', $osC_Language->getID());

      return $Qstatus->value('orders_status_name');
    }

    function &getStatusListing($id = null) {
      global $osC_Database, $osC_Language;

      if ( ($id === null) && isset($this) ) {
        $id = $this->_id;
      }

      $Qstatus = $osC_Database->query('select os.orders_status_name, osh.date_added, osh.comments from :table_orders_status os, :table_orders_status_history osh where osh.orders_id = :orders_id and osh.orders_status_id = os.orders_status_id and os.language_id = :language_id and os.public_flag = 1 order by osh.date_added');
      $Qstatus->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
      $Qstatus->bindTable(':table_orders_status_history', TABLE_ORDERS_STATUS_HISTORY);
      $Qstatus->bindInt(':orders_id', $id);
      $Qstatus->bindInt(':language_id', $osC_Language->getID());

      return $Qstatus;
    }

    function getCustomerID($id = null) {
      global $osC_Database;

      if ( ($id === null) && isset($this) ) {
        $id = $this->_id;
      }

      $Qcustomer = $osC_Database->query('select customers_id from :table_orders where orders_id = :orders_id');
      $Qcustomer->bindTable(':table_orders', TABLE_ORDERS);
      $Qcustomer->bindInt(':orders_id', $id);
      $Qcustomer->execute();

      return $Qcustomer->valueInt('customers_id');
    }

    function numberOfEntries() {
      global $osC_Database, $osC_Customer;
      static $total_entries;

      if (is_numeric($total_entries) === false) {
        if ($osC_Customer->isLoggedOn()) {
          $Qorders = $osC_Database->query('select count(*) as total from :table_orders where customers_id = :customers_id');
          $Qorders->bindTable(':table_orders', TABLE_ORDERS);
          $Qorders->bindInt(':customers_id', $osC_Customer->getID());
          $Qorders->execute();

          $total_entries = $Qorders->valueInt('total');
        } else {
          $total_entries = 0;
        }
      }

      return $total_entries;
    }

    function numberOfProducts($id = null) {
      global $osC_Database;

      if ( ($id === null) && isset($this) ) {
        $id = $this->_id;
      }

      $Qproducts = $osC_Database->query('select count(*) as total from :table_orders_products where orders_id = :orders_id');
      $Qproducts->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
      $Qproducts->bindInt(':orders_id', $id);
      $Qproducts->execute();

      return $Qproducts->valueInt('total');
    }

    function exists($id, $customer_id = null) {
      global $osC_Database;

      $Qorder = $osC_Database->query('select orders_id from :table_orders where orders_id = :orders_id');

      if (isset($customer_id) && is_numeric($customer_id)) {
        $Qorder->appendQuery('and customers_id = :customers_id');
        $Qorder->bindInt(':customers_id', $customer_id);
      }

      $Qorder->appendQuery('limit 1');
      $Qorder->bindTable(':table_orders', TABLE_ORDERS);
      $Qorder->bindInt(':orders_id', $id);
      $Qorder->execute();

      return ($Qorder->numberOfRows() === 1);
    }
    
    function hasNotReturnedProduct() {
      $quantity = 0;
      
      foreach ($this->products as $product) {
        $quantity += $product['qty'] - $this->getProductReturnedQuantity($product['orders_products_id']);
      }
      
      if ($quantity > 0) {
        return true;
      }
      
      return false;
    }
    
    function getProductReturnedQuantity($orders_products_id) {
      global $osC_Database;
      
      $Qreturn = $osC_Database->query('select sum(orp.products_quantity) as quantity from :table_orders_returns r, :table_orders_returns_products orp where r.orders_id = :orders_id and r.orders_returns_id = orp.orders_returns_id and orp.orders_products_id = :orders_products_id');
      $Qreturn->bindTable(':table_orders_returns', TABLE_ORDERS_RETURNS);
      $Qreturn->bindTable(':table_orders_returns_products', TABLE_ORDERS_RETURNS_PRODUCTS);
      $Qreturn->bindInt(':orders_id', $this->_id);
      $Qreturn->bindInt(':orders_products_id', $orders_products_id);
      $Qreturn->execute();
      
      if ($Qreturn->numberOfRows() > 0) {
        return $Qreturn->valueInt('quantity');
      }
      
      return 0;
    }

    function query($order_id) {
      global $osC_Database, $osC_Language;

      $Qorder = $osC_Database->query('select * from :table_orders where orders_id = :orders_id');
      $Qorder->bindTable(':table_orders', TABLE_ORDERS);
      $Qorder->bindInt(':orders_id', $order_id);
      $Qorder->execute();
      
      $Qtotals = $osC_Database->query('select title, text, class from :table_orders_total where orders_id = :orders_id order by sort_order');
      $Qtotals->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
      $Qtotals->bindInt(':orders_id', $order_id);
      $Qtotals->execute();

      $shipping_method_string = '';
      $order_total_string = '';

      while ($Qtotals->next()) {
        $this->totals[] = array('title' => $Qtotals->value('title'),
                                'text' => $Qtotals->value('text'));

        if ( strpos($Qtotals->value('class'), 'shipping') !== false ) {
          $shipping_method_string = strip_tags($Qtotals->value('title'));

          if (substr($shipping_method_string, -1) == ':') {
            $shipping_method_string = substr($Qtotals->value('title'), 0, -1);
          }
        }

        if ($Qtotals->value('class') == 'total') {
          $order_total_string = strip_tags($Qtotals->value('text'));
        }
      }

      $Qstatus = $osC_Database->query('select * from :table_orders_status where orders_status_id = :orders_status_id and language_id = :language_id');
      $Qstatus->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
      $Qstatus->bindInt(':orders_status_id', $Qorder->valueInt('orders_status'));
      $Qstatus->bindInt(':language_id', $osC_Language->getID());
      $Qstatus->execute();
      
      $this->_order_id = $Qorder->valueInt('orders_id');
      $this->_invoice_number = $Qorder->value('invoice_number');
      $this->_invoice_date = $Qorder->value('invoice_date');
      $this->_date_purchased = $Qorder->value('date_purchased');

      $this->info = array('currency' => $Qorder->value('currency'),
                          'currency_value' => $Qorder->value('currency_value'),
                          'payment_method' => $Qorder->value('payment_method'),
                          'date_purchased' => $Qorder->value('date_purchased'),
                          'orders_status_id' => $Qorder->valueInt('orders_status'),
                          'orders_status' => $Qstatus->value('orders_status_name'),
                          'downloads_flag' => $Qstatus->valueInt('downloads_flag'),
                          'returns_flag' => $Qstatus->valueInt('returns_flag'),
                          'gift_certificates_flag' => $Qstatus->valueInt('gift_certificates_flag'),
                          'last_modified' => $Qorder->value('last_modified'),
                          'total' => $order_total_string,
                          'shipping_method' => $shipping_method_string,
                          'tracking_no' => $Qorder->value('tracking_no'),
                          'wrapping_message' => $Qorder->value('wrapping_message'));

      $this->customer = array('id' => $Qorder->valueInt('customers_id'),
                              'name' => $Qorder->valueProtected('customers_name'),
                              'company' => $Qorder->valueProtected('customers_company'),
                              'street_address' => $Qorder->valueProtected('customers_street_address'),
                              'suburb' => $Qorder->valueProtected('customers_suburb'),
                              'city' => $Qorder->valueProtected('customers_city'),
                              'postcode' => $Qorder->valueProtected('customers_postcode'),
                              'state' => $Qorder->valueProtected('customers_state'),
                              'zone_code' => $Qorder->value('customers_state_code'),
                              'country_title' => $Qorder->valueProtected('customers_country'),
                              'country_iso2' => $Qorder->value('customers_country_iso2'),
                              'country_iso3' => $Qorder->value('customers_country_iso3'),
                              'format' => $Qorder->value('customers_address_format'),
                              'telephone' => $Qorder->valueProtected('customers_telephone'),
                              'email_address' => $Qorder->valueProtected('customers_email_address'));

      $this->delivery = array('name' => $Qorder->valueProtected('delivery_name'),
                              'company' => $Qorder->valueProtected('delivery_company'),
                              'street_address' => $Qorder->valueProtected('delivery_street_address'),
                              'suburb' => $Qorder->valueProtected('delivery_suburb'),
                              'city' => $Qorder->valueProtected('delivery_city'),
                              'postcode' => $Qorder->valueProtected('delivery_postcode'),
                              'state' => $Qorder->valueProtected('delivery_state'),
                              'zone_code' => $Qorder->value('delivery_state_code'),
                              'country_title' => $Qorder->valueProtected('delivery_country'),
                              'country_iso2' => $Qorder->value('delivery_country_iso2'),
                              'country_iso3' => $Qorder->value('delivery_country_iso3'),
                              'format' => $Qorder->value('delivery_address_format'));

      if (empty($this->delivery['name']) && empty($this->delivery['street_address'])) {
        $this->delivery = false;
      }

      $this->billing = array('name' => $Qorder->valueProtected('billing_name'),
                             'company' => $Qorder->valueProtected('billing_company'),
                             'street_address' => $Qorder->valueProtected('billing_street_address'),
                             'suburb' => $Qorder->valueProtected('billing_suburb'),
                             'city' => $Qorder->valueProtected('billing_city'),
                             'postcode' => $Qorder->valueProtected('billing_postcode'),
                             'state' => $Qorder->valueProtected('billing_state'),
                             'zone_code' => $Qorder->value('billing_state_code'),
                             'country_title' => $Qorder->valueProtected('billing_country'),
                             'country_iso2' => $Qorder->value('billing_country_iso2'),
                             'country_iso3' => $Qorder->value('billing_country_iso3'),
                             'format' => $Qorder->value('billing_address_format'));

      $Qproducts = $osC_Database->query('select orders_products_id, products_id, products_type, products_name, products_sku, products_price, products_tax, products_quantity, final_price from :table_orders_products where orders_id = :orders_id');
      $Qproducts->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
      $Qproducts->bindInt(':orders_id', $order_id);
      $Qproducts->execute();

      $index = 0;

      while ($Qproducts->next()) {
        $subindex = 0;

        $this->products[$index] = array('id' => $Qproducts->valueInt('products_id'),
                                        'orders_products_id' => $Qproducts->valueInt('orders_products_id'),
                                        'type' => $Qproducts->valueInt('products_type'),
                                        'qty' => $Qproducts->valueInt('products_quantity'),
                                        'name' => $Qproducts->value('products_name'),
                                        'sku' => $Qproducts->value('products_sku'),
                                        'tax' => $Qproducts->value('products_tax'),
                                        'price' => $Qproducts->value('products_price'),
                                        'final_price' => $Qproducts->value('final_price'));
        
        if ($Qproducts->valueInt('products_type') == PRODUCT_TYPE_DOWNLOADABLE) {
          $Qdownloadable = $osC_Database->query('select orders_products_download_id, orders_products_filename, orders_products_cache_filename, status from :table_orders_products_download where orders_id = :orders_id and orders_products_id = :orders_products_id');
          $Qdownloadable->bindTable(':table_orders_products_download', TABLE_ORDERS_PRODUCTS_DOWNLOAD);
          $Qdownloadable->bindInt(':orders_id', $order_id);
          $Qdownloadable->bindInt(':orders_products_id', $Qproducts->valueInt('orders_products_id'));
          $Qdownloadable->execute();
          
          if ($Qdownloadable->numberOfRows() > 0) {
            $this->products[$index]['downloads_status'] = $Qdownloadable->valueInt('status');
            if ($Qdownloadable->valueInt('status') == 1) {
              $this->products[$index]['orders_products_download_id'] = $Qdownloadable->valueInt('orders_products_download_id');
              $this->products[$index]['products_filename'] = $Qdownloadable->value('orders_products_filename');
              $this->products[$index]['products_cache_filename'] = $Qdownloadable->value('orders_products_cache_filename');
            }
          }
        }

        if ($Qproducts->valueInt('products_type') == PRODUCT_TYPE_GIFT_CERTIFICATE) {
          $Qcertificate = $osC_Database->query('select gift_certificates_type, senders_name, senders_email, recipients_name, recipients_email, messages from :table_gift_certificates where orders_id = :orders_id and orders_products_id = :orders_products_id');
          $Qcertificate->bindTable(':table_gift_certificates', TABLE_GIFT_CERTIFICATES);
          $Qcertificate->bindInt(':orders_id', $order_id);
          $Qcertificate->bindInt(':orders_products_id', $Qproducts->valueInt('orders_products_id'));
          $Qcertificate->execute();
  
          if ($Qcertificate->numberOfRows() > 0) {
            $this->products[$index]['gift_certificates_type'] = $Qcertificate->valueInt('gift_certificates_type');
            $this->products[$index]['senders_name'] = $Qcertificate->value('senders_name');
            $this->products[$index]['senders_email'] = $Qcertificate->value('senders_email');
            $this->products[$index]['recipients_name'] = $Qcertificate->value('recipients_name');
            $this->products[$index]['recipients_email'] = $Qcertificate->value('recipients_email');
            $this->products[$index]['messages'] = $Qcertificate->value('messages');
          }
        }

        $Qvariants = $osC_Database->query('select products_variants_groups_id as groups_id, products_variants_groups as groups_name, products_variants_values_id as values_id, products_variants_values as values_name from :table_orders_products_variants where orders_id = :orders_id and orders_products_id = :orders_products_id');
        $Qvariants->bindTable(':table_orders_products_variants', TABLE_ORDERS_PRODUCTS_VARIANTS);
        $Qvariants->bindInt(':orders_id', $order_id);
        $Qvariants->bindInt(':orders_products_id', $Qproducts->valueInt('orders_products_id'));
        $Qvariants->execute();

        if ($Qvariants->numberOfRows()) {
          while ($Qvariants->next()) {
            $this->products[$index]['variants'][$subindex] = array('groups_id' => $Qvariants->valueInt('groups_id'),
                                                                   'values_id' => $Qvariants->valueInt('values_id'),
                                                                   'groups_name' => $Qvariants->value('groups_name'),
                                                                   'values_name' => $Qvariants->value('values_name'));

            $subindex++;
          }
        }
        
        $Qcustomizations = $osC_Database->query('select orders_products_customizations_id, quantity from :table_orders_products_customizations where orders_id = :orders_id and orders_products_id = :orders_products_id');
        $Qcustomizations->bindTable(':table_orders_products_customizations', TABLE_ORDERS_PRODUCTS_CUSTOMIZATIONS);
        $Qcustomizations->bindInt(':orders_id', $order_id);
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
          $this->products[$index]['customizations'] = $customizations;
        }
        
        $this->info['tax_groups']["{$this->products[$index]['tax']}"] = '1';

        $index++;
      }
    }
    
    function insertOrderStatusHistory($orders_id, $orders_status, $comments) {
      global $osC_Database;
      
      $Qinsert = $osC_Database->query('insert into :table_orders_status_history (orders_id, orders_status_id, date_added, customer_notified, comments) values (:orders_id, :orders_status_id, now(), :customer_notified, :comments)');
      $Qinsert->bindTable(':table_orders_status_history', TABLE_ORDERS_STATUS_HISTORY);
      $Qinsert->bindInt(':orders_id', $orders_id);
      $Qinsert->bindInt(':orders_status_id', $orders_status);
      $Qinsert->bindInt(':customer_notified', (SEND_EMAILS == '1') ? '1' : '0');
      $Qinsert->bindValue(':comments', $comments);
      $Qinsert->execute();
      
      if (!$osC_Database->isError()) {
        return true;
      }
      
      return false;
    }
    

    
    function getBilling() {
      return $this->billing;    
    }
    
    function getCustomer($id = '') {
      if (empty($id)) {
        return $this->customer;
      } elseif (isset($this->customer[$id])) {
        return $this->customer[$id];
      }

      return false;
    }
    
    function getOrderID() {
      return $this->_order_id;
    }
    
    function getCurrency() {
      return $this->info['currency'];
    }

    function getCurrencyValue() {
      return $this->info['currency_value'];
    }
    
    function getInvoiceNumber() {
      return $this->_invoice_number;
    }
    
    function getInvoiceDate() {
      return $this->_invoice_date;
    }
    
    function getProducts () {
      return $this->products;
    }
    
    function getTotals() {
      return $this->totals;
    }
    
    function getDateCreated() {
      return $this->_date_purchased;
    }
  }
?>