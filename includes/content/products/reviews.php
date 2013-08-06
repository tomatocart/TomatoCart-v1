<?php
/*
  $Id: reviews.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require_once("ext/securimage/securimage.php");

  class osC_Products_Reviews extends osC_Template {

/* Private variables */

    var $_module = 'reviews',
        $_group = 'products',
        $_page_title,
        $_page_contents = 'reviews.php',
        $_page_image = 'table_background_reviews_new.gif';

/* Class constructor */

    function osC_Products_Reviews() {
      global $osC_Services, $osC_Session, $osC_Language, $breadcrumb, $osC_Product, $osC_Customer, $osC_NavigationHistory;

      if ($osC_Services->isStarted('reviews') === false) {
        osc_redirect(osc_href_link(FILENAME_DEFAULT));
      }

      $this->_page_title = $osC_Language->get('reviews_heading');

      if ($osC_Services->isStarted('breadcrumb')) {
        $breadcrumb->add($osC_Language->get('breadcrumb_reviews'), osc_href_link(FILENAME_PRODUCTS, $this->_module));
      }

      if ($_GET[$this->_module] == 'new') {
        if ( ($osC_Customer->isLoggedOn() === false ) && (SERVICE_REVIEW_ENABLE_REVIEWS == 1) ) {
          $osC_NavigationHistory->setSnapshot();
      
          osc_redirect(osc_href_link(FILENAME_ACCOUNT, 'login', 'SSL'));
        }
        
        $products_id = null;
        foreach ($_GET as $key => $value) {
          if (is_numeric($key)  && ($key != $osC_Session->getName())) {
            $products_id = $key;
            
            break;
          }
        }
        
        if ($products_id != null) {
          if (isset($_GET['action']) && ($_GET['action'] == 'process')) {
            $this->_process($products_id);
          }
        }
      }else if ($_GET[$this->_module] == 'show_captcha') {
         $this->_show_captcha();
      }
    }
  
/* Private methods */
    function _show_captcha() {
      $img = new securimage();
      
      $img->show();
    }

    function _process($id) {
      global $osC_Language, $messageStack, $osC_Customer, $osC_Reviews;

      $data = array('products_id' => $id);

      if ($osC_Customer->isLoggedOn()) {
        $data['customer_id'] = $osC_Customer->getID();
      } else {
        $data['customer_id'] = '0';
      }
      
      $data['customer_name'] = $_POST['author_name'];
      
      if (strlen(trim($_POST['review'])) < REVIEW_TEXT_MIN_LENGTH) {
        $messageStack->add('reviews', sprintf($osC_Language->get('js_review_text'), REVIEW_TEXT_MIN_LENGTH));
      } else {
        $data['review'] = strip_tags($_POST['review']);
      }
      
      $ratings = array();
      foreach ($_REQUEST as $key => $value) {
        if (substr($key, 0, 7) == 'rating_') {
          $ratings_id = substr($key, 7);
          $ratings[$ratings_id] = $value;
        }
      }
      $data['rating'] = (count($ratings) > 0) ? $ratings : $_POST['rating'];
      
      if ( !is_array($data['rating']) ) {
        if ( ($data['rating'] < 1) || ($data['rating'] > 5) ) {
          $messageStack->add_session('reviews', $osC_Language->get('js_review_rating'));
        }
      } else {
        foreach ($data['rating'] as $rating) {
          if ( ($rating < 1) || ($rating > 5) ) {
            $messageStack->add_session('reviews', $osC_Language->get('js_review_rating'));
            break;
          }
        }
      }
      
      if ((ACTIVATE_CAPTCHA === '1') && ($osC_Customer->isLoggedOn() === false) ) {
        if (isset($_POST['captcha_code']) && !empty($_POST['captcha_code'])) {
          $securimage = new Securimage();
          
          if ($securimage->check($_POST['captcha_code']) == false) {
            $messageStack->add_session('reviews', $osC_Language->get('field_concat_captcha_check_error'));
          }
        } else {
          $messageStack->add_session('reviews', $osC_Language->get('field_concat_captcha_check_error'));
        }  
      }
      
      if ($messageStack->size('reviews') < 1) {
        if ($osC_Reviews->is_moderated === true) {
          $data['status'] = '0';

          $messageStack->add_session('reviews', $osC_Language->get('success_review_moderation'), 'success');
        } else {
          $data['status'] = '1';

          $messageStack->add_session('reviews', $osC_Language->get('success_review_new'), 'success');
        }

        osC_Reviews::saveEntry($data);
        
        if (isset($_SESSION['review_author_name'])) {
          unset($_SESSION['review_author_name']);
        }
        
        if (isset($_SESSION['review'])) {
          unset($_SESSION['review']);
        }
      }else {
         $_SESSION['review_author_name'] = $data['customer_name'];
         $_SESSION['review'] = $data['review'];
      }
      
      osc_redirect(osc_href_link(FILENAME_PRODUCTS, $id . '&tab=tabReviews'));
    }
  }
?>
