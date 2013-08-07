<?php
/*
  $Id: banner_manager.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/banner_manager.php');
  
  class toC_Json_Banner_Manager {
        
    function listBanner() {
      global $toC_Json, $osC_Database;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];     
      
      $Qbanners = $osC_Database->query('select banners_id, banners_title, banners_group, status from :table_banners order by banners_title, banners_group');
      $Qbanners->bindTable(':table_banners', TABLE_BANNERS);
      $Qbanners->setExtBatchLimit($start, $limit);
      $Qbanners->execute();
      
      $records = array();     
      while ($Qbanners->next()) {          
        $records[] = array('banners_id' => $Qbanners->value('banners_id'),
                           'banners_title' => $Qbanners->value('banners_title'),
                           'banners_group' => $Qbanners->value('banners_group'),
                           'statistics' => osC_BannerManager_Admin::getStatistics($Qbanners->value('banners_id')),
                           'status' => $Qbanners->valueInt('status'));           
      }
      $Qbanners->freeResult();
      
      $response = array(EXT_JSON_READER_TOTAL => $Qbanners->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }
    
    function saveBanner(){
      global $toC_Json, $osC_Database, $osC_Language;

      $error = false;
      $feedback = array();

      $data = array('title' => $_REQUEST['title'],
                    'url' => $_REQUEST['url'],
                    'group' => $_REQUEST['group'],
                    'group_new' => $_REQUEST['group_new'],
                    'banner_type' => $_REQUEST['banner_type'],
                    'image' => (isset($_FILES['image']) ? $_FILES['image'] : null),
                    'html_text' => isset($_REQUEST['html_text']) ? $_REQUEST['html_text'] : null,
                    'date_scheduled' => $_REQUEST['date_scheduled'],
                    'date_expires' => $_REQUEST['expires_date'],
                    'expires_impressions' => $_REQUEST['expires_impressions'],
                    'status' => (isset($_REQUEST['status']) && ($_REQUEST['status'] == 'on') ? true : false));
      
      if ( osC_BannerManager_Admin::save((isset($_REQUEST['banners_id']) && is_numeric($_REQUEST['banners_id']) ? $_REQUEST['banners_id'] : null), $data) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br>' . implode($feedback));
      }

      echo $toC_Json->encode($response);
    }
    
    function deleteBanner() {
      global $toC_Json, $osC_Language;
    
      if (osC_BannerManager_Admin::delete($_REQUEST['banners_id'], true)) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
     
      echo $toC_Json->encode($response);
    }
    
    function deleteBanners() {
      global $toC_Json, $osC_Language;
     
      $error = false;
      $batch = explode(',', $_REQUEST['batch']);
      
      foreach ($batch as $id) {
        if ( !osC_BannerManager_Admin::delete($id, true) ) {
          $error = true;
          break;
        }
      }
       
      if ($error === false) {      
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
       
      echo $toC_Json->encode($response);               
    }
    
    function listGroups() {
      global $toC_Json, $osC_Database;
     
      $Qgroups = $osC_Database->query('select distinct banners_group from :table_banners order by banners_group');
      $Qgroups->bindTable(':table_banners', TABLE_BANNERS);
      $Qgroups->execute();
       
      $records = array();     
      while ($Qgroups->next()) {          
        $records[] = array('text' => $Qgroups->value('banners_group'));           
      }
      $Qgroups->freeResult();
      
      $response = array(EXT_JSON_READER_TOTAL => $Qgroups->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }
    
    function listType() {
      global $toC_Json, $osC_Language;
 
      $records = array(array('id' => 'daily','text' => $osC_Language->get('section_daily')),
                       array('id' => 'monthly','text' => $osC_Language->get('section_monthly')),
                       array('id' => 'yearly', 'text' => $osC_Language->get('section_yearly')));
      
      $response = array(EXT_JSON_READER_ROOT => $records);

      echo $toC_Json->encode($response);
    }
    
    function listMonth() {
      global $toC_Json, $osC_Language;
 
      $records = array();

      for ( $i = 1; $i < 13; $i++ ) {
        $records[] = array('id' => $i,
                           'text' => strftime('%B', mktime(0,0,0,$i)));
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
   
      echo $toC_Json->encode($response);
    }
    
    function loadMonth() {
      global $toC_Json;
 
      $data = array();
      
      if (isset($_REQUEST['month'])) {
        $data['month'] = strftime('%B', mktime(0,0,0,$_REQUEST['month'] + 1));
      } 
      
      $response = array('success' => true, 'data' => $data);
       
      echo $toC_Json->encode($response);
    }
    
    function listYear() {
      global $toC_Json, $osC_Database;
      
      $Qyears = $osC_Database->query('select distinct year(banners_history_date) as banner_year from :table_banners_history where banners_id = :banners_id');
      $Qyears->bindTable(':table_banners_history', TABLE_BANNERS_HISTORY);
      $Qyears->bindInt(':banners_id', $_REQUEST['banners_id']);
      $Qyears->execute();

      $records = array();
      while ( $Qyears->next() ) {
        $records[] = array('id' => $Qyears->valueInt('banner_year'),
                           'text' => $Qyears->valueInt('banner_year'));
      }

      $Qyears->freeResult();
      
      $response = array(EXT_JSON_READER_TOTAL => $Qyears->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }
    
    function getImage(){
       global $toC_Json,$osC_Database, $osC_Language;
            
      $Qbanner = $osC_Database->query('select banners_title from :table_banners where banners_id = :banners_id');
      $Qbanner->bindTable(':table_banners', TABLE_BANNERS);
      $Qbanner->bindInt(':banners_id', $_REQUEST['banners_id']);
      $Qbanner->execute();
      
      $title= $Qbanner->value('banners_title');
      $Qbanner->freeResult();
       
      $type = $_REQUEST['type'];
      
        switch ($type ) {
          case 'yearly':
            include('includes/graphs/banner_yearly.php');
    
            $image = '<p><img src="images/graphs/banner_yearly-' . $_REQUEST['banners_id'] . '.png? id =' . rand(0, 20) . '" /></p>';
            break;
    
          case 'monthly':
            include('includes/graphs/banner_monthly.php');
      
            $image = '<p><img src="images/graphs/banner_monthly-' . $_REQUEST['banners_id'] . '.png? id =' . rand(0, 20) . '" /></p>';
            break;
    
          case 'daily':
            include('includes/graphs/banner_daily.php');
            
            $image = '<p><img src="images/graphs/banner_daily-' . $_REQUEST['banners_id'] . '.png? id =' . rand(0, 20) . '" /></p>';
            break;
            
          default:
            include('includes/graphs/banner_daily.php');
            
            $image = '<p><img src="images/graphs/banner_daily-' . $_REQUEST['banners_id'] . '.png? id =' . rand(0, 20) . '" /></p>';
        
        }
        
      $response['image'] = $image;
     
      echo $toC_Json->encode($response);
    }
    
    
    function getTable(){
      global $toC_Json,$osC_Database, $osC_Language;
            
      $Qbanner = $osC_Database->query('select banners_title from :table_banners where banners_id = :banners_id');
      $Qbanner->bindTable(':table_banners', TABLE_BANNERS);
      $Qbanner->bindInt(':banners_id', $_REQUEST['banners_id']);
      $Qbanner->execute();
      
      $title= $Qbanner->value('banners_title');
      $Qbanner->freeResult();
       
      $type = $_REQUEST['type'];
      
      switch ($type ) {
        case 'yearly':
          include('includes/graphs/banner_yearly.php');

          break;
  
        case 'monthly':
          include('includes/graphs/banner_monthly.php');

          break;
  
        case 'daily':
          include('includes/graphs/banner_daily.php');
  
          break;
        default:
          include('includes/graphs/banner_daily.php');
  
      }
      
      $records = array();
      for ( $i = 0, $n = sizeof($stats); $i < $n; $i++ ) {
        $records[] = array('source' => $stats[$i][0],
                           'views' => number_format($stats[$i][1]),
                           'clicks' => number_format($stats[$i][2]));           
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }
    
    function loadBanner() {
      global $toC_Json;
       
      $banner = osC_BannerManager_Admin::getData($_REQUEST['banner_id']);

      $data = array(
        'title' => $banner['banners_title'],
        'url' => $banner['banners_url'],
        'banners_group' => $banner['banners_group'],
        'html_text' => $banner['banners_html_text'],
        'date_scheduled' => (empty($banner['date_scheduled']) ? '' : osC_DateTime::getDate($banner['date_scheduled'])),
        'expires_date' => (empty($banner['expires_date']) ? '' : osC_DateTime::getDate($banner['expires_date'])),
        'expires_impressions' => $banner['expires_impressions'],
        'status' => (($banner['status'] == '1') ? true : false)); 

      if ( isset($banner['banners_image']) && !empty($banner['banners_image']) ) {
        $data['banners_image'] = $banner['banners_image'];
      } 
      
      $response = array('success' => true, 'data' => $data);
       
      echo $toC_Json->encode($response);
    }
    
    function setStatus() {
      global $toC_Json, $osC_Language;
        
      if ( osC_BannerManager_Admin::setStatus($_REQUEST['banners_id'], ( isset($_REQUEST['flag']) ? $_REQUEST['flag'] : null) ) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed') );
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
  }
?>
