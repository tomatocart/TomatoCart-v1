<?php
/*
  $Id: system.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Json_System {
  
    function getTomatCartFeeds() {
      global $toC_Json;
      
      $url = 'http://www.tomatocart.com/live_feeds.php';
      
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_REFERER, HTTP_SERVER);
      curl_setopt($ch, CURLOPT_HTTPGET, true);
      curl_setopt($ch, CURLOPT_HEADER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      
      $response = curl_exec($ch);
      if (!curl_errno($ch)) {
        $data = trim(substr($response, strpos($response, "\r\n\r\n", strpos(strtolower($response), 'content-length:'))));
        
        $osC_XML = new osC_XML($data);
        $definitions = $osC_XML->toArray();

        $items = $definitions['rss']['channel']['item'];
        $feeds = '';
        if (is_array($items) && sizeof($items) > 0) {
          $feeds = '<a href="' . $items['link'] . '" target="_blank"><h1>' . $items['title'] . '</h1></a>' . '<p>' . $items['description'] . '</p><p align="right"><a href="' . $items['link'] . '" target="_blank">Read More...</a></p>';

          require_once('includes/classes/desktop_settings.php');
          $toC_Desktop_Settings = new toC_Desktop_Settings();
          
          $last_feed = (int) $toC_Desktop_Settings->getLastLiveFeed();
          $current_feed = (int) $items['pubDate'];

          if ($current_feed > $last_feed) {
            $response = array('success' => true, 'feeds' => $feeds);
            $toC_Desktop_Settings->setLastLiveFeed($current_feed);
          } else {
            $response = array('success' => false);
          }
        }
      } else {
        $response = array('success' => false);
      } 
      curl_close($ch);

      echo $toC_Json->encode($response);
    }
  }
?>
