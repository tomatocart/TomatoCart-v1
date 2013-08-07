<?php
/*
  $Id: currencies.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/currencies.php');

  class toC_Json_Currencies {

    function listCurrencies() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $osC_Currencies = new osC_Currencies_Admin();      
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qcurrencies = $osC_Database->query('select * from :table_currencies order by title');
      $Qcurrencies->bindTable(':table_currencies', TABLE_CURRENCIES);
      $Qcurrencies->setExtBatchLimit($start, $limit);
      $Qcurrencies->execute();
      
      $records = array();
      while ($Qcurrencies->next()) {
        $currency_name = $Qcurrencies->value('title');
        
        if ( $Qcurrencies->value('code') == DEFAULT_CURRENCY ) {
          $currency_name .= ' (' . $osC_Language->get('default_entry') . ')';
        }
        
        $records[] = array('currencies_id' => $Qcurrencies->valueInt('currencies_id'),
                           'title' => $currency_name,
                           'code' => $Qcurrencies->value('code'),
                           'value' => $Qcurrencies->value('value'),
                           'example' => $osC_Currencies->format(1499.99, $Qcurrencies->value('code'), 1));
      }
  
      $response = array(EXT_JSON_READER_TOTAL => $Qcurrencies->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);

      echo $toC_Json->encode($response);    
    }
  
    function loadCurrency() {
      global $toC_Json;
      
      $osC_Currencies = new osC_Currencies_Admin();
      
      $data = $osC_Currencies->getData($_REQUEST['currencies_id']);
      if ($data['code'] == DEFAULT_CURRENCY) {
        $data['is_default'] = '1';
      }
        
      $response = array('success' => true, 'data' => $data);     
       
      echo $toC_Json->encode($response);
    }
    
    function saveCurrency() {
      global $toC_Json, $osC_Language;
      
      $error = false;
      $feedback = array();
      
      $code = isset($_REQUEST['code']) ? $_REQUEST['code'] : null;
      
      if ( !is_numeric($_REQUEST['id']) && osC_Currencies_Admin::codeIsExist($code) ) {
        $error = true;
        $feedback[] = $osC_Language->get('ms_error_currency_code_exist');
      }
      
      if ($error === false) {
        $data = array('title' => $_REQUEST['title'],
                      'code' => $code,
                      'symbol_left' => $_REQUEST['symbol_left'],
                      'symbol_right' => $_REQUEST['symbol_right'],
                      'decimal_places' => $_REQUEST['decimal_places'],
                      'value' => $_REQUEST['value']);
            
        if (osC_Currencies_Admin::save((isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) ? $_REQUEST['id'] : null), $data, ((isset($_REQUEST['default']) && ($_REQUEST['default'] == 'on')) || (isset($_REQUEST['is_default']) && ($_REQUEST['is_default'] == 'on') && ($_REQUEST['code'] != DEFAULT_CURRENCY))))) {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteCurrency() {
      global $toC_Json, $osC_Language;
      
      $error = false;
      $feedback = array();
      
      $code = isset($_REQUEST['code']) ? $_REQUEST['code'] : null;
      if ($code == DEFAULT_CURRENCY) {
        $error = true;
        $feedback[] = $osC_Language->get('introduction_delete_currency_invalid');
      }

      if ( $error === false ) {   
        if (osC_Currencies_Admin::delete($_REQUEST['cID'])) {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteCurrencies() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $batch = explode(',', $_REQUEST['batch']);
      
      $Qcurrencies = $osC_Database->query('select currencies_id, title, code from :table_currencies where currencies_id in (":currencies_id") order by title');
      $Qcurrencies->bindTable(':table_currencies', TABLE_CURRENCIES);
      $Qcurrencies->bindRaw(':currencies_id', implode('", "', array_unique(array_filter(array_slice($batch, 0, MAX_DISPLAY_SEARCH_RESULTS), 'is_numeric'))));
      $Qcurrencies->execute();
    
      $error = false;
      $feedback = array();
      while ($Qcurrencies->next()) {
        if ( $Qcurrencies->value('code') == DEFAULT_CURRENCY ) {
          $error = true;
          $feedback[] = $osC_Language->get('introduction_delete_currency_invalid');
          break;
        }
      }

      if ($error === false) {
        foreach ($batch as $id) {
          if ( !osC_Currencies_Admin::delete($id) ) {
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
    
    function updateCurrencyRates() {
      global $toC_Json, $osC_Language;

      $error = false;
      $feedback = array();
      
      $results = osC_Currencies_Admin::updateRates($_REQUEST['service']);
      
      if ( count($results[0]) ) {
        $error = true;
        foreach ($results[0] as $result) {
          $feedback[]= sprintf($osC_Language->get('ms_error_invalid_currency'), $result['title'], $result['code']);
        }
      }
      
      if ( count($results[1]) ) {
        foreach ($results[1] as $result) {
          $feedback[]= sprintf($osC_Language->get('ms_success_currency_updated'), $result['title'], $result['code']);
        }
      }
      
      if ($error === false) {
        $response = array('success' => true, 
                          'feedback' => $osC_Language->get('ms_success_action_performed') . '<br />' . implode('<br />', $feedback));
      }
      else {
        $response = array('success' => false, 
                          'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }
      
      echo $toC_Json->encode($response);
    }
  }
?>
