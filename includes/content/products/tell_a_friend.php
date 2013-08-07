<?php
/*
  $Id: tell_a_friend.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Products_Tell_a_friend extends osC_Template {

/* Private variables */

    var $_module = 'tell_a_friend',
        $_group = 'products',
        $_page_title,
        $_page_contents = 'tell_a_friend.php',
        $_page_image = 'table_background_products_new.gif';

/* Class constructor */

    function osC_Products_Tell_a_friend() {
      global $osC_Services, $osC_Session, $osC_Language, $breadcrumb, $osC_Customer, $osC_NavigationHistory, $osC_Product;

      if ((ALLOW_GUEST_TO_TELL_A_FRIEND == '-1') && ($osC_Customer->isLoggedOn() === false)) {
        $osC_NavigationHistory->setSnapshot();

        osc_redirect(osc_href_link(FILENAME_ACCOUNT, 'login', 'SSL'));
      }

      $counter = 0;
      foreach ($_GET as $key => $value) {
        $counter++;

        if ($counter < 2) {
          continue;
        }

        if ( (ereg('^[0-9]+(#?([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)*$', $key) || ereg('^[a-zA-Z0-9 -_]*$', $key)) && ($key != $osC_Session->getName()) ) {
          if (osC_Product::checkEntry($key) === false) {
            $this->_page_title = $osC_Language->get('product_not_found_heading');
            $this->_page_contents = 'info_not_found.php';
          } else {
            $osC_Product = new osC_Product($key);

            $this->_page_title = $osC_Product->getTitle();

            if ($osC_Services->isStarted('breadcrumb')) {
              $breadcrumb->add($osC_Product->getTitle(), osc_href_link(FILENAME_PRODUCTS, $osC_Product->getID()));
              $breadcrumb->add($osC_Language->get('breadcrumb_tell_a_friend'), osc_href_link(FILENAME_PRODUCTS, $this->_module . '&' . $osC_Product->getID()));
            }

            if (isset($_GET['action']) && ($_GET['action'] == 'process')) {
              $this->_process();
            }
          }

          break;
        }
      }

      if ($counter < 2) {
        $this->_page_title = $osC_Language->get('product_not_found_heading');
        $this->_page_contents = 'info_not_found.php';
      }
    }

/* Private methods */

    function _process() {
      global $osC_Language, $messageStack, $osC_Product;

      if (empty($_POST['from_name'])) {
        $messageStack->add('tell_a_friend', $osC_Language->get('error_tell_a_friend_customers_name_empty'));
      }

      if (!osc_validate_email_address($_POST['from_email_address'])) {
        $messageStack->add('tell_a_friend', $osC_Language->get('error_tell_a_friend_invalid_customers_email_address'));
      }

      if (empty($_POST['to_name'])) {
        $messageStack->add('tell_a_friend', $osC_Language->get('error_tell_a_friend_friends_name_empty'));
      }

      if (!osc_validate_email_address($_POST['to_email_address'])) {
        $messageStack->add('tell_a_friend', $osC_Language->get('error_tell_a_friend_invalid_friends_email_address'));
      }

      if ($messageStack->size('tell_a_friend') < 1) {

        include('includes/classes/email_template.php');
        $email_template = toC_Email_Template::getEmailTemplate('tell_a_friend');
        $email_template->setData($_POST['from_name'], $_POST['from_email_address'], $_POST['to_name'], $_POST['to_email_address'], $_POST['message'], $osC_Product->getTitle(), osc_href_link(FILENAME_PRODUCTS, $osC_Product->getID(), 'NONSSL', false, true, true));
        $email_template->buildMessage();
        $email_template->sendEmail();

        $messageStack->add_session('header', sprintf($osC_Language->get('success_tell_a_friend_email_sent'), $osC_Product->getTitle(), osc_output_string_protected($_POST['to_name'])), 'success');

        osc_redirect(osc_href_link(FILENAME_PRODUCTS, $osC_Product->getID()));
      }
    }
  }
?>
