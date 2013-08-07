<?php
/*
  $Id: account.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Account {

    function &getEntry() {
      global $osC_Database, $osC_Customer, $osC_Language;

      $Qaccount = $osC_Database->query('select tc.customers_credits, tc.customers_gender, tc.customers_firstname, tc.customers_lastname, date_format(tc.customers_dob, "%Y") as customers_dob_year, date_format(tc.customers_dob, "%m") as customers_dob_month, date_format(tc.customers_dob, "%d") as customers_dob_date, tc.customers_email_address, tcgd.customers_groups_name from :table_customers tc left join :table_customers_groups_description tcgd on tc.customers_groups_id = tcgd.customers_groups_id where tc.customers_id = :customers_id and tcgd.language_id = :language_id');
      $Qaccount->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qaccount->bindTable(':table_customers_groups_description', TABLE_CUSTOMERS_GROUPS_DESCRIPTION);
      $Qaccount->bindInt(':customers_id', $osC_Customer->getID());
      $Qaccount->bindInt(':language_id', $osC_Language->getID());
      $Qaccount->execute();

      return $Qaccount;
    }

    function getID($email_address) {
      global $osC_Database;

      $Quser = $osC_Database->query('select customers_id from :table_customers where customers_email_address = :customers_email_address limit 1');
      $Quser->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Quser->bindValue(':customers_email_address', $email_address);
      $Quser->execute();

      if ($Quser->numberOfRows() === 1) {
        return $Quser->valueInt('customers_id');
      }

      return false;
    }

    function createEntry($data, $restore_cart_contents = true, $send_email = true) {
      global $osC_Database, $osC_Session, $osC_Language, $osC_ShoppingCart, $osC_Customer, $osC_NavigationHistory, $toC_Wishlist;

      $Qcustomer = $osC_Database->query('insert into :table_customers (customers_firstname, customers_lastname, customers_email_address, customers_newsletter, customers_status, customers_ip_address, customers_password, customers_gender, customers_dob, number_of_logons, date_account_created) values (:customers_firstname, :customers_lastname, :customers_email_address, :customers_newsletter, :customers_status, :customers_ip_address, :customers_password, :customers_gender, :customers_dob, :number_of_logons, :date_account_created)');
      $Qcustomer->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qcustomer->bindValue(':customers_firstname', $data['firstname']);
      $Qcustomer->bindValue(':customers_lastname', $data['lastname']);
      $Qcustomer->bindValue(':customers_email_address', $data['email_address']);
      $Qcustomer->bindValue(':customers_newsletter', (isset($data['newsletter']) && ($data['newsletter'] == '1') ? '1' : ''));
      $Qcustomer->bindValue(':customers_status', '1');
      $Qcustomer->bindValue(':customers_ip_address', osc_get_ip_address());
      $Qcustomer->bindValue(':customers_password', osc_encrypt_string($data['password']));
      $Qcustomer->bindValue(':customers_gender', (((ACCOUNT_GENDER > -1) && isset($data['gender']) && (($data['gender'] == 'm') || ($data['gender'] == 'f'))) ? $data['gender'] : ''));
      $Qcustomer->bindValue(':customers_dob', ((ACCOUNT_DATE_OF_BIRTH == '1') ? date('Ymd', $data['dob']) : ''));
      $Qcustomer->bindInt(':number_of_logons', 0);
      $Qcustomer->bindRaw(':date_account_created', 'now()');
      $Qcustomer->execute();
      
      if ($Qcustomer->affectedRows() === 1) {
        $customer_id = $osC_Database->nextID();

        $QcustomerGroup = $osC_Database->query('select customers_groups_id from :table_customers_groups where is_default = 1');
        $QcustomerGroup->bindTable(':table_customers_groups', TABLE_CUSTOMERS_GROUPS);
        $QcustomerGroup->execute();

        if($QcustomerGroup->numberOfRows() == 1) {
          $osC_Database->simpleQuery('update ' . TABLE_CUSTOMERS . ' set customers_groups_id = ' . $QcustomerGroup->valueInt('customers_groups_id') . ' where customers_id = ' . $customer_id);
        }

        if (SERVICE_SESSION_REGENERATE_ID == '1') {
          $osC_Session->recreate();
        }

        $osC_Customer->setCustomerData($customer_id);

//restore cart contents
        if ($restore_cart_contents === true) {
          $osC_ShoppingCart->synchronizeWithDatabase();        
        }
//restore wishlist contents
        $toC_Wishlist->synchronizeWithDatabase();    

        $osC_NavigationHistory->removeCurrentPage();

        include('email_template.php');
        $email = toC_Email_Template::getEmailTemplate('create_account_email');
        $email->setData($osC_Customer, $data['password']);
        $email->buildMessage();
        $email->sendEmail();

        return true;
      }

      return false;
    }

    function saveEntry($data) {
      global $osC_Database, $osC_Customer;

      $Qcustomer = $osC_Database->query('update :table_customers set customers_gender = :customers_gender, customers_firstname = :customers_firstname, customers_lastname = :customers_lastname, customers_email_address = :customers_email_address, customers_dob = :customers_dob, date_account_last_modified = :date_account_last_modified where customers_id = :customers_id');
      $Qcustomer->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qcustomer->bindValue(':customers_gender', ((ACCOUNT_GENDER > -1) && isset($data['gender']) && (($data['gender'] == 'm') || ($data['gender'] == 'f'))) ? $data['gender'] : '');
      $Qcustomer->bindValue(':customers_firstname', $data['firstname']);
      $Qcustomer->bindValue(':customers_lastname', $data['lastname']);
      $Qcustomer->bindValue(':customers_email_address', $data['email_address']);
      $Qcustomer->bindValue(':customers_dob', (ACCOUNT_DATE_OF_BIRTH == '1') ? date('Ymd', $data['dob']) : '');
      $Qcustomer->bindRaw(':date_account_last_modified', 'now()');
      $Qcustomer->bindInt(':customers_id', $osC_Customer->getID());
      $Qcustomer->execute();

      if ($Qcustomer->affectedRows() === 1) {
        return true;
      }

      return false;
    }

    function savePassword($password, $customer_id = null) {
      global $osC_Database, $osC_Customer;

      if (is_numeric($customer_id) === false) {
        $customer_id = $osC_Customer->getID();
      }

      $Qcustomer = $osC_Database->query('update :table_customers set customers_password = :customers_password, date_account_last_modified = :date_account_last_modified where customers_id = :customers_id');
      $Qcustomer->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qcustomer->bindValue(':customers_password', osc_encrypt_string($password));
      $Qcustomer->bindRaw(':date_account_last_modified', 'now()');
      $Qcustomer->bindInt(':customers_id', $customer_id);
      $Qcustomer->execute();

      if ($Qcustomer->affectedRows() === 1) {
        return true;
      }

      return false;
    }

    function checkEntry($email_address) {
      global $osC_Database;

      $Qcheck = $osC_Database->query('select customers_id from :table_customers where customers_email_address = :customers_email_address limit 1');
      $Qcheck->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qcheck->bindValue(':customers_email_address', $email_address);
      $Qcheck->execute();

      if ($Qcheck->numberOfRows() === 1) {
        return true;
      }

      return false;
    }

    function checkPassword($password, $email_address = null) {
      global $osC_Database, $osC_Customer;

      if ($email_address === null) {
        $Qcheck = $osC_Database->query('select customers_password from :table_customers where customers_id = :customers_id');
        $Qcheck->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcheck->bindInt(':customers_id', $osC_Customer->getID());
        $Qcheck->execute();
      } else {
        $Qcheck = $osC_Database->query('select customers_password from :table_customers where customers_email_address = :customers_email_address limit 1');
        $Qcheck->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcheck->bindValue(':customers_email_address', $email_address);
        $Qcheck->execute();
      }

      if ($Qcheck->numberOfRows() === 1) {
        if ( (strlen($password) > 0) && (strlen($Qcheck->value('customers_password')) > 0) ) {
          $stack = explode(':', $Qcheck->value('customers_password'));

          if (sizeof($stack) === 2) {
            if (md5($stack[1] . $password) == $stack[0]) {
              return true;
            }
          }
        }
      }

      return false;
    }
  
    function checkStatus($email_address) {
      global $osC_Database;

      $Qcheck = $osC_Database->query('select customers_status from :table_customers where customers_email_address = :customers_email_address limit 1');
      $Qcheck->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qcheck->bindValue(':customers_email_address', $email_address);
      $Qcheck->execute();

      if ($Qcheck->numberOfRows() === 1) {
        if ( $Qcheck->valueInt('customers_status') == 1 ) {
          return true;
        }
      }

      return false;
    }
    
    function checkDuplicateEntry($email_address) {
      global $osC_Database, $osC_Customer;

      $Qcheck = $osC_Database->query('select customers_id from :table_customers where customers_email_address = :customers_email_address and customers_id != :customers_id limit 1');
      $Qcheck->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qcheck->bindValue(':customers_email_address', $email_address);
      $Qcheck->bindInt(':customers_id', $osC_Customer->getID());
      $Qcheck->execute();

      if ($Qcheck->numberOfRows() === 1) {
        return true;
      }

      return false;
    }
    
    function createNewAddress($customers_id, $address) {
      global $osC_Database, $osC_Customer;
     
      $Qab = $osC_Database->query('insert into :table_address_book (customers_id, entry_gender, entry_company, entry_firstname, entry_lastname, entry_street_address, entry_suburb, entry_postcode, entry_city, entry_state, entry_country_id, entry_zone_id, entry_telephone, entry_fax) values (:customers_id, :entry_gender, :entry_company, :entry_firstname, :entry_lastname, :entry_street_address, :entry_suburb, :entry_postcode, :entry_city, :entry_state, :entry_country_id, :entry_zone_id, :entry_telephone, :entry_fax)');
      $Qab->bindTable(':table_address_book', TABLE_ADDRESS_BOOK);
      $Qab->bindInt(':customers_id', $customers_id);
      $Qab->bindValue(':entry_gender', $address['gender']);
      $Qab->bindValue(':entry_company', $address['company']);
      $Qab->bindValue(':entry_firstname', $address['firstname']);
      $Qab->bindValue(':entry_lastname', $address['lastname']);
      $Qab->bindValue(':entry_street_address', $address['street_address']);
      $Qab->bindValue(':entry_suburb', $address['suburb']);
      $Qab->bindValue(':entry_postcode', $address['postcode']);
      $Qab->bindValue(':entry_city', $address['city']);
      $Qab->bindValue(':entry_state', $address['state']);
      $Qab->bindInt(':entry_country_id', $address['country_id']);
      $Qab->bindInt(':entry_zone_id', $address['zone_id']);
      $Qab->bindValue(':entry_telephone', $address['telephone_number']);
      $Qab->bindValue(':entry_fax', $address['fax']);
      $Qab->execute();
      
      if (!$osC_Database->isError()) {
        $address_book_id = $osC_Database->nextID();

        $Qcheck = $osC_Database->query('select customers_default_address_id from :table_customers where customers_id = :customers_id');
        $Qcheck->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcheck->bindInt(':customers_id', $customers_id);
        $Qcheck->execute();

        if ($Qcheck->valueInt('customers_default_address_id') == 0) {
          require_once('includes/classes/address_book.php');
          
          if (osC_AddressBook::setPrimaryAddress($address_book_id)) {
            $osC_Customer->setCountryID($address['country_id']);
            $osC_Customer->setZoneID(($address['zone_id'] > 0) ? (int)$address['zone_id'] : '0');
            $osC_Customer->setDefaultAddressID($address_book_id);
  
            $osC_Customer->synchronizeCustomerDataWithSession();
            return true;
          } else {
            return false;
          }
        }
        
        

        return true;
      }

      return false;      
    }
  }
?>
