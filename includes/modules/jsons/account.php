<?php
/*
  $Id: account.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Json_Account {
  
    function displayPrivacy() {
      global $osC_Language;
      
      $osC_Language->load('info');
      
      require_once('includes/classes/articles.php');
      
      $article = toC_Articles::getEntry(INFORMATION_PRIVACY_NOTICE);

      $content = '<div style="margin: 10px">';
      $content .= '<h1>' . $osC_Language->get('info_privacy_heading') . '</h1>';
      $content .= $article['articles_description'];
      $content .= '</div>';
      
      echo $content;
    }
    
    /**
     * handle the country change event
     *
     * @access  public
     * @return json
     */
    function countryChange() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $Qzones = $osC_Database->query('select zone_name from :table_zones where zone_country_id = :zone_country_id order by zone_name');
      $Qzones->bindTable(':table_zones', TABLE_ZONES);
      $Qzones->bindInt(':zone_country_id', $_REQUEST['country_id']);
      $Qzones->execute();
  
      $zones_array = array();
      while ($Qzones->next()) {
        $zones_array[] = array('id' => $Qzones->value('zone_name'), 'text' => $Qzones->value('zone_name'));
      }
      
      if (sizeof($zones_array) > 0) {
        $response = array(
          'success' => true, 
          'html' => osc_draw_pull_down_menu('state', $zones_array));
      } else {
        $response = array(
          'success' => true, 
          'html' => osc_draw_input_field('state'));
      }
      
      echo $toC_Json->encode($response);
    }
  }
  