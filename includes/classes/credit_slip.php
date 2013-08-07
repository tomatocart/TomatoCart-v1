<?php
/*$Id: credit_slip.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  class toC_Credit_Slip {
  
    var $_orders_refunds_id, 
        $_credit_slips_id,
        $_orders_id, 
        $_date_added, 
        $_sub_total, 
        $_shipping, 
        $_handling, 
        $_total,
        $_comments,
        $_returns_products = array(),
        $_customer_info = array();
    
    function toC_Credit_Slip($credit_slips_id) {
      global $osC_Database, $osC_Language;
      
      $Qslip = $osC_Database->query('select * from :table_orders_refunds where credit_slips_id = :credit_slips_id');
      $Qslip->bindTable(':table_orders_refunds', TABLE_ORDERS_REFUNDS);
      $Qslip->bindInt(':credit_slips_id', $credit_slips_id);
      $Qslip->execute();
      
      $this->_orders_refunds_id = $Qslip->valueInt('orders_refunds_id');
      $this->_credit_slips_id = $credit_slips_id;
      $this->_orders_id = $Qslip->valueInt('orders_id');
      $this->_date_added = $Qslip->value('date_added');
      $this->_comments = $Qslip->value('comments');
      $this->_sub_total = $Qslip->value('sub_total');
      $this->_shipping = $Qslip->value('shipping');
      $this->_handling = $Qslip->value('handling');
      $this->_total = $Qslip->value('refund_total');

      $Qorder = $osC_Database->query('select * from :table_orders where orders_id = :orders_id');
      $Qorder->bindTable(':table_orders', TABLE_ORDERS);
      $Qorder->bindInt(':orders_id', $this->_orders_id);
      $Qorder->execute();

      $this->_date_purchased = $Qorder->value('date_purchased');

      $this->_customer_info = array('id' => $Qorder->valueInt('customers_id'),
                             'name' => $Qorder->valueProtected('billing_name'),
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
                             'format' => $Qorder->value('billing_address_format'),
                             'email_address' => $Qorder->valueProtected('customers_email_address'));

      $this->_currency = array('code' => $Qorder->value('currency'),
                               'value' => $Qorder->value('currency_value'));
      
      $Qproducts = $osC_Database->query('select op.orders_products_id, op.products_id, op.products_type, op.products_name, op.products_sku, op.products_price, op.final_price, op.products_tax, orp.products_quantity from :table_orders_refunds_products orp, :table_orders_products op where op.orders_id = :orders_id and orp.orders_products_id = op.orders_products_id and orp.orders_refunds_id = :orders_refunds_id');
      $Qproducts->bindTable(':table_orders_refunds_products', TABLE_ORDERS_REFUNDS_PRODUCTS);
      $Qproducts->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
      $Qproducts->bindInt(':orders_id', $this->_orders_id);
      $Qproducts->bindInt(':orders_refunds_id', $this->_orders_refunds_id);
      $Qproducts->bindInt(':language_id', $osC_Language->getID());
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
          $Qdownloadable->bindInt(':orders_id', $this->_orders_id);
          $Qdownloadable->bindInt(':orders_products_id', $Qproducts->valueInt('orders_products_id'));
          $Qdownloadable->execute();
          
          if ($Qdownloadable->numberOfRows() > 0) {
            $this->products[$index]['orders_products_download_id'] = $Qdownloadable->valueInt('orders_products_download_id');
            $this->products[$index]['products_filename'] = $Qdownloadable->value('orders_products_filename');
            $this->products[$index]['products_cache_filename'] = $Qdownloadable->value('orders_products_cache_filename');
          }
        }

        if ($Qproducts->valueInt('products_type') == PRODUCT_TYPE_GIFT_CERTIFICATE) {
          $Qcertificate = $osC_Database->query('select gift_certificates_type, senders_name, senders_email, recipients_name, recipients_email, messages from :table_gift_certificates where orders_id = :orders_id and orders_products_id = :orders_products_id');
          $Qcertificate->bindTable(':table_gift_certificates', TABLE_GIFT_CERTIFICATES);
          $Qcertificate->bindInt(':orders_id', $this->_orders_id);
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
        $Qvariants->bindInt(':orders_id', $this->_orders_id);
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

        $index++;
      }
    }    
      
    function getCreditSlipId() {
      if (is_numeric($this->_credit_slips_id)) {
        return $this->_credit_slips_id;
      }
      
      return false;
    }
    
    function getDateAdded() {
      if (!empty($this->_date_added)) {
        return $this->_date_added;
      }
      
      return false;
    }
            
    function getOrdersId() {
      if (is_numeric($this->_orders_id)) {
        return $this->_orders_id;
      }
      
      return false;
    }
    
    function getDatePurchased() {
      if (!empty($this->_date_purchased)) {
        return $this->_date_purchased;
      }
      
      return false;
    }
    
    function getProducts() {
      if (!empty($this->products)) {
        return $this->products;
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
    
    function getCustomerInfo() {
      if (!empty($this->_customer_info)) {
        return $this->_customer_info;
      }
      
      return false;
    }
    
    function getSubTotal() {
      return $this->_sub_total;
    }
    
    function getShippingFee() {
      return $this->_shipping;
    }
    
    function getHandlingFee() {
      return $this->_handling;
    }
    
    function getTotal() {
      return $this->_total;
    }
    
    function getComment() {
      if (!empty($this->_comments)) {
        return $this->_comments;
      }
      
      return false;
    }
  }
?>