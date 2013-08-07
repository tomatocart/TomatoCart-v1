<?php
/*
  $Id: wizard_installation.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Configuration_wizard extends osC_Access {
    var $_module = 'wizard_installation',
        $_group = 'configuration',
        $_icon = 'install.png',
        $_title,
        $_sort_order = 100;

    function osC_Access_Configuration_wizard() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_configuration_wizard_title');
    }
  }
?>
