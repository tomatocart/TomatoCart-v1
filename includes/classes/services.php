<?php
/*
  $Id: services.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2004 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Services {
    var $services,
        $started_services,
        $call_before_page_content = array(),
        $call_after_page_content = array();

    function osC_Services() {
      $services = explode(';', MODULE_SERVICES_INSTALLED);
      
      //ensure that the session serice will be started firstly
      $need_start_services = array();
      foreach($services as $service) {
        if ($service == 'session') {
          $this->startService($service);
        }else {
          $need_start_services[] = $service;
        }
      }
      
      $this->services = $need_start_services;
    }

    function startServices() {
      $this->started_services = array();

      foreach ($this->services as $service) {
        $this->startService($service);
      }      
    }

    function stopServices() {
/*
  ugly workaround to force the output_compression/GZIP service module to be stopped last
  to make sure all content in the buffer is compressed and sent to the client
*/
      if ($this->isStarted('output_compression')) {
        $key = array_search('output_compression', $this->started_services);
        unset($this->started_services[$key]);

        $this->started_services[] = 'output_compression';
      }

      foreach ($this->started_services as $service) {
        $this->stopService($service);
      }
    }

    function startService($service) {
      include('includes/modules/services/' . $service . '.php');

      if (@call_user_func(array('osC_Services_' . $service, 'start'))) {
        $this->started_services[] = $service;
      }
    }

    function stopService($service) {
      @call_user_func(array('osC_Services_' . $service, 'stop'));
    }


    function isStarted($service) {
      return in_array($service, $this->started_services);
    }

    function addCallBeforePageContent($object, $method) {
      $this->call_before_page_content[] = array($object, $method);
    }

    function addCallAfterPageContent($object, $method) {
      $this->call_after_page_content[] = array($object, $method);
    }

    function hasBeforePageContentCalls() {
      return !empty($this->call_before_page_content);
    }

    function hasAfterPageContentCalls() {
      return !empty($this->call_after_page_content);
    }

    function getCallBeforePageContent() {
      return $this->call_before_page_content;
    }

    function getCallAfterPageContent() {
      return $this->call_after_page_content;
    }
  }
?>
