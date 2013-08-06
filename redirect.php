<?php
/*
  $Id: redirect.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $_SERVER['SCRIPT_FILENAME'] = __FILE__;

  require('includes/application_top.php');

  switch ($_GET['action']) {
    case 'banner':
      if (isset($_GET['goto']) && is_numeric($_GET['goto'])) {
        if ($osC_Services->isStarted('banner') && $osC_Banner->isActive($_GET['goto'])) {
          osc_redirect($osC_Banner->getURL($_GET['goto'], true));
        }
      }
      break;

    case 'url':
      if (isset($_GET['goto']) && !empty($_GET['goto'])) {
        $Qcheck = $osC_Database->query('select products_url from :table_products_description where products_url = :products_url limit 1');
        $Qcheck->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
        $Qcheck->bindValue(':products_url', $_GET['goto']);
        $Qcheck->execute();

        if ($Qcheck->numberOfRows() === 1) {
          osc_redirect('http://' . $HTTP_GET_VARS['goto']);
        }
      }
      break;

    case 'manufacturer':
      if (isset($_GET['manufacturers_id']) && !empty($_GET['manufacturers_id'])) {
        $Qmanufacturer = $osC_Database->query('select manufacturers_url from :table_manufacturers_info where manufacturers_id = :manufacturers_id and languages_id = :languages_id');
        $Qmanufacturer->bindTable(':table_manufacturers_info', TABLE_MANUFACTURERS_INFO);
        $Qmanufacturer->bindInt(':manufacturers_id', $_GET['manufacturers_id']);
        $Qmanufacturer->bindInt(':languages_id', $osC_Language->getID());
        $Qmanufacturer->execute();

        if ($Qmanufacturer->numberOfRows() && !osc_empty($Qmanufacturer->value('manufacturers_url'))) {
          $Qupdate = $osC_Database->query('update :table_manufacturers_info set url_clicked = url_clicked+1, date_last_click = now() where manufacturers_id = :manufacturers_id and languages_id = :languages_id');
          $Qupdate->bindTable(':table_manufacturers_info', TABLE_MANUFACTURERS_INFO);
          $Qupdate->bindInt(':manufacturers_id', $_GET['manufacturers_id']);
          $Qupdate->bindInt(':languages_id', $osC_Language->getID());
          $Qupdate->execute();

          osc_redirect($Qmanufacturer->value('manufacturers_url'));
        } else {
// no url exists for the selected language, lets use the default language then
          $Qmanufacturer = $osC_Database->query('select mi.languages_id, mi.manufacturers_url from :table_manufacturers_info mi, :table_languages l where mi.manufacturers_id = :manufacturers_id and mi.languages_id = l.languages_id and l.code = :code');
          $Qmanufacturer->bindTable(':table_manufacturers_info', TABLE_MANUFACTURERS_INFO);
          $Qmanufacturer->bindTable(':table_languages', TABLE_LANGUAGES);
          $Qmanufacturer->bindInt(':manufacturers_id', $_GET['manufacturers_id']);
          $Qmanufacturer->bindValue(':code', DEFAULT_LANGUAGE);
          $Qmanufacturer->execute();

          if ($Qmanufacturer->numberOfRows() && !osc_empty($Qmanufacturer->value('manufacturers_url'))) {
            $Qupdate = $osC_Database->query('update :table_manufacturers_info set url_clicked = url_clicked+1, date_last_click = now() where manufacturers_id = :manufacturers_id and languages_id = :languages_id');
            $Qupdate->bindTable(':table_manufacturers_info', TABLE_MANUFACTURERS_INFO);
            $Qupdate->bindInt(':manufacturers_id', $_GET['manufacturers_id']);
            $Qupdate->bindInt(':languages_id', $Qmanufacturer->valueInt('languages_id'));
            $Qupdate->execute();

            osc_redirect($Qmanufacturer->value('manufacturers_url'));
          }
        }
      }
      break;
  }

  osc_redirect(osc_href_link(FILENAME_DEFAULT));
?>
