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

  class toC_Articles_Admin {

    function getData($id) {
      global $osC_Database, $osC_Language;

      $Qarticles = $osC_Database->query('select a.*, ad.* from :table_articles a, :table_articles_description ad where a.articles_id = :articles_id and a.articles_id =ad.articles_id and ad.language_id = :language_id');

      $Qarticles->bindTable(':table_articles', TABLE_ARTICLES);
      $Qarticles->bindTable(':table_articles_description', TABLE_ARTICLES_DESCRIPTION);
      $Qarticles->bindInt(':articles_id', $id);
      $Qarticles->bindInt(':language_id', $osC_Language->getID());
      $Qarticles->execute();

      $data = $Qarticles->toArray();

      $Qarticles->freeResult();

      return $data;
    }

    function setStatus($id, $flag){
      global $osC_Database;
      $Qstatus = $osC_Database->query('update :table_articles set articles_status= :articles_status, articles_last_modified = now() where articles_id = :articles_id');
      $Qstatus->bindInt(':articles_status', $flag);
      $Qstatus->bindInt(':articles_id', $id);
      $Qstatus->bindTable(':table_articles', TABLE_ARTICLES);
      $Qstatus->setLogging($_SESSION['module'], $id);
      $Qstatus->execute();
      return true;
    }

    function save($id = null, $data) {
      global $osC_Database, $osC_Language, $osC_Image;

      $error = false;

      $osC_Database->startTransaction();

      if ( is_numeric($id) ) {
        $Qarticle = $osC_Database->query('update :table_articles set articles_status = :articles_status, articles_order = :articles_order,articles_categories_id = :articles_categories_id,articles_last_modified = now() where articles_id = :articles_id');
        $Qarticle->bindInt(':articles_id', $id);
      } else {
        $Qarticle = $osC_Database->query('insert into :table_articles (articles_status,articles_order,articles_categories_id,articles_date_added) values (:articles_status,:articles_order,:articles_categories_id ,:articles_date_added)');
        $Qarticle->bindRaw(':articles_date_added', 'now()');
      }

      $Qarticle->bindTable(':table_articles', TABLE_ARTICLES);
      $Qarticle->bindValue(':articles_status', $data['articles_status']);
      $Qarticle->bindValue(':articles_order', $data['articles_order']);
      $Qarticle->bindValue(':articles_categories_id', $data['articles_categories']);
      $Qarticle->setLogging($_SESSION['module'], $id);
      $Qarticle->execute();

      if ( $osC_Database->isError() ) {
        $error = true;
      } else {
        if ( is_numeric($id) ) {
          $articles_id = $id;
        } else {
          $articles_id = $osC_Database->nextID();
        }
      }

  //articles images
      if($data['delimage'] == 1){
        $osC_Image->deleteArticlesImage($articles_id);

        $Qdelete = $osC_Database->query('update :table_articles set articles_image = NULL where articles_id = :articles_id');
        $Qdelete->bindTable(':table_articles', TABLE_ARTICLES);
        $Qdelete->bindInt(':articles_id', $id);
        $Qdelete->setLogging($_SESSION['module'], $id);
        $Qdelete->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
        }
      }

      if ($error === false) {
        $articles_image = new upload('articles_image', realpath('../' . DIR_WS_IMAGES . '/articles/originals'));
        if ( $articles_image->exists() && $articles_image->parse() && $articles_image->save() ) {
          $Qarticle = $osC_Database->query('update :table_articles set articles_image = :articles_image where articles_id = :articles_id');
          $Qarticle->bindTable(':table_articles', TABLE_ARTICLES);
          $Qarticle->bindValue(':articles_image', $articles_image->filename);
          $Qarticle->bindInt(':articles_id', $articles_id);
          $Qarticle->setLogging($_SESSION['module'], $articles_id);
          $Qarticle->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }else{
            foreach ($osC_Image->getGroups() as $group) {
              if ($group['id'] != '1') {
                $osC_Image->resize($articles_image->filename, $group['id'], 'articles');
              }
            }
          }

        }
      }

      //Process Languages
      //
      if ( $error === false ) {
        foreach ($osC_Language->getAll() as $l) {
          if ( is_numeric($id) ) {
            $Qad = $osC_Database->query('update :table_articles_description set articles_name = :articles_name, articles_url = :articles_url, articles_description = :articles_description, articles_page_title = :articles_page_title, articles_meta_keywords = :articles_meta_keywords, articles_meta_description = :articles_meta_description where articles_id = :articles_id and language_id = :language_id');
          } else {
            $Qad = $osC_Database->query('insert into :table_articles_description (articles_id, language_id, articles_name, articles_url, articles_description, articles_page_title, articles_meta_keywords, articles_meta_description) values (:articles_id, :language_id, :articles_name, :articles_url, :articles_description, :articles_page_title, :articles_meta_keywords, :articles_meta_description)');
          }

          $Qad->bindTable(':table_articles_description', TABLE_ARTICLES_DESCRIPTION);
          $Qad->bindInt(':articles_id', $articles_id);
          $Qad->bindInt(':language_id', $l['id']);
          $Qad->bindValue(':articles_name', $data['articles_name'][$l['id']]);
          $Qad->bindValue(':articles_url', ($data['articles_url'][$l['id']] == '') ? $data['articles_name'][$l['id']] : $data['articles_url'][$l['id']]);
          $Qad->bindValue(':articles_description', $data['articles_description'][$l['id']]);
          $Qad->bindValue(':articles_page_title', $data['page_title'][$l['id']]);
          $Qad->bindValue(':articles_meta_keywords', $data['meta_keywords'][$l['id']]);
          $Qad->bindValue(':articles_meta_description', $data['meta_description'][$l['id']]);
          $Qad->setLogging($_SESSION['module'], $articles_id);
          $Qad->execute();

          if ( $osC_Database->isError() ) {
            $error = true;
            break;
          }
        }
      }

      if ( $error === false ) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('sefu-articles');
        return true;
      }
      $osC_Database->rollbackTransaction();

      return false;
    }


    function delete($id) {
      global $osC_Database, $osC_Image;
      $error = false;

      $osC_Database->startTransaction();
      
      $osC_Image->deleteArticlesImage($id);

      $Qad = $osC_Database->query('delete from :table_articles_description where articles_id = :articles_id');
      $Qad->bindTable(':table_articles_description', TABLE_ARTICLES_DESCRIPTION);
      $Qad->bindInt(':articles_id', $id);
      $Qad->setLogging($_SESSION['module'], $id);
      $Qad->execute();

      if ( $osC_Database->isError() ) {
        $error = true;
      }

      if ( $error === false ) {
        $Qarticles = $osC_Database->query('delete from :table_articles where articles_id = :articles_id');
        $Qarticles->bindTable(':table_articles', TABLE_ARTICLES);
        $Qarticles->bindInt(':articles_id', $id);
        $Qarticles->setLogging($_SESSION['module'], $id);
        $Qarticles->execute();

        if ( $osC_Database->isError() ) {
          $error = true;
        }

        if ( $error === false ) {
          $osC_Database->commitTransaction();

          osC_Cache::clear('sefu-articles');
          return true;
        }
      }
      $osC_Database->rollbackTransaction();
      return false;
    }
  }
?>
