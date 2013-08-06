<?php
/*
  $Id: toc_cfg_set_weight_classes_pull_down_menu.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  function toc_cfg_set_weight_class_pull_down_menu($default, $key = null) {
    global $osC_Database, $osC_Language;
    
    $name = (empty($key)) ? 'configuration_value' : 'configuration[' . $key . ']';

    $weight_class_array = array(array('id' => '0',
                                      'text' => $osC_Language->get('parameter_none')));

    $Qclasses = $osC_Database->query('select weight_class_id, weight_class_title from :table_weight_class where language_id = :language_id order by weight_class_id');
    $Qclasses->bindTable(':table_weight_class', TABLE_WEIGHT_CLASS);
    $Qclasses->bindInt(':language_id', $osC_Language->getID());
    $Qclasses->execute();

    while ($Qclasses->next()) {
      $weight_class_array[] = array('id' => $Qclasses->valueInt('weight_class_id'),
                                    'text' => $Qclasses->value('weight_class_title'));
    }

    $control = array();
    $control['name'] = $name;
    $control['type'] = 'combobox';
    $control['mode'] = 'local';
    $control['values'] = $weight_class_array;

    return $control;
  }
?>
