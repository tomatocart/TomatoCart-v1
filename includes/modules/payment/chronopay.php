<?php
/*
  $Id: chronopay.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_chronopay extends osC_Payment {
    var $_title,
        $_code = 'chronopay',
        $_status = false,
        $_sort_order,
        $_order_id;

    function osC_Payment_chronopay() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;

      $this->_title = $osC_Language->get('payment_chronopay_title');
      $this->_method_title = $osC_Language->get('payment_chronopay_method_title');
      $this->_status = (MODULE_PAYMENT_CHRONOPAY_STATUS == '1') ? true : false;
      $this->_sort_order = MODULE_PAYMENT_CHRONOPAY_SORT_ORDER;

      $this->form_action_url = 'https://secure.chronopay.com/index_shop.cgi';

      if ($this->_status === true) {
        if ((int)MODULE_PAYMENT_CHRONOPAY_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_CHRONOPAY_ORDER_STATUS_ID;
        }

        if ((int)MODULE_PAYMENT_CHRONOPAY_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_CHRONOPAY_ZONE);
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

          if ($check_flag === false) {
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
      $this->_order_id = osC_Order::insert();
    }

    function process_button() {
      global $osC_Customer, $osC_Currencies, $osC_ShoppingCart;

      if (MODULE_PAYMENT_CHRONOPAY_CURRENCY == 'Selected Currency') {
        $currency = $osC_Currencies->getCode();
      } else {
        $currency = MODULE_PAYMENT_CHRONOPAY_CURRENCY;
      }

      switch ($osC_ShoppingCart->getBillingAddress('country_iso_code_3')) {
        case 'USA':
        case 'CAN':
          $state_code = $osC_ShoppingCart->getBillingAddress('state_code');
          break;

        default:
          $state_code = 'XX';
          break;
      }

      $process_button_string = osc_draw_hidden_field('product_id', MODULE_PAYMENT_CHRONOPAY_PRODUCT_ID) .
                               osc_draw_hidden_field('product_name', STORE_NAME) .
                               osc_draw_hidden_field('product_price', $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal(), $currency)) .
                               osc_draw_hidden_field('product_price_currency', $currency) .
                               osc_draw_hidden_field('cb_url', urlencode(osc_href_link(FILENAME_CHECKOUT, 'callback&module=' . $this->_code, 'SSL', null, null, true))) .
                               osc_draw_hidden_field('cb_type', 'P') .
                               osc_draw_hidden_field('decline_url', urlencode(osc_href_link(FILENAME_CHECKOUT, 'payment', 'SSL', null, null, true))) .
                               osc_draw_hidden_field('language', 'EN') . //EN, RU, NL, ES
                               osc_draw_hidden_field('f_name', $osC_ShoppingCart->getBillingAddress('firstname')) .
                               osc_draw_hidden_field('s_name', $osC_ShoppingCart->getBillingAddress('lastname')) .
                               osc_draw_hidden_field('street', $osC_ShoppingCart->getBillingAddress('street_address')) .
                               osc_draw_hidden_field('city', $osC_ShoppingCart->getBillingAddress('city')) .
                               osc_draw_hidden_field('state', $state_code) .
                               osc_draw_hidden_field('zip', $osC_ShoppingCart->getBillingAddress('postcode')) .
                               osc_draw_hidden_field('country', $osC_ShoppingCart->getBillingAddress('country_iso_code_3')) .
                               osc_draw_hidden_field('phone', $osC_ShoppingCart->getBillingAddress('telephone_number')) .
                               osc_draw_hidden_field('email', $osC_Customer->getEmailAddress()) .
                               osc_draw_hidden_field('cs1', $osC_Customer->getID()) .
                               osc_draw_hidden_field('cs2', $this->_order_id) .
                               osc_draw_hidden_field('cs3', md5(MODULE_PAYMENT_CHRONOPAY_PRODUCT_ID . $this->_order_id . $osC_Customer->getID() . $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal(), $currency) . MODULE_PAYMENT_CHRONOPAY_MD5_HASH));

      return $process_button_string;
    }

    function process() {
      global $osC_Customer, $osC_ShoppingCart;

      if (isset($_POST['cs1']) && is_numeric($_POST['cs1']) && isset($_POST['cs2']) && is_numeric($_POST['cs2']) && isset($_POST['cs3']) && (empty($_POST['cs3']) === false)) {
        if ($_POST['cs1'] == $osC_Customer->getID()) {
          if (isset($_SESSION['prepOrderID'])) {
            $_prep = explode('-', $_SESSION['prepOrderID']);

            if ( ($_prep[0] == $osC_ShoppingCart->getCartID()) && ($_prep[1] == $_POST['cs2']) ) {
              unset($_SESSION['prepOrderID']);
            }
          }
        }
      }
    }

    function callback() {
      global $osC_Database;

      $ip_address = osc_get_ip_address();

      if ( ($ip_address == '69.20.58.35') || ($ip_address == '207.97.201.192') ) {
        if (isset($_POST['cs1']) && is_numeric($_POST['cs1']) && isset($_POST['cs2']) && is_numeric($_POST['cs2']) && isset($_POST['cs3']) && (empty($_POST['cs3']) === false) && isset($_POST['product_id']) && ($_POST['product_id'] == MODULE_PAYMENT_CHRONOPAY_PRODUCT_ID) && isset($_POST['total']) && (empty($_POST['total']) === false) && isset($_POST['transaction_type']) && (empty($_POST['transaction_type']) === false)) {
          if (osC_Order::exists($_POST['cs2'], $_POST['cs1'])) {
            $pass = false;

            $post_array = array('root' => $_POST);
            $osC_XML = new osC_XML($post_array);

            if ($_POST['cs3'] == md5(MODULE_PAYMENT_CHRONOPAY_PRODUCT_ID . $_POST['cs2'] . $_POST['cs1'] . $_POST['total'] . MODULE_PAYMENT_CHRONOPAY_MD5_HASH)) {
              if (osC_Order::getStatusID($_POST['cs2']) === 4) {
                $pass = true;

                osC_Order::process($_POST['cs2'], $this->order_status);
              }
            }

            $Qtransaction = $osC_Database->query('insert into :table_orders_transactions_history (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added) values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');
            $Qtransaction->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
            $Qtransaction->bindInt(':orders_id', $_POST['cs2']);
            $Qtransaction->bindInt(':transaction_code', 1);
            $Qtransaction->bindValue(':transaction_return_value', $osC_XML->toXML());
            $Qtransaction->bindInt(':transaction_return_status', ($pass === true) ? 1 : 0);
            $Qtransaction->execute();
          }
        }
      }
    }
  }
?>
