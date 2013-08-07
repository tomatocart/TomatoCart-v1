<?php
/*
  $Id: osc_cfg_use_get_order_status_title.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  function osc_cfg_use_get_order_status_title($id) {
    global $osC_Database, $osC_Language;

    if ($id < 1) {
      return $osC_Language->get('default_entry');
    }

    $Qstatus = $osC_Database->query('select orders_status_name from :table_orders_status where orders_status_id = :orders_status_id and language_id = :language_id');
    $Qstatus->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
    $Qstatus->bindInt(':orders_status_id', $id);
    $Qstatus->bindInt(':language_id', $osC_Language->getID());
    $Qstatus->execute();

    return $Qstatus->value('orders_status_name');
  }
?>
