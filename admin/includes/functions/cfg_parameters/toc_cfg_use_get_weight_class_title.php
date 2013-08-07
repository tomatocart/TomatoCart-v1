<?php
/*
  $Id: toc_cfg_use_get_weight_class_title.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  function toc_cfg_use_get_weight_class_title($id) {
    global $osC_Database, $osC_Language;
    
    if ($id < 1) {
      return $osC_Language->get('parameter_none');
    }

    $Qclass = $osC_Database->query('select weight_class_title from :table_weight_class where weight_class_id = :weight_class_id and language_id = :language_id');
    $Qclass->bindTable(':table_weight_class', TABLE_WEIGHT_CLASS);
    $Qclass->bindInt(':weight_class_id', $id);
    $Qclass->bindInt(':language_id', $osC_Language->getID());
    $Qclass->execute();

    return $Qclass->value('weight_class_title');
  }
?>
