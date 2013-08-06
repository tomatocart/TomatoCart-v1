<?php
/*
  $Id:payment.php 188 2005-09-15 02:25:52 +0200 (Do, 15 Sep 2005) hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/classes/address_book.php');

  class osC_Checkout_Payment extends osC_Template {

/* Private variables */

    var $_module = 'payment',
        $_group = 'checkout',
        $_page_title,
        $_page_contents = 'checkout_payment.php',
        $_page_image = 'table_background_payment.gif';

/* Class constructor */

    function osC_Checkout_Payment() {
      osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'checkout', 'SSL'));
    }
  }
?>
