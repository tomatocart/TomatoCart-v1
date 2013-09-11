<?php
/*
  $Id: articles.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Articles {
    
    function &getEntry($id) {
      global $osC_Database, $osC_Language;

      $Qentry = $osC_Database->query('select a.articles_categories_id, acd.articles_categories_name, a.articles_image, ad.articles_name, ad.articles_description, ad.articles_page_title as page_title, ad.articles_meta_keywords as meta_keywords, ad.articles_meta_description as meta_description from :table_articles a, :table_articles_description ad, :table_articles_categories_description acd where a.articles_id = ad.articles_id and ad.language_id = :language_id and a.articles_id = :articles_id and a.articles_categories_id = acd.articles_categories_id and ad.language_id = acd.language_id');

      $Qentry->bindTable(':table_articles', TABLE_ARTICLES);
      $Qentry->bindTable(':table_articles_description', TABLE_ARTICLES_DESCRIPTION);
      $Qentry->bindTable(':table_articles_categories_description', TABLE_ARTICLES_CATEGORIES_DESCRIPTION);
      $Qentry->bindInt(':articles_id', $id);
      $Qentry->bindInt(':language_id', $osC_Language->getID());
      $Qentry->execute();

      $data = $Qentry->toArray();
      
      return $data;
    }

    function &getListing($categories_id = null) {
      global $osC_Database, $osC_Language;

      if (is_numeric($categories_id)) {
        $Qarticles = $osC_Database->query('select a.articles_date_added, a.articles_last_modified, a.articles_image, a.articles_id, ad.articles_name, ad.articles_description from :table_articles a, :table_articles_description ad where a.articles_id = ad.articles_id and ad.language_id = :language_id and a.articles_status = 1 and articles_categories_id = :articles_categories_id order by a.articles_id ');
        $Qarticles->bindInt(':articles_categories_id', $categories_id);
      } else {
        $Qarticles = $osC_Database->query('select a.articles_date_added, a.articles_last_modified, a.articles_image, a.articles_id, ad.articles_name, ad.articles_description from :table_articles a, :table_articles_description ad where a.articles_id = ad.articles_id and ad.language_id = :language_id and a.articles_status = 1 order by a.articles_id ');
      }
      $Qarticles->bindTable(':table_articles', TABLE_ARTICLES);
      $Qarticles->bindTable(':table_articles_description', TABLE_ARTICLES_DESCRIPTION);
      $Qarticles->bindInt(':language_id', $osC_Language->getID());
      $Qarticles->setBatchLimit((isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1), MAX_DISPLAY_SEARCH_RESULTS);
      $Qarticles->execute();

      return $Qarticles;
    }
    
    function getArticleCategoriesEntry($categories_id) {
      global $osC_Database, $osC_Language;

      $Qcategories = $osC_Database->query('select articles_categories_name, articles_categories_page_title, articles_categories_meta_keywords, articles_categories_meta_description from :table_articles_categories_description where articles_categories_id = :articles_categories_id and language_id = :language_id');
      $Qcategories->bindTable(':table_articles_categories_description', TABLE_ARTICLES_CATEGORIES_DESCRIPTION);
      $Qcategories->bindInt(':articles_categories_id', $categories_id);
      $Qcategories->bindInt(':language_id', $osC_Language->getID());
      $Qcategories->execute();

      if($Qcategories->numberOfRows() > 0){
        $data = array('articles_categories_name' => $Qcategories->value('articles_categories_name'),
                      'page_title' => $Qcategories->value('articles_categories_page_title'),
                      'meta_keywords' => $Qcategories->value('articles_categories_meta_keywords'),
                      'meta_description' => $Qcategories->value('articles_categories_meta_description'));
      }
      
      return $data;
    }
    
    function getCategoriesListing() {
    	global $osC_Database, $osC_Language;
    	
    	//get the articles categories
    	$Qcategories = $osC_Database->query('select ac.articles_categories_id, articles_categories_name from :table_articles_categories ac inner join :table_articles_categories_description acd on ac.articles_categories_id = acd.articles_categories_id where ac.articles_categories_status = 1 and acd.language_id = :language_id and ac.articles_categories_id != 1 order by articles_categories_order');
    	$Qcategories->bindTable(':table_articles_categories', TABLE_ARTICLES_CATEGORIES);
    	$Qcategories->bindTable(':table_articles_categories_description', TABLE_ARTICLES_CATEGORIES_DESCRIPTION);
    	$Qcategories->bindInt(':language_id', $osC_Language->getID());
    	$Qcategories->execute();
    	
    	//check the number of categories
    	if ($Qcategories->numberOfRows() < 1) {
    		return null;
    	}
    	
    	$categories = array();
    	while ($Qcategories->next()) {
    		//get the articles in currency article category
    		$Qarticles = $osC_Database->query('select a.articles_id, articles_name from :table_articles a inner join :table_articles_description ad on a.articles_id = ad.articles_id where a.articles_status = 1 and a.articles_categories_id = :articles_categories_id and ad.language_id = :language_id order by a.articles_order');
    		$Qarticles->bindTable(':table_articles', TABLE_ARTICLES);
    		$Qarticles->bindTable(':table_articles_description', TABLE_ARTICLES_DESCRIPTION);
    		$Qarticles->bindInt(':articles_categories_id', $Qcategories->valueInt('articles_categories_id'));
    		$Qarticles->bindInt(':language_id', $osC_Language->getID());
    		$Qarticles->execute();
    		
    		$articles = array();
    		if ($Qarticles->numberOfRows() > 0) {
    			while ($Qarticles->next()) {
    				$articles[] = array('articles_id' => $Qarticles->valueInt('articles_id'), 'articles_name' => $Qarticles->value('articles_name'));
    			}
    		}
    		
    		$categories[] = array('articles_categories_id' => $Qcategories->valueInt('articles_categories_id'), 'articles_categories_name' => $Qcategories->value('articles_categories_name'), 'articles' => $articles);
    	}
    	
    	$Qcategories->freeResult();
    	$Qarticles->freeResult();
    	
    	return $categories;
    }
  }
?>
