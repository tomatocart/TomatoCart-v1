<?php
/*
  $Id: reports_customers.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  
  class toC_Json_Reports_Customers {
    
    function listOrdersTotal() {
      global $osC_Database, $toC_Json;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qorders = $osC_Database->query('select SQL_CALC_FOUND_ROWS o.orders_id, o.customers_id, o.customers_name, sum(ot.value) as value from :table_orders o, :table_orders_total ot where o.orders_id = ot.orders_id and ot.class = :class ');
    
      if ( !empty($_REQUEST['start_date']) ) {
        $Qorders->appendQuery('and o.date_purchased >= :start_date ');
        $Qorders->bindValue(':start_date', $_REQUEST['start_date']);
      }
    
      if ( !empty($_REQUEST['end_date']) ) {
        $Qorders->appendQuery('and o.date_purchased <= :end_date ');
        $Qorders->bindValue(':end_date', $_REQUEST['end_date']);
      }
      $Qorders->appendQuery(' group by o.customers_id order by value desc');
    
      $Qorders->bindTable(':table_orders', TABLE_ORDERS);
      $Qorders->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
      $Qorders->bindValue(':class', 'total');
      $Qorders->setExtBatchLimit($start, $limit);
      $Qorders->execute();
      
      $records = array();
      while ($Qorders->next()) {
      	$records[] = array('orders_id' => $Qorders->ValueInt('orders_id'),
      	                   'customers_id' => $Qorders->ValueInt('customers_id'),
      	                   'customers_name' => osc_icon('orders.png') . '&nbsp;' . $Qorders->Value('customers_name'),
      	                   'value' => (float) $Qorders->Value('value'));
      }
      
      $response = array(EXT_JSON_READER_TOTAL => $Qorders->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);
    }
    
    function listBestOrders() {
      global $osC_Database, $toC_Json;
      
      $Qorders = $osC_Database->query('select o.orders_id, o.customers_id, o.customers_name, ot.value, o.date_purchased from :table_orders o, :table_orders_total ot where o.orders_id = ot.orders_id and ot.class = :class ');
    
      if (!empty($_REQUEST['start_date'])) {
        $Qorders->appendQuery('and o.date_purchased >= :start_date ');
        $Qorders->bindValue(':start_date', $_REQUEST['start_date']);
      }
    
      if (!empty($_REQUEST['end_date'])) {
        $Qorders->appendQuery('and o.date_purchased <= :end_date ');
        $Qorders->bindValue(':end_date', $_REQUEST['end_date']);
      }
      $Qorders->appendQuery(' order by value desc');
      $Qorders->bindTable(':table_orders', TABLE_ORDERS);
      $Qorders->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
      $Qorders->bindValue(':class', 'total');
      $Qorders->setExtBatchLimit($start, MAX_DISPLAY_SEARCH_RESULTS);
      $Qorders->execute();
      
      $records = array();
      while ($Qorders->next()) {
      	$records[] = array('orders_id' => $Qorders->ValueInt('orders_id'),
      	                   'customers_id' => $Qorders->ValueInt('customers_id'),
      	                   'customers_name' => osc_icon('orders.png') . '&nbsp;' . $Qorders->Value('customers_name'),
      	                   'date_purchased' => osC_DateTime::getShort($Qorders->value('date_purchased'), true),
      	                   'value' => (float) $Qorders->Value('value'));
      }
      
      $response = array(EXT_JSON_READER_TOTAL => $Qorders->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);
    }
  }      
?>