<?php
/*
  $Id: currencies.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Currencies {
    var $currencies = array();

// class constructor
    function osC_Currencies() {
      global $osC_Database;

      $Qcurrencies = $osC_Database->query('select * from :table_currencies');
      $Qcurrencies->bindTable(':table_currencies', TABLE_CURRENCIES);
      $Qcurrencies->setCache('currencies');
      $Qcurrencies->execute();

      while ($Qcurrencies->next()) {
        $this->currencies[$Qcurrencies->value('code')] = array('id' => $Qcurrencies->valueInt('currencies_id'),
                                                               'title' => $Qcurrencies->value('title'),
                                                               'symbol_left' => $Qcurrencies->value('symbol_left'),
                                                               'symbol_right' => $Qcurrencies->value('symbol_right'),
                                                               'decimal_places' => $Qcurrencies->valueInt('decimal_places'),
                                                               'value' => $Qcurrencies->valueDecimal('value'));
      }

      $Qcurrencies->freeResult();
    }

// class methods
    function format($number, $currency_code = '', $currency_value = '') {
      global $osC_Language;

      if (empty($currency_code) || ($this->exists($currency_code) == false)) {
        $currency_code = (isset($_SESSION['currency']) ? $_SESSION['currency'] : DEFAULT_CURRENCY);
      }

      if (empty($currency_value) || (is_numeric($currency_value) == false)) {
        $currency_value = $this->currencies[$currency_code]['value'];
      }

      return $this->currencies[$currency_code]['symbol_left'] . number_format(osc_round($number * $currency_value, $this->currencies[$currency_code]['decimal_places']), $this->currencies[$currency_code]['decimal_places'], $osC_Language->getNumericDecimalSeparator(), $osC_Language->getNumericThousandsSeparator()) . $this->currencies[$currency_code]['symbol_right'];
    }

    function formatRaw($number, $currency_code = '', $currency_value = '') {
      if (empty($currency_code) || ($this->exists($currency_code) == false)) {
        $currency_code = (isset($_SESSION['currency']) ? $_SESSION['currency'] : DEFAULT_CURRENCY);
      }

      if (empty($currency_value) || (is_numeric($currency_value) == false)) {
        $currency_value = $this->currencies[$currency_code]['value'];
      }

      return number_format(osc_round($number * $currency_value, $this->currencies[$currency_code]['decimal_places']), $this->currencies[$currency_code]['decimal_places'], '.', '');
    }

    function addTaxRateToPrice($price, $tax_rate, $quantity = 1) {
      global $osC_Tax;

      $price = osc_round($price, $this->currencies[DEFAULT_CURRENCY]['decimal_places']);

      if ( (DISPLAY_PRICE_WITH_TAX == '1') && ($tax_rate > 0) ) {
        $price += osc_round($price * ($tax_rate / 100), $this->currencies[DEFAULT_CURRENCY]['decimal_places']);
      }

      return osc_round($price * $quantity, $this->currencies[DEFAULT_CURRENCY]['decimal_places']);
    }

    function displayPrice($price, $tax_class_id, $quantity = 1, $currency_code = null, $currency_value = null) {
      global $osC_Tax;

      $price = osc_round($price, $this->currencies[DEFAULT_CURRENCY]['decimal_places']);

      if ( (DISPLAY_PRICE_WITH_TAX == '1') && ($tax_class_id > 0) ) {
        $price += osc_round($price * ($osC_Tax->getTaxRate($tax_class_id) / 100), $this->currencies[DEFAULT_CURRENCY]['decimal_places']);
      }

      return $this->format($price * $quantity, $currency_code, $currency_value);
    }

    function displayPriceWithTaxRate($price, $tax_rate, $quantity = 1, $currency_code = '', $currency_value = '') {
      global $osC_Tax;

      $price = osc_round($price, $this->currencies[DEFAULT_CURRENCY]['decimal_places']);

      if ( (DISPLAY_PRICE_WITH_TAX == '1') && ($tax_rate > 0) ) {
        $price += osc_round($price * ($tax_rate / 100), $this->currencies[DEFAULT_CURRENCY]['decimal_places']);
      }

      return $this->format($price * $quantity, $currency_code, $currency_value);
    }

    function displayRawPrice($number, $currency_code = '') {
      global $osC_Language;

      if (empty($currency_code) || ($this->exists($currency_code) == false)) {
        $currency_code = (isset($_SESSION['currency']) ? $_SESSION['currency'] : DEFAULT_CURRENCY);
      }

      return $this->currencies[$currency_code]['symbol_left'] . number_format(osc_round($number, $this->currencies[$currency_code]['decimal_places']), $this->currencies[$currency_code]['decimal_places'], $osC_Language->getNumericDecimalSeparator(), $osC_Language->getNumericThousandsSeparator()) . $this->currencies[$currency_code]['symbol_right'];
    }

    function exists($code) {
      if (isset($this->currencies[$code])) {
        return true;
      }

      return false;
    }

    function decimalPlaces($code) {
      if ($this->exists($code)) {
        return $this->currencies[$code]['decimal_places'];
      }

      return false;
    }

    function value($code) {
      if ($this->exists($code)) {
        return $this->currencies[$code]['value'];
      }

      return false;
    }

    function getData() {
      return $this->currencies;
    }

    function getCode($id = '') {
      if (is_numeric($id)) {
        foreach ($this->currencies as $key => $value) {
          if ($value['id'] == $id) {
            return $key;
          }
        }
      } else {
        return $_SESSION['currency'];
      }
    }

    function getSymbolLeft($id = '') {
      if (is_numeric($id)) {
        foreach ($this->currencies as $key => $value) {
          if ($value['id'] == $id) {
            return $value['symbol_left'];
          }
        }
      } else {
        return $this->currencies[DEFAULT_CURRENCY]['symbol_left'];
      }
    }

    function getSymbolRight($id = '') {
      if (is_numeric($id)) {
        foreach ($this->currencies as $key => $value) {
          if ($value['id'] == $id) {
            return $value['symbol_right'];
          }
        }
      } else {
        return $this->currencies[DEFAULT_CURRENCY]['symbol_right'];
      }
    }
    
    function getDecimalPlaces($id = '') {
      if (is_numeric($id)) {
        foreach ($this->currencies as $key => $value) {
          if ($value['id'] == $id) {
            return $value['decimal_places'];
          }
        }
      } else {
        return $this->currencies[DEFAULT_CURRENCY]['decimal_places'];
      }
    }   
     
    function getID($code = '') {
      if (empty($code)) {
        $code = $_SESSION['currency'];
      }

      return $this->currencies[$code]['id'];
    }
  }
?>
