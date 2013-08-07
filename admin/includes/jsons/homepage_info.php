<?php
/*
  $Id: homepage_info.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/classes/homepage_info.php');

  class toC_Json_Homepage_info {

    function saveInfo() {
      global $toC_Json, $osC_Language;

      $data = array('page_title' => $_REQUEST['HOME_PAGE_TITLE'],
                    'keywords' => $_REQUEST['HOME_META_KEYWORD'], 
                    'descriptions' => $_REQUEST['HOME_META_DESCRIPTION'],
                    'index_text' => $_REQUEST['index_text']);

      if(toC_Homepage_Info_Admin::saveData($data)) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }

    function loadInfo() {
      global $toC_Json;
      
      $data = toC_Homepage_Info_Admin::getData();
      
      $response = array('success' => true, 'data' => $data);
      
      echo $toC_Json->encode($response);
    }
  }
?>
