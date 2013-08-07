<?php
/*
  $Id: articles_categories.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Boxes_articles_categories extends osC_Modules {
    var $_title,
        $_code = 'articles_categories',
        $_author_name = 'TomatoCart',
        $_author_www = 'http://www.tomatocart.com',
        $_group = 'boxes';

    function osC_Boxes_articles_categories() {
      global $osC_Language;

      $this->_title = $osC_Language->get('box_articles_categories_heading');
    }

    function initialize() {
      global $osC_Database,$osC_Language;
      $this->_title_link = osc_href_link(FILENAME_INFO,'articles_categories');

      $Qac = $osC_Database->query('select cd.articles_categories_id, cd.articles_categories_name from :table_articles_categories c, :table_articles_categories_description cd where c.articles_categories_id = cd.articles_categories_id and cd.language_id = :language_id and c.articles_categories_status = 1 order by c.articles_categories_order, cd.articles_categories_name limit :max_display_articles_categories');
      $Qac->bindTable(':table_articles_categories', TABLE_ARTICLES_CATEGORIES);
      $Qac->bindTable(':table_articles_categories_description', TABLE_ARTICLES_CATEGORIES_DESCRIPTION);
      $Qac->bindInt(':language_id', $osC_Language->getID());
      $Qac->bindInt(':max_display_articles_categories', BOX_ARTICLES_CATEGORIES_MAX_LIST);
      $Qac->setCache('box_articles_categories-' . $osC_Language->getCode(), BOX_ARTICLES_CATEGORIES_MAX_LIST);
      $Qac->execute();

      $this->_content = '<ul>';

      while ($Qac->next()) {
        if($Qac->valueInt('articles_categories_id') > 1){
          $this->_content .= '<li>' . osc_link_object(osc_href_link(FILENAME_INFO, 'articles_categories&articles_categories_id='.$Qac->value('articles_categories_id')), $Qac->value('articles_categories_name')) . '</li>';
        }
      }

      $this->_content .= '</ul>';

      $Qac->freeResult();
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Maximum List Size', 'BOX_ARTICLES_CATEGORIES_MAX_LIST', '10', 'Maximum amount of article categories to show in the listing', '6', '0', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('BOX_ARTICLES_CATEGORIES_MAX_LIST');
      }

      return $this->_keys;
    }
  }
?>
