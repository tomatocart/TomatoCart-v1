<?php
/*$Id: order_return.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  class toC_Order_Return {
  
    var $_orders_returns_id, 
        $_orders_id, 
        $_return_comments, 
        $_return_date, 
        $_sub_total, 
        $_shipping, 
        $_handling, 
        $_total, 
        $_credit_slip_date_added,
        $_returns_products = array(),
        $_customer_info = array();

    function toC_Order_Return($orders_returns_id) {
      global $osC_Database, $osC_Language;
      
      $Qreturn = $osC_Database->query('select orders_id, orders_returns_status_id, customers_comments, date_added from :table_orders_returns where orders_returns_id=:orders_returns_id');
      $Qreturn->bindTable(':table_orders_returns', TABLE_ORDERS_RETURNS);
      $Qreturn->bindInt(':orders_returns_id', $orders_returns_id);
      $Qreturn->execute();
      
      $this->_orders_returns_id = $orders_returns_id;
      $this->_orders_id = $Qreturn->value('orders_id');
      $this->_return_comments = $Qreturn->value('customers_comments');
      $this->_return_date = $Qreturn->value('date_added');
      $this->_orders_returns_status_id = $Qreturn->valueInt('orders_returns_status_id');

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

      $Qproducts = $osC_Database->query('select op.orders_products_id, op.products_id, op.products_type, op.products_name, op.products_sku, op.products_price, op.final_price, op.products_tax, r.orders_returns_status_id, orp.products_quantity from :table_orders_returns r, :table_orders_returns_products orp, :table_orders_products op where r.orders_returns_id = orp.orders_returns_id and r.orders_id = op.orders_id and orp.orders_products_id = op.orders_products_id and orp.orders_returns_id = :orders_returns_id');
      $Qproducts->bindTable(':table_orders_returns', TABLE_ORDERS_RETURNS);
      $Qproducts->bindTable(':table_orders_returns_products', TABLE_ORDERS_RETURNS_PRODUCTS);
      $Qproducts->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
      $Qproducts->bindInt(':orders_returns_id', $orders_returns_id);
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
        
    function getOrdersId() {
      if (is_numeric($this->_orders_id)) {
        return $this->_orders_id;
      }
      
      return false;
    }
    
    function getOrdersReturnsId() {
      if (is_numeric($this->_orders_returns_id)) {
        return $this->_orders_returns_id;
      }
      
      return false;
    }
    
    function getReturnDate() {
      if (!empty($this->_return_date)) {
        return $this->_return_date;
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
    
    function getCustomerInfo() {
      if (!empty($this->_customer_info)) {
        return $this->_customer_info;
      }
      
      return false;
    }
    
    function getComment() {
      if (!empty($this->_return_comments)) {
        return $this->_return_comments;
      }
      
      return false;
    }
    
    function saveReturnRequest($orders_id, $products, $comments) {
      global $osC_Database;
      
      $error = false;
      $osC_Database->startTransaction();
      
      $Qnew = $osC_Database->query('insert into :table_orders_returns (orders_id, orders_returns_status_id, customers_comments, date_added) values (:orders_id, 1, :customers_comments, now())');
      $Qnew->bindTable(':table_orders_returns', TABLE_ORDERS_RETURNS);
      $Qnew->bindInt(':orders_id', $orders_id);
      $Qnew->bindValue(':customers_comments', $comments);
      $Qnew->execute();
      
      if ($osC_Database->isError()) {
        $error = true;
      } else {
        $orders_returns_id = $osC_Database->nextID();
        
        foreach($products as $orders_products_id => $quantity) {
          $Qproduct = $osC_Database->query('insert into :table_orders_returns_products (orders_returns_id, orders_products_id, products_quantity) values (:orders_returns_id, :orders_products_id, :products_quantity)');
          $Qproduct->bindTable(':table_orders_returns_products', TABLE_ORDERS_RETURNS_PRODUCTS);
          $Qproduct->bindInt(':orders_returns_id', $orders_returns_id);
          $Qproduct->bindInt(':orders_products_id', $orders_products_id);
          $Qproduct->bindInt(':products_quantity', $quantity);
          $Qproduct->execute();    
          
          if ($osC_Database->isError()) {
            $error = true;
            break;
          }
        }
      }
      
      if ($error === false) {
        $osC_Database->commitTransaction();
        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }
  }
?>