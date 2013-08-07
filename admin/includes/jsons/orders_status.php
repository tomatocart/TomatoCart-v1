<?php
/*
  $Id: orders_status.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com
  
  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd
  
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/classes/orders_status.php');
  
  class toC_Json_Orders_Status {
    
    function listOrdersStatus() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qstatus = $osC_Database->query('select orders_status_id, orders_status_name, public_flag, downloads_flag, returns_flag, gift_certificates_flag from :table_orders_status where language_id = :language_id order by orders_status_id');
      $Qstatus->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
      $Qstatus->bindInt(':language_id', $osC_Language->getID());
      $Qstatus->setExtBatchLimit($start, $limit);
      $Qstatus->execute();
      
      $records = array();
      while($Qstatus->next()) {
        $orders_status_name = $Qstatus->Value('orders_status_name');
        
        if ($Qstatus->ValueInt('orders_status_id') == DEFAULT_ORDERS_STATUS_ID) {
          $orders_status_name .= ' (' . $osC_Language->get('default_entry') . ')';
        }
        
        $records[] = array('orders_status_id' => $Qstatus->ValueInt('orders_status_id'),
                           'language_id' => $osC_Language->getID(),
                           'orders_status_name' => $orders_status_name,
                           'public_flag' => $Qstatus->ValueInt('public_flag'),
                           'downloads_flag' => $Qstatus->ValueInt('downloads_flag'),
                           'returns_flag' => $Qstatus->ValueInt('returns_flag'),
                           'gift_certificates_flag' => $Qstatus->ValueInt('gift_certificates_flag'));
      }
      
      $response = array(EXT_JSON_READER_TOTAL => $Qstatus->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);
    }    
    
    function loadOrdersStatus() {
      global $toC_Json, $osC_Database;
      
      $Qstatus = $osC_Database->query('select language_id, orders_status_name, public_flag, downloads_flag, returns_flag, gift_certificates_flag from :table_orders_status where orders_status_id = :orders_status_id');
      $Qstatus->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
      $Qstatus->bindInt(':orders_status_id', $_REQUEST['orders_status_id']);
      $Qstatus->execute();
      
      while ($Qstatus->next() ) {
        if (DEFAULT_ORDERS_STATUS_ID == $_REQUEST['orders_status_id']) {
          $data['default'] = '1';
        }
        
        $data['name[' . $Qstatus->valueInt('language_id') . ']'] =  $Qstatus->value('orders_status_name');
        $data['public_flag'] = $Qstatus->valueInt('public_flag');
        $data['downloads_flag'] = $Qstatus->valueInt('downloads_flag');
        $data['returns_flag'] = $Qstatus->valueInt('returns_flag');
        $data['gift_certificates_flag'] = $Qstatus->valueInt('gift_certificates_flag');  
      }

      $response = array('success' => true, 'data' => $data);
         
      echo $toC_Json->encode($response);
    }
      
    function saveOrdersStatus(){
      global $toC_Json, $osC_Language;
      
      $data = array('name' => $_REQUEST['name'],
                    'public_flag' => ((isset($_REQUEST['public_flag']) && ($_REQUEST['public_flag'] == 'on')) ? 1 : 0),
                    'downloads_flag' => ((isset($_REQUEST['downloads_flag']) && ($_REQUEST['downloads_flag'] == 'on')) ? 1 : 0),
                    'returns_flag' => ((isset($_REQUEST['returns_flag']) && ($_REQUEST['returns_flag'] == 'on')) ? 1 : 0), 
                    'gift_certificates_flag' => ((isset($_REQUEST['gift_certificates_flag']) && ($_REQUEST['gift_certificates_flag'] == 'on')) ? 1 : 0));
      
      if ( osC_OrdersStatus_Admin::save( ( isset($_REQUEST['orders_status_id']) ? $_REQUEST['orders_status_id'] : null), $data, ( isset($_REQUEST['default']) && ( $_REQUEST['default'] == 'on' ) ? true : false )) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteOrdersStatus() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $error = false;
      $feedback = array();
      
      if ( $_REQUEST['orders_status_id'] == DEFAULT_ORDERS_STATUS_ID ) {
        $error = true;
        $feedback[] = $osC_Language->get('delete_error_order_status_prohibited');
      } else {
        $Qorders = $osC_Database->query('select count(*) as total from :table_orders where orders_status = :orders_status');
        $Qorders->bindTable(':table_orders', TABLE_ORDERS);
        $Qorders->bindInt(':orders_status', $_REQUEST['orders_status_id']);
        $Qorders->execute();
      
        if ( $Qorders->valueInt('total') > 0 ) {
          $error = true;
          $feedback[] = sprintf($osC_Language->get('delete_error_order_status_in_use'), $Qorders->valueInt('total'));
        }
        
        $Qhistory = $osC_Database->query('select count(*) as total from :table_orders_status_history where orders_status_id = :orders_status_id group by orders_id');
        $Qhistory->bindTable(':table_orders_status_history', TABLE_ORDERS_STATUS_HISTORY);
        $Qhistory->bindInt(':orders_status_id', $_REQUEST['orders_status_id']);
        $Qhistory->execute();
        
        if ( $Qhistory->valueInt('total') > 0 ) {
          $error = true;
          $feedback[] = sprintf($osC_Language->get('delete_error_order_status_used'), $Qhistory->valueInt('total'));
        }
      }
      
      if ( $error === false ) {   
        if (osC_OrdersStatus_Admin::delete($_REQUEST['orders_status_id'])) {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }      
      
      echo $toC_Json->encode($response);
    }
    
    function batchDeleteOrdersStatus() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $error = false;
      $feedback = array();
      
      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if ( $id == DEFAULT_ORDERS_STATUS_ID ) {
          $error = true;
          $feedback[] = $osC_Language->get('batch_delete_error_order_status_prohibited');
        } else {
          $Qorders = $osC_Database->query('select count(*) as total from :table_orders where orders_status = :orders_status');
          $Qorders->bindTable(':table_orders', TABLE_ORDERS);
          $Qorders->bindInt(':orders_status', $id);
          $Qorders->execute();
        
          if ( $Qorders->valueInt('total') > 0 ) {
            $error = true;
            $feedback[] = $osC_Language->get('batch_delete_error_order_status_in_use');
            break;
          }
          
          $Qhistory = $osC_Database->query('select count(*) as total from :table_orders_status_history where orders_status_id = :orders_status_id group by orders_id');
          $Qhistory->bindTable(':table_orders_status_history', TABLE_ORDERS_STATUS_HISTORY);
          $Qhistory->bindInt(':orders_status_id', $id);
          $Qhistory->execute();
          
          if ( $Qhistory->valueInt('total') > 0 ) {
            $error = true;
            $feedback[] = $osC_Language->get('batch_delete_error_order_status_used');
            break;
          }
        }
      }
      
      if ($error === false) {
        foreach ($batch as $id) {
          if ( !osC_OrdersStatus_Admin::delete($id) ) {
            $error = true;
            break;
          }
        }
      
        if ($error === false) {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }      
      
      echo $toC_Json->encode($response);
    }
    
  	function setStatus(){
      global $toC_Json, $osC_Language; 
    
      $flag = $_REQUEST['flag'];
      $orders_status_id = $_REQUEST['orders_status_id'];
      $flag_name = $_REQUEST['flag_name'];
      
      if (osC_OrdersStatus_Admin::setStatus($orders_status_id, $flag_name, $flag)) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
  } 
?>