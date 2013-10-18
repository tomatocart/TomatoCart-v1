<?php
/*
  $Id: toc_constants.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

/*products type*/
  define('PRODUCT_TYPE_SIMPLE',  0);
  define('PRODUCT_TYPE_VIRTUAL', 1);
  define('PRODUCT_TYPE_DOWNLOADABLE', 2);
  define('PRODUCT_TYPE_GIFT_CERTIFICATE', 3);

/*gift certificate type*/
  define('GIFT_CERTIFICATE_TYPE_EMAIL', 0);
  define('GIFT_CERTIFICATE_TYPE_PHYSICAL', 1);
  
  define('GIFT_CERTIFICATE_TYPE_FIX_AMOUNT', 0);
  define('GIFT_CERTIFICATE_TYPE_OPEN_AMOUNT', 1);
  
/*orders status*/
  define('ORDERS_STATUS_PENDING', 1);
  define('ORDERS_STATUS_PROCESSING', 2);
  define('ORDERS_STATUS_PREPARING', 3);
  define('ORDERS_STATUS_PARTLY_PAID', 4);
  define('ORDERS_STATUS_PAID', 5);
  define('ORDERS_STATUS_PARTLY_DELIVERED', 6);
  define('ORDERS_STATUS_DELIVERED', 7);
  define('ORDERS_STATUS_CANCELLED', 8);
  
/*orders returns*/
  define('ORDERS_RETURNS_STATUS_PENDING', 1);
  define('ORDERS_RETURNS_STATUS_CONFIRMED', 2);
  define('ORDERS_RETURNS_STATUS_RECEIVED', 3);
  define('ORDERS_RETURNS_STATUS_AUTHORIZED', 4);
  define('ORDERS_RETURNS_STATUS_REFUNDED_CREDIT_MEMO', 5);
  define('ORDERS_RETURNS_STATUS_REFUNDED_STORE_CREDIT', 6);
  define('ORDERS_RETURNS_STATUS_REJECT', 7);
  
/*store credit action*/  
  define('STORE_CREDIT_ACTION_TYPE_ORDER_PURCHASE', 1);
  define('STORE_CREDIT_ACTION_TYPE_ORDER_REFUNDED', 2);
  define('STORE_CREDIT_ACTION_TYPE_ADMIN', 3);
  
/*orders returns type*/  
  define('ORDERS_RETURNS_TYPE_CREDIT_SLIP', 0);
  define('ORDERS_RETURNS_TYPE_STORE_CREDIT', 1);
  
/*orders returns type*/  
  define('COUPONS_RESTRICTION_NONE', 0);
  define('COUPONS_RESTRICTION_CATEGOREIS', 1);
  define('COUPONS_RESTRICTION_PRODUCTS', 2);
  
/*email folder flag*/
  define('EMAIL_FOLDER_UNKNOWN', 0);
  define('EMAIL_FOLDER_INBOX', 1);
  define('EMAIL_FOLDER_SENTBOX', 2);
  define('EMAIL_FOLDER_DRAFT', 3);
  define('EMAIL_FOLDER_SPAM', 4);
  define('EMAIL_FOLDER_TRASH', 5);
  
/*email message flag*/
  define('EMAIL_MESSAGE_SENT_ITEM', 2);
  define('EMAIL_MESSAGE_DRAFT', 3);
  
/*email message flag*/
  define('CUSTOMIZATION_FIELD_TYPE_INPUT_FILE', 0);
  define('CUSTOMIZATION_FIELD_TYPE_INPUT_TEXT', 1);
  
/*information*/
  define('INFORMATION_ABOUT_US', 1);
  define('INFORMATION_SHIPPING_RETURNS', 2);
  define('INFORMATION_PRIVACY_NOTICE', 3);
  define('INFORMATION_CONDITIONS_OF_USE', 4);
  define('INFORMATION_IMPRINT', 5);
?>