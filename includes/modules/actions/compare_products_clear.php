<?php
/*
  $Id: compare_products_remove.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Actions_compare_products_clear {
    function execute() {
      global $toC_Compare_Products;

      $toC_Compare_Products->reset();

      osc_redirect(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), osc_get_all_get_params(array('action')), 'NONSSL', true, true, true));
    }
  }
?>