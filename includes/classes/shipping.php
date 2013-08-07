<?php
/*
  $Id: shipping.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Shipping {
    var $_modules = array(),
        $_selected_module,
        $_quotes = array(),
        $_group = 'shipping';

// class constructor
    function osC_Shipping($module = '') {
      global $osC_Database, $osC_Language;

      $this->_quotes =& $_SESSION['osC_ShoppingCart_data']['shipping_quotes'];

      $Qmodules = $osC_Database->query('select code from :table_templates_boxes where modules_group = "shipping"');
      $Qmodules->bindTable(':table_templates_boxes', TABLE_TEMPLATES_BOXES);
      $Qmodules->setCache('modules-shipping');
      $Qmodules->execute();

      while ($Qmodules->next()) {
        $this->_modules[] = $Qmodules->value('code');
      }

      $Qmodules->freeResult();

      if (empty($this->_modules) === false) {
        if ((empty($module) === false) && in_array(substr($module, 0, strpos($module, '_')), $this->_modules)) {
          $this->_selected_module = $module;
          $this->_modules = array(substr($module, 0, strpos($module, '_')));
        }

        $osC_Language->load('modules-shipping');

        foreach ($this->_modules as $module) {
          $module_class = 'osC_Shipping_' . $module;

          if (class_exists($module_class) === false) {
            include(realpath(dirname(__FILE__) . '/../') . '/modules/shipping/' . $module . '.' . substr(basename(__FILE__), (strrpos(basename(__FILE__), '.')+1)));
          }

          $GLOBALS[$module_class] = new $module_class();
          $GLOBALS[$module_class]->initialize();
        }

        usort($this->_modules, array('osC_Shipping', '_usortModules'));
      }

      $this->_calculate();
    }

// class methods
    function getCode() {
      return $this->_code;
    }

    function getTitle() {
      return $this->_title;
    }

    function getDescription() {
      return $this->_description;
    }

    function isEnabled() {
      return $this->_status;
    }

    function getSortOrder() {
      return $this->_sort_order;
    }

    function hasQuotes() {
      return !empty($this->_quotes);
    }

    function numberOfQuotes() {
      $total_quotes = 0;

      foreach ($this->_quotes as $quotes) {
        $total_quotes += sizeof($quotes['methods']);
      }

      return $total_quotes;
    }

    function getQuotes() {
      return $this->_quotes;
    }

    function getQuote($module = '') {
      if (empty($module)) {
        $module = $this->_selected_module;
      }

      list($module_id, $method_id) = explode('_', $module);

      $rate = array();

      foreach ($this->_quotes as $quote) {
        if ($quote['id'] == $module_id) {
          foreach ($quote['methods'] as $method) {
            if ($method['id'] == $method_id) {
              $rate = array('id' => $module,
                            'title' => $quote['module'] . ((empty($method['title']) === false) ? ' (' . $method['title'] . ')' : ''),
                            'cost' => $method['cost'],
                            'tax_class_id' => $quote['tax_class_id'],
                            'is_cheapest' => null);

              break 2;
            }
          }
        }
      }

      return $rate;
    }

    function getCheapestQuote() {
      $rate = array();

      foreach ($this->_quotes as $quote) {
        if (!empty($quote['methods'])) {
          foreach ($quote['methods'] as $method) {
            if (empty($rate) || ($method['cost'] < $rate['cost'])) {
              $rate = array('id' => $quote['id'] . '_' . $method['id'],
                            'title' => $quote['module'] . ((empty($method['title']) === false) ? ' (' . $method['title'] . ')' : ''),
                            'cost' => $method['cost'],
                            'tax_class_id' => $quote['tax_class_id'],
                            'is_cheapest' => false);
            }
          }
        }
      }

      if (!empty($rate)) {
        $rate['is_cheapest'] = true;
      }

      return $rate;
    }

    function hasActive() {
      static $has_active;

      if (isset($has_active) === false) {
        $has_active = false;

        foreach ($this->_modules as $module) {
          if ($GLOBALS['osC_Shipping_' . $module]->isEnabled()) {
            $has_active = true;
            break;
          }
        }
      }

      return $has_active;
    }

    function _calculate() {
      $this->_quotes = array();

      if (is_array($this->_modules)) {
        $include_quotes = array();

        foreach ($this->_modules as $module) {
          if ($GLOBALS['osC_Shipping_' . $module]->isEnabled()) {
            $include_quotes[] = 'osC_Shipping_' . $module;
          }
        }

        foreach ($include_quotes as $module) {
          $quotes = $GLOBALS[$module]->quote();
          
          if (is_array($quotes)) {
            $this->_quotes[] = $quotes;
          }
        }
      }
    }

    function _usortModules($a, $b) {
      if ($GLOBALS['osC_Shipping_' . $a]->getSortOrder() == $GLOBALS['osC_Shipping_' . $b]->getSortOrder()) {
        return strnatcasecmp($GLOBALS['osC_Shipping_' . $a]->getTitle(), $GLOBALS['osC_Shipping_' . $a]->getTitle());
      }

      return ($GLOBALS['osC_Shipping_' . $a]->getSortOrder() < $GLOBALS['osC_Shipping_' . $b]->getSortOrder()) ? -1 : 1;
    }
  }
?>
