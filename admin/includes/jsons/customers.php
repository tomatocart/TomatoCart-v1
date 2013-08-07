<?php
/*
  $Id: customers.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/customers.php');
  require('includes/classes/currencies.php');
  
  class toC_Json_Customers {
        
    function listCustomers() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $osC_Currencies = new osC_Currencies_Admin();
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qcustomers = $osC_Database->query('select c.customers_id, c.customers_credits, c.customers_gender, c.customers_lastname, c.customers_firstname, c.customers_email_address, c.customers_status, c.customers_ip_address, c.date_account_created, c.number_of_logons, c.date_last_logon, cgd.customers_groups_name from :table_customers c left join :table_customers_groups_description cgd on (c.customers_groups_id = cgd.customers_groups_id and cgd.language_id = :language_id)');
      $Qcustomers->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qcustomers->bindTable(':table_customers_groups_description', TABLE_CUSTOMERS_GROUPS_DESCRIPTION);
      $Qcustomers->bindInt(':language_id', $osC_Language->getID());
      
      if (isset($_REQUEST['search']) && !empty($_REQUEST['search'])) {
        $Qcustomers->appendQuery('where c.customers_lastname like :customers_lastname or c.customers_firstname like :customers_firstname or c.customers_email_address like :customers_email_address');
        $Qcustomers->bindValue(':customers_lastname', '%' . $_REQUEST['search'] . '%');
        $Qcustomers->bindValue(':customers_firstname', '%' . $_REQUEST['search'] . '%');
        $Qcustomers->bindValue(':customers_email_address', '%' . $_REQUEST['search'] . '%');
      }
      
      $Qcustomers->appendQuery('order by c.customers_lastname, c.customers_firstname');
      $Qcustomers->setExtBatchLimit($start, $limit);
      $Qcustomers->execute();

      require_once('includes/classes/geoip.php');
      $osC_GeoIP = osC_GeoIP_Admin::load();
      
      if ( $osC_GeoIP->isInstalled() ) {
        $osC_GeoIP->activate();
      }
      
      $records = array();     
      while ( $Qcustomers->next() ) {           
        $geoip = '';
        $iso_code_2 = $osC_GeoIP->getCountryISOCode2($Qcustomers->value('customers_ip_address'));
        
        if ( $osC_GeoIP->isActive() && $osC_GeoIP->isValid($Qcustomers->value('customers_ip_address')) && !empty($iso_code_2)) {
          $geoip = osc_image('../images/worldflags/' . $iso_code_2 . '.png',  $country . ', ' . $Qcustomers->value('customers_ip_address'), 18, 12). '&nbsp;' . $Qcustomers->value('customers_ip_address');
        } else {
          $geoip = $Qcustomers->value('customers_ip_address');
        }

        $customers_info = 
          '<table width="100%" cellspacing="5">' .
            '<tbody>' . 
              '<tr>
                <td width="150">' . $osC_Language->get('field_gender') . '</td>
                <td>' . ($Qcustomers->value('customers_gender') == 'm' ? $osC_Language->get('gender_male') : $osC_Language->get('gender_female')) . '</td>
              </tr>' . 
              '<tr>
                <td>' . $osC_Language->get('field_email_address') . '</td>
                <td>' . $Qcustomers->value('customers_email_address') . '</td>
              </tr>' .
              '<tr>
                <td>' . $osC_Language->get('field_customers_group') . '</td>
                <td>' . $Qcustomers->value('customers_groups_name') . '</td>
              </tr>' . 
              '<tr>
                <td>' . $osC_Language->get('field_ip_address') . '</td>
                <td>' . $geoip . '</td>
              </tr>' .
              '<tr>
                <td>' . $osC_Language->get('field_number_of_logons') . '</td>
                <td>' . $Qcustomers->valueInt('number_of_logons') . '</td>
              </tr>' .
              '<tr>
                <td>' . $osC_Language->get('field_date_last_logon') . '</td>
                <td>' . osC_DateTime::getShort($Qcustomers->value('date_last_logon')) . '</td>
              </tr>' .
            '</tbody>' .
          '</table>';
        
        $records[] = array(
          'customers_id' => $Qcustomers->valueInt('customers_id'),
          'customers_lastname' => $Qcustomers->value('customers_lastname'),
          'customers_firstname' => $Qcustomers->value('customers_firstname'),
          'customers_credits' => $osC_Currencies->format($Qcustomers->value('customers_credits')),
          'date_account_created' => osC_DateTime::getShort($Qcustomers->value('date_account_created')),  
          'customers_status' => $Qcustomers->valueInt('customers_status'),
          'customers_info' => $customers_info
        );           
      }
      $Qcustomers->freeResult();
      
      $response = array(EXT_JSON_READER_TOTAL => $Qcustomers->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }          
    
    function listStoreCredits() {
      global $toC_Json, $osC_Database, $osC_Language;

      $osC_Currencies = new osC_Currencies_Admin();
      
      $Qcredits = $osC_Database->query('select customers_credits_history_id, date_added, action_type, amount, comments from :table_customers_credit where customers_id = :customers_id');
      $Qcredits->bindTable(':table_customers_credit', TABLE_CUSTOMERS_CREDITS_HISTORY);
      $Qcredits->bindInt(':customers_id', $_REQUEST['customers_id']);
      $Qcredits->execute();

      $records = array();
      while ( $Qcredits->next() ) {
        if ($Qcredits->valueInt('action_type') == STORE_CREDIT_ACTION_TYPE_ORDER_PURCHASE) {
          $actionType = $osC_Language->get('store_credits_action_purchase');
        } else if ($Qcredits->valueInt('action_type') == STORE_CREDIT_ACTION_TYPE_ORDER_REFUNDED) {
          $actionType = $osC_Language->get('store_credits_action_refund');
        } else if ($Qcredits->valueInt('action_type') == STORE_CREDIT_ACTION_TYPE_ADMIN) {
          $actionType = $osC_Language->get('store_credits_action_admin');
        }
        
        $records[] = array(
          'customers_credits_history_id' => $Qcredits->valueInt('customers_credits_history_id'),
          'date_added' => osC_DateTime::getShort($Qcredits->value('date_added')),
          'action_type' => $actionType,
          'amount' => $osC_Currencies->format($Qcredits->value('amount')),
          'comments' => $Qcredits->value('comments')      
        );           
      }      

      $response = array(EXT_JSON_READER_TOTAL => $Qcredits->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }
    
    function saveBlance(){
      global $toC_Json, $osC_Language, $osC_Database;
      
      $osC_Currencies = new osC_Currencies_Admin();

      $data = array('amount' => $_REQUEST['amount'],           
                    'comments' => $_REQUEST['comments'],
                    'customers_id' => $_REQUEST['customers_id'],
                    'notify' => ( isset($_REQUEST['notify']) && ($_REQUEST['notify'] == 'on') ? '1' : '0' ));

      if ( osC_Customers_Admin::saveBlance($data) ) {
        $data = osC_Customers_Admin::getData($_REQUEST['customers_id']);
        
        $response = array('success' => true,
                          'customers_credits' => $osC_Currencies->format($data['customers_credits']), 
                          'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
     
      echo $toC_Json->encode($response);  
    }
    
    function saveCustomer() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $customers_dob = explode('-', $_REQUEST['customers_dob']);
      $dob_year = $customers_dob[0];
      $dob_month = $customers_dob[1];
      $dob_date= $customers_dob[2];
      
      $data = array('gender' => (isset($_REQUEST['customers_gender']) ? $_REQUEST['customers_gender'] : ''), 
                    'firstname' => $_REQUEST['customers_firstname'],
                    'lastname' => $_REQUEST['customers_lastname'],
                    'dob_year' => $dob_year,
                    'dob_month' => $dob_month,
                    'dob_day' => $dob_date,
                    'email_address' => $_REQUEST['customers_email_address'],
                    'password' => $_REQUEST['customers_password'],
                    'newsletter' => ( isset($_REQUEST['customers_newsletter']) && ($_REQUEST['customers_newsletter'] == 'on') ? '1' : '0' ),           
                    'status' => ( isset($_REQUEST['customers_status']) && ($_REQUEST['customers_status'] == 'on') ? '1' : '0'),
                    'customers_groups_id' => ( isset($_REQUEST['customers_groups_id']) ? $_REQUEST['customers_groups_id'] : '') );
      
      $error = false;
      $feedback = array();
      if ( ACCOUNT_GENDER > 0 ) {
        if ( ($data['gender'] != 'm') && ($data['gender'] != 'f') ) {
          $error = true;
          $feedback[] = $osC_Language->get('ms_error_gender');
        }
      }
      
      if ( strlen(trim($data['firstname'])) < ACCOUNT_FIRST_NAME ) {
        $error = true;     
        $feedback[] = sprintf($osC_Language->get('ms_error_first_name'), ACCOUNT_FIRST_NAME); 
      }
      
      if ( strlen(trim($data['lastname'])) < ACCOUNT_LAST_NAME ) {
        $error = true;
        $feedback[] = sprintf($osC_Language->get('ms_error_last_name'), ACCOUNT_LAST_NAME);
      }

      if ( strlen(trim($data['email_address'])) < ACCOUNT_EMAIL_ADDRESS ) {
        $error = true;
        $feedback[] = sprintf($osC_Language->get('ms_error_email_address'), ACCOUNT_EMAIL_ADDRESS);
      } elseif ( !osc_validate_email_address($data['email_address']) ) {
        $error = true;
        $feedback[] = $osC_Language->get('ms_error_email_address_invalid');
      } else {
        $Qcheck = $osC_Database->query('select customers_id from :table_customers where customers_email_address = :customers_email_address');

        if ( isset($_REQUEST['customers_id']) && is_numeric($_REQUEST['customers_id']) ) {
          $Qcheck->appendQuery('and customers_id != :customers_id');
          $Qcheck->bindInt(':customers_id', $_REQUEST['customers_id']);
        }

        $Qcheck->appendQuery('limit 1');
        $Qcheck->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcheck->bindValue(':customers_email_address', $data['email_address']);
        $Qcheck->execute();
        
        if ( $Qcheck->numberOfRows() > 0 ) {
          $error = true;
          $feedback[] = $osC_Language->get('ms_error_email_address_exists');
        }
        
        $Qcheck->freeResult();
      }

      if ( ( !isset($_REQUEST['customers_id']) || !empty($data['password']) ) && (strlen(trim($data['password'])) < ACCOUNT_PASSWORD) ) {
        $error = true;
        $feedback[] = sprintf($osC_Language->get('ms_error_password'), ACCOUNT_PASSWORD);
      } elseif ( !empty($_REQUEST['confirm_password']) && ( (trim($data['password']) != trim($_REQUEST['confirm_password'])) || ( strlen(trim($data['password'])) != strlen(trim($_REQUEST['confirm_password'])) ) ) ) {
        $error = true;
        $feedback[] = $osC_Language->get('ms_error_password_confirmation_invalid');
      }
      
      if ($error === false) {
        if ( osC_Customers_Admin::save( ( isset($_REQUEST['customers_id'] ) && is_numeric( $_REQUEST['customers_id'] ) ? $_REQUEST['customers_id'] : null ), $data) ) {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function listAddressBooks() {
      global $toC_Json, $osC_Language;

      $osC_ObjectInfo = new osC_ObjectInfo( osC_Customers_Admin::getData($_REQUEST['customers_id']) );
      $Qaddresses = osC_Customers_Admin::getAddressBookData($_REQUEST['customers_id']);
      
      $records = array();
      while ( $Qaddresses->next() ) {
        $address = osC_Address::format($Qaddresses->toArray(), '<br/>');

        if ( $osC_ObjectInfo->get('customers_default_address_id') == $Qaddresses->valueInt('address_book_id') ) {
          $address .= '&nbsp;<i>(' . $osC_Language->get('primary_address') . ')</i>';
        }
        
        $records[] = array(
          'address_book_id' => $Qaddresses->value('address_book_id'),
          'address_html' => $address
        );
      }

      $Qaddresses->freeResult(); 
      
      $response = array(EXT_JSON_READER_ROOT => $records);
                                                 
      echo $toC_Json->encode($response);     
    }
    
    function getCustomersGroups() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $Qgroups = $osC_Database->query('select cg.customers_groups_id, cg.is_default, cgd.customers_groups_name from :table_customers_groups cg, :table_customers_groups_description cgd where cg.customers_groups_id = cgd.customers_groups_id and cgd.language_id = :language_id order by cg.customers_groups_id');
      $Qgroups->bindTable(':table_customers_groups', TABLE_CUSTOMERS_GROUPS);
      $Qgroups->bindTable(':table_customers_groups_description', TABLE_CUSTOMERS_GROUPS_DESCRIPTION);
      $Qgroups->bindInt(':language_id', $osC_Language->getID());
      $Qgroups->execute();
    
      $records = array(array('id' => '', 'text' => $osC_Language->get('none')));
      while ($Qgroups->next()) {
        $records[] = array('id' => $Qgroups->valueInt('customers_groups_id'),
                           'text' => $Qgroups->value('customers_groups_name'));
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response);
    }
    
    function getCountries() {
      global $toC_Json;
      
      $records = array();
      foreach ( osC_Address::getCountries() as $country ) {
        $records[] = array(
          'country_id' => $country['id'],
          'country_title' => $country['name']
        );                     
      }        
      
      echo $toC_Json->encode(array(EXT_JSON_READER_ROOT => $records));
    }
    
    function getZones() {
      global $toC_Json;
      
      $country_id = isset($_REQUEST['country_id']) ? $_REQUEST['country_id'] : null;
      
      $records = array();     
      foreach (osC_Address::getZones($country_id) as $zone) {
        $records[] = array(
           'zone_code' => $zone['code'],
           'zone_name' => $zone['name'] 
        );      
      }
      
      echo $toC_Json->encode(array(EXT_JSON_READER_ROOT => $records));
    }
    
    function loadCustomer() {
      global $toC_Json;
      
      $data = osC_Customers_Admin::getData($_REQUEST['customers_id']);
      
      $data['customers_dob'] = osC_DateTime::getDate($data['customers_dob']);
      $data['customers_password'] = '';
      $data['confirm_password'] = '';
         
      $response = array('success' => true, 'data' => $data);
     
      echo $toC_Json->encode($response);   
    }
   
    function loadAddressBook() {
      global $toC_Json;
     
      $osC_ObjectInfo = new osC_ObjectInfo(osC_Customers_Admin::getData($_REQUEST['customers_id']));
      $data = osC_Customers_Admin::getAddressBookData($_REQUEST['customers_id'], $_REQUEST['address_book_id']);
      
      if ( $osC_ObjectInfo->get('customers_default_address_id') ==  $_REQUEST['address_book_id'] ) {
        $data['primary'] = true; 
      } else {
        $data['primary'] = false;
      }
      
      $response = array('success' => true, 'data' => $data);

      echo $toC_Json->encode($response);
    }
    
    function saveAddressBook(){
      global $toC_Json, $osC_Language, $osC_Database;
      
      $data = array( 
         'customer_id' => $_REQUEST['customers_id'], 
         'gender' => (isset($_REQUEST['gender']) ? $_REQUEST['gender'] : ''),
         'firstname' => $_REQUEST['firstname'],
         'lastname' => $_REQUEST['lastname'],
         'company' => (isset($_REQUEST['company']) ? $_REQUEST['company'] : ''),
         'street_address' => $_REQUEST['street_address'],
         'suburb' => (isset($_REQUEST['suburb']) ? $_REQUEST['suburb'] : ''),
         'postcode' => (isset($_REQUEST['postcode']) ? $_REQUEST['postcode'] : ''),
         'city' => $_REQUEST['city'],
         'state' => (isset($_REQUEST['z_code']) ? $_REQUEST['z_code'] : ''),
         'zone_id' => '0', //set blow
         'country_id' => $_REQUEST['country_id'],
         'telephone' => (isset($_REQUEST['telephone_number']) ? $_REQUEST['telephone_number'] : ''),
         'fax' => (isset($_REQUEST['fax_number']) ? $_REQUEST['fax_number'] : ''),
         'primary' => (isset($_REQUEST['primary']) && ($_REQUEST['primary'] == 'on') ? true : false));

      $error = false;
      $feedback = array();
      
      if ( ACCOUNT_GENDER > 0 ) {
        if ( ($data['gender'] != 'm') && ($data['gender'] != 'f') ) {
          $error = true;
          $feedback[] = $osC_Language->get('ms_error_gender');
        }
      }
      
      if ( strlen(trim($data['firstname'])) < ACCOUNT_FIRST_NAME ) {
        $error = true;
        $feedback[] = sprintf($osC_Language->get('ms_error_first_name'), ACCOUNT_FIRST_NAME);
      }

      if ( strlen(trim($data['lastname'])) < ACCOUNT_LAST_NAME ) {
        $error = true;
        $feedback[] = sprintf($osC_Language->get('ms_error_last_name'), ACCOUNT_LAST_NAME);
      }
 
      if ( ACCOUNT_COMPANY > 0 ) {
        if ( strlen(trim($data['company'])) < ACCOUNT_COMPANY ) {
          $error = true;
          $feedback[] = sprintf($osC_Language->get('ms_error_company'), ACCOUNT_COMPANY);
        }
      }
    
      if ( strlen(trim($data['street_address'])) < ACCOUNT_STREET_ADDRESS ) {
        $error = true;
        $feedback[] = sprintf($osC_Language->get('ms_error_street_address'), ACCOUNT_STREET_ADDRESS);
      }
      
      if ( ACCOUNT_SUBURB > 0 ) {
        if ( strlen(trim($data['suburb'])) < ACCOUNT_SUBURB ) {
          $error = true;
          $feedback[] = sprintf($osC_Language->get('ms_error_suburb'), ACCOUNT_SUBURB);
        }
      }
    
      if ( ACCOUNT_POST_CODE > 0 ) {
        if ( strlen(trim($data['postcode'])) < ACCOUNT_POST_CODE ) {
          $error = true;
          $feedback[] = sprintf($osC_Language->get('entry_post_code'), ACCOUNT_POST_CODE);
        }
      }
      
      if ( strlen(trim($data['city'])) < ACCOUNT_CITY ) {
        $error = true;
        $feedback[] = sprintf($osC_Language->get('ms_error_city'), ACCOUNT_CITY);
      }
    
      if ( ACCOUNT_STATE > 0 ) {
        $Qcheck = $osC_Database->query('select zone_id from :table_zones where zone_country_id = :zone_country_id limit 1');
        $Qcheck->bindTable(':table_zones', TABLE_ZONES);
        $Qcheck->bindInt(':zone_country_id', $data['country_id']);
        $Qcheck->execute();
  
        $entry_state_has_zones = ( $Qcheck->numberOfRows() > 0 );
  
        $Qcheck->freeResult();
  
        if ( $entry_state_has_zones === true ) {
          $Qzone = $osC_Database->query('select zone_id from :table_zones where zone_country_id = :zone_country_id and zone_code = :zone_code');
          $Qzone->bindTable(':table_zones', TABLE_ZONES);
          $Qzone->bindInt(':zone_country_id', $data['country_id']);
          $Qzone->bindValue(':zone_code', strtoupper($data['state']));
          $Qzone->execute();
  
          if ( $Qzone->numberOfRows() === 1 ) {
            $data['zone_id'] = $Qzone->valueInt('zone_id');
          } else {
            $Qzone = $osC_Database->query('select zone_id from :table_zones where zone_country_id = :zone_country_id and zone_name like :zone_name');
            $Qzone->bindTable(':table_zones', TABLE_ZONES);
            $Qzone->bindInt(':zone_country_id', $data['country_id']);
            $Qzone->bindValue(':zone_name', $data['state'] . '%');
            $Qzone->execute();
  
            if ( $Qzone->numberOfRows() === 1 ) {
              $data['zone_id'] = $Qzone->valueInt('zone_id');
            } else {
              $error = true;
              $feedback[] = $osC_Language->get('ms_warning_state_select_from_list');
            }
          }
  
          $Qzone->freeResult();
        } else {
          if ( strlen(trim($data['state'])) < ACCOUNT_STATE ) {
            $error = true;
            $feedback[] = sprintf($osC_Language->get('ms_error_state'), ACCOUNT_STATE);
          }
        }
      }
      
      if ( !is_numeric($data['country_id']) || ($data['country_id'] < 1) ) {
        $error = true;
        $feedback[] = $osC_Language->get('ms_error_country');
      }
      
      if ( ACCOUNT_TELEPHONE > 0 ) {
        if ( strlen(trim($data['telephone'])) < ACCOUNT_TELEPHONE ) {
          $error = true;
          $feedback[] = sprintf($osC_Language->get('ms_error_telephone_number'), ACCOUNT_TELEPHONE);
        }
      }
  
      if ( ACCOUNT_FAX > 0 ) {
        if ( strlen(trim($data['fax'])) < ACCOUNT_FAX ) {
          $error = true;
          $feedback[] = sprintf($osC_Language->get('ms_error_fax_number'), ACCOUNT_FAX);
        }
      }
      
      if ($error === false ) {    
        if ( osC_Customers_Admin::saveAddress( ( isset($_REQUEST['address_book_id']) && is_numeric($_REQUEST['address_book_id']) ) ? $_REQUEST['address_book_id'] : null, $data) ) {      
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }   
      
      echo $toC_Json->encode($response);     
    }    
  
    function deleteCustomer() {
      global $toC_Json, $osC_Language;
      
      if (osC_Customers_Admin::delete($_REQUEST['customers_id'])) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
     
      echo $toC_Json->encode($response);                            
    }
    
    function deleteAddressBook() {
      global $toC_Json, $osC_Language;
      
      $address_book_id = isset($_REQUEST['address_book_id']) ? $_REQUEST['address_book_id'] : null;
      
      $osC_ObjectInfo_Customer = new osC_ObjectInfo(osC_Customers_Admin::getData($_REQUEST['customers_id']));
      
      $error = false;
      $feedback = array();
      
      if ( $osC_ObjectInfo_Customer->get('customers_default_address_id') == $address_book_id ) {
        $error = true;
        $feedback[] = $osC_Language->get('delete_warning_primary_address_book_entry');
      }
      
      if ($error === false) {    
        if ( osC_Customers_Admin::deleteAddress($address_book_id) ) {      
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteAddressBooks() {
      global $toC_Json, $osC_Language;
      
      $osC_ObjectInfo_Customer = new osC_ObjectInfo(osC_Customers_Admin::getData($_REQUEST['customers_id']));
      
      $error = false;
      $feedback = array();
      
      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if ( $osC_ObjectInfo_Customer->get('customers_default_address_id') == $id ) {
          $error = true;
          $feedback[] = $osC_Language->get('delete_warning_primary_address_book_entry');
        }
      }
      
      if ($error === false) {   
        foreach($batch as $id) {
          if (!osC_Customers_Admin::deleteAddress($id)) {
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
    
    function listWishlists() {
      global $toC_Json, $osC_Language, $osC_Database;       
      
      $customers_id = isset($_REQUEST['customers_id']) ? $_REQUEST['customers_id'] : null;
      
      if (is_numeric($customers_id)) {
        $Qwishlists = $osC_Database->query('select wp.wishlists_products_id, wp.products_id, wp.date_added, wp.comments from :table_wishlists w, :table_wishlists_products wp where w.wishlists_id = wp.wishlists_id and w.customers_id = :customers_id');
        $Qwishlists->bindTable(':table_wishlists', TABLE_WISHLISTS);
        $Qwishlists->bindTable(':table_wishlists_products', TABLE_WISHLISTS_PRODUCTS);
        $Qwishlists->bindInt(':customers_id', $customers_id);
        $Qwishlists->execute(); 
        
        $records = array();
        while($Qwishlists->next()) {
          $products_id = osc_get_product_id($Qwishlists->value('products_id'));
          $variants = osc_parse_variants_from_id_string($Qwishlists->value('products_id'));

          $Qname = $osC_Database->query('select products_name from :table_products_description where products_id = :products_id and language_id = :language_id');
          $Qname->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
          $Qname->bindInt(':products_id', $products_id);
          $Qname->bindInt(':language_id', $osC_Language->getID());
          $Qname->execute();
          
          $products_name = $Qname->value('products_name');
          
          if (!empty($variants)) {
            $variants_name = array();
            foreach ($variants as $groups_id => $values_id) {
              $Qvariants = $osC_Database->query('select pvg.products_variants_groups_name, pvv.products_variants_values_name from :table_products_variants pv, :table_products_variants_entries pve, :table_products_variants_groups pvg, :table_products_variants_values pvv where pv.products_id = :products_id and pv.products_variants_id = pve.products_variants_id and pve.products_variants_groups_id = :groups_id and pve.products_variants_values_id = :variants_values_id and pve.products_variants_groups_id = pvg.products_variants_groups_id and pve.products_variants_values_id = pvv.products_variants_values_id and pvg.language_id = :pvg_language_id and pvv.language_id = :pvv_language_id');
              $Qvariants->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
              $Qvariants->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
              $Qvariants->bindTable(':table_products_variants_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
              $Qvariants->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
              $Qvariants->bindInt(':products_id', $products_id);
              $Qvariants->bindInt(':groups_id', $groups_id);
              $Qvariants->bindInt(':variants_values_id', $values_id);
              $Qvariants->bindInt(':pvg_language_id', $osC_Language->getID());
              $Qvariants->bindInt(':pvv_language_id', $osC_Language->getID());
              $Qvariants->execute();
              
              $variants_name[] = $Qvariants->value('products_variants_groups_name') . ' : ' . $Qvariants->value('products_variants_values_name');
            }
            $products_name .= '<br />' . implode('<br />', $variants_name);  
          }
          
          $records[] = array('wishlists_products_id' => $Qwishlists->value('wishlists_products_id'),
                             'products_name' => $products_name,
                             'date_added' => osC_DateTime::getShort($Qwishlists->value('date_added')),
                             'comments' => $Qwishlists->value('comments'));
        }
        $Qwishlists->freeResult();

        $response = array(EXT_JSON_READER_ROOT => $records);
     
        echo $toC_Json->encode($response);        
      }
    }

    function setStatus(){
      global $toC_Json, $osC_Language; 
    
      $flag = $_REQUEST['flag'];
      $customers_id = $_REQUEST['customers_id'];
      
      if (osC_Customers_Admin::setStatus($customers_id, $flag)) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
  }
?>
