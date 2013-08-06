<?php
/*
  $Id: osc_cfg_set_countries_pulldown_menu.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  function osc_cfg_set_countries_pulldown_menu($default, $key = null) {
    global $toC_Json;
    
    $name = (!empty($key) ? 'configuration[' . $key . ']' : 'configuration_value');
   
    $control = array();
    $control['name'] = $name;
    $control['type'] = 'combobox';
    $control['mode'] = 'remote';
    $control['module'] = 'cfg';
    $control['action'] = 'get_countries';

    return $control;
  }
?>
