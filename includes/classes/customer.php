<?php
/*
  $Id: customer.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Customer {

/* Private variables */

    var $_is_logged_on = false,
        $_data = array();

/* Class constructor */

    function osC_Customer() {
      if (isset($_SESSION['osC_Customer_data']) && is_array($_SESSION['osC_Customer_data']) && isset($_SESSION['osC_Customer_data']['id']) && is_numeric($_SESSION['osC_Customer_data']['id'])) {
        $this->setIsLoggedOn(true);
        $this->_data =& $_SESSION['osC_Customer_data'];
      }
    }

/* Public methods */

    function getID() {
      if (isset($this->_data['id']) && is_numeric($this->_data['id'])) {
        return $this->_data['id'];
      }

      return false;
    }

    function getCustomerGroupID() {
      if (isset($this->_data['customers_groups_id']) && is_numeric($this->_data['customers_groups_id'])) {
        return $this->_data['customers_groups_id'];
      }

      return false;
    }

    function getFirstName() {
      static $first_name = null;

      if (is_null($first_name)) {
        if (isset($this->_data['first_name'])) {
          $first_name = $this->_data['first_name'];
        }
      }

      return $first_name;
    }

    function getLastName() {
      static $last_name = null;

      if (is_null($last_name)) {
        if (isset($this->_data['last_name'])) {
          $last_name = $this->_data['last_name'];
        }
      }

      return $last_name;
    }

    function getName() {
      static $name = '';

      if (empty($name)) {
        if (isset($this->_data['first_name'])) {
          $name .= $this->_data['first_name'];
        }

        if (isset($this->_data['last_name'])) {
          if (empty($name) === false) {
            $name .= ' ';
          }

          $name .= $this->_data['last_name'];
        }
      }

      return $name;
    }

    function getGender() {
      static $gender = null;

      if (is_null($gender)) {
        if (isset($this->_data['gender'])) {
          $gender = $this->_data['gender'];
        }
      }

      return $gender;
    }

    function getEmailAddress() {
      static $email_address = null;

      if (is_null($email_address)) {
        if (isset($this->_data['email_address'])) {
          $email_address = $this->_data['email_address'];
        }
      }

      return $email_address;
    }

    function getCountryID() {
      static $country_id = null;

      if (is_null($country_id)) {
        if (isset($this->_data['country_id'])) {
          $country_id = $this->_data['country_id'];
        }
      }

      return $country_id;
    }

    function getZoneID() {
      static $zone_id = null;

      if (is_null($zone_id)) {
        if (isset($this->_data['zone_id'])) {
          $zone_id = $this->_data['zone_id'];
        }
      }

      return $zone_id;
    }

    function getDefaultAddressID() {
      static $id = null;

      if (is_null($id)) {
        if (isset($this->_data['default_address_id'])) {
          $id = $this->_data['default_address_id'];
        }
      }

      return $id;
    }

    function getCustomerGroupDiscount() {
      static $customers_groups_discount = null;

      if (is_null($customers_groups_discount)) {
        if (isset($this->_data['customers_groups_discount'])) {
          $customers_groups_discount = $this->_data['customers_groups_discount'];
        }
      }

      return $customers_groups_discount;
    }

    function setCustomerData($customer_id = -1) {
      global $osC_Database;

      $this->_data = array();

      if (is_numeric($customer_id) && ($customer_id > 0)) {
        $Qcustomer = $osC_Database->query('select c.customers_gender, c.customers_firstname, c.customers_lastname, c.customers_email_address, c.customers_default_address_id, c.customers_groups_id, c.customers_credits, cg.customers_groups_discount from :table_customers c left join :table_customers_groups cg on c.customers_groups_id = cg.customers_groups_id where customers_id = :customers_id');
        $Qcustomer->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcustomer->bindTable(':table_customers_groups', TABLE_CUSTOMERS_GROUPS);
        $Qcustomer->bindInt(':customers_id', $customer_id);
        $Qcustomer->execute();

        if ($Qcustomer->numberOfRows() === 1) {
          $this->setIsLoggedOn(true);
          $this->setID($customer_id);
          $this->setCustomerGroupID($Qcustomer->value('customers_groups_id'));
          $this->setGender($Qcustomer->value('customers_gender'));
          $this->setFirstName($Qcustomer->value('customers_firstname'));
          $this->setLastName($Qcustomer->value('customers_lastname'));
          $this->setEmailAddress($Qcustomer->value('customers_email_address'));
          $this->setCustomerGroupDiscount($Qcustomer->value('customers_groups_discount'));
          $this->setStoreCredit($Qcustomer->value('customers_credits'));

          if (is_numeric($Qcustomer->value('customers_default_address_id')) && ($Qcustomer->value('customers_default_address_id') > 0)) {
            $Qab = $osC_Database->query('select entry_country_id, entry_zone_id from :table_address_book where address_book_id = :address_book_id and customers_id = :customers_id');
            $Qab->bindTable(':table_address_book', TABLE_ADDRESS_BOOK);
            $Qab->bindInt(':address_book_id', $Qcustomer->value('customers_default_address_id'));
            $Qab->bindInt(':customers_id', $customer_id);
            $Qab->execute();

            if ($Qab->numberOfRows() === 1) {
              $this->setCountryID($Qab->value('entry_country_id'));
              $this->setZoneID($Qab->value('entry_zone_id'));
              $this->setDefaultAddressID($Qcustomer->value('customers_default_address_id'));

              $Qab->freeResult();
            }
          }
        }

        $Qcustomer->freeResult();
      }

      if (sizeof($this->_data) > 0) {
        $_SESSION['osC_Customer_data'] = $this->_data;
      } elseif (isset($_SESSION['osC_Customer_data'])) {
        $this->reset();
      }
    }
    
    function synchronizeCustomerDataWithSession() {
      if (sizeof($this->_data) > 0) {
        $_SESSION['osC_Customer_data'] = $this->_data;
      } elseif (isset($_SESSION['osC_Customer_data'])) {
        $this->reset();
      }
    }

    function setIsLoggedOn($state) {
      if ($state === true) {
        $this->_is_logged_on = true;
      } else {
        $this->_is_logged_on = false;
      }
    }

    function isLoggedOn() {
      if ($this->_is_logged_on === true) {
        return true;
      }

      return false;
    }

    function setID($id) {
      if (is_numeric($id) && ($id > 0)) {
        $this->_data['id'] = $id;
      } else {
        $this->_data['id'] = false;
      }
    }

    function setCustomerGroupID($customers_groups_id) {
      if (is_numeric($customers_groups_id) && ($customers_groups_id > 0)) {
        $this->_data['customers_groups_id'] = $customers_groups_id;
      } else {
        $this->_data['customers_groups_id'] = false;
      }
    }

    function setCustomerGroupDiscount($customers_groups_discount) {
      if (is_numeric($customers_groups_discount) && ($customers_groups_discount > 0)) {
        $this->_data['customers_groups_discount'] = $customers_groups_discount;
      } else {
        $this->_data['customers_groups_discount'] = false;
      }
    }

    function setDefaultAddressID($id) {
      if (is_numeric($id) && ($id > 0)) {
        $this->_data['default_address_id'] = $id;
      } else {
        $this->_data['default_address_id'] = false;
      }
    }

    function hasDefaultAddress() {
      if (isset($this->_data['default_address_id']) && is_numeric($this->_data['default_address_id'])) {
        return true;
      }

      return false;
    }

    function setGender($gender) {
      if ( (strtolower($gender) == 'm') || (strtolower($gender) == 'f') ) {
        $this->_data['gender'] = strtolower($gender);
      } else {
        $this->_data['gender'] = false;
      }
    }

    function setFirstName($first_name) {
      $this->_data['first_name'] = $first_name;
    }

    function setLastName($last_name) {
      $this->_data['last_name'] = $last_name;
    }

    function setEmailAddress($email_address) {
      $this->_data['email_address'] = $email_address;
    }

    function setCountryID($id) {
      $this->_data['country_id'] = $id;
    }

    function setZoneID($id) {
      $this->_data['zone_id'] = $id;
    }
    
    function synchronizeStoreCreditWithDatabase() {
      global $osC_Database;
     
      if ( $this->isLoggedOn() === true ) {
        $Qcredit = $osC_Database->query('select customers_credits from :table_customers where customers_id = :customers_id');
        $Qcredit->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcredit->bindInt(':customers_id', $this->getID());
        $Qcredit->execute();

        if ($Qcredit->numberOfRows() === 1) {
          $this->_data['customers_credits'] = $Qcredit->value('customers_credits');
        }

        $Qcredit->freeResult();
      }
    }
    
    function hasStoreCredit() {
      if (isset($this->_data['customers_credits']) && ($this->_data['customers_credits'] > 0)) {
        return true;
      }

      return false;
    }
  
    function setStoreCredit($credit) {
      $this->_data['customers_credits'] = $credit;
    }
    
    function getStoreCredit() {
      static $customers_credits = null;

      if (is_null($customers_credits)) {
        if (isset($this->_data['customers_credits'])) {
          $customers_credits = $this->_data['customers_credits'];
        }
      }

      return $customers_credits;
    }

    function reset() {
      $this->_is_logged_on = false;
      $this->_data = array();

      if (isset($_SESSION['osC_Customer_data'])) {
        unset($_SESSION['osC_Customer_data']);
      }
    }
  }
?>
