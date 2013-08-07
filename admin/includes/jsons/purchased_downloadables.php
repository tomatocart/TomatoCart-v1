<?php
/*
  $Id: purchased_downloadables.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require_once('includes/classes/purchased_downloadables.php');

  class toC_Json_Purchased_Downloadables {
    
    function listPurchasedDownloadables() {
      global $osC_Database, $toC_Json, $osC_Language, $osC_Currencies;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qdownloadables = $osC_Database->query('select opd.orders_products_download_id, op.products_name, opd.orders_products_filename, o.customers_name, o.date_purchased, opd.status from :table_orders o, :table_orders_products op, :table_orders_products_download opd where o.orders_id =  op.orders_id and op.orders_products_id = opd.orders_products_id ');
            
      if ( !empty($_REQUEST['search']) ) {
        $Qdownloadables->appendQuery('and o.customers_name like :customers_name');
        $Qdownloadables->bindValue(':customers_name', '%' . $_REQUEST['search'] . '%');
      }      
      
      $Qdownloadables->bindTable(':table_orders', TABLE_ORDERS);
      $Qdownloadables->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
      $Qdownloadables->bindTable(':table_orders_products_download', TABLE_ORDERS_PRODUCTS_DOWNLOAD);
      $Qdownloadables->setExtBatchLimit($start, $limit);
      $Qdownloadables->execute();
      
      $records = array();
      while ($Qdownloadables->next()) {
        $Qhistory = $osC_Database->query('select * from :table_products_download_history where orders_products_download_id = :orders_products_download_id');
        $Qhistory->bindTable(':table_products_download_history', TABLE_PRODUCTS_DOWNLOAD_HISTORY);
        $Qhistory->bindInt(':orders_products_download_id', $Qdownloadables->ValueInt('orders_products_download_id'));
        $Qhistory->execute();
        
        $total_downloads = 0;
          
        $history = '<table style="padding-left: 20px;" cellspacing="5">
                     <tr><td>' . $osC_Language->get('table_heading_download_date') . '</td></tr>';
        
        while ($Qhistory->next()) {
          $history .= '<tr><td>' . osC_DateTime::getShort($Qhistory->Value('download_date')) . '</td></tr>';
          $total_downloads++;
        }
        $history .= '</table>';
        $Qhistory->freeResult();
        
        $records[] = array('orders_products_download_id' => $Qdownloadables->ValueInt('orders_products_download_id'),
                           'products_name' => $Qdownloadables->Value('products_name'),
                           'file_name' => $Qdownloadables->Value('orders_products_filename'),
                           'customer' => $Qdownloadables->Value('customers_name'),
                           'date_purchased' => osC_DateTime::getShort($Qdownloadables->Value('date_purchased'), true),
                           'total_downloads' => $total_downloads,
                           'status' => $Qdownloadables->Value('status'),
                           'history' => $history);        
      }
      
      $response = array(EXT_JSON_READER_TOTAL => $Qdownloadables->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);      
      
      echo $toC_Json->encode($response);
    }
    
    function setStatus() {
      global $toC_Json, $osC_Language;
      
      if ( isset($_REQUEST['orders_products_download_id']) && toC_PurchasedDownloadables_Admin::setStatus($_REQUEST['orders_products_download_id'], (isset($_REQUEST['flag']) ? $_REQUEST['flag'] : null)) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }    
  }
?>