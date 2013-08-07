<?php
/*
  $Id: templates.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require_once realpath(dirname(__FILE__) . '/../../') . '/includes/classes/template_info.php';

  class osC_Template_glass_gray extends osC_TemplateInfo {
    var $_id,
        $_title = 'TomatoCart Glass Gray',
        $_code = 'glass_gray',
        $_author_name = 'TomatoCart',
        $_author_www = 'http://www.tomatocart.com',
        $_markup_version = 'XHTML 1.0 Transitional',
        $_css_based = '1', /* 0=No; 1=Yes*/
        $_medium = 'Screen',
        $_groups = array('boxes' => array('left', 'right'),
                         'content' => array('slideshow', 'before', 'after')),
        $_keys,
        $_logo_width = '180',
        $_logo_height = '50';
        
    function install() {
      global $osC_Database;
      
      $Qinstall = $osC_Database->query('insert into :table_templates (title, code, author_name, author_www, markup_version, css_based, medium) values (:title, :code, :author_name, :author_www, :markup_version, :css_based, :medium)');
      $Qinstall->bindTable(':table_templates', TABLE_TEMPLATES);
      $Qinstall->bindValue(':title', $this->_title);
      $Qinstall->bindValue(':code', $this->_code);
      $Qinstall->bindValue(':author_name', $this->_author_name);
      $Qinstall->bindValue(':author_www', $this->_author_www);
      $Qinstall->bindValue(':markup_version', $this->_markup_version);
      $Qinstall->bindValue(':css_based', $this->_css_based);
      $Qinstall->bindValue(':medium', $this->_medium);
      $Qinstall->execute();
      
      $id = $osC_Database->nextID();
      
      $data = array('categories' => array('*', 'left', '100'),
                    'manufacturers' => array('*', 'left', '200'),
                    'information' => array('*', 'left', '300'),
                    'whats_new' => array('*', 'right', '100'),
                    'manufacturer_info' => array('products/info', 'right', '200'),
                    'order_history' => array('*', 'right', '300'),
                    'best_sellers' => array('*', 'right', '400'),
                    'product_notifications' => array('products/info', 'right', '500'),
                    'tell_a_friend' => array('products/info', 'right', '600'),
                    'specials' => array('*', 'right', '700'),
                    'reviews' => array('*', 'right', '800'),
                    'currencies' => array('*', 'right', '1000'),
                    'slide_show' => array('index/index', 'slideshow', 100),
                    'new_products' => array('index/category_listing', 'after', 400),
                    'new_products' => array('index/index', 'after', 400),
                    'upcoming_products' => array('index/index', 'after', 450),
                    'recently_visited' => array('*', 'after', 500),
                    'also_purchased_products' => array('products/info', 'after', 100));
      
      $Qboxes = $osC_Database->query('select id, code from :table_templates_boxes');
      $Qboxes->bindTable(':table_templates_boxes', TABLE_TEMPLATES_BOXES);
      $Qboxes->execute();
      
      while ($Qboxes->next()) {
        if (isset($data[$Qboxes->value('code')])) {
          $Qrelation = $osC_Database->query('insert into :table_templates_boxes_to_pages (templates_boxes_id, templates_id, content_page, boxes_group, sort_order, page_specific) values (:templates_boxes_id, :templates_id, :content_page, :boxes_group, :sort_order, :page_specific)');
          $Qrelation->bindTable(':table_templates_boxes_to_pages', TABLE_TEMPLATES_BOXES_TO_PAGES);
          $Qrelation->bindInt(':templates_boxes_id', $Qboxes->valueInt('id'));
          $Qrelation->bindInt(':templates_id', $id);
          $Qrelation->bindValue(':content_page', $data[$Qboxes->value('code')][0]);
          $Qrelation->bindValue(':boxes_group', $data[$Qboxes->value('code')][1]);
          $Qrelation->bindInt(':sort_order', $data[$Qboxes->value('code')][2]);
          $Qrelation->bindInt(':page_specific', 0);
          $Qrelation->execute();
        }
      }
      
      $this->resizeLogo();
    }

    function remove() {
      global $osC_Database;
      
      $Qdel = $osC_Database->query('delete from :table_templates_boxes_to_pages where templates_id = :templates_id');
      $Qdel->bindTable(':table_templates_boxes_to_pages', TABLE_TEMPLATES_BOXES_TO_PAGES);
      $Qdel->bindValue(':templates_id', $this->getID());
      $Qdel->execute();
      
      $Qdel = $osC_Database->query('delete from :table_templates where id = :id');
      $Qdel->bindTable(':table_templates', TABLE_TEMPLATES);
      $Qdel->bindValue(':id', $this->getID());
      $Qdel->execute();
      
      if ($this->hasKeys()) {
        $Qdel = $osC_Database->query('delete from :table_configuration where configuration_key in (":configuration_key")');
        $Qdel->bindTable(':table_configuration', TABLE_CONFIGURATION);
        $Qdel->bindRaw(':configuration_key', implode('", "', $this->getKeys()));
        $Qdel->execute();
      }

      $this->deleteLogo();    
    }
        
  }
?>