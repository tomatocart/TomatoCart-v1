<?php
/*
  $Id: orders.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/classes/order.php');
  require('includes/classes/order_return.php');

  class osC_Account_Orders extends osC_Template {

/* Private variables */

    var $_module = 'orders',
        $_group = 'account',
        $_page_title,
        $_page_contents = 'account_history.php',
        $_page_image = 'table_background_history.gif';

/* Class constructor */

    function osC_Account_Orders() {
      global $osC_Services, $osC_Language, $osC_Customer, $breadcrumb, $returns_orders;

      $this->_page_title = $osC_Language->get('orders_heading');

      $osC_Language->load('order');

      if ($osC_Services->isStarted('breadcrumb')) {
        $breadcrumb->add($osC_Language->get('breadcrumb_my_orders'), osc_href_link(FILENAME_ACCOUNT, $this->_module, 'SSL'));

        if (is_numeric($_REQUEST[$this->_module])) {
          $breadcrumb->add(sprintf($osC_Language->get('breadcrumb_order_information'), $_REQUEST[$this->_module]), osc_href_link(FILENAME_ACCOUNT, $this->_module . '=' . $_REQUEST[$this->_module], 'SSL'));
        }
      }
      
      if (is_numeric($_GET[$this->_module])) {
        if (osC_Order::getCustomerID($_GET[$this->_module]) !== $osC_Customer->getID()) {
          osc_redirect(osc_href_link(FILENAME_ACCOUNT, $this->_module, 'SSL'));
        }
        
        $this->_page_title = sprintf($osC_Language->get('order_information_heading'), $_GET[$this->_module]);
        $this->_page_contents = 'account_history_info.php';
      } else if (!empty($_GET[$this->_module])) {
        switch ($_GET[$this->_module]) {
          case 'list_return_requests':
              $this->_page_title = $osC_Language->get('orders_returns_heading');
              $this->_page_contents = 'return_requests_history.php';
            
              break;
          case 'list_credit_slips':
              $this->_page_title = $osC_Language->get('credit_slips_heading');
              $this->_page_contents = 'credit_slips_history.php';
              
              break;
          case 'new_return_request':
			        $this->_page_title = sprintf($osC_Language->get('orders_returns_information_heading'), $_GET['orders_id']);
      			  $this->_page_contents = 'return_request_process.php';
      			  
              break;
          case 'save_return_request':
       				$this->_page_title = sprintf($osC_Language->get('orders_returns_information_heading'), $_GET['orders_id']);
        			$this->_page_contents = 'return_request_process.php';
      			  $this->_save_orders_returns();
        
              break;
        }      
      }
    }
    
    function _save_orders_returns() {
      global $messageStack, $osC_Database, $osC_Language, $osC_Customer;
      
      
      $error = false;
      $products = array();
      
      if (isset($_POST['return_items']) && !empty($_POST['return_items'])) {
        foreach($_POST['return_items'] as $orders_products_id => $on) {
          if (isset($_POST['quantity'][$orders_products_id]) && ($_POST['quantity'][$orders_products_id] > 0)) {
            $products[$orders_products_id] = $_POST['quantity'][$orders_products_id]; 
          } else {
            $messageStack->add($this->_module, sprintf($osC_Language->get('error_quantity_for_return_product'), $_POST['products_name'][$orders_products_id]));
          }
        }
      }

      if (sizeof($products) == 0) {
        $messageStack->add($this->_module, $osC_Language->get('error_return_items_empty'));
      }
      
      if (isset($_POST['comments']) && empty($_POST['comments'])) {
        $messageStack->add($this->_module, $osC_Language->get('error_return_comments_empty'));
      }
      
      if ($messageStack->size($this->_module) === 0) {
        if (toC_Order_Return::saveReturnRequest($_GET['orders_id'], $products, $_POST['comments'])) {
          $messageStack->add_session($this->_module, $osC_Language->get('success_account_updated'), 'success');
        }

        osc_redirect(osc_href_link(FILENAME_ACCOUNT, 'orders=list_return_requests', 'SSL'));
      }
    }
  }
?>
