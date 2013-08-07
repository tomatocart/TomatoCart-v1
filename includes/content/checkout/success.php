<?php
/*
  $Id: success.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Checkout_Success extends osC_Template {

/* Private variables */

    var $_module = 'success',
        $_group = 'checkout',
        $_page_title,
        $_page_contents = 'checkout_success.php';

/* Class constructor */

    function osC_Checkout_Success() {
      global $osC_Services, $osC_Language, $osC_Customer, $osC_NavigationHistory, $breadcrumb;

      $this->_page_title = $osC_Language->get('success_heading');

      if ($osC_Customer->isLoggedOn() === false) {
        $osC_NavigationHistory->setSnapshot();

        osc_redirect(osc_href_link(FILENAME_ACCOUNT, 'login', 'SSL'));
      }
      
      if ($osC_Services->isStarted('breadcrumb')) {
        $breadcrumb->add($osC_Language->get('breadcrumb_checkout_success'), osc_href_link(FILENAME_CHECKOUT, $this->_module, 'SSL'));
      }

      if ($_GET[$this->_module] == 'update') {
        $this->_process();
      }
    }

/* Private methods */

    function _process() {
      $notify_string = '';

      $products_array = (isset($_POST['notify']) ? $_POST['notify'] : array());

      if (!is_array($products_array)) {
        $products_array = array($products_array);
      }

      $notifications = array();

      foreach ($products_array as $product_id) {
        if (is_numeric($product_id) && !in_array($product_id, $notifications)) {
          $notifications[] = $product_id;
        }
      }

      if (!empty($notifications)) {
        $notify_string = 'action=notify_add&products=' . implode(';', $notifications);
      }

      osc_redirect(osc_href_link(FILENAME_DEFAULT, $notify_string, 'AUTO'));
    }
  }
?>
