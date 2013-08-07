<?php
/*
  $Id: osc_cfg_set_boolean_value.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  function osc_cfg_set_boolean_value($select_array, $default, $key = null) {
    global $osC_Language;

    $string = '';

    $select_array = explode(',', substr($select_array, 6, -1));

    $name = (!empty($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $values = array();
    for ($i=0, $n=sizeof($select_array); $i<$n; $i++) {
      $value = trim($select_array[$i]);

      if (strpos($value, '\'') !== false) {
        $value = substr($value, 1, -1);
      } else {
        $value = (int)$value;
      }

      $select_array[$i] = $value;

      if ($value === -1) {
        $value = $osC_Language->get('parameter_false');
      } elseif ($value === 0) {
        $value = $osC_Language->get('parameter_optional');
      } elseif ($value === 1) {
        $value = $osC_Language->get('parameter_true');
      }

      $values[] = array(
        'id' => $select_array[$i],
        'text' => $value
      );
    }

    $control = array();
    $control['name'] = $name;
    $control['type'] = 'combobox';
    $control['mode'] = 'local';
    $control['values'] = $values;

    return $control;
  }
?>
