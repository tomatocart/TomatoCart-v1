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

  require('includes/classes/articles_categories.php');
  
  class toC_Json_Articles_categories {
  
    function listArticlesCategories() {
      global $osC_Database, $toC_Json, $osC_Language;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
  
      $Qcategories = $osC_Database->query('select c.articles_categories_id, c.articles_categories_status, cd.articles_categories_name, c.articles_categories_order from :table_articles_categories c, :table_articles_categories_description cd where c.articles_categories_id = cd.articles_categories_id and c.articles_categories_id > 1 and cd.language_id = :language_id');
      $Qcategories->appendQuery('order by c.articles_categories_order, cd.articles_categories_name');
      $Qcategories->bindTable(':table_articles_categories', TABLE_ARTICLES_CATEGORIES);
      $Qcategories->bindTable(':table_articles_categories_description', TABLE_ARTICLES_CATEGORIES_DESCRIPTION);
      $Qcategories->bindInt(':language_id', $osC_Language->getID());
      $Qcategories->setExtBatchLimit($start, $limit);
      $Qcategories->execute();
      
      $records = array();
      while ($Qcategories->next()) {
        $records[] = array('articles_categories_id' => $Qcategories->ValueInt('articles_categories_id'),
                           'articles_categories_status' => $Qcategories->ValueInt('articles_categories_status'),
                           'articles_categories_name' => $Qcategories->Value('articles_categories_name'),
                           'articles_categories_order' => $Qcategories->Value('articles_categories_order'));
      }
  
      $response = array(EXT_JSON_READER_TOTAL => $Qcategories->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
  
      echo $toC_Json->encode($response);
    }
  
    function loadArticlesCategories() {
      global $osC_Database, $toC_Json;
      
      $data = toC_Articles_Categories_Admin::getData($_REQUEST['articles_categories_id']);
      
      $Qcd = $osC_Database->query('select language_id, articles_categories_name, articles_categories_url, articles_categories_page_title, articles_categories_meta_keywords, articles_categories_meta_description from :table_articles_categories_description where articles_categories_id = :articles_categories_id');
      $Qcd->bindTable(':table_articles_categories_description', TABLE_ARTICLES_CATEGORIES_DESCRIPTION);
      $Qcd->bindInt(':articles_categories_id', $_REQUEST['articles_categories_id']);
      $Qcd->execute();
      
      while ($Qcd->next()) {
        $data['articles_categories_name[' . $Qcd->ValueInt('language_id') . ']'] = $Qcd->Value('articles_categories_name');
        $data['articles_categories_url[' . $Qcd->ValueInt('language_id') . ']'] = $Qcd->Value('articles_categories_url');
        $data['page_title[' . $Qcd->ValueInt('language_id') . ']'] = $Qcd->Value('articles_categories_page_title');
        $data['meta_keywords[' . $Qcd->ValueInt('language_id') . ']'] = $Qcd->Value('articles_categories_meta_keywords');
        $data['meta_description[' . $Qcd->ValueInt('language_id') . ']'] = $Qcd->Value('articles_categories_meta_description');
      }
      
      $response = array('success' => true, 'data' => $data);
      
      echo $toC_Json->encode($response);
    }
  
    function saveArticlesCategory() {
      global $toC_Json, $osC_Language;
      
      //search engine friendly urls
      $formatted_urls = array();
      $urls = $_REQUEST['articles_categories_url'];
      if (is_array($urls) && !empty($urls)) {
        foreach($urls as $languages_id => $url) {
          $url = toc_format_friendly_url($url);
          if (empty($url)) {
            $url = toc_format_friendly_url($_REQUEST['articles_categories_name'][$languages_id]);
          }
          
          $formatted_urls[$languages_id] = $url;
        }
      }
      
      $data = array('name' => $_REQUEST['articles_categories_name'],
                    'url' => $formatted_urls,
                    'status' => $_REQUEST['articles_categories_status'],
                    'articles_order' => $_REQUEST['articles_categories_order'],
                    'page_title' => $_REQUEST['page_title'],
                    'meta_keywords' => $_REQUEST['meta_keywords'],
                    'meta_description' => $_REQUEST['meta_description']);

      if ( toC_Articles_Categories_Admin::save((isset($_REQUEST['articles_categories_id']) && is_numeric($_REQUEST['articles_categories_id'] ) ? $_REQUEST['articles_categories_id'] : null), $data) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
  
      echo $toC_Json->encode($response);
    }
  
    function deleteArticleCategory() {
      global $toC_Json, $osC_Language;
      
      $error = false;
      
      $count = toC_Articles_Categories_Admin::getNumberOfArticles($_REQUEST['articles_categories_id']);
      if ($count > 0) {
        $error = true;
        $feedback = sprintf($osC_Language->get('delete_warning_category_in_use_articles'), $count);
      }
      
      if ($error === false) {
        if ( !toC_Articles_Categories_Admin::delete($_REQUEST['articles_categories_id']) ) {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        } else {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . $feedback);
      }
     
      echo $toC_Json->encode($response);
    }
  
    function deleteArticlesCategories() {
      global $toC_Json, $osC_Language;
      
      $error = false;
      $feedback = array();
      $batch = explode(',', $_REQUEST['batch']);
      
      $check_categories_array = array();
      foreach ($batch as $categories_id) {
        $count = toC_Articles_Categories_Admin::getNumberOfArticles($categories_id);
        
        if ($count > 0) {
          $data = toC_Articles_Categories_Admin::getData($categories_id);
          $check_categories_array[] = $data['articles_categories_name'];
        }
      }

      if ( !empty($check_categories_array) ) {
        $error = true;
        $feedback[] = $osC_Language->get('batch_delete_error_articles_categories_in_use') . '<br />' . implode(', ', $check_categories_array);
      }

      if ($error === false) {
        foreach ($batch as $categories_id) {
          if ( !toC_Articles_Categories_Admin::delete($categories_id) ) {
            $error = true;
            break;
          }   
        }
        
        if ($error === false) {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
      }
  
      echo $toC_Json->encode($response);
    }
  
    function setStatus() {
      global $toC_Json, $osC_Language;
  
      if ( isset($_REQUEST['articles_categories_id']) && toC_Articles_Categories_Admin::setStatus($_REQUEST['articles_categories_id'], (isset($_REQUEST['flag']) ? $_REQUEST['flag'] : null)) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
  
      echo $toC_Json->encode($response);
    }
  }
?>