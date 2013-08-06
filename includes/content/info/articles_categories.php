<?php
/*
  $Id: articles_categories.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require_once('includes/classes/articles.php');

  class osC_Info_Articles_categories extends osC_Template {

/* Private variables */

    var $_module = 'articles_categories',
        $_group = 'info',
        $_page_title,
        $_page_contents = 'articles_categories.php',
        $_page_image = 'table_background_reviews_new.gif';

/* Class constructor */

    function osC_Info_Articles_categories() {
      global $osC_Language, $osC_Services, $breadcrumb, $article_categories;
      
      if ( isset($_GET['articles_categories_id']) && !empty($_GET['articles_categories_id']) ) {
        $article_categories = toC_Articles::getArticleCategoriesEntry($_GET['articles_categories_id']);
        
        $this->_page_title = $article_categories['articles_categories_name'];
        
        if (!empty($article_categories['page_title'])) {
          $this->setMetaPageTitle($article_categories['page_title']);        
        }
        
        if (!empty($article_categories['meta_keywords'])) {
          $this->addPageTags('keywords', $enty['meta_keywords']);
        }
        
        if (!empty($article_categories['meta_description'])) {
          $this->addPageTags('description', $enty['meta_description']);
        }
        
        if ($osC_Services->isStarted('breadcrumb')) {
          $breadcrumb->add($article_categories['articles_categories_name'], osc_href_link(FILENAME_INFO, 'articles_categories&articles_categories_id=' . $_GET['articles_categories_id']));
        }
      } else {
        $this->_page_title = $osC_Language->get('info_not_found_heading');
        $this->_page_contents = 'info_not_found.php';
      }
    }
  }
?>
