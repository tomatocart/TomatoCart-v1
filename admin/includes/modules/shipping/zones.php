<?php
/*
  $Id: zones.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.

  USAGE
  By default, the module comes with support for 1 zone.  This can be
  easily changed by editing the line below in the zones constructor
  that defines $this->num_zones.

  Next, you will want to activate the module by going to the Admin screen,
  clicking on Modules, then clicking on Shipping.  A list of all shipping
  modules should appear.  Click on the green dot next to the one labeled
  zones.php.  A list of settings will appear to the right.  Click on the
  Edit button.

  PLEASE NOTE THAT YOU WILL LOSE YOUR CURRENT SHIPPING RATES AND OTHER
  SETTINGS IF YOU TURN OFF THIS SHIPPING METHOD.  Make sure you keep a
  backup of your shipping settings somewhere at all times.

  If you want an additional handling charge applied to orders that use this
  method, set the Handling Fee field.

  Next, you will need to define which countries are in each zone.  Determining
  this might take some time and effort.  You should group a set of countries
  that has similar shipping charges for the same weight.  For instance, when
  shipping from the US, the countries of Japan, Australia, New Zealand, and
  Singapore have similar shipping rates.  As an example, one of my customers
  is using this set of zones:
    1: USA
    2: Canada
    3: Austria, Belgium, Great Britain, France, Germany, Greenland, Iceland,
       Ireland, Italy, Norway, Holland/Netherlands, Denmark, Poland, Spain,
       Sweden, Switzerland, Finland, Portugal, Israel, Greece
    4: Japan, Australia, New Zealand, Singapore
    5: Taiwan, China, Hong Kong

  When you enter these country lists, enter them into the Zone X Countries
  fields, where "X" is the number of the zone.  They should be entered as
  two character ISO country codes in all capital letters.  They should be
  separated by commas with no spaces or other punctuation. For example:
    1: US
    2: CA
    3: AT,BE,GB,FR,DE,GL,IS,IE,IT,NO,NL,DK,PL,ES,SE,CH,FI,PT,IL,GR
    4: JP,AU,NZ,SG
    5: TW,CN,HK

  Now you need to set up the shipping rate tables for each zone.  Again,
  some time and effort will go into setting the appropriate rates.  You
  will define a set of weight ranges and the shipping price for each
  range.  For instance, you might want an order than weighs more than 0
  and less than or equal to 3 to cost 5.50 to ship to a certain zone.
  This would be defined by this:  3:5.5

  You should combine a bunch of these rates together in a comma delimited
  list and enter them into the "Zone X Shipping Table" fields where "X"
  is the zone number.  For example, this might be used for Zone 1:
    1:3.5,2:3.95,3:5.2,4:6.45,5:7.7,6:10.4,7:11.85, 8:13.3,9:14.75,10:16.2,11:17.65,
    12:19.1,13:20.55,14:22,15:23.45

  The above example includes weights over 0 and up to 15.  Note that
  units are not specified in this explanation since they should be
  specific to your locale.

  CAVEATS
  At this time, it does not deal with weights that are above the highest amount
  defined.  This will probably be the next area to be improved with the
  module.  For now, you could have one last very high range with a very
  high shipping rate to discourage orders of that magnitude.  For
  instance:  999:1000

  If you want to be able to ship to any country in the world, you will
  need to enter every country code into the Country fields. For most
  shops, you will not want to enter every country.  This is often
  because of too much fraud from certain places. If a country is not
  listed, then the module will add a $0.00 shipping charge and will
  indicate that shipping is not available to that destination.
  PLEASE NOTE THAT THE ORDER CAN STILL BE COMPLETED AND PROCESSED!

  It appears that the osC shipping system automatically rounds the
  shipping weight up to the nearest whole unit.  This makes it more
  difficult to design precise shipping tables.  If you want to, you
  can hack the shipping.php file to get rid of the rounding.

  Lastly, there is a limit of 255 characters on each of the Zone
  Shipping Tables and Zone Countries.

*/

  class osC_Shipping_zones extends osC_Shipping_Admin {
    var $num_zones;

    var $_title,
        $_code = 'zones',
        $_author_name = 'osCommerce',
        $_author_www = 'http://www.oscommerce.com',
        $_status = true,
        $_sort_order;

// class constructor
    function osC_Shipping_zones() {
      global $osC_Language;

      $this->icon = '';

      $this->_title = $osC_Language->get('shipping_zones_title');
      $this->_description = $osC_Language->get('shipping_zones_description');
      $this->_status = (defined('MODULE_SHIPPING_ZONES_STATUS') && (MODULE_SHIPPING_ZONES_STATUS == 'True') ? true : false);
      $this->_sort_order = (defined('MODULE_SHIPPING_ZONES_SORT_ORDER') ? MODULE_SHIPPING_ZONES_SORT_ORDER : null);

// CUSTOMIZE THIS SETTING FOR THE NUMBER OF ZONES NEEDED
// HPDL; also needs to match the value at oscommerce/includes/modules/shipping/zones.php
      $this->num_zones = 1;
    }

// class methods
    function isInstalled() {
      return (bool)defined('MODULE_SHIPPING_ZONES_STATUS');
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Zones Method', 'MODULE_SHIPPING_ZONES_STATUS', 'True', 'Do you want to offer zone rate shipping?', '6', '0', 'osc_cfg_set_boolean_value(array(\'True\', \'False\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_ZONES_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'osc_cfg_use_get_tax_class_title', 'osc_cfg_set_tax_classes_pull_down_menu', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_ZONES_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");

      for ($i = 1; $i <= $this->num_zones; $i++) {
        $default_countries = '';

        if ($i == 1) {
          $default_countries = 'US,CA';
        }

        $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Zone " . $i ." Countries', 'MODULE_SHIPPING_ZONES_COUNTRIES_" . $i ."', '" . $default_countries . "', 'Comma separated list of two character ISO country codes that are part of Zone " . $i . ".', '6', '0', now())");
        $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Zone " . $i ." Shipping Table', 'MODULE_SHIPPING_ZONES_COST_" . $i ."', '3:8.50,7:10.50,99:20.00', 'Shipping rates to Zone " . $i . " destinations based on a group of maximum order weights. Example: 3:8.50,7:10.50,... Weights less than or equal to 3 would cost 8.50 for Zone " . $i . " destinations.', '6', '0', now())");
        $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Zone " . $i ." Handling Fee', 'MODULE_SHIPPING_ZONES_HANDLING_" . $i."', '0', 'Handling Fee for this shipping zone', '6', '0', now())");
        $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Module weight Unit', 'MODULE_SHIPPING_ZONES_WEIGHT_UNIT', '2', 'What unit of weight does this shipping module use?.', '6', '0', 'osC_Weight::getTitle', 'osc_cfg_set_weight_classes_pulldown_menu', now())");
      }
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_SHIPPING_ZONES_STATUS',
                             'MODULE_SHIPPING_ZONES_TAX_CLASS',
                             'MODULE_SHIPPING_ZONES_SORT_ORDER',
                             'MODULE_SHIPPING_ZONES_WEIGHT_UNIT');

        for ($i=1; $i<=$this->num_zones; $i++) {
          $this->_keys[] = 'MODULE_SHIPPING_ZONES_COUNTRIES_' . $i;
          $this->_keys[] = 'MODULE_SHIPPING_ZONES_COST_' . $i;
          $this->_keys[] = 'MODULE_SHIPPING_ZONES_HANDLING_' . $i;
        }
      }

      return $this->_keys;
    }
  }
?>
