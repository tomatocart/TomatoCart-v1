<?php
/*
  $Id: invoices.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require_once('includes/classes/currencies.php');
  require_once('includes/classes/tax.php');
  require('includes/classes/order.php');
  require('includes/classes/invoices.php');

  class toC_Json_Invoices {
        
    function listInvoices() {
      global $toC_Json, $osC_Database ,$osC_Language;
      
      $osC_Tax = new osC_Tax_Admin();
      $osC_Currencies = new osC_Currencies_Admin();
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];      
      
      $Qinvoices = $osC_Database->query('select o.orders_id, o.customers_ip_address, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, o.invoice_number, o.invoice_date, s.orders_status_name, ot.text as order_total from :table_orders o, :table_orders_total ot, :table_orders_status s where o.orders_id = ot.orders_id and ot.class = "total" and o.orders_status = s.orders_status_id and s.language_id = :language_id and o.invoice_number IS NOT NULL');

      if ( isset($_REQUEST['orders_id']) && is_numeric($_REQUEST['orders_id']) ) {
        $Qinvoices->appendQuery(' and o.orders_id = :orders_id ');
        $Qinvoices->bindInt(':orders_id', $_REQUEST['orders_id']);
      }
      
      if ( isset($_REQUEST['customers_id']) && is_numeric($_REQUEST['customers_id']) ) {
        $Qinvoices->appendQuery(' and o.customers_id = :customers_id ');
        $Qinvoices->bindInt(':customers_id', $_REQUEST['customers_id']);
      }
      
      if ( isset($_REQUEST['status']) && is_numeric($_REQUEST['status']) ) {
        $Qinvoices->appendQuery(' and s.orders_status_id = :orders_status_id ');
        $Qinvoices->bindInt(':orders_status_id', $_REQUEST['status']);
      }
      
      $Qinvoices->appendQuery('order by o.date_purchased desc, o.last_modified desc, o.invoice_number desc');
      $Qinvoices->bindTable(':table_orders', TABLE_ORDERS);
      $Qinvoices->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
      $Qinvoices->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
      $Qinvoices->bindInt(':language_id', $osC_Language->getID());
      $Qinvoices->setExtBatchLimit($start, $limit);
      $Qinvoices->execute();
      
      $records = array();     
      while ( $Qinvoices->next() ) {
        $osC_Order = new osC_Order($Qinvoices->valueInt('orders_id')); 
        
        $products_table = '<table width="100%">';
        foreach ($osC_Order->getProducts() as $product) {
          $product_info = $product['quantity'] . '&nbsp;x&nbsp;' . $product['name'];
          
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
          
          $products_table .= '<tr><td>' . $product_info . '</td><td width="60" valign="top" align="right">' . $osC_Currencies->displayPriceWithTaxRate($product['final_price'], $product['tax'], 1, $osC_Order->getCurrency(), $osC_Order->getCurrencyValue()) . '</td></tr>';
        }
        $products_table .= '</table>';
        
        $order_total = '<table width="100%">';
        foreach ( $osC_Order->getTotals() as $total ) {
          $order_total .= '<tr><td align="right">' . $total['title'] . '&nbsp;&nbsp;&nbsp;</td><td width="60" align="right">' . $total['text'] . '</td></tr>';
        }
        $order_total .= '</table>';
                
        $records[] = array('orders_id' => $Qinvoices->valueInt('orders_id'),
                           'customers_name' => $Qinvoices->valueProtected('customers_name'),
                           'order_total' => strip_tags($Qinvoices->value('order_total')),
                           'date_purchased' => osC_DateTime::getShort($Qinvoices->value('date_purchased')),
                           'orders_status_name' => $Qinvoices->value('orders_status_name'),
                           'invoices_number' => $Qinvoices->value('invoice_number'),
                           'invoices_date' => osC_DateTime::getShort($Qinvoices->value('invoice_date')),
                           'shipping_address' => osC_Address::format($osC_Order->getDelivery(), '<br />'),
                           'shipping_method' => $osC_Order->getDeliverMethod(),
                           'billing_address' => osC_Address::format($osC_Order->getBilling(), '<br />'),
                           'payment_method' => $osC_Order->getPaymentMethod(),
                           'products' => $products_table,
                           'totals' => $order_total);  
      }
      $Qinvoices->freeResult();
      
      $response = array(EXT_JSON_READER_TOTAL => $Qinvoices->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }

    function loadSummaryData() {
      global $toC_Json, $osC_Language;
      
      $osC_Order = new osC_Order($_REQUEST['orders_id']);
      
      $response['customer'] = '<p style="margin-left:10px;">' . osC_Address::format($osC_Order->getCustomer(), '<br />') . '</p>' . 
                              '<p style="margin-left:10px;>' . 
                                osc_icon('telephone.png') . $osC_Order->getCustomer('telephone') . '<br />' . osc_icon('write.png') . $osC_Order->getCustomer('email_address') . 
                              '</p>';
      $response['shippingAddress'] = '<p style="margin-left:10px;">'.osC_Address::format($osC_Order->getDelivery(), '<br />').'</p>';
      $response['billingAddress'] = '<p style="margin-left:10px;">'.osC_Address::format($osC_Order->getBilling(), '<br />').'</p>';
      $response['paymentMethod'] = '<p style="margin-left:10px;">' . $osC_Order->getPaymentMethod() . '</p>';

      if ( $osC_Order->isValidCreditCard() ) {
        $response['paymentMethod'] .= '
          <table border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td>' . $osC_Language->get('credit_card_type') . '</td>
              <td>' . $osC_Order->getCreditCardDetails('type') . '</td>
            </tr>
            <tr>
              <td>' . $osC_Language->get('credit_card_owner_name') . '</td>
              <td>' . $osC_Order->getCreditCardDetails('owner') . '</td>
            </tr>
            <tr>
              <td>' . $osC_Language->get('credit_card_number') . '</td>
              <td>' . $osC_Order->getCreditCardDetails('number') . '</td>
            </tr>
            <tr>
              <td>' . $osC_Language->get('credit_card_expiry_date') . '</td>
              <td>' . $osC_Order->getCreditCardDetails('expires') . '</td>
            </tr>
          </table>';
      }
      
      $response['status'] = '<p style="margin-left:10px;">' . 
                              $osC_Order->getStatus() . '<br />' . ( $osC_Order->getDateLastModified() > $osC_Order->getDateCreated() ? osC_DateTime::getShort($osC_Order->getDateLastModified(), true) : osC_DateTime::getShort($osC_Order->getDateCreated(), true)) . 
                            '</p>' . 
                            '<p style="margin-left:10px;">' . 
                              $osC_Language->get('number_of_comments') . ' ' . $osC_Order->getNumberOfComments() . 
                            '</p>';
      $response['total'] = '<p style="margin-left:10px;">' . $osC_Order->getTotal().'</p>' . 
                           '<p style="margin-left:10px;">' . 
                              $osC_Language->get('number_of_products') . ' ' . $osC_Order->getNumberOfProducts() . '<br />' . 
                              $osC_Language->get('number_of_items') . ' ' . $osC_Order->getNumberOfItems() . 
                           '</p>';
      
      echo $toC_Json->encode($response);
    }
    
    function getProducts(){
      global $toC_Json, $osC_Language;
      
      $osC_Tax = new osC_Tax_Admin();
      $osC_Currencies = new osC_Currencies_Admin();
      
      $osC_Order = new osC_Order($_REQUEST['orders_id']);

      $records = array();
      foreach ( $osC_Order->getProducts() as $product ) {
        $product_info = $product['quantity'] . '&nbsp;x&nbsp;' . $product['name'];
        
        if ( isset($product['variants']) && is_array($product['variants']) && ( sizeof($product['variants']) > 0 ) ) {
          foreach ( $product['variants'] as $variants ) {
            $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $variants['groups_name'] . ': ' . $variants['values_name'] . '</i></nobr>';
          }
        }
        
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
        
        $records[] = array('products' => $product_info, 
                           'return_quantity' => ($product['return_quantity'] > 0) ? $product['return_quantity'] : '',
                           'model' => $product['model'],
                           'tax' => $osC_Tax->displayTaxRateValue($product['tax']), 
                           'price_net' =>$osC_Currencies->format($product['final_price'], $osC_Order->getCurrency(), $osC_Order->getCurrencyValue()), 
                           'price_gross' => $osC_Currencies->displayPriceWithTaxRate($product['final_price'], $product['tax'], 1, $osC_Order->getCurrency(), $osC_Order->getCurrencyValue()), 
                           'total_net' => $osC_Currencies->format($product['final_price'] * $product['quantity'], $osC_Order->getCurrency(), $osC_Order->getCurrencyValue()), 
                           'total_gross' => $osC_Currencies->displayPriceWithTaxRate($product['final_price'], $product['tax'], $product['quantity'], $osC_Order->getCurrency(), $osC_Order->getCurrencyValue()));
      }
      
      foreach ( $osC_Order->getTotals() as $totals ) {
        $records[] = array('products' => '', 
                           'model' => '', 
                           'tax' => '', 
                           'price_net' => '', 
                           'price_gross' => $totals['title'], 
                           'total_net' => '', 
                           'total_gross' => $totals['text']);
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }    
      
    function getTransactionHistory(){
      global $toC_Json;
      
      $osC_Order = new osC_Order($_REQUEST['orders_id']);
      
      $records = array();
      foreach ( $osC_Order->getTransactionHistory() as $history ) {
        $records[] = array('date' => osC_DateTime::getShort($history['date_added'], true), 
                           'status' => ( !empty($history['status']) ) ? $history['status'] : $history['status_id'], 
                           'comments' => nl2br($history['return_value']));
      }

      $response = array(EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response);
    }
      
    function listOrdersStatus(){
      global $toC_Json;
      
      $osC_Order = new osC_Order($_REQUEST['orders_id']);

      $records = array();
      foreach ( $osC_Order->getStatusHistory() as $status_history ) {
        $records[] = array('date_added' => osC_DateTime::getShort($status_history['date_added'], true), 
                           'status' => $status_history['status'], 
                           'comments' => nl2br($status_history['comment']), 
                           'customer_notified' => osc_icon((($status_history['customer_notified'] === 1) ? 'checkbox_ticked.gif' : 'checkbox_crossed.gif')));
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response);
    }
    
    function getAvailableProducts(){
      global $toC_Json, $osC_Language;
      
      $osC_Tax = new osC_Tax_Admin();
      $osC_Currencies = new osC_Currencies_Admin();

      $osC_Order = new osC_Order($_REQUEST['orders_id']);

      $records = array();
      foreach ( $osC_Order->getProducts() as $product ) {
        $available = $product['quantity'] > $product['return_quantity'];
        
        $allow_return = true;
        if (($product['type'] == PRODUCT_TYPE_DOWNLOADABLE) && (ALLOW_GIFT_CERTIFICATE_RETURN == '-1')) {
          $allow_return = false;
        } else if (($product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) && (ALLOW_DOWNLOADABLE_RETURN == '-1')) {
          $allow_return = false;
        }
        
        if (($available > 0) && ($allow_return == true)) {
          $product_info = $product['quantity'] . '&nbsp;x&nbsp;' . $product['name'];
          
          if ( isset($product['variants']) && is_array($product['variants']) && ( sizeof($product['variants']) > 0 ) ) {
            foreach ( $product['variants'] as $variants ) {
              $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $variants['groups_name'] . ': ' . $variants['values_name'] . '</i></nobr>';
            }
          }
          
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
          
          $records[] = array('orders_products_id' => $product['orders_products_id'],
                             'products_name' => $product_info, 
                             'products_price' => $osC_Currencies->addTaxRateToPrice($product['final_price'], $product['tax'], 1),
                             'products_format_price' => $osC_Currencies->displayPriceWithTaxRate($product['final_price'], $product['tax'], 1, $osC_Order->getCurrency(), $osC_Order->getCurrencyValue()),
                             'quantity_available' => ($product['quantity'] - $product['return_quantity']),
                             'return_quantity' => '0');
        }
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }
    
    function getRefundHistory() {
      global $osC_Database, $osC_Language, $toC_Json;

      $osC_Currencies = new osC_Currencies_Admin();
      
      $osC_Order = new osC_Order($_REQUEST['orders_id']);
      
      $Qrefunds = $osC_Database->query('select * from :table_orders_refunds where orders_id = :orders_id');
      $Qrefunds->bindTable(':table_orders_refunds', TABLE_ORDERS_REFUNDS);
      $Qrefunds->bindInt(':orders_id', $_REQUEST['orders_id']);
      $Qrefunds->execute();
      
      $records = array();
      while ( $Qrefunds->next() ) {
        $Qproducts = $osC_Database->query('select * from :table_orders_refunds_products where orders_refunds_id = :orders_refunds_id');
        $Qproducts->bindTable(':table_orders_refunds_products', TABLE_ORDERS_REFUNDS_PRODUCTS);
        $Qproducts->bindInt(':orders_refunds_id', $Qrefunds->valueInt('orders_refunds_id'));
        $Qproducts->execute();
        
        $total_products = 0;
        $products = array();
        $products_table = '<table width="100%">';
        while($Qproducts->next()) {
          foreach ($osC_Order->getProducts() as $product) {
            if ( $Qproducts->valueInt('orders_products_id') == $product['orders_products_id'] ) {
              $total_products += $Qproducts->valueInt('products_quantity');
              $product_info = $Qproducts->valueInt('products_quantity') . '&nbsp;x&nbsp;' . $product['name'];
              
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
              $products_table .= '<tr><td>' . $product_info . '</td><td width="60" valign="top" align="right">' . $osC_Currencies->displayPriceWithTaxRate($product['final_price'], $product['tax'], 1, $osC_Order->getCurrency(), $osC_Order->getCurrencyValue()) . '</td></tr>';              
            }
          }
        }
        $products_table .= '</table>';
        
        $order_total = '<table width="100%">';
        $order_total .= '<tr><td align="right">' . $osC_Language->get("field_sub_total") . '&nbsp;&nbsp;&nbsp;</td><td width="60">' . $osC_Currencies->format($Qrefunds->value('sub_total')) . '</td></tr>';
        $order_total .= '<tr><td align="right">' . $osC_Language->get("field_shipping_fee") . '&nbsp;&nbsp;&nbsp;</td><td width="60">' . $osC_Currencies->format($Qrefunds->value('shipping')) . '</td></tr>';
        $order_total .= '<tr><td align="right">' . $osC_Language->get("field_handling") . '&nbsp;&nbsp;&nbsp;</td><td width="60">' . $osC_Currencies->format($Qrefunds->value('handling')) . '</td></tr>';
        $order_total .= '<tr><td align="right">' . $osC_Language->get("field_refund_total") . '&nbsp;&nbsp;&nbsp;</td><td width="60">' . $osC_Currencies->format($Qrefunds->value('refund_total')) . '</td></tr>';
        $order_total .= '</table>';
        
        $records[] = array('orders_refunds_id' => $Qrefunds->valueInt('orders_refunds_id'),
                           'orders_refunds_type' => ($Qrefunds->valueInt('orders_refunds_type') == ORDERS_RETURNS_TYPE_CREDIT_SLIP) ? $osC_Language->get('text_credit_slip') : $osC_Language->get('text_store_credit'),
                           'total_products' => $total_products,
                           'total_refund' => $osC_Currencies->format($Qrefunds->value('refund_total')),
                           'sub_total' => $osC_Currencies->format($Qrefunds->value('sub_total')),
                           'date_added' => osC_DateTime::getShort($Qrefunds->value('date_added')),
                           'comments' => $Qrefunds->value('comments'), 
                           'products' => $products_table,
                           'totals' => $order_total);  
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }
    
    function createCreditSlip() {
      global $osC_Database, $osC_Language, $toC_Json;
      
      $data = array('orders_id' => $_REQUEST['orders_id'],
                    'sub_total' => $_REQUEST['sub_total'],
                    'shipping_fee' => $_REQUEST['shipping_fee'],
                    'handling' => $_REQUEST['handling'],
                    'return_quantity' => $_REQUEST['return_quantity'],
                    'comments' => $_REQUEST['comments'],
                    'restock_quantity' => ((isset($_REQUEST['restock_quantity']) && ($_REQUEST['restock_quantity'] == 'on')) ? true : false));
      
      if ( toC_Invoices_Admin::createCreditSlip($data) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }

      echo $toC_Json->encode($response);
    }
    
    function createStoreCredit() {
      global $osC_Database, $osC_Language, $toC_Json;
      
      $data = array('orders_id' => $_REQUEST['orders_id'],
                    'sub_total' => $_REQUEST['sub_total'],
                    'shipping_fee' => $_REQUEST['shipping_fee'],
                    'handling' => $_REQUEST['handling'],
                    'return_quantity' => $_REQUEST['return_quantity'],
                    'comments' => $_REQUEST['comments'],
                    'restock_quantity' => ((isset($_REQUEST['restock_quantity']) && ($_REQUEST['restock_quantity'] == 'on')) ? true : false));
      
      if ( toC_Invoices_Admin::createStoreCredit($data) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }

      echo $toC_Json->encode($response);
    }
    
    function getOrdersReturns() {
      global $toC_Json, $osC_Language, $osC_Database;

      $osC_Order = new osC_Order($Qreturns->valueInt('orders_id'));
      
      $Qreturns = $osC_Database->query('select r.orders_returns_id, r.orders_id, r.orders_returns_status_id, r.customers_comments, r.date_added, o.customers_name, ors.orders_returns_status_name from :table_orders o, :table_orders_returns r, :table_orders_returns_status ors where r.orders_id = o.orders_id and r.orders_returns_status_id = ors.orders_returns_status_id and r.orders_id = :orders_id and ors.languages_id = :languages_id');
      $Qreturns->bindTable(':table_orders', TABLE_ORDERS);
      $Qreturns->bindTable(':table_orders_returns', TABLE_ORDERS_RETURNS);
      $Qreturns->bindTable(':table_orders_returns_status', TABLE_ORDERS_RETURNS_STATUS);
      $Qreturns->bindInt(':orders_id', $_REQUEST['orders_id']);
      $Qreturns->bindInt(':languages_id', $osC_Language->getID());
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
            $total += $return_products_qty[$product['orders_products_id']] * $product['final_price'];
            $quantity += $return_products_qty[$product['orders_products_id']];
          }
        }
        
        $records[] = array('orders_returns_id' => $orders_returns_id,
                           'orders_id' => $Qreturns->valueInt('orders_id'),
                           'orders_returns_customer' => $Qreturns->value('customers_name'),                   
                           'quantity' => $quantity,
                           'date_added' => osC_DateTime::getShort($Qreturns->value('date_added')),
                           'status' => $Qreturns->value('orders_returns_status_name'),
                           'status_id' => $orders_returns_status_id,
                           'products' => implode('<br />' , $products),
                           'admin_comments' => $Qreturns->value('admin_comments'),
                           'customers_comments' => $Qreturns->value('customers_comments'),
                           'total' => $total);
      }
      
      $response = array(EXT_JSON_READER_TOTAL => $Qreturns->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records); 
                        
      echo $toC_Json->encode($response);
    }
  }
?>
