<?php
/*
  $Id: toc_cfg_set_usps_domestic_services_checkbox_field.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  function toc_cfg_set_usps_domestic_services_checkbox_field($default, $key = null) {
    $name = (empty($key)) ? 'configuration_value' : 'configuration[' . $key . '][]';

    $dometric_services = array();
    $dometric_services[] = array('id' => '0', 'text' => 'First-Class');
    $dometric_services[] = array('id' => '1', 'text' => 'Priority Mail');
    $dometric_services[] = array('id' => '2', 'text' => 'Express Mail Hold for Pickup');
    $dometric_services[] = array('id' => '3', 'text' => 'Express Mail PO to Addressee');
    $dometric_services[] = array('id' => '4', 'text' => 'Parcel Post');
    $dometric_services[] = array('id' => '5', 'text' => 'Bound Printed Matter');
    $dometric_services[] = array('id' => '6', 'text' => 'Media Mail');
    $dometric_services[] = array('id' => '7', 'text' => 'Library');
    $dometric_services[] = array('id' => '12', 'text' => 'First-Class Postcard Stamped');
    $dometric_services[] = array('id' => '13', 'text' => 'Express Mail Flat-Rate Envelope');
    $dometric_services[] = array('id' => '16', 'text' => 'Priority Mail Flat-Rate Envelope');
    $dometric_services[] = array('id' => '17', 'text' => 'Priority Mail Regular Flat-Rate Box');
    $dometric_services[] = array('id' => '18', 'text' => 'Priority Mail Keys and IDs');
    $dometric_services[] = array('id' => '19', 'text' => 'First-Class Keys and IDs');
    $dometric_services[] = array('id' => '22', 'text' => 'Priority Mail Flat-Rate Large Box');
    $dometric_services[] = array('id' => '23', 'text' => 'Express Mail Sunday/Holiday');
    $dometric_services[] = array('id' => '25', 'text' => 'Express Mail Flat-Rate Envelope Sunday/Holiday');
    $dometric_services[] = array('id' => '27', 'text' => 'Express Mail Flat-Rate Envelope Hold For Pickup');
    $dometric_services[] = array('id' => '28', 'text' => 'Priority Mail Small Flat-Rate Box');

    $control = array();
    $control['name'] = $name;
    $control['type'] = 'usps_checkbox';
    $control['values'] = $dometric_services;

    return $control;
  }
?>
