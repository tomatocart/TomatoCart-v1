<?php
/*
  $Id: osc_cfg_use_get_zone_class_title.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  function osc_cfg_use_get_zone_class_title($id) {
    global $osC_Database, $osC_Language;

    if ($id == '0') {
      return $osC_Language->get('parameter_none');
    }

    $Qclass = $osC_Database->query('select geo_zone_name from :table_geo_zones where geo_zone_id = :geo_zone_id');
    $Qclass->bindTable(':table_geo_zones', TABLE_GEO_ZONES);
    $Qclass->bindInt(':geo_zone_id', $id);
    $Qclass->execute();

    return $Qclass->value('geo_zone_name');
  }
?>
