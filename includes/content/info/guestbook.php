<?php
/*
  $Id: guestbook.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require_once('includes/classes/guestbook.php');
  require_once("ext/securimage/securimage.php");
  
  class osC_Info_Guestbook extends osC_Template {

/* Private variables */

    var $_module = 'guestbook',
        $_group = 'info',
        $_page_title,
        $_page_contents = 'guestbooks.php',
        $_page_image = 'table_background_reviews_new.gif';

/* Class constructor */

    function osC_Info_Guestbook() {
      global $osC_Language, $osC_Services, $breadcrumb;

      $this->_page_title = $osC_Language->get('guestbook_heading');
    
      if ($osC_Services->isStarted('breadcrumb')) {
        $breadcrumb->add($osC_Language->get('breadcrumb_guestbook'), osc_href_link(FILENAME_INFO, $this->_module));
      }
      
      if (isset($_GET[$this->_module])) {
        if ($_GET[$this->_module] == 'new') {
          $this->_page_contents = 'guestbook_new.php';
        }else if ($_GET[$this->_module] == 'save') {
          $this->_page_contents = 'guestbook_new.php';
        
          $this->_process();
        }
      }

      if ($_GET[$this->_module] == 'show_captcha') {
        $this->_show_captcha();
      }
    }

/* Private methods */
    function _show_captcha() {
      $img = new securimage();
      
      $img->show();
    }
        
    function _process() {
      global $osC_Database, $messageStack, $osC_Language;
      
      $data = array();
      $data['url'] = osc_sanitize_string($_POST['url']);
      
      if( isset($_POST['title']) && !empty($_POST['title']) ) {
        $data['title'] = osc_sanitize_string($_POST['title']);
      } else {
        $messageStack->add('guestbook', $osC_Language->get('field_guestbook_title_error'));
      }
      
      if( isset($_POST['email']) && !empty($_POST['email']) && (osc_validate_email_address($_POST['email'])) ) {
        $data['email'] = $_POST['email'];
      } else {
        $messageStack->add('guestbook', $osC_Language->get('field_guestbook_email_error'));
      }
          
      if( isset($_POST['content']) && !empty($_POST['content']) ) {
        $data['content'] = osc_sanitize_string($_POST['content']);
      } else {
        $messageStack->add('guestbook', $osC_Language->get('field_guestbook_content_error'));
      }
      
      if ( ACTIVATE_CAPTCHA == '1' ) {
        if (isset($_POST['captcha_code']) && !empty($_POST['captcha_code'])) {
          $securimage = new Securimage();
          
          if ($securimage->check($_POST['captcha_code']) == false) {
            $messageStack->add('guestbook', $osC_Language->get('field_guestbook_verify_code_error'));
          }
        } else {
          $messageStack->add('guestbook', $osC_Language->get('field_guestbook_verify_code_error'));
        }  
      }
              
      if($messageStack->size('guestbook') === 0) {
        if ( toC_Guestbook::saveEntry($data) ) {
          $messageStack->add_session('guestbook', $osC_Language->get('success_guestbook_saved'), 'success');
        }

        osc_redirect(osc_href_link(FILENAME_INFO, 'guestbook'));
      }
    }
  }
?>
