<?php
/*
  $Id: credit_card.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_CreditCard {

/* Private variables */

    var $_owner,
        $_number,
        $_expiry_month,
        $_expiry_year,
        $_cvc,
        $_type,
        $_data;

/* Class constructor */

    function osC_CreditCard($number = '', $exp_month = '', $exp_year = '') {
      global $osC_Database;

      if (empty($number) === false) {
        $this->_number = ereg_replace('[^0-9]', '', $number);
        $this->_expiry_month = (int)$exp_month;
        $this->_expiry_year = (int)$exp_year;
      }

      $this->_data = array();

      $Qcc = $osC_Database->query('select id, credit_card_name as title, pattern from :table_credit_cards where credit_card_status = "1" order by sort_order, credit_card_name');
      $Qcc->bindTable(':table_credit_cards', TABLE_CREDIT_CARDS);
//      $Qcc->setCache('credit_cards');
      $Qcc->execute();

      while ($Qcc->next()) {
        $this->_data[$Qcc->valueInt('id')] = $Qcc->toArray();
      }
    }

/* Public variables */

    function isValid($valid_cc_types = '') {
      if (CFG_CREDIT_CARDS_VERIFY_WITH_REGEXP == '1') {
        if ($this->hasValidNumber() === false) {
          return -1;
        }

        if ($this->isAccepted($valid_cc_types) === false) {
          return -5;
        }
      }

      if ($this->hasValidExpiryDate() === false) {
        return -2;
      }

      if ($this->hasExpired() === true) {
        return -3;
      }

      if ($this->hasOwner() && ($this->hasValidOwner() === false)) {
        return -4;
      }

      return true;
    }

    function hasValidNumber() {
      if ( (empty($this->_number) === false) && (strlen($this->_number) >= CC_NUMBER_MIN_LENGTH) ) {
        $cardNumber = strrev($this->_number);
        $numSum = 0;

        for ($i=0, $n=strlen($cardNumber); $i<$n; $i++) {
          $currentNum = substr($cardNumber, $i, 1);

// Double every second digit
          if ($i % 2 == 1) {
            $currentNum *= 2;
          }

// Add digits of 2-digit numbers together
          if ($currentNum > 9) {
            $firstNum = $currentNum % 10;
            $secondNum = ($currentNum - $firstNum) / 10;
            $currentNum = $firstNum + $secondNum;
          }

          $numSum += $currentNum;
        }

// If the total has no remainder it's OK
        return ($numSum % 10 == 0);
      }

      return false;
    }

    function isAccepted($valid_cc_types) {
      if ( (empty($valid_cc_types) === false) && (empty($this->_number) === false) && (strlen($this->_number) >= CC_NUMBER_MIN_LENGTH) ) {
        if (is_array($valid_cc_types) === false) {
          $valid_cc_types = explode(',', $valid_cc_types);
        }

        foreach ($this->_data as $data) {
          if (in_array($data['id'], $valid_cc_types)) {
            if (preg_match($data['pattern'], $this->_number) === 1) {
              $this->_type = $data['title'];

              return true;
            }
          }
        }
      }

      return false;
    }

    function hasValidExpiryDate() {
      $year = date('Y');

      return ( ($this->_expiry_month > 0) && ($this->_expiry_month < 13) && ($this->_expiry_year >= $year) && ($this->_expiry_year <= ($year+10)) );
    }

    function hasExpired() {
      return ( ($this->_expiry_year <= date('Y')) && ($this->_expiry_month < date('n')) );
    }

    function hasOwner() {
      return (isset($this->_owner));
    }

    function hasValidOwner() {
      return ( (empty($this->_owner) === false) && (strlen($this->_owner) >= CC_OWNER_MIN_LENGTH) );
    }

    function typeExists($id) {
      return isset($this->_data[$id]);
    }

    function getNumber() {
      return $this->_number;
    }

    function getSafeNumber() {
      return str_repeat('X', strlen($this->_number)-4) . substr($this->_number, -4);
    }

    function getExpiryMonth() {
      return str_pad($this->_expiry_month, 2, '0', STR_PAD_LEFT);
    }

    function getExpiryYear() {
      return $this->_expiry_year;
    }

    function getCVC() {
      return $this->_cvc;
    }

    function getOwner() {
      return $this->_owner;
    }

    function getTypePattern($id) {
      return $this->_data[$id]['pattern'];
    }

    function setOwner($name) {
      $this->_owner = trim($name);
    }

    function setCVC($cvc) {
      $this->_cvc = trim($cvc);
    }
  }
?>
