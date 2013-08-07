<?php
/*
  $Id: gift_certificates.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require_once('includes/classes/currencies.php');
  require_once('includes/classes/gift_certificates.php');

  class toC_Json_Gift_Certificates {
    
    function listGiftCertificates() {
      global $osC_Database, $toC_Json, $osC_Language, $osC_Currencies;
      
      $osC_Currencies = new osC_Currencies_Admin();
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qcertificates = $osC_Database->query('select gc.*, o.customers_name, o.date_purchased from :table_gift_certificates gc, :table_orders o, :table_orders_products op where gc.orders_products_id = op.orders_products_id and op.orders_id = o.orders_id ');
            
      if ( !empty($_REQUEST['search']) ) {
        $Qcertificates->appendQuery('and o.customers_name like :customers_name');
        $Qcertificates->bindValue(':customers_name', '%' . $_REQUEST['search'] . '%');
      }      
      
      $Qcertificates->bindTable(':table_gift_certificates', TABLE_GIFT_CERTIFICATES);
      $Qcertificates->bindTable(':table_orders', TABLE_ORDERS);
      $Qcertificates->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
      $Qcertificates->setExtBatchLimit($start, $limit);
      $Qcertificates->execute();
      
      $records = array();
      while ($Qcertificates->next()) {
        $Qhistory = $osC_Database->query('select gcrh.*, o.customers_name from :table_gift_certificates_redeem_history gcrh, :table_orders o where gcrh.orders_id = o.orders_id and gcrh.gift_certificates_id = :gift_certificates_id');
        $Qhistory->bindTable(':table_gift_certificates_redeem_history', TABLE_GIFT_CERTIFICATES_REDEEM_HISTORY);
        $Qhistory->bindTable(':table_orders', TABLE_ORDERS);
        $Qhistory->bindInt(':gift_certificates_id', $Qcertificates->ValueInt('gift_certificates_id'));
        $Qhistory->execute();
          
        $history = '<table style="padding-left: 20px;" cellspacing="5">
                     <tr>
                       <td>' . $osC_Language->get('table_heading_customer') . '</td>
                       <td>' . $osC_Language->get('table_heading_redeem_date') . '</td>
                       <td>' . $osC_Language->get('table_heading_redeem_amount') . '</td>
                     </tr>';
        
        $redeem_amount = 0;
        while ($Qhistory->next()) {
          $redeem_amount += $Qhistory->Value('redeem_amount');
          $history .= '<tr><td>' . $Qhistory->Value('customers_name') . '</td>
                           <td>' . osC_DateTime::getShort($Qhistory->Value('redeem_date')) . '</td>
                           <td>' . $osC_Currencies->format($Qhistory->Value('redeem_amount')) . '</td></tr>';
        }
        $history .= '</table>';
        $Qhistory->freeResult();
        
        $certificate_details = '<table style="padding-left: 20px" cellspacing="5">';
        $certificate_details .= '<tr><td>' . $osC_Language->get('field_recipient_name') . '</td><td>' . $Qcertificates->Value('recipients_name') . '</td></tr>';
        if ($Qcertificates->valueInt('gift_certificates_type') == GIFT_CERTIFICATE_TYPE_EMAIL) {
          $certificate_details .= '<tr><td>' . $osC_Language->get('field_recipient_email') . '</td><td>' . $Qcertificates->Value('recipients_email') . '</td></tr>';
        }
        $certificate_details .= '<tr><td>' . $osC_Language->get('field_recipient_sender_name') . '</td><td>' . $Qcertificates->Value('senders_name') . '</td></tr>';
        if ($Qcertificates->valueInt('gift_certificates_type') == GIFT_CERTIFICATE_TYPE_EMAIL) {
          $certificate_details .= '<tr><td>' . $osC_Language->get('field_recipient_sender_email') . '</td><td>' . $Qcertificates->Value('senders_email') . '</td></tr>';
        }
        $certificate_details .= '<tr><td>' . $osC_Language->get('field_message') . '</td><td>' . $Qcertificates->Value('messages') . '</td></tr>';
        $certificate_details .= '</table>';
        
        
        $records[] = array('gift_certificates_id' => $Qcertificates->ValueInt('gift_certificates_id'),
                           'orders_products_id' => $Qcertificates->ValueInt('orders_products_id'),
                           'gift_certificates_code' => $Qcertificates->Value('gift_certificates_code'),
                           'gift_certificates_customer' => $Qcertificates->Value('customers_name'),
                           'gift_certificates_amount' => $osC_Currencies->format($Qcertificates->Value('amount')),
                           'gift_certificates_balance' => $osC_Currencies->format($Qcertificates->Value('amount') - $redeem_amount),
                           'gift_certificates_date_purchased' => osC_DateTime::getShort($Qcertificates->Value('date_purchased')),
                           'gift_certificates_date_status' => $Qcertificates->Value('status'),
                           'recipients_name' => $Qcertificates->Value('recipients_name'),
                           'recipients_email' => $Qcertificates->Value('recipients_email'),
                           'senders_name' => $Qcertificates->Value('senders_name'),
                           'senders_email' => $Qcertificates->Value('senders_email'),
                           'messages' => $Qcertificates->Value('messages'),
                           'certificate_details' => $certificate_details,
                           'history' => $history);        
      }
      
      $response = array(EXT_JSON_READER_TOTAL => $Qcertificates->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);      
      
      echo $toC_Json->encode($response);
    }
    
    function setStatus() {
      global $toC_Json, $osC_Language;
      
      if ( isset($_REQUEST['gift_certificates_id']) && toC_GiftCertificates_Admin::setStatus($_REQUEST['gift_certificates_id'], (isset($_REQUEST['flag']) ? $_REQUEST['flag'] : null)) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }    
  }
?>