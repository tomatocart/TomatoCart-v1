<?php
/*
  $Id: osc_cfg_set_articles_categories_pulldown_menu.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  function osc_cfg_set_articles_categories_pulldown_menu($default, $key = null) {
    global $osC_Database, $osC_Language;

    $Qarticles = $osC_Database->query('select articles_categories_id, articles_categories_name  from :table_articles_categories_description where language_id = :language_id and articles_categories_id != 1');
    $Qarticles->bindTable(':table_articles_categories_description', TABLE_ARTICLES_CATEGORIES_DESCRIPTION);
    $Qarticles->bindInt(':language_id', $osC_Language->getID());
    $Qarticles->execute();

    while ($Qarticles->next()) {
        $type_array[] = array('id' => $Qarticles->value('articles_categories_id'), 'text' => $Qarticles->value('articles_categories_name'));
    }
    $Qarticles->freeResult();

    $name = (empty($key)) ? 'configuration_value' : 'configuration[' . $key . ']';
    
    $control = array();
    $control['name'] = $name;
    $control['type'] = 'combobox';
    $control['mode'] = 'local';
    $control['values'] = $type_array;

    return $control;
  }
?>
