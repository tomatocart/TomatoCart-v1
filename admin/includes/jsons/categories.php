<?php
/*
  $Id: categories.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/categories.php');
  require('includes/classes/category_tree.php');
  require('includes/classes/image.php');
  require('includes/classes/products.php');

  class toC_Json_Categories {
  
    function listCategories() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qcategories = $osC_Database->query('select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, c.sort_order, c.categories_status, c.date_added, c.last_modified from :table_categories c, :table_categories_description cd where c.categories_id = cd.categories_id and cd.language_id = :language_id');
      $Qcategories->appendQuery('and c.parent_id = :parent_id');
      
      if ( isset($_REQUEST['categories_id']) && !empty($_REQUEST['categories_id']) ) {
        $Qcategories->bindInt(':parent_id', $_REQUEST['categories_id']);  
      } else {
        $Qcategories->bindInt(':parent_id', 0);
      }      
      
      if ( isset($_REQUEST['search']) && !empty($_REQUEST['search']) ) {
        $Qcategories->appendQuery('and cd.categories_name like :categories_name');
        $Qcategories->bindValue(':categories_name', '%' . $_REQUEST['search'] . '%');
      } 
    
      $Qcategories->appendQuery('order by c.sort_order, cd.categories_name');
      $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
      $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
      $Qcategories->bindInt(':language_id', $osC_Language->getID());
      $Qcategories->setExtBatchLimit($start, $limit);
      $Qcategories->execute();
      
      $records = array();
      $osC_CategoryTree = new osC_CategoryTree();
      while ($Qcategories->next()) {
        $records[] = array('categories_id' => $Qcategories->value('categories_id'),
                           'categories_name' => $Qcategories->value('categories_name'),
                           'status' => $Qcategories->valueInt('categories_status'),
                           'path' => $osC_CategoryTree->buildBreadcrumb($Qcategories->valueInt('categories_id'))); 
      }
        
      $response = array(EXT_JSON_READER_TOTAL => $Qcategories->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records); 
                        
      echo $toC_Json->encode($response);
    }
    
    function listRatings() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $Qratings = $osC_Database->query('select r.ratings_id, rd.ratings_text from :table_ratings r inner join :table_ratings_description rd on rd.ratings_id = r.ratings_id and rd.languages_id = :languages_id and status = 1');
      $Qratings->bindTable(':table_ratings', TABLE_RATINGS);
      $Qratings->bindTable(':table_ratings_description', TABLE_RATINGS_DESCRIPTION);
      $Qratings->bindInt(':languages_id', $osC_Language->getID());
      $Qratings->execute();
      
      $records = array();
      while ( $Qratings->next() ) {
        $records[] = array(
          'ratings_id' => $Qratings->valueInt('ratings_id'),
          'ratings_text' => $Qratings->value('ratings_text')
        );
      }
        
      $response = array(EXT_JSON_READER_ROOT => $records);
                        
      $Qratings->freeResult();                  
     
      echo $toC_Json->encode($response);
    }

    function deleteCategory() {
      global $toC_Json, $osC_Language, $osC_Image, $osC_CategoryTree;
      
      $osC_Image = new osC_Image_Admin();
      $osC_CategoryTree = new osC_CategoryTree_Admin();
      
      if ( isset($_REQUEST['categories_id']) && is_numeric($_REQUEST['categories_id']) && osC_Categories_Admin::delete($_REQUEST['categories_id']) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
         
      echo $toC_Json->encode($response);
    }
    
    function deleteCategories() {
      global $toC_Json, $osC_Language, $osC_Image, $osC_CategoryTree;
      
      $osC_Image = new osC_Image_Admin();
      $osC_CategoryTree = new osC_CategoryTree_Admin();
      
      $error = false;
      
      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if (!osC_Categories_Admin::delete($id)) {
          $error = true;
          break;
        }
      }
     
      if ($error === false) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
      
      echo $toC_Json->encode($response);
    }
      
    function moveCategories(){
      global $toC_Json, $osC_Language;
      
      $error = false;
      $batch = explode(',', $_REQUEST['categories_ids']);
     
      foreach ($batch as $id) {
        if ( !osC_Categories_Admin::move($id, $_REQUEST['parent_category_id']) ) {
          $error = true;
          break;
        }
      }
       
      if ($error === false) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
      
      echo $toC_Json->encode($response);
    }
        
    function loadCategory(){
      global $toC_Json, $osC_Language, $osC_Database, $osC_CategoryTree;
      
      $osC_CategoryTree = new osC_CategoryTree();
      
      $data = osC_Categories_Admin::getData($_REQUEST['categories_id']);
      
      $Qcategories = $osC_Database->query('select c.*, cd.* from :table_categories c left join :table_categories_description cd on c.categories_id = cd.categories_id where c.categories_id = :categories_id  ');
      $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
      $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
      $Qcategories->bindInt(':categories_id', $_REQUEST['categories_id']);
      $Qcategories->execute();
      
      while ($Qcategories->next()) {
        $data['categories_name[' . $Qcategories->ValueInt('language_id') . ']'] = $Qcategories->Value('categories_name');
        $data['categories_url[' . $Qcategories->ValueInt('language_id') . ']'] = $Qcategories->Value('categories_url');
        $data['page_title['. $Qcategories->ValueInt('language_id') . ']'] = $Qcategories->Value('categories_page_title');
        $data['meta_keywords['. $Qcategories->ValueInt('language_id') . ']'] = $Qcategories->Value('categories_meta_keywords');
        $data['meta_description[' . $Qcategories->ValueInt('language_id') . ']'] = $Qcategories->Value('categories_meta_description');
      }
      $Qcategories->freeResult();
      
      $response = array('success' => true, 'data' => $data);
      
      echo $toC_Json->encode($response);
    }
      
    function saveCategory() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $parent_id = isset($_REQUEST['parent_category_id']) ? end(explode('_', $_REQUEST['parent_category_id'])) : null;
      
      //search engine friendly urls
      $formatted_urls = array();
      $urls = $_REQUEST['categories_url'];
      if (is_array($urls) && !empty($urls)) {
        foreach($urls as $languages_id => $url) {
          $url = toc_format_friendly_url($url);
          if (empty($url)) {
            $url = toc_format_friendly_url($_REQUEST['categories_name'][$languages_id]);
          }
          
          $formatted_urls[$languages_id] = $url;
        }
      }
      
      $data = array('parent_id' => $parent_id, 
                    'sort_order' => $_REQUEST['sort_order'],
                    'image' => $_FILES['image'],  
                    'categories_status'  => $_REQUEST['categories_status'],
                    'name' => $_REQUEST['categories_name'],
                    'url' => $formatted_urls,
                    'page_title' => $_REQUEST['page_title'],
                    'meta_keywords' => $_REQUEST['meta_keywords'],
                    'meta_description' => $_REQUEST['meta_description'],
                    'flag' => (isset($_REQUEST['product_flag']))? $_REQUEST['product_flag']: 0,
                    'ratings' => $_REQUEST['ratings']);
      
      //editing the parent category
      if (isset($_REQUEST['categories_id']) && is_numeric($_REQUEST['categories_id'])) {
        $subcategories = array();
        
        $osC_CategoryTree = new osC_CategoryTree();
        $subcategories = $osC_CategoryTree->getChildren($_REQUEST['categories_id'], $subcategories);
        
        if (!osc_empty($subcategories)) {
          $data['subcategories'] = $subcategories;
        }
      }
      
      $category_id = osC_Categories_Admin::save((isset($_REQUEST['categories_id']) && is_numeric($_REQUEST['categories_id']) ? $_REQUEST['categories_id'] : null), $data); 
      if ( $category_id > 0) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'), 'categories_id' => $category_id, 'text' => $_REQUEST['categories_name'][$osC_Language->getID()]);
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
      
      header('Content-Type: text/html');
      echo $toC_Json->encode($response);
    }
      
    function listParentCategory(){
      global $toC_Json, $osC_Language;
      
      $osC_CategoryTree = new osC_CategoryTree_Admin();
      
      $records = array(array('id' => '0',
                             'text' => $osC_Language->get('top_category')));
      
      foreach ($osC_CategoryTree->getTree() as $value) {
        $records[] = array('id' => $value['id'],
                           'text' => $value['title']);
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records); 
                          
      echo $toC_Json->encode($response);
    }
    
    function loadCategoriesTree() {
      global $toC_Json, $osC_Language;
      
      $osC_CategoryTree = new osC_CategoryTree(true, true, false);
      $categories_array = array();

      $categories_array = $osC_CategoryTree->buildExtJsonTreeArray();
      
      echo $toC_Json->encode($categories_array);                     
    }
    
    function setStatus() {
      global $toC_Json, $osC_Language;
      
      if ( isset($_REQUEST['categories_id']) && osC_Categories_Admin::setStatus($_REQUEST['categories_id'], (isset($_REQUEST['flag']) ? $_REQUEST['flag'] : 1), (isset($_REQUEST['product_flag']) ? $_REQUEST['product_flag'] : 0)) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
  
      echo $toC_Json->encode($response);
    }
  }
?>
