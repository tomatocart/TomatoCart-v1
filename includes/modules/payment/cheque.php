<?php
/*
  $Id: cheque.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_cheque extends osC_Payment {
    
  var $_title,
        $_code = 'cheque',
        $_status = false,
        $_sort_order,
        $_order_id;

    function osC_Payment_cheque() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;

      $this->_title = $osC_Language->get('payment_cheque_title');
      $this->_method_title = $osC_Language->get('payment_cheque_method_title');
      $this->_status = (MODULE_PAYMENT_CHEQUE_STATUS == '1') ? true : false;
      $this->_sort_order = MODULE_PAYMENT_CHEQUE_SORT_ORDER;

      if ($this->_status === true) {
        if ((int)MODULE_PAYMENT_CHEQUE_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_CHEQUE_ORDER_STATUS_ID;
        }

        if ((int)MODULE_PAYMENT_CHEQUE_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_CHEQUE_ZONE);
          $Qcheck->bindInt(':zone_country_id', $osC_ShoppingCart->getBillingAddress('country_id'));
          $Qcheck->execute();

          while ($Qcheck->next()) {
            if ($Qcheck->valueInt('zone_id') < 1) {
              $check_flag = true;
              break;
            } elseif ($Qcheck->valueInt('zone_id') == $osC_ShoppingCart->getBillingAddress('zone_id')) {
              $check_flag = true;
              break;
            }
          }

          if ($check_flag == false) {
            $this->_status = false;
          }
        }
      }
    }
  

   function selection() {
    return array('id' => $this->_code,
                 'module' => $this->_method_title);
   }
    
   function confirmation() {
    global $osC_Language;
    
    $confirmation = array('title' => $this->_method_title,
                          'fields' => array(array('title' => $osC_Language->get('payment_cheque_account_owner'),
                                                  'field' => MODULE_PAYMENT_CHEQUE_ACCOUNT_OWNER),
                                            array('title' => $osC_Language->get('payment_cheque_address'),
                                                  'field' => MODULE_PAYMENT_CHEQUE_ADDRESS)
                          ));

    return $confirmation;
  }

  function process() {
      $this->_order_id = osC_Order::insert();
      osC_Order::process($this->_order_id, $this->order_status);
    }
  }
?>
