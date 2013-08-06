<?php
/*
  $Id: osc_cfg_set_order_statuses_pull_down_menu.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  function osc_cfg_set_order_statuses_pull_down_menu($default, $key = null) {
    global $osC_Database, $osC_Language;

    $name = (empty($key)) ? 'configuration_value' : 'configuration[' . $key . ']';

    $statuses_array = array(array('id' => '0',
                                  'text' => $osC_Language->get('default_entry')));

    $Qstatuses = $osC_Database->query('select orders_status_id, orders_status_name from :table_orders_status where language_id = :language_id order by orders_status_id');
    $Qstatuses->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
    $Qstatuses->bindInt(':language_id', $osC_Language->getID());
    $Qstatuses->execute();

    while ($Qstatuses->next()) {
      $statuses_array[] = array('id' => $Qstatuses->valueInt('orders_status_id'),
                                'text' => $Qstatuses->value('orders_status_name'));
    }
    
    $control = array();
    $control['name'] = $name;
    $control['type'] = 'combobox';
    $control['mode'] = 'local';
    $control['values'] = $statuses_array;

    return $control;
  }
?>
