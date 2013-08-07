<?php
/*
  $Id: shop_by_price.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require_once('includes/classes/currencies.php');

  class osC_Boxes_shop_by_price extends osC_Modules {
    var $_title,
        $_code = 'shop_by_price',
        $_author_name = 'TomatoCart',
        $_author_www = 'http://www.tomatocart.com',
        $_group = 'boxes';

    function osC_Boxes_shop_by_price() {
      global $osC_Language;

      $this->_title = $osC_Language->get('box_shop_by_price_heading');
    }

    function initialize() {
      global $osC_Database, $osC_Language, $osC_Currencies;

      $constant = constant('BOX_SHOP_BY_PRICE_' . $osC_Currencies->getCode());
      if ( !empty($constant) ) {
        $prices = explode(";", $constant);
        
        if (is_array($prices) && sizeof($prices) > 0) {
          $this->_content = '<ol>';
  
          $pfrom = 0;
          $pto = 0;
  
          if(isset($_GET['pfrom']) && !empty($_GET['pfrom'])){
            $pfrom = $_GET['pfrom'];
          }
  
          if(isset($_GET['pto']) && !empty($_GET['pto'])){
            $pto = $_GET['pto'];
          }
  
          for($n = 0; $n <= sizeof($prices); $n++){
            $filters = array();
            if (isset($_GET['cPath']) && !empty($_GET['cPath'])) {
              $filters[] = 'cPath=' . $_GET['cPath'];
              
              if (isset($_GET['filter']) && !empty($_GET['filter'])) {
                $filters[] = 'filter=' . $_GET['filter'];
              }
            }
            
            if (isset($_GET['manufacturers']) && !empty($_GET['manufacturers'])) {
              $filters[] = 'manufacturers=' . $_GET['manufacturers'];
              
              if (isset($_GET['filter']) && !empty($_GET['filter'])) {
                $filters[] = 'filter=' . $_GET['filter'];
              }
            }
            
            if ($n == 0) {
              $price_section = $osC_Currencies->displayRawPrice(0) . ' ~ ' . $osC_Currencies->displayRawPrice($prices[$n]);
  
              if ($pfrom == 0 && $pto == $prices[$n]) {
                $price_section = '<b>' . $price_section . '</b>';
              }
              
              $params = 'keywords=' . $_GET['keywords'] . '&x=0&y=0&pfrom=' . 0 . '&pto=' . $prices[$n] . '&' . implode('&', $filters);
            } else if ($n == sizeof($prices)) {
              $price_section = $osC_Currencies->displayRawPrice($prices[$n-1]) . ' + ';
  
              if ($pfrom == $prices[$n-1] && $pto == 0) {
                $price_section = '<b>' . $price_section . '</b>';
              }
              
              $params = 'keywords=' . $_GET['keywords'] . '&x=0&y=0&pfrom=' . $prices[$n-1] . '&pto=' . '&' . implode('&', $filters);
            } else {
              $price_section = $osC_Currencies->displayRawPrice($prices[$n-1]) . ' ~ ' . $osC_Currencies->displayRawPrice($prices[$n]);
  
              if ($pfrom == $prices[$n-1] && $pto == $prices[$n]) {
                $price_section = '<b>' . $price_section . '</b>';
              }
              
              $params = 'keywords=' . $_GET['keywords'] . '&x=0&y=0&pfrom=' . $prices[$n-1] . '&pto=' . $prices[$n] . '&' . implode('&', $filters);
            }
            
            if ( (defined('BOX_SHOP_BY_PRICE_RECURSIVE')) && ((int)BOX_SHOP_BY_PRICE_RECURSIVE == 1) ) {
              $params .= '&recursive=1';
            }
  
            $this->_content .= '<li>' . osc_link_object(osc_href_link(FILENAME_SEARCH, $params), $price_section) . '</li>';
          }
  
          $this->_content .= '</ol>';
        }
      }
    }

    function install() {
      global $osC_Database, $osC_Currencies;

      parent::install();

      if (!isset($osC_Currencies) && !is_object($osC_Currencies)) {
        $osC_Currencies = new osC_Currencies;
      }

      foreach ($osC_Currencies->currencies as $key => $value) {
        $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . $value['title'] . "', '" . "BOX_SHOP_BY_PRICE_" . $key . "', '','" . $value['title'] . " price interval (Price seperated by \";\")', '6', '0', now())");
      }
      
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('search to be recursive? ', 'BOX_SHOP_BY_PRICE_RECURSIVE', '1', 'Do you want the search to be recursive?If it is true, the products in the sub categories will be displayed.', '6', '0', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
    }

    function getKeys() {
      global $osC_Currencies;

      if (!isset($osC_Currencies) && !is_object($osC_Currencies)) {
        $osC_Currencies = new osC_Currencies;
      }
      
      self::_verifyCurrencies();

      if (!isset($this->_keys)) {
        foreach ($osC_Currencies->currencies as $key => $value) {
          $this->_keys[] = 'BOX_SHOP_BY_PRICE_' . $key;
        }
        
        $this->_keys[] = 'BOX_SHOP_BY_PRICE_RECURSIVE';
      }

      return $this->_keys;
    }
    
    function _verifyCurrencies() {
      global $osC_Currencies, $osC_Database;
      
      foreach ($osC_Currencies->currencies as $key => $value) {
        if (!defined('BOX_SHOP_BY_PRICE_' . $key)) {
          $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . $value['title'] . "', '" . "BOX_SHOP_BY_PRICE_" . $key . "', '','" . $value['title'] . " price interval (Price seperated by \";\")', '6', '0', now())");
        
          define('BOX_SHOP_BY_PRICE_' . $key, '');
        }
      }
    }
  }
?>
