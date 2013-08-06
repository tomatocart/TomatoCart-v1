<?php
/*
  $Id: articles_categories.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Articles_Categories_Admin {
    function getData($id, $language_id = null) {
      global $osC_Database, $osC_Language;

      if ( empty($language_id) ) {
        $language_id = $osC_Language->getID();
      }

      $Qcategories = $osC_Database->query('select c.*, cd.* from :table_articles_categories c, :table_articles_categories_description cd where c.articles_categories_id = :articles_categories_id and c.articles_categories_id = cd.articles_categories_id and cd.language_id = :language_id');
      $Qcategories->bindTable(':table_articles_categories', TABLE_ARTICLES_CATEGORIES);
      $Qcategories->bindTable(':table_articles_categories_description', TABLE_ARTICLES_CATEGORIES_DESCRIPTION);
      $Qcategories->bindInt(':articles_categories_id', $id);
      $Qcategories->bindInt(':language_id', $language_id);
      $Qcategories->execute();

      $data = $Qcategories->toArray();
      $Qcategories->freeResult();

      return $data;
    }

    function getNumberOfArticles($id) {
      global $osC_Database;
      $Qnoa = $osC_Database->query('select count(articles_id) as num_of_articles from :table_articles where articles_categories_id = :articles_categories_id');
      $Qnoa->bindInt(':articles_categories_id', $id);
      $Qnoa->bindTable(':table_articles', TABLE_ARTICLES);
      $Qnoa->execute();

      $data = $Qnoa->toArray();
      $Qnoa->freeResult();

      return $data['num_of_articles'];
    }

    function setStatus($id, $flag) {
      global $osC_Database;
  
      $Qstatus = $osC_Database->query('update :table_articles_categories set articles_categories_status= :articles_categories_status where articles_categories_id = :articles_categories_id');
      $Qstatus->bindInt(':articles_categories_status', $flag);
      $Qstatus->bindInt(':articles_categories_id', $id);
      $Qstatus->bindTable(':table_articles_categories', TABLE_ARTICLES_CATEGORIES);
      $Qstatus->execute();
      
      if (!$osC_Database->isError()) {
        osC_Cache::clear('box-article-categories');
        osC_Cache::clear('sefu-article-categories'); 

        return true;
      }
      
      return false;
    }

    function save($id = null, $data) {
      global $osC_Database, $osC_Language;

      $category_id = '';
      $error = false;

      $osC_Database->startTransaction();

      if ( is_numeric($id) ) {
        //update category
        $Qcategories = $osC_Database->query('update :table_articles_categories set articles_categories_order = :articles_order, articles_categories_status= :articles_categories_status where articles_categories_id = :articles_categories_id');
        $Qcategories->bindInt(':articles_categories_id', $id);
      } else {
        //insert a new category
        $Qcategories = $osC_Database->query('insert into :table_articles_categories (articles_categories_status, articles_categories_order) values (:articles_categories_status, :articles_order)');
      }

      $Qcategories->bindTable(':table_articles_categories', TABLE_ARTICLES_CATEGORIES);
      $Qcategories->bindInt(':articles_order', $data['articles_order']);
      $Qcategories->bindInt(':articles_categories_status', $data['status']);
      $Qcategories->setLogging($_SESSION['module'], $id);
      $Qcategories->execute();

      //update languages
      if ( !$osC_Database->isError() ) {
        $articles_category_id = (is_numeric($id)) ? $id : $osC_Database->nextID();

        foreach ($osC_Language->getAll() as $l) {
          if ( is_numeric($id) ) {
            $Qacd = $osC_Database->query('update :table_articles_categories_description set articles_categories_name = :articles_categories_name, articles_categories_url = :articles_categories_url, articles_categories_page_title = :articles_categories_page_title, articles_categories_meta_keywords = :articles_categories_meta_keywords, articles_categories_meta_description = :articles_categories_meta_description where articles_categories_id = :articles_categories_id and language_id = :language_id');
          } else {
            $Qacd = $osC_Database->query('insert into :table_articles_categories_description (articles_categories_id, language_id, articles_categories_name, articles_categories_url, articles_categories_page_title, articles_categories_meta_keywords, articles_categories_meta_description) values (:articles_categories_id, :language_id, :articles_categories_name, :articles_categories_url, :articles_categories_page_title, :articles_categories_meta_keywords, :articles_categories_meta_description)');
          }

          $Qacd->bindTable(':table_articles_categories_description', TABLE_ARTICLES_CATEGORIES_DESCRIPTION);
          $Qacd->bindInt(':articles_categories_id', $articles_category_id);
          $Qacd->bindInt(':language_id', $l['id']);
          $Qacd->bindValue(':articles_categories_name', $data['name'][$l['id']]);
          $Qacd->bindValue(':articles_categories_url', ($data['url'][$l['id']] == '') ? $data['name'][$l['id']] : $data['url'][$l['id']]);
          $Qacd->bindValue(':articles_categories_page_title', $data['page_title'][$l['id']]);
          $Qacd->bindValue(':articles_categories_meta_keywords', $data['meta_keywords'][$l['id']]);
          $Qacd->bindValue(':articles_categories_meta_description', $data['meta_description'][$l['id']]);
          $Qacd->setLogging($_SESSION['module'], $articles_category_id);
          $Qacd->execute();

          if ( $osC_Database->isError() ) {
            $error = true;
            break;
          }
        }
      }

      if ( $error === false ) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('box-article-categories');
        osC_Cache::clear('sefu-article-categories');
        return true;
      }

      $osC_Database->rollbackTransaction();
      return false;
    }

    function delete($id) {
      global $osC_Database;
      $error = false;

      if ( is_numeric($id) ) {
        $osC_Database->startTransaction();

        $Qcategories = $osC_Database->query('delete from :table_articles_categories where articles_categories_id = :articles_categories_id');
        $Qcategories->bindTable(':table_articles_categories', TABLE_ARTICLES_CATEGORIES);
        $Qcategories->bindInt(':articles_categories_id',$id);
        $Qcategories->setLogging($_SESSION['module'], $id);
        $Qcategories->execute();
        
        if ( !$osC_Database->isError() ) {
          $Qacd = $osC_Database->query('delete from :table_articles_categories_description where articles_categories_id = :articles_categories_id');
          $Qacd->bindTable(':table_articles_categories_description', TABLE_ARTICLES_CATEGORIES_DESCRIPTION);
          $Qacd->bindInt(':articles_categories_id', $id);
          $Qacd->setLogging($_SESSION['module'], $id);
          $Qacd->execute();

          if ( $osC_Database->isError() ){
            $error = true;
            break;
          }
        }

        if ( $error === false ) {
          $osC_Database->commitTransaction();

          osC_Cache::clear('box-article-categories');
          osC_Cache::clear('sefu-article-categories');
          return true;
        }

        $osC_Database->rollbackTransaction();
        return false;
      }
      return false;
    }

    function getArticlesCategories(){
      global $osC_Database, $osC_Language;
      
      $Qac = $osC_Database->query('select * from :table_articles_categories c, :table_articles_categories_description cd where c.articles_categories_id = cd.articles_categories_id and language_id = :language_id');
      $Qac->bindTable(':table_articles_categories', TABLE_ARTICLES_CATEGORIES);
      $Qac->bindTable(':table_articles_categories_description', TABLE_ARTICLES_CATEGORIES_DESCRIPTION);
      $Qac->bindInt(':language_id', $osC_Language->getID());
      $Qac->execute();

      $data = array();
      while($Qac->next()){
        $data[] = $Qac->toArray();
      }

      $Qac->freeResult();

      return $data;
    }
  }
?>
