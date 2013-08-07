<?php
/*
  $Id: image_menu.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Content_image_menu extends osC_Modules {
    var $_title,
        $_code = 'image_menu',
        $_author_name = 'TomatoCart',
        $_author_www = 'http://www.tomatocart.com',
        $_group = 'content';

/* Class constructor */

    function osC_Content_image_menu() {
      global $osC_Language;

      $this->_title = $osC_Language->get('image_menu_show_title');
    }

    function initialize() {
      global $osC_Database, $osC_Language, $osC_Template;

      $Qimages=$osC_Database->query('select image ,image_url from :table_slide_images where language_id =:language_id and status = 1 order by sort_order desc');
      $Qimages->bindTable(':table_slide_images', TABLE_SLIDE_IMAGES);
      $Qimages->bindInt(':language_id', $osC_Language->getID());
      $Qimages->setCache('slide-images-' . $osC_Language->getCode());
      $Qimages->execute();

      if($Qimages->numberOfRows() > 0){

      $i = 0;
        $css = '';
        $this->_content = '<div id="imageMenu"><ul>';
        while ($Qimages->next()) {
          $this->_content .= '<li class="imageMenu' . $i . '"><a href="' . $Qimages->value('image_url') . '">&nbsp;</a></li>';
          $css .= '#imageMenu ul li.imageMenu' . $i . ' a {background: url(images/' . $Qimages->value('image') . ') repeat scroll 0%;}' . "\n";
          $i++;
        }
        $this->_content .= '</ul></div>';

        $width = MODULE_CONTENT_IMAGE_MENU_WIDTH;
        $height = MODULE_CONTENT_IMAGE_MENU_HEIGHT;
        $opened_width = MODULE_CONTENT_IMAGE_MENU_OPEN_WIDTH;
        $cosed_width = MODULE_CONTENT_IMAGE_MENU_CLOSE_WIDTH;
        $border_width = MODULE_CONTENT_IMAGE_MENU_BORDER_WIDTH;
        $interval = MODULE_CONTENT_IMAGE_MENU_INTERVAL;
        $duration = MODULE_CONTENT_IMAGE_MENU_DURATION;

        $css = '#imageMenu {position: relative;width:' . $width . 'px;height:' . $height . 'px;overflow: hidden;}' . "\n" .
               '#imageMenu ul {list-style: none;margin: 0px;display: block;height: ' . $height . 'px;width: 1000px;padding:0px}' . "\n" .
               '#imageMenu ul li {float: left;}' . "\n" .
               '#imageMenu ul li a {text-indent: -1000px;background:#FFFFFF none repeat scroll 0%;border-right: ' . $border_width . 'px solid #fff;cursor:pointer;display:block;overflow:hidden;width:' . $cosed_width . 'px;height:' . $height . 'px;}' . "\n" .
            $css . "\n" .
            '#imageMenu ul li.imageMenu0 a {width:' . $opened_width . 'px;}';

        $osC_Template->addStyleDeclaration($css);
        $osC_Template->addHeaderJavascriptFilename('ext/image_menu/imageMenu.js');

        $this->_content .=
            '<script type="text/javascript">' . "\n" .
              'var myMenu = new ImageMenu($$(\'#imageMenu a\'),{openWidth:' . $opened_width . ', closeWidth:' . $cosed_width . ', interval:' . $interval . ', border:' . $border_width . ', duration:' . $duration . '});' . "\n" .
            '</script>';

          $this->_content .=
            '<script type="text/javascript">
              window.addEvent(\'domready\',function(){' .
                  'var myMenu = new ImageMenu($$(\'#imageMenu a\'),{openWidth:' . $opened_width . ', closeWidth:' . $cosed_width . ', interval:' . $interval . ', border:' . $border_width . ', duration:' . $duration . '});' . "\n" .
              '});' .
            '</script>';
      }

      $Qimages->freeResult();
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Image menu width', 'MODULE_CONTENT_IMAGE_MENU_WIDTH', '530', 'The width of the Image Menu (px)', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Image menu height', 'MODULE_CONTENT_IMAGE_MENU_HEIGHT', '200', 'The height of the Image Menu (px)', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Opened image width', 'MODULE_CONTENT_IMAGE_MENU_OPEN_WIDTH', '320', 'The width of opened Image (px)', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Closed image width', 'MODULE_CONTENT_IMAGE_MENU_CLOSE_WIDTH', '45', 'The width of closed Image (px)', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Border width between images', 'MODULE_CONTENT_IMAGE_MENU_BORDER_WIDTH', '2', 'The width of border between images (px)', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Slide interval', 'MODULE_CONTENT_IMAGE_MENU_INTERVAL', '6000', 'Slide interval (ms)', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Transition duration', 'MODULE_CONTENT_IMAGE_MENU_DURATION', '500', 'Transition duration (ms)', '6', '0', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_CONTENT_IMAGE_MENU_WIDTH',
                             'MODULE_CONTENT_IMAGE_MENU_HEIGHT',
                             'MODULE_CONTENT_IMAGE_MENU_OPEN_WIDTH',
                             'MODULE_CONTENT_IMAGE_MENU_CLOSE_WIDTH',
                             'MODULE_CONTENT_IMAGE_MENU_BORDER_WIDTH',
                             'MODULE_CONTENT_IMAGE_MENU_INTERVAL',
                             'MODULE_CONTENT_IMAGE_MENU_DURATION');
      }

      return $this->_keys;
    }
  }
?>
