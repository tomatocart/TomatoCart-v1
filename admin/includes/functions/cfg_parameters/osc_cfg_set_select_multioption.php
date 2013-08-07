<?php
/*
  $Id: osc_cfg_set_select_multioption.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  function osc_cfg_set_select_multioption($default, $key = null) {
    $name = (empty($key)) ? 'configuration_value' : 'configuration[' . $key . ']';

    $options = array('1DM' => 'Next Day Air Early AM', 
                     '1DML' => 'Next Day Air Early AM Letter', 
                     '1DA' => 'Next Day Air', 
                     '1DAL' => 'Next Day Air Letter', 
                     '1DAPI' => 'Next Day Air Intra (Puerto Rico)', 
                     '1DP' => 'Next Day Air Saver', 
                     '1DPL' => 'Next Day Air Saver Letter', 
                     '2DM' => '2nd Day Air AM', 
                     '2DML' => '2nd Day Air AM Letter', 
                     '2DA' => '2nd Day Air', 
                     '2DAL' => '2nd Day Air Letter', 
                     '3DS' => '3 Day Select', 
                     'GND' => 'Ground', 
                     'STD' => 'Canada Standard', 
                     'XPR' => 'Worldwide Express', 
                     'XPRL' => 'worldwide Express Letter', 
                     'XDM' => 'Worldwide Express Plus', 
                     'XDML' => 'Worldwide Express Plus Letter', 
                     'XPD' => 'Worldwide Expedited');

    $select_options = array();
    foreach($options as $key => $option) {
      $select_options[] = array('id' => $key, 'text' => $option);
    }

    $control = array();
    $control['name'] = $name;
    $control['type'] = 'multiselect';
    $control['values'] = $select_options;

    return $control;
  }
?>
