<?php
/*
  $Id: orders_returns.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require_once('../includes/classes/product.php');

  class toC_OrdersReturns_Admin {
    
    function createCreditSlip($data) {
      global $osC_Database;

      $error = false;
      
      $osC_Database->startTransaction();
      
//credit slip id
      $Qslip = $osC_Database->query('select max(credit_slips_id) as credit_slips_id from :table_orders_refunds');
      $Qslip->bindTable(':table_orders_refunds', TABLE_ORDERS_REFUNDS);
      $Qslip->execute();
      
      $credit_slips_id = $Qslip->valueInt('credit_slips_id') + 1;

//order refund
      $Qinsert = $osC_Database->query('insert into :table_orders_refunds (orders_refunds_type, orders_id, credit_slips_id, sub_total, shipping, handling, refund_total, comments, date_added) values (:orders_refunds_type, :orders_id, :credit_slips_id, :sub_total, :shipping, :handling, :refund_total, :comments, now())');
      $Qinsert->bindTable(':table_orders_refunds', TABLE_ORDERS_REFUNDS);
      $Qinsert->bindInt(':orders_refunds_type', ORDERS_RETURNS_TYPE_CREDIT_SLIP);
      $Qinsert->bindInt(':orders_id', $data['orders_id']);
      $Qinsert->bindInt(':credit_slips_id', $credit_slips_id);
      $Qinsert->bindValue(':sub_total', $data['sub_total']);
      $Qinsert->bindValue(':shipping', $data['shipping_fee']);
      $Qinsert->bindValue(':handling', $data['handling']);
      $Qinsert->bindValue(':refund_total', $data['sub_total'] + $data['shipping_fee'] + $data['handling']);
      $Qinsert->bindValue(':comments', $data['comments']);
      $Qinsert->setLogging($_SESSION['module'], null);
      $Qinsert->execute(); 
      
      if ($osC_Database->isError()) {
        $error = true;
      } else {
        $orders_refunds_id = $osC_Database->nextID();
  
//orders refunds products
        $return_products = explode(';', $data['return_quantity']);
        foreach ($return_products as $product) {
          list($orders_products_id, $quantity) = explode(':', $product);
          
          $Qproduct = $osC_Database->query('insert into :table_orders_refunds_products (orders_refunds_id, orders_products_id, products_quantity) values (:orders_refunds_id, :orders_products_id, :products_quantity)');
          $Qproduct->bindTable(':table_orders_refunds_products', TABLE_ORDERS_REFUNDS_PRODUCTS);
          $Qproduct->bindInt(':orders_refunds_id', $orders_refunds_id);
          $Qproduct->bindInt(':orders_products_id', $orders_products_id);
          $Qproduct->bindInt(':products_quantity', $quantity);
          $Qproduct->setLogging($_SESSION['module'], $orders_refunds_id);
          $Qproduct->execute(); 
          
          if ( $osC_Database->isError() ) {
            $error = true;
            break;
          } 
          
          if ($error === false) {
            $Qupdate = $osC_Database->query('update :table_orders_products set products_return_quantity = products_return_quantity + :products_return_quantity where orders_products_id = :orders_products_id');
            $Qupdate->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
            $Qupdate->bindInt(':products_return_quantity', $quantity);
            $Qupdate->bindInt(':orders_products_id', $orders_products_id);
            $Qupdate->setLogging($_SESSION['module'], $orders_refunds_id);
            $Qupdate->execute();

            if ( $osC_Database->isError() ) {
              $error = true;
              break;
            }
          }
          
          if ( ($error === false) && ($data['restock_quantity'] === true) ) {
            $Qcheck = $osC_Database->query('select products_id from :table_orders_products where orders_products_id = :orders_products_id and orders_id = :orders_id');
            $Qcheck->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
            $Qcheck->bindInt(':orders_products_id', $orders_products_id);
            $Qcheck->bindInt(':orders_id', $data['orders_id']);
            $Qcheck->setLogging($_SESSION['module'], $orders_refunds_id);
            $Qcheck->execute();

            $products_id = $Qcheck->valueInt('products_id'); 
          
            if (!osC_Product::restock( $data['orders_id'], $orders_products_id, $products_id, $quantity )) {
              $error = true;
              break;
            }
          }
        }
      }
      
      if ($error === false) {
        $Qreturn = $osC_Database->query('update :table_orders_returns set orders_returns_status_id = :orders_returns_status_id, admin_comments = :admin_comments, date_updated = now() where orders_returns_id = :id');
        $Qreturn->bindTable(':table_orders_returns', TABLE_ORDERS_RETURNS);
        $Qreturn->bindInt(':orders_returns_status_id', ORDERS_RETURNS_STATUS_REFUNDED_CREDIT_MEMO);
        $Qreturn->bindValue(':admin_comments', $data['comment']);
        $Qreturn->bindInt(':id', $data['orders_returns_id']);
        $Qreturn->setLogging($_SESSION['module'], $data['orders_returns_id']);
        $Qreturn->execute();
        
        if ($osC_Database->isError()) {
          $error = true;
        }
      }
      
      if ($error === false) {
        $osC_Database->commitTransaction();
        
        $osC_Order = new osC_Order($data['orders_id']);

        $return_products_ids = array();
        $return_products_qty = array();
        $return_products = explode(';', $data['return_quantity']);
        foreach ($return_products as $product) {
          list($orders_products_id, $quantity) = explode(':', $product);
          
          $return_products_ids[] = $orders_products_id;
          $return_products_qty[$orders_products_id] = $quantity;
        }

        $products = array();
        foreach ($osC_Order->getProducts() as $product) {
          if (in_array($product['orders_products_id'], $return_products_ids)) {
            $product_info = $return_products_qty[$product['orders_products_id']] . '&nbsp;x&nbsp;' . $product['name'];
            
            if ( $product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE ) {
              $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $osC_Language->get('senders_name') . ': ' . $product['senders_name'] . '</i></nobr>';
              
              if ($product['gift_certificates_type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
                $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $osC_Language->get('senders_email') . ': ' . $product['senders_email'] . '</i></nobr>';
              }
              
              $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $osC_Language->get('recipients_name') . ': ' . $product['recipients_name'] . '</i></nobr>';
              
              if ($product['gift_certificates_type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
                $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $osC_Language->get('recipients_email') . ': ' . $product['recipients_email'] . '</i></nobr>';
              }
              
              $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $osC_Language->get('messages') . ': ' . $product['messages'] . '</i></nobr>';
            }
            
            if ( isset($product['variants']) && is_array($product['variants']) && ( sizeof($product['variants']) > 0 ) ) {
              foreach ( $product['variants'] as $variants ) {
                $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $variants['groups_name'] . ': ' . $variants['values_name'] . '</i></nobr>';
              }
            }
            
            $products[] = $product_info;
          }
        }       
        
        $customers_name = $osC_Order->getCustomer('name');
        $customers_email_address = $osC_Order->getCustomer('email_address');
        
        require_once('includes/classes/currencies.php');
        $osC_Currencies = new osC_Currencies_Admin();
        
        include('../includes/classes/email_template.php');
        $email_template = toC_Email_Template::getEmailTemplate('admin_create_order_credit_slip');
        $email_template->setData($customers_name, $customers_email_address, implode('<br />', $products), $data['orders_id'], $credit_slips_id, $osC_Currencies->format($data['sub_total'] + $data['shipping_fee'] + $data['handling']));
        $email_template->buildMessage();
        $email_template->sendEmail();        
        
        return true;
      } 
      
      $osC_Database->rollbackTransaction();
        
      return false;
    }
    
    function createStoreCredit($data) {
      global $osC_Database, $osC_Language;

      $error = false;
      
      $osC_Database->startTransaction();

//order refund
      $Qinsert = $osC_Database->query('insert into :table_orders_refunds (orders_refunds_type, orders_id, credit_slips_id, sub_total, shipping, handling, refund_total, comments, date_added) values (:orders_refunds_type, :orders_id, :credit_slips_id, :sub_total, :shipping, :handling, :refund_total, :comments, now())');
      $Qinsert->bindTable(':table_orders_refunds', TABLE_ORDERS_REFUNDS);
      $Qinsert->bindInt(':orders_refunds_type', ORDERS_RETURNS_TYPE_STORE_CREDIT);
      $Qinsert->bindInt(':orders_id', $data['orders_id']);
      $Qinsert->bindRaw(':credit_slips_id', 'null');
      $Qinsert->bindValue(':sub_total', $data['sub_total']);
      $Qinsert->bindValue(':shipping', $data['shipping_fee']);
      $Qinsert->bindValue(':handling', $data['handling']);
      $Qinsert->bindValue(':refund_total', $data['sub_total'] + $data['shipping_fee'] + $data['handling']);
      $Qinsert->bindValue(':comments', $data['comments']);
      $Qinsert->setLogging($_SESSION['module'], null);
      $Qinsert->execute(); 
      
    if ($osC_Database->isError()) {
        $error = true;
      } else {
        $orders_refunds_id = $osC_Database->nextID();
  
//orders refunds products
        $return_products = explode(';', $data['return_quantity']);
        foreach ($return_products as $product) {
          list($orders_products_id, $quantity) = explode(':', $product);
          
          $Qproduct = $osC_Database->query('insert into :table_orders_refunds_products (orders_refunds_id, orders_products_id, products_quantity) values (:orders_refunds_id, :orders_products_id, :products_quantity)');
          $Qproduct->bindTable(':table_orders_refunds_products', TABLE_ORDERS_REFUNDS_PRODUCTS);
          $Qproduct->bindInt(':orders_refunds_id', $orders_refunds_id);
          $Qproduct->bindInt(':orders_products_id', $orders_products_id);
          $Qproduct->bindInt(':products_quantity', $quantity);
          $Qproduct->setLogging($_SESSION['module'], $orders_refunds_id);
          $Qproduct->execute(); 
          
          if ( $osC_Database->isError() ) {
            $error = true;
            break;
          } 
          
          if ($error === false) {
            $Qupdate = $osC_Database->query('update :table_orders_products set products_return_quantity = products_return_quantity + :products_return_quantity where orders_products_id = :orders_products_id');
            $Qupdate->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
            $Qupdate->bindInt(':products_return_quantity', $quantity);
            $Qupdate->bindInt(':orders_products_id', $orders_products_id);
            $Qupdate->setLogging($_SESSION['module'], $orders_refunds_id);
            $Qupdate->execute();

            if ( $osC_Database->isError() ) {
              $error = true;
              break;
            }
          }
          
          if ( ($error === false) && ($data['restock_quantity'] === true) ) {
            $Qcheck = $osC_Database->query('select products_id from :table_orders_products where orders_products_id = :orders_products_id and orders_id = :orders_id');
            $Qcheck->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
            $Qcheck->bindInt(':orders_products_id', $orders_products_id);
            $Qcheck->bindInt(':orders_id', $data['orders_id']);
            $Qcheck->setLogging($_SESSION['module'], $orders_refunds_id);
            $Qcheck->execute();

            $products_id = $Qcheck->valueInt('products_id'); 
          
            if (!osC_Product::restock( $data['orders_id'], $orders_products_id, $products_id, $quantity )) {
              $error = true;
              break;
            }
          }
        }
      }

      if ($error === false) {
        $Qreturn = $osC_Database->query('update :table_orders_returns set orders_returns_status_id = :orders_returns_status_id, admin_comments = :admin_comments, date_updated = now() where orders_returns_id = :id');
        $Qreturn->bindTable(':table_orders_returns', TABLE_ORDERS_RETURNS);
        $Qreturn->bindInt(':orders_returns_status_id', ORDERS_RETURNS_STATUS_REFUNDED_STORE_CREDIT);
        $Qreturn->bindValue(':admin_comments', $data['comment']);
        $Qreturn->bindInt(':id', $data['orders_returns_id']);
        $Qreturn->setLogging($_SESSION['module'], $data['orders_returns_id']);
        $Qreturn->execute();
        
        if($osC_Database->isError()){
          $error = true;
        }
      }
      
      if ($error === false) {
        $Qcustomer = $osC_Database->query('select customers_id from :table_orders where orders_id = :orders_id');
        $Qcustomer->bindTable(':table_orders', TABLE_ORDERS);
        $Qcustomer->bindInt(':orders_id', $data['orders_id']);
        $Qcustomer->execute();
        
        $customers_id = $Qcustomer->valueInt('customers_id');
      
        $Qhistory = $osC_Database->query('insert into :table_customers_credits_history (customers_id, action_type, date_added, amount, comments) values (:customers_id, :action_type, now(), :amount, :comments)');
        $Qhistory->bindTable(':table_customers_credits_history', TABLE_CUSTOMERS_CREDITS_HISTORY);
        $Qhistory->bindInt(':customers_id', $customers_id);
        $Qhistory->bindInt(':action_type', STORE_CREDIT_ACTION_TYPE_ORDER_REFUNDED);
        $Qhistory->bindValue(':amount', $data['sub_total'] + $data['shipping_fee'] + $data['handling']);
        $Qhistory->bindValue(':comments', sprintf($osC_Language->get('infomation_store_credit_from_order'), $data['orders_id']));
        $Qhistory->execute();
        
        if ($osC_Database->isError()) {
          $error = true;
        }
  
        if ($error === false) {
          $Qupdate = $osC_Database->query('update :table_customers set customers_credits = (customers_credits + :customers_credits) where customers_id = :customers_id');
          $Qupdate->bindTable(':table_customers', TABLE_CUSTOMERS);
          $Qupdate->bindRaw(':customers_credits', $data['sub_total'] + $data['shipping_fee'] + $data['handling']);
          $Qupdate->bindInt(':customers_id', $customers_id);
          $Qupdate->setLogging($_SESSION['module'], $data['$orders_refunds_id']);
          $Qupdate->execute();
  
          if ($osC_Database->isError()) {
            $error = true;
          }
        } 
      }
      
      if ($error === false) {
        $osC_Database->commitTransaction();
        
        $osC_Order = new osC_Order($data['orders_id']);

        $return_products_ids = array();
        $return_products_qty = array();
        $return_products = explode(';', $data['return_quantity']);
        foreach ($return_products as $product) {
          list($orders_products_id, $quantity) = explode(':', $product);
          
          $return_products_ids[] = $orders_products_id;
          $return_products_qty[$orders_products_id] = $quantity;
        }

        $products = array();
        foreach ($osC_Order->getProducts() as $product) {
          if (in_array($product['orders_products_id'], $return_products_ids)) {
            $product_info = $return_products_qty[$product['orders_products_id']] . '&nbsp;x&nbsp;' . $product['name'];
            
            if ( $product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE ) {
              $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $osC_Language->get('senders_name') . ': ' . $product['senders_name'] . '</i></nobr>';
              
              if ($product['gift_certificates_type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
                $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $osC_Language->get('senders_email') . ': ' . $product['senders_email'] . '</i></nobr>';
              }
              
              $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $osC_Language->get('recipients_name') . ': ' . $product['recipients_name'] . '</i></nobr>';
              
              if ($product['gift_certificates_type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
                $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $osC_Language->get('recipients_email') . ': ' . $product['recipients_email'] . '</i></nobr>';
              }
              
              $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $osC_Language->get('messages') . ': ' . $product['messages'] . '</i></nobr>';
            }
            
            if ( isset($product['variants']) && is_array($product['variants']) && ( sizeof($product['variants']) > 0 ) ) {
              foreach ( $product['variants'] as $variants ) {
                $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $variants['groups_name'] . ': ' . $variants['values_name'] . '</i></nobr>';
              }
            }
            
            $products[] = $product_info;
          }
        }       
        
        $customers_name = $osC_Order->getCustomer('name');
        $customers_email_address = $osC_Order->getCustomer('email_address');
        
        require_once('includes/classes/currencies.php');
        $osC_Currencies = new osC_Currencies_Admin();
        
        include('../includes/classes/email_template.php');
        $email_template = toC_Email_Template::getEmailTemplate('admin_create_order_store_credit');
        $email_template->setData($customers_name, $customers_email_address, implode('<br />', $products), $data['orders_id'], $osC_Currencies->format($data['sub_total'] + $data['shipping_fee'] + $data['handling']));
        $email_template->buildMessage();
        $email_template->sendEmail();   
        
        return true;
      } 
      
      $osC_Database->rollbackTransaction();
        
      return false;
    }

    
    function saveOrderReturn($returnId, $data){
      global $osC_Database;
      
      $Qupate = $osC_Database->query('update :table_orders_returns set admin_comments = :comments, date_updated = now(), orders_returns_status_id = :orders_returns_status_id where orders_returns_id = :id');
      $Qupate->bindTable(':table_orders_returns', TABLE_ORDERS_RETURNS);
      $Qupate->bindValue(':comments', $data['comment']);
      $Qupate->bindInt(':orders_returns_status_id', $data['orders_returns_status_id']);
      $Qupate->bindInt(':id', $returnId);
      $Qupate->setLogging($_SESSION['module'], $returnId);
      $Qupate->execute();
      
      if (!$osC_Database->isError()) {
        return true;
      }
      
      return false;
    }
  }
?>
