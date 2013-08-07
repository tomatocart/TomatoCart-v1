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
  require_once('includes/classes/order.php');
  require_once('includes/classes/orders_returns.php');
  
  class toC_Json_Orders_Returns {
  
    function listOrdersReturns() {
      global $toC_Json, $osC_Language, $osC_Database;

      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qreturns = $osC_Database->query('select r.orders_returns_id, r.orders_id, r.orders_returns_status_id, r.customers_comments, r.admin_comments, r.date_added, o.customers_name, ors.orders_returns_status_name from :table_orders o, :table_orders_returns r, :table_orders_returns_status ors where r.orders_id = o.orders_id and r.orders_returns_status_id = ors.orders_returns_status_id and ors.languages_id = :languages_id');
      
      if (isset($_REQUEST['orders_id']) && !empty($_REQUEST['orders_id'])) {
        $Qreturns->appendQuery('and r.orders_id = :orders_id ');
        $Qreturns->bindInt(':orders_id', $_REQUEST['orders_id']);
      }
      
      if (isset($_REQUEST['customers_id']) && !empty($_REQUEST['customers_id'])) {
        $Qreturns->appendQuery('and o.customers_id = :customers_id ');
        $Qreturns->bindInt(':customers_id', $_REQUEST['customers_id']);
      }
      
      if (isset($_REQUEST['orders_returns_status_id']) && !empty($_REQUEST['orders_returns_status_id'])) {
        $Qreturns->appendQuery('and r.orders_returns_status_id = :orders_returns_status_id ');
        $Qreturns->bindInt(':orders_returns_status_id', $_REQUEST['orders_returns_status_id']);
      }
      
      $Qreturns->appendQuery('order by r.orders_returns_id desc ');
      
      $Qreturns->bindTable(':table_orders', TABLE_ORDERS);
      $Qreturns->bindTable(':table_orders_returns', TABLE_ORDERS_RETURNS);
      $Qreturns->bindTable(':table_orders_returns_status', TABLE_ORDERS_RETURNS_STATUS);
      $Qreturns->bindInt(':languages_id', $osC_Language->getID());
      $Qreturns->setExtBatchLimit($start, $limit);
      $Qreturns->execute();

      $records = array();
      while ($Qreturns->next()) {
        $orders_returns_id = $Qreturns->value('orders_returns_id');
        
        $Qproducts = $osC_Database->query("select orders_products_id, products_quantity from :table_orders_returns_products where orders_returns_id = :orders_returns_id");
        $Qproducts->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
        $Qproducts->bindTable(':table_orders_returns_products', TABLE_ORDERS_RETURNS_PRODUCTS);
        $Qproducts->bindInt(':orders_returns_id', $orders_returns_id);
        $Qproducts->execute();
        
        $return_products_ids = array();
        $return_products_qty = array();
        while ($Qproducts->next()) {
          $return_products_ids[] = $Qproducts->valueInt('orders_products_id');
          $return_products_qty[$Qproducts->valueInt('orders_products_id')] = $Qproducts->valueInt('products_quantity');
        }
        
        $total = 0;
        $quantity = 0;
        $products = array();
        $return_quantity = array();
        $osC_Order = new osC_Order($Qreturns->valueInt('orders_id'));
        
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
            $total += $return_products_qty[$product['orders_products_id']]  * $product['final_price'] * (1 + $product['tax'] / 100);
            $quantity += $return_products_qty[$product['orders_products_id']];
            $return_quantity[] = $product['orders_products_id'] . ':' . $return_products_qty[$product['orders_products_id']];
          }
        }
        
        $action = array();
        $orders_returns_status_id = $Qreturns->value('orders_returns_status_id');
        
        if ( ($orders_returns_status_id == ORDERS_RETURNS_STATUS_REFUNDED_CREDIT_MEMO) || ($orders_returns_status_id == ORDERS_RETURNS_STATUS_REFUNDED_STORE_CREDIT) || ($orders_returns_status_id == ORDERS_RETURNS_STATUS_REJECT) ) {
          $action[] = array('class' => 'icon-edit-gray-record', 'qtip' => $osC_Language->get('icon_edit'));
          $action[] = array('class' => 'icon-credit-slip-gray-record', 'qtip' => $osC_Language->get('icon_credit_slip'));
          $action[] = array('class' => 'icon-store-credit-gray-record', 'qtip' => $osC_Language->get('icon_issue_store_credit'));
        } else {
          $action[] = array('class' => 'icon-edit-record', 'qtip' => $osC_Language->get('icon_edit'));
          $action[] = array('class' => 'icon-credit-slip-record', 'qtip' => $osC_Language->get('icon_credit_slip'));
          $action[] = array('class' => 'icon-store-credit-record', 'qtip' => $osC_Language->get('icon_issue_store_credit'));
        }
        
        $records[] = array('orders_returns_id' => $orders_returns_id,
                           'orders_id' => $Qreturns->valueInt('orders_id'),
                           'orders_returns_customer' => $Qreturns->value('customers_name'),                   
                           'quantity' => $quantity,
                           'date_added' => osC_DateTime::getShort($Qreturns->value('date_added')),
                           'status' => $Qreturns->value('orders_returns_status_name'),
                           'status_id' => $orders_returns_status_id,
                           'products' => implode('<br />' , $products),
                           'return_quantity' => implode(';' , $return_quantity),
                           'billing_address' => osC_Address::format($osC_Order->getBilling(), '<br />'),
                           'customers_comments' => $Qreturns->value('customers_comments'),
                           'admin_comments' => $Qreturns->value('admin_comments'),
                           'total' => number_format($total, 2, '.', ''),
                           'action' => $action);
      }
      
      $response = array(EXT_JSON_READER_TOTAL => $Qreturns->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records); 
                        
      echo $toC_Json->encode($response);
    }
    
    function saveOrderReturn(){
      global $toC_Json, $osC_Language;
            
      $data = array('orders_returns_status_id' => $_REQUEST['orders_returns_status_id'],
                    'comment' => $_REQUEST['admin_comment']);
      
      if(toC_OrdersReturns_Admin::saveOrderReturn($_REQUEST['orders_returns_id'], $data)){
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }

    function createCreditSlip() {
      global $osC_Database, $osC_Language, $toC_Json;
      
      $errors = array();
      
      $data = array('orders_id' => $_REQUEST['orders_id'],
                    'orders_returns_id' => $_REQUEST['orders_returns_id'],
                    'sub_total' => $_REQUEST['sub_total'],
                    'shipping_fee' => $_REQUEST['shipping_fee'],
                    'handling' => $_REQUEST['handling'],
                    'return_quantity' => $_REQUEST['return_quantity'],
                    'comments' => $_REQUEST['admin_comment'],
                    'restock_quantity' => ((isset($_REQUEST['restock_quantity']) && ($_REQUEST['restock_quantity'] == 'on')) ? true : false));
      
      $return_products = explode(';', $_REQUEST['return_quantity']);
      foreach ($return_products as $product) {
        list($orders_products_id, $quantity) = explode(':', $product);
          
        $Qproducts = $osC_Database->query('select pd.products_name, op.products_quantity, op.products_return_quantity from :table_orders_products op, :table_products_description pd where op.products_id = pd.products_id and pd.language_id = :language_id and orders_id = :orders_id and orders_products_id = :orders_products_id');
        $Qproducts->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
        $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
        $Qproducts->bindInt(':language_id', $osC_Language->getID());
        $Qproducts->bindInt(':orders_id', $_REQUEST['orders_id']);
        $Qproducts->bindInt(':orders_products_id', $orders_products_id);
        $Qproducts->execute(); 
        
        while($Qproducts->next()) {
          $left = $Qproducts->valueInt('products_quantity') - $Qproducts->valueInt('products_return_quantity');
          if($quantity > $left) {
            $errors[] = sprintf($osC_Language->get('error_return_quantity_incorrect'), $Qproducts->value('products_name'));
          }
        }
      }
      
      if (sizeof($errors) > 0) {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br /><br />' . implode('<br />', $errors));
      } else {
       if ( toC_OrdersReturns_Admin::createCreditSlip($data) ) {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }
      }

      echo $toC_Json->encode($response);
    }
    
    function createStoreCredit() {
      global $osC_Database, $osC_Language, $toC_Json;
      
      $errors = array();
      
      $data = array('orders_id' => $_REQUEST['orders_id'],
                    'orders_returns_id' => $_REQUEST['orders_returns_id'],
                    'sub_total' => $_REQUEST['sub_total'],
                    'shipping_fee' => $_REQUEST['shipping_fee'],
                    'handling' => $_REQUEST['handling'],
                    'return_quantity' => $_REQUEST['return_quantity'],
                    'comments' => $_REQUEST['admin_comment'],
                    'restock_quantity' => ((isset($_REQUEST['restock_quantity']) && ($_REQUEST['restock_quantity'] == 'on')) ? true : false));
      
      $return_products = explode(';', $_REQUEST['return_quantity']);
      foreach ($return_products as $product) {
        list($orders_products_id, $quantity) = explode(':', $product);
          
        $Qproducts = $osC_Database->query('select pd.products_name, op.products_quantity, op.products_return_quantity from :table_orders_products op, :table_products_description pd where op.products_id = pd.products_id and pd.language_id = :language_id and orders_id = :orders_id and orders_products_id = :orders_products_id');
        $Qproducts->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
        $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
        $Qproducts->bindInt(':language_id', $osC_Language->getID());
        $Qproducts->bindInt(':orders_id', $_REQUEST['orders_id']);
        $Qproducts->bindInt(':orders_products_id', $orders_products_id);
        $Qproducts->execute(); 
        
        while($Qproducts->next()) {
          $left = $Qproducts->valueInt('products_quantity') - $Qproducts->valueInt('products_return_quantity');
          if($quantity > $left) {
            $errors[] = sprintf($osC_Language->get('error_return_quantity_incorrect'), $Qproducts->value('products_name'));
          }
        }
      }
      
      if (sizeof($errors) > 0) {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br /><br />' . implode('<br />', $errors));
      } else {
       if ( toC_OrdersReturns_Admin::createStoreCredit($data) ) {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }
      }

      echo $toC_Json->encode($response);
    }
        
    function listReturnStatus(){
      global $toC_Json, $osC_Database, $osC_Language;
      
      $Qstatus = $osC_Database->query('select orders_returns_status_id, orders_returns_status_name from :table_orders_returns_status where languages_id = :language_id');
      $Qstatus->bindTable(':table_orders_returns_status', TABLE_ORDERS_RETURNS_STATUS);
      $Qstatus->bindInt(':language_id', $osC_Language->getID());
      $Qstatus->execute();
      
      $records = array();
      if (isset($_REQUEST['top']) && ($_REQUEST['top'] == 'true')) {
        $records[] = array('status_id' => '', 'status_name' => $osC_Language->get('all_status'));
      }
      
      while($Qstatus->next()) {
         $records[] = array('status_id' => $Qstatus->valueInt('orders_returns_status_id'), 'status_name' => $Qstatus->value('orders_returns_status_name'));
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response);
      
    }
  }
?>
