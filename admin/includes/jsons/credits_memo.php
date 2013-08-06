<?php
/*
  $Id: credits_memo.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require_once('includes/classes/currencies.php');
  require_once('includes/classes/order.php');
  
  class toC_Json_Credits_Memo {
  
    function listCreditsMemo() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $osC_Currencies = new osC_Currencies_Admin();     
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qslips = $osC_Database->query('select r.* from :table_orders_refunds r ');

      if (isset($_REQUEST['customers_id']) && !empty($_REQUEST['customers_id'])) {
        $Qslips->appendQuery(', ' . TABLE_ORDERS . ' o where r.orders_id = o.orders_id and o.customers_id = :customers_id and r.orders_refunds_type = :orders_refunds_type');
        
        $Qslips->bindInt(':customers_id', $_REQUEST['customers_id']);
      } else {
        $Qslips->appendQuery('where orders_refunds_type = :orders_refunds_type');
      }
      
      if (isset($_REQUEST['orders_id']) && !empty($_REQUEST['orders_id'])) {
        $Qslips->appendQuery('and orders_id = :orders_id ');
        $Qslips->bindInt(':orders_id', $_REQUEST['orders_id']);
      }
      
      $Qslips->bindTable(':table_orders_refunds', TABLE_ORDERS_REFUNDS);
      $Qslips->bindInt(':orders_refunds_type', ORDERS_RETURNS_TYPE_CREDIT_SLIP);
      $Qslips->setExtBatchLimit($start, $limit);
      $Qslips->execute();
      
      $records = array();
      while ($Qslips->next()) {
        $orders_refunds_id = $Qslips->value('orders_refunds_id');
        
        $Qproducts = $osC_Database->query("select orders_products_id, products_quantity from :table_orders_refunds_products where orders_refunds_id = :orders_refunds_id");
        $Qproducts->bindTable(':table_orders_refunds_products', TABLE_ORDERS_REFUNDS_PRODUCTS);
        $Qproducts->bindInt(':orders_refunds_id', $orders_refunds_id);
        $Qproducts->execute();
        
        $products_ids = array();
        $products_qty = array();
        while ($Qproducts->next()) {
          $products_ids[] = $Qproducts->valueInt('orders_products_id');
          $products_qty[$Qproducts->valueInt('orders_products_id')] = $Qproducts->valueInt('products_quantity');
        }
        
        $total = 0;
        $quantity = 0;
        $products = array();
        $osC_Order = new osC_Order($Qslips->valueInt('orders_id'));
        
        $products_table = '<table width="100%">';
        foreach ($osC_Order->getProducts() as $product) {
          if (in_array($product['orders_products_id'], $products_ids)) {
            $product_info = $products_qty[$product['orders_products_id']] . '&nbsp;x&nbsp;' . $product['name'];
            
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
            $quantity += $products_qty[$product['orders_products_id']];
            $products_table .= '<tr><td>' . $product_info . '</td><td width="60" valign="top" align="right">' . $osC_Currencies->displayPriceWithTaxRate($product['final_price'], $product['tax'], 1, $osC_Order->getCurrency(), $osC_Order->getCurrencyValue()) . '</td></tr>';
          }
        }
        $products_table .= '</table>';
        
        $order_total = '<table width="100%">';
        $order_total .= '<tr><td align="right">' . $osC_Language->get("field_sub_total") . '&nbsp;&nbsp;&nbsp;</td><td width="60" align="right">' . $osC_Currencies->format($Qslips->value('sub_total')) . '</td></tr>';
        $order_total .= '<tr><td align="right">' . $osC_Language->get("field_shipping_fee") . '&nbsp;&nbsp;&nbsp;</td><td width="60" align="right">' . $osC_Currencies->format($Qslips->value('shipping')) . '</td></tr>';
        $order_total .= '<tr><td align="right">' . $osC_Language->get("field_handling") . '&nbsp;&nbsp;&nbsp;</td><td width="60" align="right">' . $osC_Currencies->format($Qslips->value('handling')) . '</td></tr>';
        $order_total .= '<tr><td align="right">' . $osC_Language->get("field_refund_total") . '&nbsp;&nbsp;&nbsp;</td><td width="60" align="right">' . $osC_Currencies->format($Qslips->value('refund_total')) . '</td></tr>';
        $order_total .= '</table>';        
        
        $records[] = array('orders_refunds_id' => $Qslips->valueInt('orders_refunds_id'),
                           'credit_slips_id' => $Qslips->valueInt('credit_slips_id'),
                           'orders_id' => $Qslips->valueInt('orders_id'),
                           'customers_name' => $osC_Order->getCustomer('name'),
                           'total_products' => $quantity,
                           'total_refund' => $osC_Currencies->format($Qslips->value('refund_total')),
                           'sub_total' => $osC_Currencies->format($Qslips->value('sub_total')),
                           'date_added' => osC_DateTime::getShort($Qslips->value('date_added')),
                           'shipping_address' => osC_Address::format($osC_Order->getDelivery(), '<br />'),
                           'shipping_method' => $osC_Order->getDeliverMethod(),
                           'billing_address' => osC_Address::format($osC_Order->getBilling(), '<br />'),
                           'payment_method' => $osC_Order->getPaymentMethod(),
                           'comments' => $Qslips->value('comments'), 
                           'products' => $products_table,
                           'totals' => $order_total);  
      }      
      
      $response = array(EXT_JSON_READER_TOTAL => $Qslips->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records); 
                        
      echo $toC_Json->encode($response);
    }
  }
?>
