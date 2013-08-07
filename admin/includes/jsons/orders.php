<?php
/*
  $Id: orders.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require_once('includes/classes/currencies.php');
  require_once('includes/classes/tax.php');
  require_once('includes/classes/order.php');
  require_once('includes/classes/customers.php');
  require_once('includes/classes/payment.php');
  require_once('includes/classes/shopping_cart_adapter.php');
  require_once('../includes/classes/products.php');
  require_once('includes/classes/shipping.php');
  require_once('../includes/classes/gift_certificates.php');
  require_once('includes/classes/orders_status.php');

  class toC_Json_Orders {
        
    function listOrders() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $osC_Tax = new osC_Tax_Admin();
      $osC_Currencies = new osC_Currencies_Admin();
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qorders = $osC_Database->query('select o.invoice_number, o.tracking_no, o.orders_id, o.customers_ip_address, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from :table_orders o, :table_orders_total ot, :table_orders_status s where o.orders_id = ot.orders_id and ot.class = "total" and o.orders_status = s.orders_status_id and s.language_id = :language_id ');
      
      if ( isset($_REQUEST['orders_id']) && is_numeric($_REQUEST['orders_id']) ) {
        $Qorders->appendQuery(' and o.orders_id = :orders_id ');
        $Qorders->bindInt(':orders_id', $_REQUEST['orders_id']);
      }
      
      if ( isset($_REQUEST['customers_id']) && is_numeric($_REQUEST['customers_id']) ) {
        $Qorders->appendQuery(' and o.customers_id = :customers_id ');
        $Qorders->bindInt(':customers_id', $_REQUEST['customers_id']);
      }
      
      if ( isset($_REQUEST['status']) && is_numeric($_REQUEST['status']) ) {
        $Qorders->appendQuery(' and s.orders_status_id = :orders_status_id ');
        $Qorders->bindInt(':orders_status_id', $_REQUEST['status']);
      }
      
      $Qorders->appendQuery('order by o.date_purchased desc, o.last_modified desc, o.orders_id desc');
      $Qorders->bindTable(':table_orders', TABLE_ORDERS);
      $Qorders->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
      $Qorders->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
      $Qorders->bindInt(':language_id', $osC_Language->getID());
      $Qorders->setExtBatchLimit($start, $limit);
      $Qorders->execute();
      
      $records = array();
      while ( $Qorders->next() ) {
        $osC_Order = new osC_Order($Qorders->valueInt('orders_id')); 
  
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
          
          if ( isset($product['customizations']) && !empty($product['customizations']) ) {
            $product_info .= '<p>';
              foreach ($product['customizations'] as $key => $customization) {
                $product_info .= '<div style="float: left">' . $customization['qty'] . ' x ' . '</div>';
                $product_info .= '<div style="margin-left: 25px">';
                  foreach ($customization['fields'] as $orders_products_customizations_values_id => $field) {
                    if ($field['customization_type'] == CUSTOMIZATION_FIELD_TYPE_INPUT_TEXT) {
                      $product_info .= $field['customization_fields_name'] . ': ' . $field['customization_value'] . '<br />';
                    } else {
                      $product_info .= $field['customization_fields_name'] . ': <a href="' . osc_href_link_admin(FILENAME_JSON, 'module=orders&action=download_customization_file&file=' . $field['customization_value'] . '&cache_file=' . $field['cache_filename']) . '&token=' . $_SESSION["token"] . '">' . $field['customization_value'] . '</a>' . '<br />';
                    }
                  }
                $product_info .= '</div>';
              }
            $product_info .= '</p>';
          }
          
          $products_table .= '<tr><td>' . $product_info . '</td><td width="60" valign="top" align="right">' . $osC_Currencies->displayPriceWithTaxRate($product['final_price'], $product['tax'], 1, $osC_Order->getCurrency(), $osC_Order->getCurrencyValue()) . '</td></tr>';
        }
        $products_table .= '</table>';
        
        $order_total = '<table width="100%">';
        foreach ( $osC_Order->getTotals() as $total ) {
          $order_total .= '<tr><td align="right">' . $total['title'] . '&nbsp;&nbsp;&nbsp;</td><td width="60" align="right">' . $total['text'] . '</td></tr>';
        }
        $order_total .= '</table>';
        
        $action = array();
        $invoice_number = $Qorders->value('invoice_number');
        if (empty($invoice_number)) {
          $action[] = array('class' => 'icon-order-pdf-record', 'qtip' => $osC_Language->get('tip_print_order'));
          $action[] = array('class' => 'icon-view-record', 'qtip' => $osC_Language->get('tip_view_order'));
          $action[] = array('class' => 'icon-invoice-record', 'qtip' => $osC_Language->get('tip_create_invoice'));                    
          $action[] = array('class' => 'icon-edit-record', 'qtip' => $osC_Language->get('tip_edit_order'));
          $action[] = array('class' => 'icon-delete-record', 'qtip' => $osC_Language->get('tip_delete_order'));
        } else {
          $action[] = array('class' => 'icon-order-pdf-record', 'qtip' => $osC_Language->get('tip_print_order'));
          $action[] = array('class' => 'icon-view-record', 'qtip' => $osC_Language->get('tip_view_order'));          
          $action[] = array('class' => 'icon-invoice-gray-record', 'qtip' => $osC_Language->get('tip_create_invoice'));
        }
        
        $records[] = array('orders_id' => $Qorders->valueInt('orders_id'),
                           'invoice' => (empty($invoice_number) ? '' : osc_image('images/invoices.png', $osC_Language->get('tip_invoice_number') . $invoice_number)),
                           'customers_name' => $Qorders->valueProtected('customers_name'),
                           'order_total' => strip_tags($Qorders->value('order_total')),
                           'date_purchased' => osC_DateTime::getShort($Qorders->value('date_purchased')),
                           'tracking_no' => $Qorders->value('tracking_no'),
                           'orders_status_name' => $Qorders->value('orders_status_name'),
                           'shipping_address' => osC_Address::format($osC_Order->getDelivery(), '<br />'),
                           'shipping_method' => $osC_Order->getDeliverMethod(),
                           'billing_address' => osC_Address::format($osC_Order->getBilling(), '<br />'),
                           'payment_method' => $osC_Order->getPaymentMethod(),
                           'products' => $products_table,
                           'totals' => $order_total,
                           'action' => $action);         
      }
      $Qorders->freeResult();
      
      $response = array(EXT_JSON_READER_TOTAL => $Qorders->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }          
    
    function deleteOrder() {
      global $toC_Json, $osC_Language, $osC_Currencies, $osC_Tax;
      
      $osC_Tax = new osC_Tax_Admin();
      $osC_Currencies = new osC_Currencies_Admin();
      
      if (osC_Order::delete($_REQUEST['orders_id'], (isset($_REQUEST['restock']) && ($_REQUEST['restock'] == 'on') ? true : false))) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
           
    function deleteOrders() {
      global $toC_Json, $osC_Language, $osC_Currencies, $osC_Tax;
      
      $osC_Tax = new osC_Tax_Admin();
      $osC_Currencies = new osC_Currencies_Admin();
     
      $error = false;
      
      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if (!osC_Order::delete($id, (isset($_REQUEST['restock']) && ($_REQUEST['restock'] == 'on') ? true : false))) {
          $error = true;
          break;
        }
      }      
       
      if ($error === false) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
       
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
                              
      $response['customers_comment'] = $osC_Order->getCustomersComment();
      $response['admin_comment'] = $osC_Order->getAdminComment();
      
      $response['total'] = '<p style="margin-left:10px;">' . $osC_Order->getTotal().'</p>' . 
                           '<p style="margin-left:10px;">' . 
                             $osC_Language->get('number_of_products') . ' ' . $osC_Order->getNumberOfProducts() . '<br />' . 
                             $osC_Language->get('number_of_items') . ' ' . $osC_Order->getNumberOfItems() . 
                           '</p>';
      
      echo $toC_Json->encode($response);
    }
    
    function createInvoice() {
      global $toC_Json, $osC_Database, $osC_Language;

      if ( osC_Order::createInvoice($_REQUEST['orders_id']) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function addCoupon() {
      global $toC_Json, $osC_Database, $osC_Language, $osC_ShoppingCart, $osC_Currencies, $osC_Weight, $osC_Tax, $osC_Customer;

      $osC_Tax = new osC_Tax_Admin();
      $osC_Weight = new osC_Weight();
      $osC_Currencies = new osC_Currencies();
      $osC_Language->load('checkout');
      
      $Qorder = $osC_Database->query('select * from :table_orders where orders_id = :orders_id');
      $Qorder->bindTable(':table_orders', TABLE_ORDERS);
      $Qorder->bindInt(':orders_id', $_REQUEST['orders_id']);
      $Qorder->execute();
      
      require_once('../includes/classes/customer.php');
      $osC_Customer = new osC_Customer();
      $osC_Customer->setID($Qorder->value('customers_id '));
      $osC_ShoppingCart = new toC_ShoppingCart_Adapter($_REQUEST['orders_id']);
      
      if ( isset($_REQUEST['coupon_code']) && !empty($_REQUEST['coupon_code']) && (count($osC_ShoppingCart->getProducts()) != 0) ) {
      
        require_once('../includes/classes/coupon.php');
        $toC_Coupon = new toC_Coupon($_REQUEST['coupon_code']);
  
        $errors = array();
  
        if(!$toC_Coupon->isExist()){
          $errors[] = $osC_Language->get('error_coupon_not_exist');
        } else if(!$toC_Coupon->isValid()){
          $errors[] = $osC_Language->get('error_coupon_not_valid');
        } else if(!$toC_Coupon->isDateValid()){
          $errors[] = $osC_Language->get('error_coupon_invalid_date');
        } else if(!$toC_Coupon->isUsesPerCouponValid()){
          $errors[] = $osC_Language->get('error_coupon_exceed_uses_per_coupon');
        } else if(!$toC_Coupon->isUsesPerCustomerValid()){
          $errors[] = $osC_Language->get('error_coupon_exceed_uses_per_customer');
        } else if($toC_Coupon->hasRestrictCategories() || $toC_Coupon->hasRestrictProducts()){
          if(!$toC_Coupon->containRestrictProducts()){
            $errors[] = $osC_Language->get('error_coupon_no_match_products');
          }
        } else if(!$toC_Coupon->checkMinimumOrderQuantity()){
          $errors[] = $osC_Language->get('error_coupon_minimum_order_quantity');
        } else if($osC_ShoppingCart->isTotalZero()){
          $errors[] = $osC_Language->get('error_shopping_cart_total_zero');
        }
  
        if(sizeof($errors) == 0){
          $osC_ShoppingCart->setCouponCode($_REQUEST['coupon_code']);
          
          $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false ,'feedback' => implode('<br />', $errors));
        }      
      } else {
        $response = array('success' => false ,'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }       
      
      echo $toC_Json->encode($response);
    }
    
    function deleteCoupon() {
      global $toC_Json, $osC_Database, $osC_Language, $osC_ShoppingCart, $osC_Currencies, $osC_Weight, $osC_Tax;

      $osC_Tax = new osC_Tax_Admin();
      $osC_Weight = new osC_Weight();
      $osC_Currencies = new osC_Currencies();
      
      $osC_ShoppingCart = new toC_ShoppingCart_Adapter($_REQUEST['orders_id']);
      $osC_ShoppingCart->deleteCoupon();
      $osC_ShoppingCart->updateOrderTotal();
      
      $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      
      echo $toC_Json->encode($response);
    }
    
    function addGiftCertificate() {
      global $toC_Json, $osC_Language, $osC_ShoppingCart, $osC_Currencies, $osC_Weight, $osC_Tax;

      $osC_Tax = new osC_Tax_Admin();
      $osC_Weight = new osC_Weight();
      $osC_Currencies = new osC_Currencies();
      $osC_Language->load('checkout');
      $osC_ShoppingCart = new toC_ShoppingCart_Adapter($_REQUEST['orders_id']);
      $errors = array();
      
      if ($osC_ShoppingCart->isTotalZero()) {
        $errors[] = $osC_Language->get('error_shopping_cart_total_zero');
      }
      
      if ($osC_ShoppingCart->containsGiftCertifcate($_REQUEST['gift_certificate_code'])) {
        $errors[] = $osC_Language->get('error_gift_certificate_exist');
      }
      
      if (!toC_Gift_Certificates::isGiftCertificateValid($_REQUEST['gift_certificate_code'])) {
        $errors[] = $osC_Language->get('error_invalid_gift_certificate');
      }
      
      if(sizeof($errors) == 0){
        $osC_ShoppingCart->addGiftCertificateCode($_REQUEST['gift_certificate_code']);
        
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $errors);   
      }

      echo $toC_Json->encode($response);
    }

    function deleteGiftCertificate() {
     global $toC_Json, $osC_Language, $osC_ShoppingCart, $osC_Currencies, $osC_Weight, $osC_Tax;

      $osC_Tax = new osC_Tax_Admin();
      $osC_Weight = new osC_Weight();
      $osC_Currencies = new osC_Currencies();
      
      $osC_ShoppingCart = new toC_ShoppingCart_Adapter($_REQUEST['orders_id']);
      $osC_ShoppingCart->deleteGiftCertificate($_REQUEST['gift_certificate_code']);
      $osC_ShoppingCart->updateOrderTotal();
      
      $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      
      echo $toC_Json->encode($response);
    }
    
    function listGiftCertificates() {
      global $toC_Json;
      
      $osC_Order = new osC_Order(isset($_REQUEST['orders_id']) ? $_REQUEST['orders_id'] : null);
      
      $records = array();
      foreach ( $osC_Order->_gift_certificate_codes as $code ) {
        $records[] = array('gift_code' => $code);
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response); 
    }
    
    function listOrderProducts(){
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

        if ( isset($product['customizations']) && !empty($product['customizations']) ) {
          $product_info .= '<p>';
            foreach ($product['customizations'] as $key => $customization) {
              $product_info .= '<div style="float: left">' . $customization['qty'] . ' x ' . '</div>';
              $product_info .= '<div style="margin-left: 25px">';
                foreach ($customization['fields'] as $orders_products_customizations_values_id => $field) {
                  if ($field['customization_type'] == CUSTOMIZATION_FIELD_TYPE_INPUT_TEXT) {
                    $product_info .= $field['customization_fields_name'] . ': ' . $field['customization_value'] . '<br />';
                  } else {
                    $product_info .= $field['customization_fields_name'] . ': <a href="' . osc_href_link_admin(FILENAME_JSON, 'module=orders&action=download_customization_file&file=' . $field['customization_value'] . '&cache_file=' . $field['cache_filename']) . '&token=' . $_SESSION["token"] . '">' . $field['customization_value'] . '</a>' . '<br />';
                  }
                }
              $product_info .= '</div>';
            }
          $product_info .= '</p>';
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
                           'sku' => $product['sku'],
                           'tax' => $osC_Tax->displayTaxRateValue($product['tax']), 
                           'price_net' =>$osC_Currencies->format($product['final_price'], $osC_Order->getCurrency(), $osC_Order->getCurrencyValue()), 
                           'price_gross' => $osC_Currencies->displayPriceWithTaxRate($product['final_price'], $product['tax'], 1, $osC_Order->getCurrency(), $osC_Order->getCurrencyValue()), 
                           'total_net' => $osC_Currencies->format($product['final_price'] * $product['quantity'], $osC_Order->getCurrency(), $osC_Order->getCurrencyValue()), 
                           'total_gross' => $osC_Currencies->displayPriceWithTaxRate($product['final_price'], $product['tax'], $product['quantity'], $osC_Order->getCurrency(), $osC_Order->getCurrencyValue()));
      }
      
      foreach ( $osC_Order->getTotals() as $totals ) {
        $records[] = array('products' => '', 
                           'sku' => '', 
                           'tax' => '', 
                           'price_net' => '', 
                           'price_gross' => $totals['title'], 
                           'total_net' => '', 
                           'total_gross' => $totals['text']);
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }
    
    function changeCurrency() {
      global $toC_Json, $osC_Database, $osC_Language, $osC_Tax, $osC_Weight, $osC_ShoppingCart, $osC_Currencies;
      
      $osC_Tax = new osC_Tax_Admin();
      $osC_Weight = new osC_Weight();
      $osC_Currencies = new osC_Currencies();

      $currency = $_REQUEST['currency'];
      $currency_value = 1;
      
      foreach ($osC_Currencies->currencies as $key => $value) {
        if ($key == $currency) {
          $currency_value = $value['value'];
          break;
        }
      }

      if (osC_Order::updateCurrency($_REQUEST['orders_id'], $_REQUEST['currency'], $currency_value)) {
        $osC_ShoppingCart = new toC_ShoppingCart_Adapter($_REQUEST['orders_id']);
        $osC_ShoppingCart->_calculate();
        $osC_ShoppingCart->updateOrderTotal();
      
        $response = array('success' => true , 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed')); 
      }
      
      echo $toC_Json->encode($response);
    }
    
    function listOrdersEditProducts() {
      global $toC_Json, $osC_Database, $osC_Language, $osC_ShoppingCart, $osC_Currencies, $osC_Weight, $osC_Tax;

      $osC_Tax = new osC_Tax_Admin();
      $osC_Weight = new osC_Weight();
      $osC_Currencies = new osC_Currencies();
      
      $osC_Order = new osC_Order($_REQUEST['orders_id']);

      $records = array();
      foreach ( $osC_Order->getProducts() as $products_id_string => $product ) {
        $product_info = $product['name'];
        
        if ( isset($product['variants']) && is_array($product['variants']) && ( sizeof($product['variants']) > 0 ) ) {
          foreach ( $product['variants'] as $variants ) {
            $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $variants['groups_name'] . ': ' . $variants['values_name'] . '</i></nobr>';
          }
        }

        if ( isset($product['customizations']) && !empty($product['customizations']) ) {
          $product_info .= '<p>';
            foreach ($product['customizations'] as $key => $customization) {
              $product_info .= '<div style="float: left">' . $customization['qty'] . ' x ' . '</div>';
              $product_info .= '<div style="margin-left: 25px">';
                foreach ($customization['fields'] as $orders_products_customizations_values_id => $field) {
                  if ($field['customization_type'] == CUSTOMIZATION_FIELD_TYPE_INPUT_TEXT) {
                    $product_info .= $field['customization_fields_name'] . ': ' . $field['customization_value'] . '<br />';
                  } else {
                    $product_info .= $field['customization_fields_name'] . ': <a href="' . osc_href_link_admin(FILENAME_JSON, 'module=orders&action=download_customization_file&file=' . $field['customization_value'] . '&cache_file=' . $field['cache_filename']) . '">' . $field['customization_value'] . '</a>' . '<br />';
                  }
                }
              $product_info .= '</div>';
            }
          $product_info .= '</p>';
        }
        
        if ( $product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE ) {
          $product_info .= '&nbsp;(' . $product['gift_certificates_code'] . ')';  
        
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
  
        $osC_Product = new osC_Product($product['id'], $osC_Order->getCustomer('customers_id'));
        $records[] = array('orders_products_id' => $product['orders_products_id'],
                           'products_id' => $product['id'],
                           'products_type' => $product['type'],
                           'products' => $product_info, 
                           'quantity' => ($product['quantity'] > 0) ? $product['quantity'] : '',
                           'qty_in_stock' => $osC_Product->getQuantity($products_id_string),
                           'sku' => $product['sku'],
                           'tax' => $osC_Tax->displayTaxRateValue($product['tax']), 
                           'price_net' => round($product['final_price'] * $osC_Order->getCurrencyValue(), 2),
                           'price_gross' => $osC_Currencies->displayPriceWithTaxRate($product['final_price'], $product['tax'], 1, $osC_Order->getCurrency(), $osC_Order->getCurrencyValue()), 
                           'total_net' => $osC_Currencies->format($product['final_price'] * $product['quantity'], $osC_Order->getCurrency(), $osC_Order->getCurrencyValue()), 
                           'total_gross' => $osC_Currencies->displayPriceWithTaxRate($product['final_price'], $product['tax'], $product['quantity'], $osC_Order->getCurrency(), $osC_Order->getCurrencyValue()),
                           'action' => array('class' => 'icon-delete-record', 'qtip' => ''));
      }
      
      $order_totals = '<table cellspacing="5" cellpadding="5" width="300" border="0">';
      foreach ( $osC_Order->getTotals() as $totals ) {
        $order_totals .= '<tr><td align="right">' . $totals['title'] . '&nbsp;&nbsp;&nbsp;&nbsp;</td><td width="60">' . $totals['text'] . '</td></tr>';
      }
      $order_totals .= '</table>';
      
      $response = array(EXT_JSON_READER_ROOT => $records, 'totals' => $order_totals, 'shipping_method' => $osC_Order->getDeliverMethod());
     
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
                         'orders_status_history_id' => $status_history['orders_status_history_id'], 
                         'status' => $status_history['status'], 
                         'comments' => nl2br($status_history['comment']), 
                         'customer_notified' => osc_icon((($status_history['customer_notified'] === 1) ? 'checkbox_ticked.gif' : 'checkbox_crossed.gif')));
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response);
    }
    
    function getCurrentStatus() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $osC_Order = new osC_Order($_REQUEST['orders_id']);
      
      $status_id = intval($osC_Order->getStatusID());
      $response = array();
      $response = array('status_id' => $status_id);
      
      echo $toC_Json->encode($response);
    }
    
    function getStatus() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $Qstatus = $osC_Database->query('select orders_status_id, orders_status_name from :table_orders_status where language_id = :language_id');
      $Qstatus->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
      $Qstatus->bindInt(':language_id', $osC_Language->getID());
      $Qstatus->execute();
      
      $records = array();
      if (isset($_REQUEST['top']) && ($_REQUEST['top'] == '1')) {
        $records[] = array('status_id' => '', 'status_name' => $osC_Language->get('all_status'));
      }
      
      while($Qstatus->next()) {
         $records[] = array('status_id' => $Qstatus->valueInt('orders_status_id'), 'status_name' => $Qstatus->value('orders_status_name'));
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response);
    
    }
    
    function _updateStatus($id, $data) {
      global $osC_Database, $osC_Language, $orders_status_array;

      $error = false;

      $osC_Database->startTransaction();
      
      $orders_status = osC_OrdersStatus_Admin::getData($data['status_id']);

      if ($orders_status['downloads_flag'] == 1) {
        osC_Order::activeDownloadables($id);
      }
      
      if ($orders_status['gift_certificates_flag'] == 1) {
        osC_Order::activeGiftCertificates($id);
      }

      if (($data['status_id'] == ORDERS_STATUS_CANCELLED) && ($data['restock_products'] == true)) {
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

      $Qupdate = $osC_Database->query('update :table_orders set orders_status = :orders_status, last_modified = now() where orders_id = :orders_id');
      $Qupdate->bindTable(':table_orders', TABLE_ORDERS);
      $Qupdate->bindInt(':orders_status', $data['status_id']);
      $Qupdate->bindInt(':orders_id', $id);
      $Qupdate->setLogging($_SESSION['module'], $id);
      $Qupdate->execute();

      if (!$osC_Database->isError()) {
        $Qupdate = $osC_Database->query('insert into :table_orders_status_history (orders_id, orders_status_id, date_added, customer_notified, comments) values (:orders_id, :orders_status_id, now(), :customer_notified, :comments)');
        $Qupdate->bindTable(':table_orders_status_history', TABLE_ORDERS_STATUS_HISTORY);
        $Qupdate->bindInt(':orders_id', $id);
        $Qupdate->bindInt(':orders_status_id', $data['status_id']);
        $Qupdate->bindInt(':customer_notified', ( $data['notify_customer'] === true ? '1' : '0'));
        $Qupdate->bindValue(':comments', $data['comment']);
        $Qupdate->setLogging($_SESSION['module'], $id);
        $Qupdate->execute();

        if ($osC_Database->isError()) {
          $error = true;
        }
        
        if ($data['notify_customer'] === true) {
          $Qorder = $osC_Database->query('select o.customers_name, o.customers_email_address, s.orders_status_name, o.date_purchased from :table_orders o, :table_orders_status s where o.orders_status = s.orders_status_id and s.language_id = :language_id and o.orders_id = :orders_id');
          $Qorder->bindTable(':table_orders', TABLE_ORDERS);
          $Qorder->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
          $Qorder->bindInt(':language_id', $osC_Language->getID());
          $Qorder->bindInt(':orders_id', $id);
          $Qorder->execute();
      
          require_once('../includes/classes/email_template.php');
          $email_template = toC_Email_Template::getEmailTemplate('admin_order_status_updated');
          $email_template->setData($id, osc_href_link(FILENAME_ACCOUNT, 'orders=' . $id, 'SSL', false, true, true), osC_DateTime::getLong($Qorder->value('date_purchased')), $data['append_comment'], $data['comment'], $Qorder->value('orders_status_name'), $Qorder->value('customers_name'), $Qorder->value('customers_email_address'));
          $email_template->buildMessage();
          $email_template->sendEmail();
        }
      } else {
        $error = true;
      }

      if ($error === false) {
        $osC_Database->commitTransaction();

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }
    
    function updateOrdersStatus() {
      global $toC_Json, $osC_Language;

      $data = array( 'status_id' =>  $_REQUEST['status'],
                     'comment' => $_REQUEST['comment'],
                     'restock_products' => ( isset($_REQUEST['restock_products']) && ( $_REQUEST['restock_products'] == '1') ? true : false ),
                     'notify_customer' => ( isset($_REQUEST['notify_customer']) && ( $_REQUEST['notify_customer'] == 'on') ? true : false ),
                     'append_comment' => ( isset($_REQUEST['notify_with_comments']) && ( $_REQUEST['notify_with_comments'] == 'on') ? true : false ));       
        
        if ( self::_updateStatus($_REQUEST['orders_id'], $data)) {
          $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
        }
        else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed')); 
        }
      
      echo $toC_Json->encode($response);
    }
    
    function printPdf() {
      require_once('includes/classes/print_pdf.php');
      $osC_Print = new osC_Print_Pdf();
      $osC_Print->producePdf($_REQUEST['oID']);
    }
    
    function invoice() {
      global $osC_Language, $osC_Tax;
      
      $osC_Tax = new osC_Tax_Admin();
      $osC_Currencies = new osC_Currencies_Admin();
      

      header('Content-Type: text/html');
      require_once('includes/modules/invoice.php');
      
      exit;
    }
    
    function packagingSlip() {
      global $osC_Language, $osC_Tax;
      
      $osC_Tax = new osC_Tax_Admin();
      $osC_Currencies = new osC_Currencies_Admin();
      

      header('Content-Type: text/html');
      require_once('includes/modules/packaging_slip.php');
      
      exit;
    }
    
    function updateSku() {
      global $toC_Json, $osC_Language;
      
      if (osC_Order::updateProductSKU($_REQUEST['orders_id'], $_REQUEST['orders_products_id'] , $_REQUEST['products_sku'])) {
        $response = array('success' => true , 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed')); 
      }
      
      echo $toC_Json->encode($response);
    }
    
    function updatePrice() {
      global $toC_Json, $osC_Language, $osC_Tax, $osC_Weight, $osC_Currencies, $osC_ShoppingCart;
      
      $error = false;
      $feedback = array();
      
      $osC_ShoppingCart = new toC_ShoppingCart_Adapter($_REQUEST['orders_id']);
      $osC_Tax = new osC_Tax_Admin();
      $osC_Weight = new osC_Weight();
      $osC_Currencies = new osC_Currencies();
      
      if ($osC_ShoppingCart->updateProductPrice($_REQUEST['orders_products_id'], $_REQUEST['price'])) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed')); 
      }

      echo $toC_Json->encode($response); 
    }
    
    function updateQuantity() {
      global $toC_Json, $osC_Language, $osC_Tax, $osC_Weight, $osC_Currencies, $osC_ShoppingCart;
      
      $error = false;
      $feedback = array();
      
      $osC_ShoppingCart = new toC_ShoppingCart_Adapter($_REQUEST['orders_id']);
      $osC_Tax = new osC_Tax_Admin();
      $osC_Weight = new osC_Weight();
      $osC_Currencies = new osC_Currencies();
      
      if ($osC_ShoppingCart->updateProductQuantity($_REQUEST['orders_products_id'], $_REQUEST['quantity'])) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed')); 
      }

      echo $toC_Json->encode($response); 
    }
    
    function getCustomerAddresses() {
      global $toC_Json, $osC_Language;
      
      $osC_Order = new osC_Order($_REQUEST['orders_id']);
      $Qaddresses = osC_Customers_Admin::getAddressBookData($osC_Order->getCustomersID());
      
      $records = array(array('id' => '0', 'text' => $osC_Language->get('add_new_address')));
      while ( $Qaddresses->next() ) {
        $records[] = array('id' => $Qaddresses->valueInt('address_book_id'),
                           'text' => $Qaddresses->value('firstname') . ' ' . $Qaddresses->value('lastname') . ',' . $Qaddresses->value('company') . ',' . $Qaddresses->value('street_address') . ',' . $Qaddresses->value('suburb') . ',' . $Qaddresses->value('city') . ',' . $Qaddresses->value('postcode') . ',' . $Qaddresses->value('state') . ',' . $Qaddresses->value('country_title'));
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records); 
                  
      echo $toC_Json->encode($response); 
    }
    
    function listCurrencies() {
      global $toC_Json;
      
      $osC_Currencies = new osC_Currencies();

      $records = array();
      foreach ($osC_Currencies->currencies as $key => $value) {
        $records[] = array(
          'id' => $key, 
          'text' => $value['title'], 
          'symbol_left' => $value['symbol_left'],
          'symbol_right' => $value['symbol_right'],
          'decimal_places' => $value['decimal_places']);
      }
  
      $response = array(EXT_JSON_READER_ROOT => $records); 
                  
      echo $toC_Json->encode($response); 
    }
    
    function listPaymentMethods() {
      global $toC_Json;
      
      $records = array();
      foreach (osC_Payment_Admin::getInstalledModules() as $key => $value) {
        $records[] = array('id' => $key, 'text' => $value);
      }
  
      $response = array(EXT_JSON_READER_ROOT => $records); 
                  
      echo $toC_Json->encode($response); 
    }
    
    function listCountries() {
      global $toC_Json, $osC_Database, $osC_Language;
     
      $Qentries = $osC_Database->query('select countries_name,countries_id from :table_countries');
      $Qentries->bindTable(':table_countries',TABLE_COUNTRIES);
      $Qentries->execute(); 
      
      while ($Qentries->next()) {
        $records[] = array('countries_id' => $Qentries->value('countries_id'),
                           'countries_name' => $Qentries->value('countries_name'));
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records); 
      
      echo $toC_Json->encode($response); 
    }
    
    
    function listZones() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      if(isset($_REQUEST['countries_id'])){
        $Qentries = $osC_Database->query('select zone_id, zone_code, zone_name from :table_zone where zone_country_id=:country_id');
        $Qentries->bindTable(':table_zone',TABLE_ZONES);
        $Qentries->bindInt(':country_id', $_REQUEST['countries_id']);
        $Qentries->execute(); 
        
        $records = array();
        while ($Qentries->next()) {
          $records[] = array('zone_id' => $Qentries->valueInt('zone_id'),
                             'zone_code' => $Qentries->value('zone_code'),
                             'zone_name' => $Qentries->value('zone_name'));
        }
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records); 
                  
      echo $toC_Json->encode($response); 
    }
    
    function listCustomers() {
      global $toC_Json, $osC_Database;
      
      $osC_Currencies = new osC_Currencies_Admin();
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qcustomers = $osC_Database->query('select * from :table_customers');
      
      if ( isset($_REQUEST['filter']) && !empty($_REQUEST['filter']) ) {
        $Qcustomers->appendQuery(' where customers_firstname like :customers_firstname or customers_lastname like :customers_lastname');
        $Qcustomers->bindValue(':customers_firstname', '%' . $_REQUEST['filter'] . '%');
        $Qcustomers->bindValue(':customers_lastname', '%' . $_REQUEST['filter'] . '%');
      }
      
      $Qcustomers->bindTable(':table_customers',TABLE_CUSTOMERS);
      $Qcustomers->setExtBatchLimit($start, $limit);
      $Qcustomers->execute();
      
      $records = array();
      while ($Qcustomers->next()) {

        switch ( $Qcustomers->value('customers_gender') ) {
          case 'm': 
            $customer_icon = osc_icon('user_male.png'); 
            break;
          case 'f': 
            $customer_icon = osc_icon('user_female.png'); 
            break;
          default:
            $customer_icon = osc_icon('people.png');
        }
      
        $records[] = array('customers_id' => $Qcustomers->value('customers_id'),
                           'customers_firstname' => $Qcustomers->value('customers_firstname'),
                           'customers_lastname' => $Qcustomers->value('customers_lastname'),
                           'customers_email_address' => $Qcustomers->value('customers_email_address'),
                           'customers_gender' => $customer_icon,
                           'customers_credits' => $osC_Currencies->format($Qcustomers->value('customers_credits')));
      }

      $response = array(EXT_JSON_READER_TOTAL => $Qcustomers->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records); 
                  
      echo $toC_Json->encode($response);    
    }
    
    function createOrder() {
      global $toC_Json, $osC_Language, $osC_Currencies;
      
      $osC_Currencies = new osC_Currencies();
      
      $data = array('customers_id' => $_REQUEST['customers_id'], 
                    'customers_name' => $_REQUEST['customers_firstname'] . ',' . $_REQUEST['customers_lastname'],
                    'customers_email_address' => $_REQUEST['customers_email_address']);
      
      $orders_id =  osC_Order::createOrder($data);

      if ($orders_id > 0) {
        $response = array('success' => true ,'orders_id' => $orders_id, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed')); 
      }
      
      echo $toC_Json->encode($response);
    }
    
    
    function saveAddress() {
      global $toC_Json, $osC_Database, $osC_Language, $osC_ShoppingCart, $osC_Currencies, $osC_Weight, $osC_Tax;

      $osC_Tax = new osC_Tax_Admin();
      $osC_Weight = new osC_Weight();
      $osC_Currencies = new osC_Currencies();
      $osC_ShoppingCart = new toC_ShoppingCart_Adapter($_REQUEST['orders_id']);

      $data['orders_id'] = $_REQUEST['orders_id'];
      
      $data['billing_name'] = $_REQUEST['billing_name'];
      $data['billing_company'] = $_REQUEST['billing_company'];
      $data['billing_street_address'] = $_REQUEST['billing_street_address'];
      $data['billing_suburb'] = $_REQUEST['billing_suburb'];
      $data['billing_city'] = $_REQUEST['billing_city'];
      $data['billing_postcode'] = $_REQUEST['billing_postcode'];
      $data['billing_state'] = $_REQUEST['billing_state'];
      $data['billing_zone_id'] = $_REQUEST['billing_zone_id'];
      $data['billing_state_code'] = $_REQUEST['billing_state_code'];
      $data['billing_country_id'] = $_REQUEST['billing_countries_id'];
      $data['billing_country'] = $_REQUEST['billing_countries'];

      $data['delivery_name'] = $_REQUEST['shipping_name'];
      $data['delivery_company'] = $_REQUEST['shipping_company'];
      $data['delivery_street_address'] = $_REQUEST['shipping_street_address'];
      $data['delivery_suburb'] = $_REQUEST['shipping_suburb'];
      $data['delivery_city'] = $_REQUEST['shipping_city'];
      $data['delivery_postcode'] = $_REQUEST['shipping_postcode'];
      $data['delivery_state'] = $_REQUEST['shipping_state'];
      $data['delivery_zone_id'] = $_REQUEST['shipping_zone_id'];
      $data['delivery_state_code'] = $_REQUEST['shipping_state_code'];
      $data['delivery_country_id'] = $_REQUEST['shipping_countries_id'];
      $data['delivery_country'] = $_REQUEST['shipping_countries'];

      if ($osC_ShoppingCart->updateOrderInfo($data) === true) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed')); 
      }
      
      echo $toC_Json->encode($response);
    }
    
    function loadOrder() {
      global $toC_Json;
      
      $osC_Order = new osC_Order(isset($_REQUEST['orders_id']) ? $_REQUEST['orders_id'] : null);
      
      require_once('../includes/classes/customer.php');
      $osC_Customer = new osC_Customer();
      $osC_Customer->setCustomerData($osC_Order->getCustomersID());

      $enable_store_credit = false;
      if ($osC_Order->isUseStoreCredit() || $osC_Customer->hasStoreCredit()) {
        $enable_store_credit = true;
      }
      
      $data = array('customers_name' => $osC_Order->_customer['name'],
                    'currency' => $osC_Order->getCurrency(),
                    'email_address' => $osC_Order->_customer['email_address'],
                    'coupon_code' => $osC_Order->_coupon_code,
                    'payment_method' => $osC_Order->getPaymentModule(),
                    'use_store_credit' => $osC_Order->isUseStoreCredit(),
                    'has_payment_method' => $osC_Order->hasPaymentMethod(),
                    'enable_store_credit' => $enable_store_credit,
                    'gift_wrapping' => $osC_Order->_customer['gift_wrapping'] == '1' ? true : false,
                    'wrapping_message' => $osC_Order->_customer['wrapping_message'],
                    'billing_address' => str_replace(',', ' ', $osC_Order->getBilling('name')) . ',' .
                                        $osC_Order->getBilling('company'). ',' .
                                        $osC_Order->getBilling('street_address'). ',' .
                                        $osC_Order->getBilling('suburb'). ',' .
                                        $osC_Order->getBilling('city'). ',' .
                                        $osC_Order->getBilling('postcode'). ',' .
                                        $osC_Order->getBilling('state'). ',' .
                                        $osC_Order->getBilling('country_title'),
                    'shipping_address' => str_replace(',', ' ', $osC_Order->getDelivery('name')) . ',' .
                                        $osC_Order->getDelivery('company'). ',' .
                                        $osC_Order->getDelivery('street_address'). ',' .
                                        $osC_Order->getDelivery('suburb'). ',' .
                                        $osC_Order->getDelivery('city'). ',' .
                                        $osC_Order->getDelivery('postcode'). ',' .
                                        $osC_Order->getDelivery('state'). ',' .
                                        $osC_Order->getDelivery('country_title'));
      
      unset($_SESSION['osC_Customer_data']);
      
      $response = array('success' => true, 'data' => $data); 
      
      echo $toC_Json->encode($response); 
    }
    
    function listChooseProducts() {
      global $toC_Json, $osC_Database, $osC_Language, $osC_Currencies, $osC_Tax;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qproducts = $osC_Database->query('select SQL_CALC_FOUND_ROWS * from :table_products p left join :table_products_description pd on p.products_id = pd.products_id where p.products_status = 1 and pd.language_id = :language_id and p.products_status = 1');
      
      if ( !empty($_REQUEST['search']) ) {
        $Qproducts->appendQuery('and pd.products_name  like :products_name');
        $Qproducts->bindValue(':products_name', '%' . $_REQUEST['search'] . '%');
      }
      
      $Qproducts->appendQuery('order by p.products_id ');
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindInt(':language_id', $osC_Language->getID());
      $Qproducts->setExtBatchLimit($start, $limit);
      $Qproducts->execute();
      
      $osC_Currencies = new osC_Currencies();
      $osC_Order = new osC_Order($_REQUEST['orders_id']);
      $osC_Tax = new osC_Tax_Admin();
      
      $_SESSION['currency'] = $osC_Order->getCurrency();
      
      $records = array();
      while ($Qproducts->next()) {
        $products_id = $Qproducts->valueInt('products_id');
        $osC_Product = new osC_Product($products_id, $osC_Order->getCustomer('customers_id'));

        if (!$osC_Product->hasVariants()) {
          $products_name = $osC_Product->getTitle();
          $products_price = $osC_Product->getPriceFormated();
          
          if ( $osC_Product->isGiftCertificate() ) {
            $products_name .= '<table cellspacing="0" cellpadding="0" border="0">';
            
            if($osC_Product->isOpenAmountGiftCertificate()) {
              $products_name .= '<tr><td><i>--&nbsp;&nbsp;' . $osC_Language->get('field_amount') . '</i></td><td><input id="' . $products_id . '_price'. '" type="text" class="x-form-text x-form-field x-form-empty-field" style="width: 140px" value="' . round($osC_Product->getOpenAmountMinValue() * $osC_Order->getCurrencyValue(), 2) . '"/></td></tr>';
            }
            
            if($osC_Product->isEmailGiftCertificate()) {
              $products_name .= '<tr><td><i>--&nbsp;&nbsp;' . $osC_Language->get('field_recipient_sender_name') . '</i></td><td><input id="' . $products_id . '_sender_name'. '" type="text" class="x-form-text x-form-field x-form-empty-field" style="width: 140px" /></td></tr>' .
                                '<tr><td><i>--&nbsp;&nbsp;' . $osC_Language->get('field_recipient_sender_email') . '</i></td><td><input id="' . $products_id . '_sender_email'. '" type="text" class="x-form-text x-form-field x-form-empty-field" style="width: 140px" /></td></tr>' .
                                '<tr><td><i>--&nbsp;&nbsp;' . $osC_Language->get('field_recipient_name') . '</i></td><td><input id="' . $products_id . '_recipient_name'. '" type="text" class="x-form-text x-form-field x-form-empty-field" style="width: 140px" /></td></tr>' .
                                '<tr><td><i>--&nbsp;&nbsp;' . $osC_Language->get('field_recipient_email') . '</i></td><td><input id="' . $products_id . '_recipient_email'. '" type="text" class="x-form-text x-form-field x-form-empty-field" style="width: 140px" /></td></tr>' . 
                                '<tr><td><i>--&nbsp;&nbsp;' . $osC_Language->get('field_message') . '</i></td><td><textarea id="' . $products_id . '_message'. '" class=" x-form-textarea x-form-field" style="width: 140px" /></textarea></td></tr>';
            } else if($osC_Product->isPhysicalGiftCertificate()) {
              $products_name .= '<tr><td><i>--&nbsp;&nbsp;' . $osC_Language->get('field_recipient_sender_name') . '</i></td><td><input id="' . $products_id . '_sender_name'. '" type="text" class="x-form-text x-form-field x-form-empty-field" style="width: 140px" /></td></tr>' .
                                '<tr><td><i>--&nbsp;&nbsp;' . $osC_Language->get('field_recipient_name') . '</i></td><td><input id="' . $products_id . '_recipient_name'. '" type="text" class="x-form-text x-form-field x-form-empty-field" style="width: 140px" /></td></tr>' .
                                '<tr><td><i>--&nbsp;&nbsp;' . $osC_Language->get('field_message') . '</i></td><td><textarea id="' . $products_id . '_message'. '" class=" x-form-textarea x-form-field" style="width: 140px" /></textarea></td></tr>';
            }
          
            $products_name .= '</table>';
          }

          $records[] = array('products_id' => $products_id,
                             'products_name' => $products_name,
                             'products_type' => $osC_Product->getProductType(),
                             'products_sku' => $osC_Product->getSKU(),
                             'products_price' => $products_price,
                             'products_quantity' => $osC_Product->getQuantity(),
                             'new_qty' => $Qproducts->valueInt('products_moq'),
                             'has_variants' => false);
        } else {
          $records[] = array('products_id' => $products_id,
                             'products_name' => $osC_Product->getTitle(),
                             'products_type' => $osC_Product->getProductType(),
                             'products_sku' => $osC_Product->getSKU(),
                             'products_price' => $osC_Product->getPriceFormated(),
                             'products_quantity' => $osC_Product->getQuantity(),
                             'new_qty' => $Qproducts->valueInt('products_moq'),
                             'has_variants' => true);
          
          foreach ($osC_Product->getVariants() as $product_id_string => $details) {
            $variants = '';
            
            foreach($details['groups_name'] as $groups_name => $values_name){
              $variants .= '&nbsp;&nbsp;&nbsp;<i>' . $groups_name . ' : ' . $values_name . '</i><br />';
            }

            $records[] = array('products_id' => $product_id_string,
                               'products_name' => $variants,
                               'products_type' => $osC_Product->getProductType(),
                               'products_sku' => $osC_Product->getSKU(osc_parse_variants_from_id_string($product_id_string)),
                               'products_price' => $osC_Currencies->format($osC_Product->getPrice(osc_parse_variants_from_id_string($product_id_string)), $osC_Order->getCurrency()),
                               'products_quantity' => $details['quantity'],
                               'new_qty' => $Qproducts->valueInt('products_moq'),
                               'has_variants' => false);
          }
        }
      }
      unset($_SESSION['currency']);
      
      $response = array(EXT_JSON_READER_TOTAL => $Qproducts->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response); 
    }
    
    function addProduct() {
      global $toC_Json, $osC_Language, $osC_Tax, $osC_Weight, $osC_Currencies, $osC_ShoppingCart;
      
      $error = false;
      $feedback = array();
      
      $osC_ShoppingCart = new toC_ShoppingCart_Adapter($_REQUEST['orders_id']);
      $osC_Tax = new osC_Tax_Admin();
      $osC_Weight = new osC_Weight();
      $osC_Currencies = new osC_Currencies();
      
      $osC_Product = new osC_Product(osc_get_product_id($_REQUEST['products_id']));
      $gift_certificate_data = null;
      
      if ( $osC_Product->isGiftCertificate() ) {
        if (!isset($_REQUEST['senders_name']) || empty($_REQUEST['senders_name'])) {
          $error = true;
          $feedback[] = $osC_Language->get('error_sender_name_empty');
        }
        
        if (!isset($_REQUEST['recipients_name']) || empty($_REQUEST['recipients_name'])) {
          $error = true;
          $feedback[] = $osC_Language->get('error_recipients_name_empty');
        }
        
        if (!isset($_REQUEST['message']) || empty($_REQUEST['message'])) {
         $error = true;
         $feedback[] = $osC_Language->get('error_message_empty');
        }
              
        if ($osC_Product->isEmailGiftCertificate()) {
          if (!isset($_REQUEST['senders_email']) || empty($_REQUEST['senders_email'])) {
            $error = true;
            $feedback[] = $osC_Language->get('error_sender_email_empty');
          } 
          
          if ( !osc_validate_email_address($_REQUEST['senders_email']) ) {
            $error = true;
            $feedback[] = $osC_Language->get('error_sender_email_invalid');
          }
          
          if (!isset($_REQUEST['recipients_email']) || empty($_REQUEST['recipients_email'])) {
            $error = true;
            $feedback[] = $osC_Language->get('error_recipients_email_empty');
          }
          
          if ( !osc_validate_email_address($_REQUEST['recipients_email']) ) {
            $error = true;
            $feedback[] = $osC_Language->get('error_recipients_email_invalid');
          }
        }
        
        if($error === false) {
          if ( $osC_Product->isEmailGiftCertificate() ) {
            $gift_certificate_data = array('senders_name' => $_REQUEST['senders_name'],
                                           'senders_email' => $_REQUEST['senders_email'],
                                           'recipients_name' => $_REQUEST['recipients_name'],
                                           'recipients_email' => $_REQUEST['recipients_email'],
                                           'message' => $_REQUEST['message']);
          } else {
            $gift_certificate_data = array('senders_name' => $_REQUEST['senders_name'],
                                           'recipients_name' => $_REQUEST['recipients_name'],
                                           'message' => $_REQUEST['message']);
          }
          $gift_certificate_data['type'] = $osC_Product->getGiftCertificateType();
          
          if ($osC_Product->isOpenAmountGiftCertificate()) {
            $gift_certificate_data['price'] = $_REQUEST['gift_certificate_amount'] / $osC_ShoppingCart->getCurrencyValue(); 
          }
        }
      }
      
      if ($error === false) {
        if ($osC_ShoppingCart->addProduct($_REQUEST['products_id'], $_REQUEST['new_qty'], $gift_certificate_data)) {
          $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed')); 
        }
      } else {
        $response = array('success' => false, 'feedback' => implode('<br />', $feedback));
      }
            
      echo $toC_Json->encode($response); 
    }
    
    function deleteProduct() {
      global $toC_Json, $osC_Language, $osC_Tax, $osC_Weight, $osC_Currencies, $osC_ShoppingCart;
      
      $osC_Tax = new osC_Tax_Admin();
      $osC_Weight = new osC_Weight();
      $osC_Currencies = new osC_Currencies();
      $osC_ShoppingCart = new toC_ShoppingCart_Adapter($_REQUEST['orders_id']);
      
      $is_del_coupon = false;
      if( isset($_REQUEST['orders_products_id']) && !empty($_REQUEST['orders_products_id']) && $osC_ShoppingCart->deleteProduct($_REQUEST['orders_products_id'], $_REQUEST['products_id']) === true ) {
        if (count($osC_ShoppingCart->getProducts()) == 0) {
          $osC_ShoppingCart->deleteCoupon();
          $osC_ShoppingCart->updateOrderTotal();
          $is_del_coupon = true;
        }
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'), 'isDel' => $is_del_coupon);
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed')); 
      }
      
      echo $toC_Json->encode($response); 
    }
    
    function listShippingMethods() {
      global $toC_Json, $osC_Language, $osC_Currencies, $osC_Tax, $osC_Weight, $osC_ShoppingCart, $osC_Shipping;
      
      $osC_Language->loadIniFile($_SESSION['module'] . '.php');

      $osC_Currencies = new osC_Currencies_Admin();
      $osC_Tax = new osC_Tax_Admin();
      $osC_Weight = new osC_Weight();

      $osC_ShoppingCart = new toC_ShoppingCart_Adapter($_REQUEST['orders_id']);
      $osC_ShoppingCart->_calculate();

      unset($_SESSION['osC_ShoppingCart_data']['shipping_quotes']);

      $osC_Shipping = new osC_Shipping();
      if ($osC_ShoppingCart->hasShippingMethod() === false) {
        $osC_ShoppingCart->setShippingMethod($osC_Shipping->getCheapestQuote());
      }
      
      $records = array();
      foreach ($osC_Shipping->getQuotes() as $quotes) {
        $module = $quotes['module'];
        if (isset($quotes['icon']) && !empty($quotes['icon'])) { 
          $module .= '&nbsp;' . $quotes['icon']; 
        }
        
        $records[] = array('title' => '<b>' . $module . '</b>',
                          'code' => $quotes['id'],
                          'price' => '',
                          'action' => array());
        
        if (isset($quotes['error'])) {
          $records[] = array('title' => '&nbsp;&nbsp;--&nbsp;<i>' . $quotes['error'] . '</i>',
                            'code' => $quotes['id'] . '_error',
                            'price' => '',
                            'action' => array());
        } else {
          foreach ($quotes['methods'] as $methods) {
            $records[] = array('title' => '&nbsp;&nbsp;--&nbsp;<i>' . $methods['title'] . '</i>',
                              'code' => $quotes['id'] . '_' . $methods['id'],
                              'price' => $osC_Currencies->displayPrice($methods['cost'], $quotes['tax_class_id'], 1, $osC_ShoppingCart->getCurrency()),
                              'action' => array('class' => 'icon-add-record', 'qtip' => ''));
          }
        }
      }

      $response = array(EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response); 
    }
    
    function saveShippingMethod() {
      global $toC_Json, $osC_Language, $osC_Shipping, $osC_ShoppingCart, $osC_Weight, $osC_Tax, $osC_Currencies;  
    
      $osC_ShoppingCart = new toC_ShoppingCart_Adapter($_REQUEST['orders_id']);
      $osC_Shipping = new osC_Shipping();
      $osC_Tax = new osC_Tax_Admin();
      $osC_Weight = new osC_Weight();
      $osC_Currencies = new osC_Currencies();

      if ($osC_Shipping->hasQuotes()) {
        if (isset($_REQUEST['code']) && strpos($_REQUEST['code'], '_')) {
          list($module, $method) = explode('_', $_REQUEST['code']);
          $module = 'osC_Shipping_' . $module;

          if (is_object($GLOBALS[$module]) && $GLOBALS[$module]->isEnabled()) {
            $quote = $osC_Shipping->getQuote($_REQUEST['code']);

            if (isset($quote['error'])) {
              $osC_ShoppingCart->resetShippingMethod();
            } else {
              $osC_ShoppingCart->setShippingMethod($quote);
            }
          } else {
            $osC_ShoppingCart->resetShippingMethod();
          }
        }
      }
      $osC_ShoppingCart->updateOrderTotal();
      
      $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));

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
    
    function getOrdersReturns() {
      global $toC_Json, $osC_Language, $osC_Database;

      $osC_Order = new osC_Order($_REQUEST['orders_id']);
      
      $Qreturns = $osC_Database->query('select r.orders_returns_id, r.orders_id, r.orders_returns_status_id, r.customers_comments, r.admin_comments, r.date_added, o.customers_name, ors.orders_returns_status_name from :table_orders o, :table_orders_returns r, :table_orders_returns_status ors where r.orders_id = o.orders_id and r.orders_returns_status_id = ors.orders_returns_status_id and r.orders_id = :orders_id and ors.languages_id = :languages_id');
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
    
    function updateTrackingNo() {
      global $toC_Json, $osC_Language, $osC_Database;

      $Qupdate = $osC_Database->query('update :table_orders set tracking_no = :tracking_no where orders_id = :orders_id');
      $Qupdate->bindTable(':table_orders', TABLE_ORDERS);
      $Qupdate->bindValue(':tracking_no', $_REQUEST['tracking_no']);
      $Qupdate->bindInt(':orders_id', $_REQUEST['orders_id']);
      $Qupdate->execute();
      
      if (!$osC_Database->isError()) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed')); 
      }
      
      echo $toC_Json->encode($response);
    }
    
    function updatePaymentMethod() {
      global $toC_Json, $osC_Language, $osC_Shipping, $osC_ShoppingCart, $osC_Weight, $osC_Tax, $osC_Currencies, $osC_Customer;  
    
      $osC_ShoppingCart = new toC_ShoppingCart_Adapter($_REQUEST['orders_id']);
      $osC_Shipping = new osC_Shipping();
      $osC_Tax = new osC_Tax_Admin();
      $osC_Weight = new osC_Weight();
      $osC_Currencies = new osC_Currencies();
      
      if ($osC_ShoppingCart->updatePaymentMethod($_REQUEST['payment_method'], ($_REQUEST['use_store_credit'] == 'true') ? true : false)) {
        $response = array('success' => true ,
                          'feedback' => $osC_Language->get('ms_success_action_performed'),
                          'use_store_credit' => ($osC_ShoppingCart->isUseStoreCredit() ? true : false),
                          'disable_cbo_payment' => ($osC_ShoppingCart->isUseStoreCredit() && $osC_ShoppingCart->isTotalZero()) ? true : false);
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed')); 
      }

      echo $toC_Json->encode($response);  
    }
    
    function updateComment() {
      global $toC_Json, $osC_Language;
      
      if ( osC_Order::updateAdminComment($_REQUEST['orders_id'], $_REQUEST['admin_comment']) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed')); 
      }
      
      echo $toC_Json->encode($response);
    }
    
    function setGiftWrapping() {
      global $toC_Json, $osC_Language, $osC_Tax, $osC_Weight, $osC_Currencies, $osC_ShoppingCart;
      
      $error = false;
      $feedback = array();
      
      $osC_ShoppingCart = new toC_ShoppingCart_Adapter($_REQUEST['orders_id']);
      $osC_Tax = new osC_Tax_Admin();
      $osC_Weight = new osC_Weight();
      $osC_Currencies = new osC_Currencies();
      
      if($osC_ShoppingCart->setGiftWrapping(($_REQUEST['checked'] == 'true' ? true : false), $_REQUEST['message'])) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed')); 
      }
      
      echo $toC_Json->encode($response);
    }
    
    function downloadCustomizationFile() {
      header('Content-Description: File Transfer');
      header('Content-Type: application/octet-stream');
      header('Content-Transfer-Encoding: binary');
      header('Content-Disposition: attachment; filename=' . $_REQUEST['file']);
      header('Content-Length: ' . filesize(DIR_FS_CACHE . 'orders_customizations/' . $_REQUEST['cache_file']));
      header('Pragma: public');
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      
      ob_clean();
      flush();
      readfile(DIR_FS_CACHE . 'orders_customizations/' . $_REQUEST['cache_file']);       
      exit;
    }
  }
?>
