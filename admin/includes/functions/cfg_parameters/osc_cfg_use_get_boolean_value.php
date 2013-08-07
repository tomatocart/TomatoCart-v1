<?php
/*
  $Id: osc_cfg_use_get_boolean_value.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  function osc_cfg_use_get_boolean_value($string) {
    global $osC_Language;

    switch ($string) {
      case -1:
      case '-1':
        return $osC_Language->get('parameter_false');
        break;

      case 0:
      case '0':
        return $osC_Language->get('parameter_optional');
        break;

      case 1:
      case '1':
        return $osC_Language->get('parameter_true');
        break;

      default:
        return $string;
        break;
    }
  }
?>
