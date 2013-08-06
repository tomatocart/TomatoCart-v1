<?php
/*
  $Id: toc_cfg_set_usps_international_services_checkbox_field.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  function toc_cfg_set_usps_international_services_checkbox_field($default, $key = null) {
    $name = (empty($key)) ? 'configuration_value' : 'configuration[' . $key . '][]';

    $international_services = array();
    $international_services[] = array('id' => '1', 'text' => 'Express Mail International');
    $international_services[] = array('id' => '2', 'text' => 'Priority Mail International');
    $international_services[] = array('id' => '4', 'text' => 'Global Express Guaranteed (Document and Non-document)');
    $international_services[] = array('id' => '5', 'text' => 'Global Express Guaranteed Document used');
    $international_services[] = array('id' => '6', 'text' => 'Global Express Guaranteed Non-Document Rectangular shape');
    $international_services[] = array('id' => '7', 'text' => 'Global Express Guaranteed Non-Document Non-Rectangular');
    $international_services[] = array('id' => '8', 'text' => 'Priority Mail Flat Rate Envelope');
    $international_services[] = array('id' => '9', 'text' => 'Priority Mail Flat Rate Box');
    $international_services[] = array('id' => '10', 'text' => 'Express Mail International Flat Rate Envelope');
    $international_services[] = array('id' => '11', 'text' => 'Priority Mail Large Flat Rate Box');
    $international_services[] = array('id' => '12', 'text' => 'Global Express Guaranteed Envelope');
    $international_services[] = array('id' => '13', 'text' => 'First Class Mail International Letters');
    $international_services[] = array('id' => '14', 'text' => 'First Class Mail International Flats');
    $international_services[] = array('id' => '15', 'text' => 'First Class Mail International Parcels');
    $international_services[] = array('id' => '16', 'text' => 'Priority Mail Small Flat Rate Box');
    $international_services[] = array('id' => '21', 'text' => 'Postcards');

    $control = array();
    $control['name'] = $name;
    $control['type'] = 'usps_checkbox';
    $control['values'] = $international_services;

    return $control;
  }
?>
