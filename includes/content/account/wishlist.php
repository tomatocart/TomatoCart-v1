<?php
/*
  $Id: wishlist.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Account_Wishlist extends osC_Template {

/* Private variables */

    var $_module = 'wishlist',
        $_group = 'account',
        $_page_title,
        $_page_contents = 'wishlist.php',
        $_page_image = 'table_background_account.gif';

    function osC_Account_Wishlist() {
      global $osC_Language, $osC_Services, $breadcrumb, $messageStack, $osC_Customer, $osC_NavigationHistory;
      
      if ($osC_Customer->isLoggedOn() === false) {
        $osC_NavigationHistory->setSnapshot();

        osc_redirect(osc_href_link(FILENAME_ACCOUNT, 'login', 'SSL'));
      }
      
      $this->_page_title = $osC_Language->get('wishlist_heading');
      
      if ($osC_Services->isStarted('breadcrumb')) {
        $breadcrumb->add($osC_Language->get('breadcrumb_wishlist'), osc_href_link(FILENAME_ACCOUNT, $this->_module, 'SSL'));
      }
      
      if ($_GET[$this->_module] == 'delete') {
        $this->_delete();
      } else if ($_GET[$this->_module] == 'update') {
        $this->_update();
      } else if ($_GET[$this->_module] == 'share_wishlist') {
        $this->_share_wishlist();
      } else if ($_GET[$this->_module] == 'display_wishlist') {
        $this->_page_contents = 'display_wishlist.php';
      }  
    }
    
    function _delete() {
      global $osC_Language, $messageStack, $toC_Wishlist;
      
      if (isset($_GET['pid'])) {
        if ($toC_Wishlist->deleteProduct($_GET['pid'])) {
          $messageStack->add_session($this->_module, $osC_Language->get('success_wishlist_entry_deleted'), 'success');
          
          osc_redirect(osc_href_link(FILENAME_ACCOUNT, 'wishlist', 'SSL'));
        }        
      }
    }

    function _update() {
      global $osC_Language, $messageStack, $toC_Wishlist;
      
      if (isset($_POST['comments'])) {
        if ($toC_Wishlist->updateWishlist($_POST['comments'])) {
          $messageStack->add_session($this->_module, $osC_Language->get('success_wishlist_entry_updated'), 'success');
        }else {
        	$messageStack->add_session($this->_module, $osC_Language->get('failed_wishlist_entry_updated'));
        }

        osc_redirect(osc_href_link(FILENAME_ACCOUNT, 'wishlist', 'SSL'));
      }
    }
          
    function _share_wishlist() {
      global $osC_Language, $messageStack, $toC_Wishlist;
      
      $data = array();
      if (isset($_POST['wishlist_customer']) && !empty($_POST['wishlist_customer'])) {
        $data['wishlist_customer'] = $_POST['wishlist_customer'];
      } else {
        $messageStack->add($this->_module, $osC_Language->get('field_share_wishlist_customer_name_error'));
      }

      if (isset($_POST['wishlist_from_email']) && !empty($_POST['wishlist_from_email'])) {
        $data['wishlist_from_email'] = $_POST['wishlist_from_email'];
      } else {
        $messageStack->add($this->_module, $osC_Language->get('field_share_wishlist_customer_email_error'));
      }      
      
      if (isset($_POST['wishlist_emails']) && !empty($_POST['wishlist_emails'])) {
        $data['wishlist_emails'] = $_POST['wishlist_emails'];
      } else {
        $messageStack->add($this->_module, $osC_Language->get('field_share_wishlist_emails_error'));
      }
      
      if (isset($_POST['wishlist_message']) && !empty($_POST['wishlist_message'])) {
        $data['wishlist_message'] = $_POST['wishlist_message'];
      } else {
        $messageStack->add($this->_module, $osC_Language->get('field_share_wishlist_message_error'));
      }

      if ($messageStack->size($this->_module) === 0) {
        include('includes/classes/email_template.php');
        
        $wishlist_url = osc_href_link(FILENAME_ACCOUNT, 'wishlist=display_wishlist&token=' . $toC_Wishlist->getToken(), 'NONSSL', true, true, true);

        $email = toC_Email_Template::getEmailTemplate('share_wishlist');
        $email->setData($data['wishlist_customer'], $data['wishlist_from_email'], $data['wishlist_emails'], $data['wishlist_message'], $wishlist_url);
        $email->buildMessage();
        $email->sendEmail();

        osc_redirect(osc_href_link(FILENAME_ACCOUNT, 'wishlist', 'SSL'));
      }
    }
  }
?>
