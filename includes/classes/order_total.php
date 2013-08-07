<?php
/*
  $Id: order_total.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_OrderTotal {
    var $_modules = array(),
        $_data = array(),
        $_group = 'order_total';

// class constructor
    function osC_OrderTotal() {
      global $osC_Database, $osC_Language;

      $Qmodules = $osC_Database->query('select code from :table_templates_boxes where modules_group = "order_total"');
      $Qmodules->bindTable(':table_templates_boxes', TABLE_TEMPLATES_BOXES);
      $Qmodules->setCache('modules-order_total');
      $Qmodules->execute();

      while ($Qmodules->next()) {
        $this->_modules[] = $Qmodules->value('code');
      }

      $Qmodules->freeResult();

      $osC_Language->load('modules-order_total');

      foreach ($this->_modules as $module) {
        $module_class = 'osC_OrderTotal_' . $module;

        if (class_exists($module_class) === false) {
          include(realpath(dirname(__FILE__) . '/../') . '/modules/order_total/' . $module . '.' . substr(basename(__FILE__), (strrpos(basename(__FILE__), '.')+1)));
        }

        $GLOBALS[$module_class] = new $module_class();
      }

      usort($this->_modules, array('osC_OrderTotal', '_usortModules'));
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

    function &getResult() {
      global $osC_ShoppingCart;

      $this->_data = array();

      foreach ($this->_modules as $module) {
        $module = 'osC_OrderTotal_' . $module;

        if ($GLOBALS[$module]->isEnabled() === true) {
          //use the cart total value to caculate the tax of order total module 
          //cart total value before module process
          $pre_total = $osC_ShoppingCart->getTotal();
          
          $GLOBALS[$module]->process();
          
          //cart total value after module process
          $post_total = $osC_ShoppingCart->getTotal();

          foreach ($GLOBALS[$module]->output as $output) {
            if (!empty($output['title']) && !empty($output['text'])) {
              $this->_data[] = array('code' => $GLOBALS[$module]->getCode(),
                                     'title' => $output['title'],
                                     'text' => $output['text'],
                                     'value' => $output['value'],
                                     'tax' => ($post_total - $pre_total - $output['value']),
                                     'sort_order' => $GLOBALS[$module]->getSortOrder());
            }
          }
        }
      }

      return $this->_data;
    }

    function hasActive() {
      static $has_active;

      if (isset($has_active) === false) {
        $has_active = false;

        foreach ($this->_modules as $module) {
          if ($GLOBALS['osC_OrderTotal_' . $module]->isEnabled() === true) {
            $has_active = true;
            break;
          }
        }
      }

      return $has_active;
    }

    function _usortModules($a, $b) {
      if ($GLOBALS['osC_OrderTotal_' . $a]->getSortOrder() == $GLOBALS['osC_OrderTotal_' . $b]->getSortOrder()) {
        return strnatcasecmp($GLOBALS['osC_OrderTotal_' . $a]->getTitle(), $GLOBALS['osC_OrderTotal_' . $a]->getTitle());
      }

      return ($GLOBALS['osC_OrderTotal_' . $a]->getSortOrder() < $GLOBALS['osC_OrderTotal_' . $b]->getSortOrder()) ? -1 : 1;
    }
  }
?>
