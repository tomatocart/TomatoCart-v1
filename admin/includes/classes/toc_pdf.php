<?php
 /*
  $Id: toc_pdf.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
  */
  
  require_once('includes/classes/logo_upload.php');
  require_once('includes/classes/currencies.php');

  require_once('../ext/tcpdf/config/lang/eng.php');
  require_once('../ext/tcpdf/tcpdf.php');

  define('TOC_PDF_POS_START_X', 70);
  define('TOC_PDF_POS_START_Y', 50);
  define('TOC_PDF_LOGO_UPPER_LEFT_CORNER_X', 100);
  define('TOC_PDF_LOGO_UPPER_LEFT_CORNER_Y', 10);
  define('TOC_PDF_LOGO_WIDTH', 80);
  define('TOC_PDF_LOGO_HEIGHT', 20);
  
  define('TOC_PDF_POS_ADDRESS_INFO_Y', TOC_PDF_POS_START_Y);
  define('TOC_PDF_POS_STORE_ADDRESS_Y', TOC_PDF_POS_START_Y);
  define('TOC_PDF_POS_CONTENT_Y', (TOC_PDF_POS_START_Y + 40));
  define('TOC_PDF_POS_HEADING_TITLE_Y', TOC_PDF_POS_CONTENT_Y);
  define('TOC_PDF_POS_DOC_INFO_FIELD_Y', TOC_PDF_POS_CONTENT_Y);
  define('TOC_PDF_POS_DOC_INFO_VALUE_Y', TOC_PDF_POS_CONTENT_Y);
  define('TOC_PDF_POS_PRODUCTS_TABLE_HEADING_Y', (TOC_PDF_POS_CONTENT_Y + 20));
  define('TOC_PDF_POS_PRODUCTS_TABLE_CONTENT_Y', (TOC_PDF_POS_PRODUCTS_TABLE_HEADING_Y + 6));
  
  define('TOC_PDF_FONT', 'times');
  define('TOC_PDF_HEADER_BILLING_INFO_FONT_SIZE', 11);
  define('TOC_PDF_HEADER_STORE_ADDRESS_FONT_SIZE', 9);
  define('TOC_PDF_FOOTER_PAGEING_FONT_SIZE', 8);
  define('TOC_PDF_TITLE_FONT_SIZE', 14);
  define('TOC_PDF_FIELD_DATE_PURCHASE_FONT_SIZE', 9);
  define('TOC_PDF_TABLE_HEADING_FONT_SIZE', 10);
  define('TOC_PDF_TABLE_CONTENT_FONT_SIZE', 9);
  define('TOC_PDF_TABLE_CONTENT_HEIGHT', 5);
  define('TOC_PDF_TABLE_PRODUCT_VARIANT_FONT_SIZE', 8);
  define('TOC_PDF_SHIP_TO_ADDRESS_FONT_SIZE', 10);
  define('TOC_PDF_SHIP_TO_TITLE_FONT_SIZE', 11);
  
  /*
   *  Class TOCPDF 
   */
  class TOCPDF extends TCPDF {

    var $_customer_info = array();

    function setCustomerInfo($customer_info) {
      $this->_customer_info = $customer_info;
    }
    
    function Header() {
    
      //Logo
      $logo = toC_Logo_Upload::getOriginalLogo();
      
      //verify whether the default logo is existed when the template logo isn't uploded yet
      if ($logo === false) {
          if (file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . 'store_logo.jpg')) {
              $logo = DIR_FS_CATALOG . DIR_WS_IMAGES . 'store_logo.jpg';
          }else if (file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . 'store_logo.png')){
              $logo = DIR_FS_CATALOG . DIR_WS_IMAGES . 'store_logo.png';
          }
      }
      
      $this->Image($logo, TOC_PDF_LOGO_UPPER_LEFT_CORNER_X, TOC_PDF_LOGO_UPPER_LEFT_CORNER_Y, TOC_PDF_LOGO_WIDTH, TOC_PDF_LOGO_HEIGHT);
      
      //Line
      $this->line(10, 45, 98, 45);
      
      //Customer Information
      $this->SetFont(TOC_PDF_FONT, 'B', TOC_PDF_HEADER_BILLING_INFO_FONT_SIZE);
      $this->SetY(TOC_PDF_POS_ADDRESS_INFO_Y);
      $this->MultiCell(100, 4, $this->_customer_info['name'] . "\n" . 
                               $this->_customer_info['street_address'] . " " . $this->_customer_info['suburb'] . "\n" .
                               $this->_customer_info['postcode'] . " " . $this->_customer_info['city'] . "\n" .
                               $this->_customer_info['country_title']  . "\n" . 
                               $this->_customer_info['email_address'], 0, 'L');
      
      //Store Information
      $this->SetFont(TOC_PDF_FONT, 'B', TOC_PDF_HEADER_STORE_ADDRESS_FONT_SIZE);
      $this->SetY(TOC_PDF_POS_STORE_ADDRESS_Y);
      $this->Cell(100);
      $this->MultiCell(80, 4, STORE_NAME_ADDRESS, 0 ,'R');
    }
    
    function Footer() {
      // Position at 1.5 cm from bottom
      $this->SetY(-15);
      // Set font
      $this->SetFont(TOC_PDF_FONT, 'I', TOC_PDF_FOOTER_PAGEING_FONT_SIZE);
      // Page number
      $this->Cell(0, 10, $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'R');
    }
  }
?>