<?php
/*
  $Id: osc_cfg_use_get_tax_class_title.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  function osc_cfg_use_get_tax_class_title($id) {
    global $osC_Database, $osC_Language;

    if ($id < 1) {
      return $osC_Language->get('parameter_none');
    }

    $Qclass = $osC_Database->query('select tax_class_title from :table_tax_class where tax_class_id = :tax_class_id');
    $Qclass->bindTable(':table_tax_class', TABLE_TAX_CLASS);
    $Qclass->bindInt(':tax_class_id', $id);
    $Qclass->execute();

    return $Qclass->value('tax_class_title');
  }
?>
