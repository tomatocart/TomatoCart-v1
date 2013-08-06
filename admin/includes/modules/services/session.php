<?php
/*
  $Id: session.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Services_session_Admin {
    var $title,
        $description,
        $uninstallable = false,
        $depends,
        $precedes;

    function osC_Services_session_Admin() {
      global $osC_Language;

      $osC_Language->loadIniFile('modules/services/session.php');

      $this->title = $osC_Language->get('services_session_title');
      $this->description = $osC_Language->get('services_session_description');
    }

    function install() {
      global $osC_Database;

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Force Cookie Usage', 'SERVICE_SESSION_FORCE_COOKIE_USAGE', '-1', 'Only start a session when cookies are enabled.', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Block Search Engine Spiders', 'SERVICE_SESSION_BLOCK_SPIDERS', '-1', 'Block search engine spider robots from starting a session.', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Check SSL Session ID', 'SERVICE_SESSION_CHECK_SSL_SESSION_ID', '-1', 'Check the SSL_SESSION_ID on every secure HTTPS page request.', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Check User Agent', 'SERVICE_SESSION_CHECK_USER_AGENT', '-1', 'Check the browser user agent on every page request.', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Check IP Address', 'SERVICE_SESSION_CHECK_IP_ADDRESS', '-1', 'Check the IP address on every page request.', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Regenerate Session ID', 'SERVICE_SESSION_REGENERATE_ID', '-1', 'Regenerate the session ID when a customer logs on or creates an account (requires PHP >= 4.1).', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Max Life Time', 'SERVICE_SESSION_MAX_LIFETIME', '15', 'The session will be expired after the number of minutes', '6', '0', now())");
    }

    function remove() {
      global $osC_Database;

      $osC_Database->simpleQuery("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('SERVICE_SESSION_FORCE_COOKIE_USAGE',
                   'SERVICE_SESSION_BLOCK_SPIDERS',
                   'SERVICE_SESSION_CHECK_SSL_SESSION_ID',
                   'SERVICE_SESSION_CHECK_USER_AGENT',
                   'SERVICE_SESSION_CHECK_IP_ADDRESS',
                   'SERVICE_SESSION_REGENERATE_ID', 
                   'SERVICE_SESSION_MAX_LIFETIME');
    }
  }
?>
