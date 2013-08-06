<?php
/*
  $Id: templates.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Boxes_templates extends osC_Modules {
    var $_title,
        $_code = 'templates',
        $_author_name = 'osCommerce',
        $_author_www = 'http://www.oscommerce.com',
        $_group = 'boxes';

    function osC_Boxes_templates() {
      global $osC_Language;

      $this->_title = $osC_Language->get('box_templates_heading');
    }

    function initialize() {
      global $osC_Session;

      $data = array();

      foreach (osC_Template::getTemplates() as $template) {
        $data[] = array('id' => $template['code'], 'text' => $template['title']);
      }

      if (sizeof($data) > 1) {
        $hidden_get_variables = '';

        foreach ($_GET as $key => $value) {
          if ( ($key != 'template') && ($key != $osC_Session->getName()) && ($key != 'x') && ($key != 'y') ) {
            if (is_array($value)) {
              foreach($value as $hidden_value) {
                $hidden_get_variables .= osc_draw_hidden_field($key, $hidden_value);
              }
            }else {
              $hidden_get_variables .= osc_draw_hidden_field($key, $value);
            }
          }
        }

        $this->_content = '<form name="templates" action="' . osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), null, 'AUTO', false) . '" method="get">' .
                          $hidden_get_variables . osc_draw_pull_down_menu('template', $data, $_SESSION['template']['code'], 'onchange="this.form.submit();" style="width: 99%"') . osc_draw_hidden_session_id_field() .
                          '</form>';
      }
    }
  }
?>
