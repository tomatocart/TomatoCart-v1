<?php
/*
  $Id: import_export.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Import_Export extends osC_Access {
    var $_module = 'import_export',
        $_group = 'tools',
        $_icon = 'import_export.png',
        $_title,
        $_sort_order = 500;

    function osC_Access_Import_Export() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_customers_export_title');
    }
  }
?>
