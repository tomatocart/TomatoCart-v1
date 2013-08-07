<?php
/*
  $Id: departments.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Departments extends osC_Access {
    var $_module = 'departments',
        $_group = 'tools',
        $_icon = 'department.png',
        $_title,
        $_sort_order = 1600;

    function osC_Access_Departments() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_departments_management_title');
    }
  }
?>
