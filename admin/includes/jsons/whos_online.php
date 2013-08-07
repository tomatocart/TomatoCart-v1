<?php
/*
  $Id: whos_online.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/whos_online.php');

  class toC_Json_Whos_online {
  
    function listOnlineCustomers() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      require_once('includes/classes/currencies.php');
      $osC_Currencies = new osC_Currencies();
      
      require_once('includes/classes/geoip.php');
      $osC_GeoIP = osC_GeoIP_Admin::load();
    
      if ( $osC_GeoIP->isInstalled() ) {
        $osC_GeoIP->activate();
      }
    
      $active_time = 300;
      $track_time = 900;
      
      osC_WhosOnline_Admin::removeExpiredEntries($track_time);

      $xx_mins_ago_active = (time() - $active_time);
  
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qwho = $osC_Database->query('select customer_id, full_name, ip_address, time_entry, time_last_click, session_id, referrer_url from :table_whos_online ');
      $Qwho->bindTable(':table_whos_online', TABLE_WHOS_ONLINE);
    
      if ($_REQUEST['customers_filter'] == 'customers') {
        $Qwho->appendQuery('where customer_id >= 1 ');
      } else if ($_REQUEST['customers_filter'] == 'guests') {
        $Qwho->appendQuery('where customer_id = 0 ');
      } else if ($_REQUEST['customers_filter'] == 'customers_guests') {
        $Qwho->appendQuery('where customer_id >= 0 ');
      } else if ($_REQUEST['customers_filter'] == 'bots') {
        $Qwho->appendQuery('where customer_id = -1 ');
      }
    
      $Qwho->appendQuery('order by time_last_click desc');
      $Qwho->setExtBatchLimit($start, $limit);
      $Qwho->execute();
      
      $record = array();
      while ( $Qwho->next() ) {
        $session_data = osC_WhosOnline_Admin::getSessionData($Qwho->value('session_id'));
        $navigation = unserialize(osc_get_serialized_variable($session_data, 'osC_NavigationHistory_data', 'array'));
        
        if ( is_array($navigation) ) {
          $last_page = end($navigation);
        }
        $currency = unserialize(osc_get_serialized_variable($session_data, 'currency', 'string'));
        $cart = unserialize(osc_get_serialized_variable($session_data, 'osC_ShoppingCart_data', 'array'));
        
        $status = '';
        if($Qwho->value('customer_id') < 0) {
          if ($Qwho->value('time_last_click') < $xx_mins_ago_active) {
            $status = osc_icon('status_green.png', $osC_Language->get('text_status_inactive_bot'));
          } else {
            $status =  osc_icon('status_red.png', $osC_Language->get('text_status_active_bot'));
          }
        } else {
          if(is_array($cart['contents']) && sizeof($cart['contents']) > 0){
            if ($Qwho->value('time_last_click') < $xx_mins_ago_active) {
              $status = osc_icon('cart_red.png', $osC_Language->get('text_status_inactive_cart'));
            } else {
              $status = osc_icon('cart_green.png', $osC_Language->get('text_status_active_cart'));
            }
          }else{
            if ($Qwho->value('time_last_click') < $xx_mins_ago_active) {
              $status = osc_icon('people_red.png', $osC_Language->get('text_status_inactive_nocart'));
            } else {
              $status = osc_icon('people_green.png', $osC_Language->get('text_status_active_nocart'));
            }
          }
        }
        
        $geoip = '';
        $iso_code_2 = $osC_GeoIP->getCountryISOCode2($Qwho->value('ip_address'));
        if ( $osC_GeoIP->isActive() && $osC_GeoIP->isValid($Qwho->value('ip_address')) && !empty($iso_code_2)) {
          $geoip = osc_image('../images/worldflags/' . $iso_code_2 . '.png', $osC_GeoIP->getCountryName($Qwho->value('ip_address')) . ', ' . $Qwho->value('ip_address'), 18, 12). '&nbsp;' . $Qwho->value('ip_address');
        } else {
          $geoip = $Qwho->value('ip_address');
        } 
        
        $customers_info = '<table width="100%">';
        $customers_info .= '<tr><td width="120"><b>' . $osC_Language->get('field_session_id') . '</b></td><td>' . $Qwho->value('session_id') . '</td></tr>';
        $customers_info .= '<tr><td><b>' . $osC_Language->get('field_customer_name') . '</b></td><td>' . $Qwho->value('full_name') . '</td></tr>';
        $customers_info .= '<tr><td><b>' . $osC_Language->get('field_ip_address') . '</b></td><td>' . $Qwho->value('ip_address') . '</td></tr>';
        $customers_info .= '<tr><td><b>' . $osC_Language->get('field_entry_time') . '</b></td><td>' . date('H:i:s', $Qwho->value('time_entry')) . '</td></tr>';
        $customers_info .= '<tr><td><b>' . $osC_Language->get('field_last_click') . '</b></td><td>' . date('H:i:s', $Qwho->value('time_last_click')) . '</td></tr>';
        $customers_info .= '<tr><td><b>' . $osC_Language->get('field_time_online') . '</b></td><td>' . gmdate('H:i:s', time() - $Qwho->value('time_entry')) . '</td></tr>';
        $customers_info .= '<tr><td><b>' . $osC_Language->get('field_referrer_url') . '</b></td><td>' . $Qwho->value('referrer_url') . '</td></tr>';
        $customers_info .= '</table>';
        
        $products_table = '<table width="100%">';
        foreach ($cart['contents'] as $product) {
          $product_info = $product['quantity'] . '&nbsp;x&nbsp;' . $product['name'];
          
          if ( $product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE ) {
            $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $osC_Language->get('senders_name') . ': ' . $product['gc_data']['senders_name'] . '</i></nobr>';
            
            if ($product['gift_certificates_type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
              $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $osC_Language->get('senders_email') . ': ' . $product['gc_data']['senders_email'] . '</i></nobr>';
            }
            
            $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $osC_Language->get('recipients_name') . ': ' . $product['gc_data']['recipients_name'] . '</i></nobr>';
            
            if ($product['gift_certificates_type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
              $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $osC_Language->get('recipients_email') . ': ' . $product['gc_data']['recipients_email'] . '</i></nobr>';
            }
            
            $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $osC_Language->get('messages') . ': ' . $product['gc_data']['message'] . '</i></nobr>';
          }
          
          if ( isset($product['variants']) && is_array($product['variants']) && ( sizeof($product['variants']) > 0 ) ) {
            foreach ( $product['variants'] as $variants ) {
              $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $variants['groups_name'] . ': ' . $variants['values_name'] . '</i></nobr>';
            }
          }
          
          $products_table .= '<tr><td>' . $product_info . '</td><td width="60" valign="top" align="right">' . $osC_Currencies->displayPriceWithTaxRate($product['final_price'], $product['tax'], 1, $currency) . '</td></tr>';
        }
        $products_table .= '</table>';
      
        $customers_name = $Qwho->value('full_name') . ' (' . $Qwho->valueInt('customer_id') . ')';
        $customers_name .= ' -- ' . (($geoip === $_SERVER['REMOTE_ADDR']) ? $osC_Language->get('text_administrator') : '' );
        $record[] = array('session_id' => $Qwho->value('session_id'),
                          'status' => $status,
                          'geoip' => $geoip,
                          'online_time' => gmdate('H:i:s', time() - $Qwho->value('time_entry')),
                          'last_url' => $last_page['page'],
                          'custormers_name' => $customers_name,
                          'customers_info' => $customers_info,
                          'products' => $products_table,
                          'total' => $osC_Currencies->format($cart['total_cost'], true, $currency));         
      }
      
      if ( $osC_GeoIP->isActive() ) {
        $osC_GeoIP->deactivate();
      }
      
      $response = array(EXT_JSON_READER_TOTAL => $Qwho->getBatchSize(),
                        EXT_JSON_READER_ROOT => $record); 
                        
      echo $toC_Json->encode($response);
      
    }
    
    function deleteOnlineCustomer() {
      global $toC_Json, $osC_Language;
      
      if ( osC_WhosOnline_Admin::delete($_REQUEST['session_id']) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }

      echo $toC_Json->encode($response);
    }
    
    function deleteOnlineCustomers() {
      global $toC_Json, $osC_Language;
    
      $error = false;
      
      $batch = explode(',', $_REQUEST['batch']);
      
      foreach ($batch as $id) {
        if ( !osC_WhosOnline_Admin::delete($id) ) {
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
}
?>
