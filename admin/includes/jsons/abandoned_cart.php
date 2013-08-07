<?php
/*
  $Id: abandoned_cart.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/classes/abandoned_cart.php');
  require('../includes/classes/currencies.php');
  
  class toC_Json_abandoned_cart {
    
    function listAbandonedCart() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      $osC_Currencies = new osC_Currencies();
      
      $customers_id = osc_get_session_customers_id();
      
      $Qcustomers = $osC_Database->query("select SQL_CALC_FOUND_ROWS DISTINCT cb.customers_id, cb.products_id, cb.customers_basket_quantity, max(cb.customers_basket_date_added) date_added, c.customers_firstname, c.customers_lastname, c.customers_telephone phone, c.customers_email_address email, c.abandoned_cart_last_contact_date date_contacted from " . TABLE_CUSTOMERS_BASKET . " cb, " . TABLE_CUSTOMERS . " c where c.customers_id not in ('" . implode(',', $customers_id) . "') and cb.customers_id = c.customers_id  group by cb.customers_id order by cb.customers_id, cb.customers_basket_date_added desc ");
      $Qcustomers->setExtBatchLimit($start, $limit);
      $Qcustomers->execute();
      
      $records = array();
      while ($Qcustomers->next()) {
        $action = array();
        $action[] = array('class' => 'icon-send-email-record', 'qtip' => $osC_Language->get('icon_email_send'));
        $action[] = array('class' => 'icon-delete-record', 'qtip' => $osC_Language->get('icon_trash'));
        
        $cart_contents = toC_Abandoned_Cart_Admin::getCartContents($Qcustomers->valueInt('customers_id'));
        $total = 0;
        $products = array();
        foreach($cart_contents as $product){
          $total += $product['price'] * $product['qty'];
          $products[] = $product['qty'] . '&nbsp;x&nbsp;' . $product['name'];
        }
        
        $date_contacted = $Qcustomers->value('date_contacted');
        $records[] = array('customers_id' => $Qcustomers->valueInt('customers_id'), 
                           'products' => implode('<br />', $products),
                           'date_contacted' => empty($date_contacted) ? '---' : osC_DateTime::getShort($date_contacted),
                           'date_added' => osC_DateTime::getShort($Qcustomers->value('date_added')),
                           'customers_name' => $Qcustomers->value('customers_firstname') . '&nbsp;' . $Qcustomers->value('customers_lastname'),
                           'email' => $Qcustomers->value('email'),
                           'action' => $action,
                           'total' => $osC_Currencies->format($total));                        
      }
      $Qcustomers->freeResult();
      
      $response = array(EXT_JSON_READER_TOTAL => sizeof($records),
                        EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);                       
    }
    
    function sendEmail() {
      global $toC_Json, $osC_Language;
      
      $customers_id = ( isset($_REQUEST['customers_id']) && is_numeric($_REQUEST['customers_id']) ) ? $_REQUEST['customers_id'] : null;
      
      if ( toC_Abandoned_Cart_Admin::sendEmail($customers_id, $_REQUEST['message']) ) {
        osC_Customers_Admin::setAbandonedCartLastContactDate($customers_id);
        
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));    
      }else {
        $response = array('success' => false , 'feedback' => $osC_Language->get('ms_error_action_not_performed'));      
      }
      
      echo $toC_Json->encode($response);    
    }
    
    function deleteAbandonedCart() {
      global $toC_Json, $osC_Language;
      
      $customers_id = ( isset($_REQUEST['customers_id']) && is_numeric($_REQUEST['customers_id']) ) ? $_REQUEST['customers_id'] : null;
      
      if ( toC_Abandoned_Cart_Admin::delete($customers_id) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));      
      }else {
        $response = array('success' => false , 'feedback' => $osC_Language->get('ms_error_action_not_performed'));     
      }
      
      echo $toC_Json->encode($response);
    
    }
    
    function deleteAbandonedCarts() {
       global $toC_Json, $osC_Language;
     
      $error = false;

      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if ( !toC_Abandoned_Cart_Admin::delete($id) ) {
          $error = true;
          break;
        }
      }
       
      if ($error === false) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));               
      }
       
      echo $toC_Json->encode($response);               
    }           
  
  }
?>

