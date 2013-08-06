<?php
/*
  $Id: slide_show.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Content_slide_show extends osC_Modules {
    var $_title,
        $_code = 'slide_show',
        $_author_name = 'TomatoCart',
        $_author_www = 'http://www.tomatocart.com',
        $_group = 'content';

/* Class constructor */

    function osC_Content_slide_show() {
      global $osC_Language;

      $this->_title = $osC_Language->get('slide_show_title');
    }

    function initialize() {
      global $osC_Database, $osC_Services, $osC_Language, $osC_Image, $osC_Template;

      $Qimages = $osC_Database->query('select image ,image_url, description from :table_slide_images where language_id =:language_id and status = 1 order by sort_order desc');
      $Qimages->bindTable(':table_slide_images', TABLE_SLIDE_IMAGES);
      $Qimages->bindInt(':language_id', $osC_Language->getID());
      $Qimages->setCache('slide-images-' . $osC_Language->getCode());
      $Qimages->execute();

      if ($Qimages->numberOfRows() > 0) {

        $tmp = array();
        for($i = 0; $i < $Qimages->numberOfRows(); $i++) $tmp[] = $i;
        $items = 'items: [' . implode(',', $tmp) . '],';

        $this->_content =
          '<div style="overflow: auto; height: 100%; margin: 0px auto;">' . "\n" .
            '<div id="slideWrapper">' . "\n" .
              '<div id="slideItems">' . "\n";

        $descriptions = array();
        while($Qimages->next()){
          $this->_content .= '<span><a href="' . $Qimages->value('image_url') . '">' . osc_image(DIR_WS_IMAGES . $Qimages->value('image'), $Qimages->value('description')) . '</a></span>' . "\n";
          $descriptions[] = '{description:\'' . $Qimages->value('description') . '\', link:\'' . $Qimages->value('image_url') . '\'}';
        }

        $info_div = '';
        $info_css = '';
        $info_js = '';
        $info_on_walk_js = '';
        if(MODULE_CONTENT_SLIDE_SHOW_DISPLAY_INFO == 'True'){
          $info_div = '<div id="slideInfo"></div>' . "\n";

          $info_css =
            '#slideInfo{bottom:0;}' . "\n" .
            '#slideInfo{width:' . MODULE_CONTENT_SLIDE_SHOW_WIDTH . 'px; height:40px; background:#000; position:absolute;}' . "\n" .
            '#slideInfo p{color:#fff;padding:3px 8px;font-family:Arial;font-size:13px;}' . "\n" .
            '#slideInfo a{float:right;background:#fff;color:#000;font-size:10px;margin:5px 5px;width:30px;text-decoration:none;text-align:center}' . "\n";
          $info_js  =
              'var slide_info = $(\'slideInfo\').set(\'opacity\',0.5);' . "\n" .
              'var infoItems =[' . implode(",", $descriptions) . '];' . "\n";

          $items = 'items: infoItems,';
          $info_on_walk_js = ',
            onWalk: function(currentItem){
            slide_info.empty();
            new Element(\'a\', {href: currentItem.link}).appendText(\'link\').inject(slide_info);
            new Element(\'p\').set(\'html\',currentItem.description).inject(slide_info);
          }';
        }

        $this->_content .=
              '</div>' . "\n" .
                $info_div .
            '</div>
          </div>' . "\n";



        $css = '#slideWrapper{position:relative; width:' . MODULE_CONTENT_SLIDE_SHOW_WIDTH . 'px; height:' . MODULE_CONTENT_SLIDE_SHOW_HEIGHT . 'px; overflow:hidden;}' . chr(13) .
               '#slideItems{position:absolute;}' . chr(13) .
               '#slideItems span{display:block; float:left;}' . chr(13) .
               '#slideItems span img{display:block;border:none;}' . chr(13) .
               $info_css;

        $osC_Template->addStyleDeclaration($css);
        $osC_Template->addJavascriptFilename('ext/noobslide/noobslide.js');

        $size = (MODULE_CONTENT_SLIDE_SHOW_MODE == 'horizontal') ? MODULE_CONTENT_SLIDE_SHOW_WIDTH : MODULE_CONTENT_SLIDE_SHOW_HEIGHT;
        $this->_content .=
          ' <script type="text/javascript">
            window.addEvent(\'domready\',function(){' .
                $info_js . '
                var slide_show = new noobSlide({
                mode: \'' . MODULE_CONTENT_SLIDE_SHOW_MODE . '\',' .
                $items .
                'size: ' . $size . ',
                box: $(\'slideItems\'),
                interval: ' . MODULE_CONTENT_SLIDE_SHOW_INTERVAL . ',
                duration: ' . MODULE_CONTENT_SLIDE_SHOW_DURATION . ',' .
                'autoPlay: true' .
                $info_on_walk_js .
              '});
        });' . '</script>';
      }
      $Qimages->freeResult();
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) values ('Slide show mode [vertical, horizontal]', 'MODULE_CONTENT_SLIDE_SHOW_MODE', 'horizontal', 'Slideshow Mode', '6', '0', 'osc_cfg_set_boolean_value(array(\'horizontal\', \'vertical\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) values ('Display Slide info', 'MODULE_CONTENT_SLIDE_SHOW_DISPLAY_INFO', 'True', 'Display Slide Info', '6', '0', 'osc_cfg_set_boolean_value(array(\'True\', \'False\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Image width (px)', 'MODULE_CONTENT_SLIDE_SHOW_WIDTH', '500', 'Image width', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Image height (px)', 'MODULE_CONTENT_SLIDE_SHOW_HEIGHT', '210', 'Image height', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Interval (ms)', 'MODULE_CONTENT_SLIDE_SHOW_INTERVAL', '3000', 'slide show interval', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Duration (ms)', 'MODULE_CONTENT_SLIDE_SHOW_DURATION', '1000', 'slide show duration', '6', '0', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_CONTENT_SLIDE_SHOW_MODE',
                             'MODULE_CONTENT_SLIDE_SHOW_DISPLAY_INFO',
                             'MODULE_CONTENT_SLIDE_SHOW_WIDTH',
                             'MODULE_CONTENT_SLIDE_SHOW_HEIGHT',
                             'MODULE_CONTENT_SLIDE_SHOW_INTERVAL',
                             'MODULE_CONTENT_SLIDE_SHOW_DURATION');
      }

      return $this->_keys;
    }
  }
?>
