<?php
/*
  $Id: sefu.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class toC_Sefu {

  var $_process_methods = array(),
      $_anchors = array(),
      $_products_cache = array(),
      $_products_reviews_cache = array(),
      $_articles_cache = array(),
      $_article_categories_cache = array(),
      $_manufacturers_cache = array(),
      $_faqs_cache = array();

  function toC_Sefu(){
    $this->_reg_anchors = array('products_id' => '-',
                                'cPath' => '-',
                                'manufacturers' => '_',
                                'articles_categories_id' => '--',
                                'articles_id' => '--',
                                'faqs_id' => '-f-',
                                'tell_a_friend' => '-t-');

    $this->initialize();
  }

  function initialize(){
    $this->_iniProductsCache();
    $this->_iniArticlesCache();
    $this->_iniProductsReviewsCache();
    $this->_iniArticleCategoriesCache();
    $this->_iniFaqsCache();
    $this->_iniManufacturersCache();
  }

  function generateURL($link, $page, $parameters){
    if (SERVICES_KEYWORD_RICH_URLS == '0') {
      $link = str_replace(array('?', '&', '='), array('/', '/', ','), $link);
    } else if(SERVICES_KEYWORD_RICH_URLS == '1'){
      $link = $this->generateRichKeywordURL($link, $page, $parameters);
    }

    return $link;
  }

  function generateRichKeywordURL($link, $page, $parameters){
    //cPath
    if ( preg_match("/index.php\?cPath=([0-9_]+)(.*)/", $link, $matches) > 0 ) {
      $categories = @explode('_', $matches[1]);

      $category_id = $matches[1];
    
      $link = $this->makeUrl($page, $this->getCategoryUrl($matches[1]), 'cPath', $category_id, '');
      if( !empty($matches[2]) ) {
        $link .= '?' . substr($matches[2], 1);
      }
    } 
    //manufacturers
    else if ( preg_match("/index.php\?manufacturers=([0-9]+)(.*)/", $link, $matches) > 0 ) {
      $link = $this->makeUrl($page, $this->getManufacturersUrl($matches[1]), 'manufacturers', $matches[1], '');
      if ( !empty($matches[2]) ) {
        $link .= '?' . substr($matches[2], 1);
      }
    }
    //cPath & products
    else if ( preg_match("/products.php\?([0-9]+)&cPath=([0-9_]+)(.*)/", $link, $matches) > 0 ) {
      $link = $matches[2] . $this->_reg_anchors['cPath'] . $this->getCategoryUrl($matches[2]) . '/' . 
              $matches[1] . $this->_reg_anchors['products_id'] . $this->getProductUrl($matches[1]) . '.html';
      
      if( !empty($matches[3]) ) {
        $link .= '?' . substr($matches[3], 1);
      }
    }
    //manufacturers & products
    else if ( preg_match("/products.php\?([0-9]+)&manufacturers=([0-9]+)(.*)/", $link, $matches) > 0 ) {
      $link = $matches[2] . $this->_reg_anchors['manufacturers'] . $this->getManufacturersUrl($matches[2]) . '/' . 
              $matches[1] . $this->_reg_anchors['products_id'] . $this->getProductUrl($matches[1]) . '.html';
      
      if( !empty($matches[3]) ) {
        $link .= '?' . substr($matches[3], 1);
      }
    }
    //products
    else if ( preg_match("/products.php\?([0-9]+)(.*)/", $link, $matches) > 0 ) {
      if ( (strpos($link, 'action=compare_products_add') === false) && (strpos($link, 'action=wishlist_add') === false) ) {
        
        //Modify the code to make the product url in the new products page, specials page same as the product url in the catalog
        //To fix the bug - [#123] Two Different SEO link for one product
        $link = $this->getProductCategoryLink($matches[1]);
        
        if(isset($matches[2]) &&  !empty($matches[2])) {
          $link .= '?' . substr($matches[2], 1);
        }
      }
    }
    //products tell a friend
    else if ( preg_match("/products.php\?tell_a_friend\&([0-9]+)(.*)/", $link, $matches) > 0 ) {
      $link = $this->makeUrl($page, $this->getProductUrl($matches[1]), 'tell_a_friend', $matches[1]);
      if ( !empty($matches[2]) ) {
        $link .= '?' . substr($matches[2], 1);
      }
    //new products
    }else if (preg_match("/products.php\?new/", $link) > 0) {
      $link = 'new-products.html';
      
      $parameters = str_replace(array('new', '&'), '', $parameters);
      
      if (!empty($parameters)) {
        $link .= '?' . $parameters;
      }
    //specials  
    }else if (preg_match("/products.php\?specials/", $link) > 0) {
      $link = 'specials.html';
      
      if (!empty($parameters)) {
         $parameters = str_replace(array('specials', '&'), '', $parameters);
         
         if (!empty($parameters)) {
           $link .= '?' . $parameters;
         }
      }
    }
    //article categories  
    else if ( preg_match("/\?(.*)articles_categories_id=([0-9]+)(.*)/", $link, $matches) > 0 ) {
      $link = $this->makeUrl($page, $this->getArticleCategoryUrl($matches[2]), 'articles_categories_id', $matches[2], '');
      if ( !empty($matches[3]) ) {
        $link .= '?' . substr($matches[3], 1);
      }
    }
    //articles
    else if ( preg_match("/\?(.*)articles_id=([0-9]+)(.*)/", $link, $matches) > 0 ) {
      $link = $this->getArticleCategoryUrl($this->getArticleCategory($matches[2])) . '/' . 
              $matches[2] . $this->_reg_anchors['articles_id'] . $this->getArticleUrl($matches[2]) . '.html';
      
      if( !empty($matches[3]) ) {
        $link .= '?' . substr($matches[3], 1);
      }
    }
    //faqs
    else if ( preg_match("/faqs_id=([0-9]+)(.*)/", $link, $matches) > 0 ) {
      $link = $this->makeUrl($page, $this->getFaqUrl($matches[1]), 'faqs_id', $matches[1]);
      if ( !empty($matches[2]) ) {
        $link .= '?' . substr($matches[2], 1);
      }
    }
    //faqs
    else if ( preg_match("/info.php\?faqs(.*)/", $link, $matches) > 0 ) {
      $link = 'faqs.html';
      if ( !empty($matches[1]) ) {
        $link .= '?' . substr($matches[1], 1);
      }
    }
    //contact
    else if ( preg_match("/info.php\?contact(.*)/", $link, $matches) > 0 ) {
      $link = 'contact.html';
      if ( !empty($matches[1]) ) {
        $link .= '?' . substr($matches[1], 1);
      }
    }
    //sitemap
    else if ( preg_match("/info.php\?sitemap(.*)/", $link, $matches) > 0 ) {
      $link = 'sitemap.html';
      if ( !empty($matches[1]) ) {
        $link .= '?' . substr($matches[1], 1);
      }
    }

    return $link;
  }

  function makeUrl($page, $string, $anchor_type, $id, $extension = '.html') {
    return $id . $this->_reg_anchors[$anchor_type] . $string . $extension;
  }

  function getProductUrl($products_id) {
    $data = $this->_products_cache[$products_id];
    $data = explode('#', $data);
    return $data[1];
  }
  
  function getProductCategory($products_id) {
    $data = $this->_products_cache[$products_id];
    $data = explode('#', $data);
    return $data[0];
  }
  
  /**
   * Get full category link for the product
   * Modify the code to make the product url in the new products page, specials page same as the product url in the catalog
   * To fix the bug - [#123] Two Different SEO link for one product
   * 
   * @access private
   * @param int $products_id
   * @return string
   */
  function getProductCategoryLink($products_id) {
    global $osC_CategoryTree;
    
    $link = '';
    
    $product_category = $this->getProductCategory($products_id);
    $category_url = $this->getCategoryUrl($product_category);
    $full_category_path = $osC_CategoryTree->getFullcPath($product_category);
    
    $link .= $full_category_path . $this->_reg_anchors['cPath'] . $category_url . '/' .
             $products_id . $this->_reg_anchors['products_id'] . $this->getProductUrl($products_id) . '.html';
    
    return $link;
  }
  
  function getCategoryUrl($cPath) {
    global $osC_CategoryTree;
    
    return $osC_CategoryTree->getCategoryUrl($cPath);
  }
  
  function getProductUrlViaReviews($reviews_id) {
    return $this->getProductUrl($this->_products_reviews_cache[$reviews_id]);
  }

  function getArticleCategoryUrl($article_categories_id) {
    return $this->_article_categories_cache[$article_categories_id];
  }
  
  function getArticleUrl($article_id) {
    $data = $this->_articles_cache[$article_id];
    $data = explode('#', $data);
    return $data[1];
  }
  
  function getArticleCategory($article_id) {
    $data = $this->_articles_cache[$article_id];
    $data = explode('#', $data);
    return $data[0];
  }
  
  function getFaqUrl($faqs_id) {
    return $this->_faqs_cache[$faqs_id];
  }

  function getManufacturersUrl($manufacturers_id) {
    return $this->_manufacturers_cache[$manufacturers_id];
  }

  function _iniProductsCache() {
    global $osC_Database, $osC_Language;

    $Qproducts = $osC_Database->query('select pd.products_id, ptc.categories_id, pd.products_friendly_url from :table_products_description pd left join :table_products_to_categories ptc on pd.products_id = ptc.products_id where pd.language_id=:language_id');
    $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
    $Qproducts->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
    $Qproducts->bindInt(':language_id', $osC_Language->getID());
    $Qproducts->setCache('sefu-products-' . $osC_Language->getCode());
    $Qproducts->execute();

    while ($Qproducts->next()) {
      $this->_products_cache[$Qproducts->valueInt('products_id')] = $Qproducts->valueInt('categories_id') . '#' . $Qproducts->value('products_friendly_url');
    }
    
    $Qproducts->freeResult();
  }

  function _iniArticlesCache() {
    global $osC_Database, $osC_Language;

    $Qarticles = $osC_Database->query('select a.articles_id, a.articles_categories_id, ad.articles_url from :table_articles a left join :table_articles_description ad on a.articles_id = ad.articles_id where ad.language_id=:language_id ');
    $Qarticles->bindTable(':table_articles', TABLE_ARTICLES);
    $Qarticles->bindTable(':table_articles_description', TABLE_ARTICLES_DESCRIPTION);
    $Qarticles->bindInt(':language_id', $osC_Language->getID());
    $Qarticles->setCache('sefu-articles-' . $osC_Language->getCode());
    $Qarticles->execute();

    while ($Qarticles->next()) {
      $this->_articles_cache[$Qarticles->valueInt('articles_id')] = $Qarticles->valueInt('articles_categories_id') . '#' . $Qarticles->value('articles_url');
    }
    
    $Qarticles->freeResult();
  }

  function _iniProductsReviewsCache() {
    global $osC_Database, $osC_Language;

    $Qreviews = $osC_Database->query('select reviews_id, products_id from :table_reviews where languages_id=:language_id ');
    $Qreviews->bindTable(':table_reviews', TABLE_REVIEWS);
    $Qreviews->bindInt(':language_id', $osC_Language->getID());
    $Qreviews->setCache('sefu-products-reviews-' . $osC_Language->getCode());
    $Qreviews->execute();

    while ($Qreviews->next()) {
      $this->_products_reviews_cache[$Qreviews->valueInt('reviews_id')] = $Qreviews->value('products_id');
    }
    $Qreviews->freeResult();
  }

  function _iniArticleCategoriesCache() {
    global $osC_Database, $osC_Language;

    $Qcategories = $osC_Database->query('select articles_categories_id, articles_categories_url from :table_articles_categories_description where language_id=:language_id ');
    $Qcategories->bindTable(':table_articles_categories_description', TABLE_ARTICLES_CATEGORIES_DESCRIPTION);
    $Qcategories->bindInt(':language_id', $osC_Language->getID());
    $Qcategories->setCache('sefu-article-categories-' . $osC_Language->getCode());
    $Qcategories->execute();

    while ($Qcategories->next()) {
      $this->_article_categories_cache[$Qcategories->valueInt('articles_categories_id')] = $Qcategories->value('articles_categories_url');
    }
    $Qcategories->freeResult();
  }

  function _iniFaqsCache() {
    global $osC_Database, $osC_Language;

    $Qfaqs = $osC_Database->query('select faqs_id, faqs_url from :table_faqs_description where language_id=:language_id ');
    $Qfaqs->bindTable(':table_faqs_description', TABLE_FAQS_DESCRIPTION);
    $Qfaqs->bindInt(':language_id', $osC_Language->getID());
    $Qfaqs->setCache('sefu-faqs-' . $osC_Language->getCode());
    $Qfaqs->execute();

    while ($Qfaqs->next()) {
      $this->_faqs_cache[$Qfaqs->valueInt('faqs_id')] = $Qfaqs->value('faqs_url');
    }
    $Qfaqs->freeResult();
  }

  function _iniManufacturersCache() {
    global $osC_Database, $osC_Language;

    $Qmanufacturers = $osC_Database->query('select manufacturers_id , manufacturers_friendly_url from :table_manufacturers_info where languages_id = :languages_id');
    $Qmanufacturers->bindTable(':table_manufacturers_info', TABLE_MANUFACTURERS_INFO);
    $Qmanufacturers->bindInt(':languages_id', $osC_Language->getID());
    $Qmanufacturers->setCache('sefu-manufacturers-' . $osC_Language->getCode());
    $Qmanufacturers->execute();

    while ($Qmanufacturers->next()) {
      $this->_manufacturers_cache[$Qmanufacturers->valueInt('manufacturers_id')] = $Qmanufacturers->value('manufacturers_friendly_url');
    }
    $Qmanufacturers->freeResult();
  }
}
?>
