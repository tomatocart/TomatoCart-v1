<?php
/*
  $Id: categories.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Categories_Admin {
    function getData($id, $language_id = null) {
      global $osC_Database, $osC_Language, $osC_CategoryTree;

      if ( empty($language_id) ) {
        $language_id = $osC_Language->getID();
      }

      $Qcategories = $osC_Database->query('select c.*, cd.* from :table_categories c left join :table_categories_description cd on c.categories_id = cd.categories_id where c.categories_id = :categories_id and cd.language_id = :language_id ');
      $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
      $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
      $Qcategories->bindInt(':categories_id', $id);
      $Qcategories->bindInt(':language_id', $language_id);
      $Qcategories->execute();

      $data = $Qcategories->toArray();

      $data['childs_count'] = sizeof($osC_CategoryTree->getChildren($Qcategories->valueInt('categories_id'), $dummy = array()));
      $data['products_count'] = $osC_CategoryTree->getNumberOfProducts($Qcategories->valueInt('categories_id'));
      
      $cPath = explode('_', $osC_CategoryTree->getFullcPath($Qcategories->valueInt('categories_id')));
      array_pop($cPath);
      $data['parent_category_id'] = implode('_',$cPath);
      
      $Qcategories->freeResult();
      
      $Qcategories_ratings = $osC_Database->query('select ratings_id from toc_categories_ratings where categories_id = :categories_id');
      $Qcategories_ratings->bindTable(':toc_categories_ratings', TABLE_CATEGORIES_RATINGS);
      $Qcategories_ratings->bindInt(':categories_id', $id);
      $Qcategories_ratings->execute();
      
      $ratings = array();
      while ($Qcategories_ratings->next()) {
        $ratings[] = $Qcategories_ratings->ValueInt('ratings_id');
      }
      $data['ratings'] = $ratings;
      
      $Qcategories_ratings->freeResult();

      return $data;
    }

    function save($id = null, $data) {
      global $osC_Database, $osC_Language;
      
      $category_id = '';
      $error = false;

      $osC_Database->startTransaction();

      if ( is_numeric($id) ) {
        //editing the parent category
        if (isset($data['subcategories'])) {
          $data['subcategories'][] = $id;
          
          $Qcat = $osC_Database->query('update :table_categories set categories_status = :categories_status, sort_order = :sort_order, last_modified = now() where categories_id in (:categories_ids)');
          $Qcat->bindRaw(':categories_ids', implode(',', $data['subcategories']));
        }else {
          $Qcat = $osC_Database->query('update :table_categories set categories_status = :categories_status, sort_order = :sort_order, last_modified = now() where categories_id = :categories_id');
          $Qcat->bindInt(':categories_id', $id);
        }
      } else {
        $Qcat = $osC_Database->query('insert into :table_categories (parent_id, categories_status, sort_order, date_added) values (:parent_id, :categories_status, :sort_order, now())');
        $Qcat->bindInt(':parent_id', $data['parent_id']);
      }

      $Qcat->bindTable(':table_categories', TABLE_CATEGORIES);
      $Qcat->bindInt(':sort_order', $data['sort_order']);
      $Qcat->bindInt(':categories_status', $data['categories_status']);
      $Qcat->setLogging($_SESSION['module'], $id);
      $Qcat->execute();
      
      if ( !$osC_Database->isError() ) {
        $category_id = (is_numeric($id)) ? $id : $osC_Database->nextID();
        
        if(is_numeric($id)) {
          if($data['categories_status']){
            //editing the parent category
            if (isset($data['subcategories'])) {
              $data['subcategories'][] = $id;
              
              $Qpstatus = $osC_Database->query('update :table_products set products_status = 1 where products_id in (select products_id from :table_products_to_categories where categories_id in (:categories_ids))');
              $Qpstatus->bindRaw(':categories_ids', implode(',', $data['subcategories']));
            }else {
              $Qpstatus = $osC_Database->query('update :table_products set products_status = 1 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
              $Qpstatus->bindInt(":categories_id", $id);
            }
            
            $Qpstatus->bindTable(':table_products', TABLE_PRODUCTS);
            $Qpstatus->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
            $Qpstatus->execute(); 
          }else{
            if($data['flag']) {
              //editing the parent category
              if (isset($data['subcategories'])) {
                $data['subcategories'][] = $id;
                
                $Qpstatus = $osC_Database->query('update :table_products set products_status = 0 where products_id in (select products_id from :table_products_to_categories where categories_id in (:categories_ids))');
                $Qpstatus->bindRaw(':categories_ids', implode(',', $data['subcategories']));
              }else {
                $Qpstatus = $osC_Database->query('update :table_products set products_status = 0 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
                $Qpstatus->bindInt(":categories_id", $id);
              }
            
              $Qpstatus->bindTable(':table_products', TABLE_PRODUCTS);
              $Qpstatus->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
              $Qpstatus->execute();
            }          
          }
        }
        
        if($osC_Database->isError()){
          $error = true;
        }
        
        foreach ($osC_Language->getAll() as $l) {
          if ( is_numeric($id) ) {
            $Qcd = $osC_Database->query('update :table_categories_description set categories_name = :categories_name, categories_url = :categories_url, categories_page_title = :categories_page_title, categories_meta_keywords = :categories_meta_keywords, categories_meta_description = :categories_meta_description where categories_id = :categories_id and language_id = :language_id');
          } else {
            $Qcd = $osC_Database->query('insert into :table_categories_description (categories_id, language_id, categories_name, categories_url, categories_page_title, categories_meta_keywords, categories_meta_description) values (:categories_id, :language_id, :categories_name, :categories_url, :categories_page_title, :categories_meta_keywords, :categories_meta_description)');
          }

          $Qcd->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
          $Qcd->bindInt(':categories_id', $category_id);
          $Qcd->bindInt(':language_id', $l['id']);
          $Qcd->bindValue(':categories_name', $data['name'][$l['id']]);
          $Qcd->bindValue(':categories_url', $data['url'][$l['id']]);
          $Qcd->bindValue(':categories_page_title', $data['page_title'][$l['id']]);
          $Qcd->bindValue(':categories_meta_keywords', $data['meta_keywords'][$l['id']]);
          $Qcd->bindValue(':categories_meta_description', $data['meta_description'][$l['id']]);
          $Qcd->setLogging($_SESSION['module'], $category_id);
          $Qcd->execute();

          if ( $osC_Database->isError() ) {
            $error = true;
            break;
          }
        }
        
        $Qdelete = $osC_Database->query('delete from :toc_categories_ratings where categories_id = :categories_id');
        $Qdelete->bindTable(':toc_categories_ratings', TABLE_CATEGORIES_RATINGS);
        $Qdelete->bindInt(':categories_id', $category_id);
        $Qdelete->execute();
          
        if ( !empty($data['ratings']) ) {
          $ratings = explode(',', $data['ratings']);
          
          foreach($ratings as $ratings_id){
            $Qinsert = $osC_Database->query('insert into :toc_categories_ratings (categories_id, ratings_id) values (:categories_id, :ratings_id)');
            $Qinsert->bindTable(':toc_categories_ratings', TABLE_CATEGORIES_RATINGS);
            $Qinsert->bindInt(':categories_id', $category_id);
            $Qinsert->bindInt(':ratings_id', $ratings_id);
            $Qinsert->execute();
            
            if ( $osC_Database->isError() ) {
            	$error = true;
            	break;
            }
          }
        }

        if ( $error === false ) {
          $categories_image = new upload($data['image'], realpath('../' . DIR_WS_IMAGES . 'categories'));
          
          if ( $categories_image->exists() && $categories_image->parse() && $categories_image->save() ) {

            $Qimage = $osC_Database->query('select categories_image from :table_categories where categories_id = :categories_id');
            $Qimage->bindTable(':table_categories', TABLE_CATEGORIES);
            $Qimage->bindInt(':categories_id', $category_id);
            $Qimage->execute();

            $old_image = $Qimage->value('categories_image');
          
            if (!empty($old_image)) {
              $Qcheck = $osC_Database->query('select count(*) as image_count from :table_categories where categories_image = :categories_image');
              $Qcheck->bindTable(':table_categories', TABLE_CATEGORIES);
              $Qcheck->bindValue(':categories_image', $old_image);
              $Qcheck->execute();
              
              if ($Qcheck->valueInt('image_count') == 1) {
                $path = realpath('../' . DIR_WS_IMAGES . 'categories') . '/' . $old_image;
                unlink($path);
              }
            }

            $Qcf = $osC_Database->query('update :table_categories set categories_image = :categories_image where categories_id = :categories_id');
            $Qcf->bindTable(':table_categories', TABLE_CATEGORIES);
            $Qcf->bindValue(':categories_image', $categories_image->filename);
            $Qcf->bindInt(':categories_id', $category_id);
            $Qcf->setLogging($_SESSION['module'], $category_id);
            $Qcf->execute();

            if ( $osC_Database->isError() ) {
              $error = true;
            }
          }
        }
      }

      if ( $error === false ) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('categories');
        osC_Cache::clear('category_tree');
        osC_Cache::clear('also_purchased');

        return $category_id;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }

    function delete($id) {
      global $osC_Database, $osC_CategoryTree;
      
      $error = false;
    
      if ( is_numeric($id) ) {
        $osC_CategoryTree->setBreadcrumbUsage(false);

        $categories = array_merge(array(array('id' => $id, 'text' => '')), $osC_CategoryTree->getTree($id));
        $products = array();
        $products_delete = array();

        foreach ($categories as $c_entry) {
          $Qproducts = $osC_Database->query('select products_id from :table_products_to_categories where categories_id = :categories_id');
          $Qproducts->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
          $Qproducts->bindInt(':categories_id', $c_entry['id']);
          $Qproducts->execute();

          while ($Qproducts->next()) {
            $products[$Qproducts->valueInt('products_id')]['categories'][] = $c_entry['id'];
          }
        }

        foreach ($products as $key => $value) {
          $Qcheck = $osC_Database->query('select count(*) as total from :table_products_to_categories where products_id = :products_id and categories_id not in :categories_id');
          $Qcheck->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
          $Qcheck->bindInt(':products_id', $key);
          $Qcheck->bindRaw(':categories_id', '("' . implode('", "', $value['categories']) . '")');
          $Qcheck->execute();

          if ($Qcheck->valueInt('total') < 1) {
            $products_delete[$key] = $key;
          }
        }

        osc_set_time_limit(0);

        foreach ($categories as $c_entry) {
          $osC_Database->startTransaction();

          $Qimage = $osC_Database->query('select categories_image from :table_categories where categories_id = :categories_id');
          $Qimage->bindTable(':table_categories', TABLE_CATEGORIES);
          $Qimage->bindInt(':categories_id', $c_entry['id']);
          $Qimage->execute();
          
          $image = $Qimage->value('categories_image');
                  
          if (!empty($image)) {
            $Qcheck = $osC_Database->query('select count(*) as image_count from :table_categories where categories_image = :categories_image');
            $Qcheck->bindTable(':table_categories', TABLE_CATEGORIES);
            $Qcheck->bindValue(':categories_image', $image);
            $Qcheck->execute();
            
            if ($Qcheck->valueInt('image_count') == 1) {
              $path = realpath('../' . DIR_WS_IMAGES . 'categories') . '\\' . $image;
              if( file_exists($path) ) {
                unlink($path);
              }
            }
          }

          $Qc = $osC_Database->query('delete from :table_categories where categories_id = :categories_id');
          $Qc->bindTable(':table_categories', TABLE_CATEGORIES);
          $Qc->bindInt(':categories_id', $c_entry['id']);
          $Qc->setLogging($_SESSION['module'], $id);
          $Qc->execute();
          
          if ($osC_Database->isError()) {
            $error = true;
          }
          
          if ($error === false) {
            $Qratings = $osC_Database->query('delete from :table_categories_ratings where categories_id = :categories_id');
  	        $Qratings->bindTable(':table_categories_ratings', TABLE_CATEGORIES_RATINGS);
	          $Qratings->bindInt(':categories_id', $id);
            $Qratings->setLogging($_SESSION['module'], $id);
	          $Qratings->execute();

	          if ($osC_Database->isError()) {
              $error = true;
            }
	        }
	      
          if ($error === false) {
            $Qcd = $osC_Database->query('delete from :table_categories_description where categories_id = :categories_id');
            $Qcd->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
            $Qcd->bindInt(':categories_id', $c_entry['id']);
            $Qcd->setLogging($_SESSION['module'], $id);
            $Qcd->execute();

            if ( !$osC_Database->isError() ) {
              $Qp2c = $osC_Database->query('delete from :table_products_to_categories where categories_id = :categories_id');
              $Qp2c->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
              $Qp2c->bindInt(':categories_id', $c_entry['id']);
              $Qp2c->setLogging($_SESSION['module'], $id);
              $Qp2c->execute();

              if ( !$osC_Database->isError() ) {
                $osC_Database->commitTransaction();

                osC_Cache::clear('categories');
                osC_Cache::clear('category_tree');
                osC_Cache::clear('also_purchased');
                osC_Cache::clear('sefu-products');
                osC_Cache::clear('new_products');

                if ( !osc_empty($Qimage->value('categories_image')) ) {
                  $Qcheck = $osC_Database->query('select count(*) as total from :table_categories where categories_image = :categories_image');
                  $Qcheck->bindTable(':table_categories', TABLE_CATEGORIES);
                  $Qcheck->bindValue(':categories_image', $Qimage->value('categories_image'));
                  $Qcheck->execute();

                  if ( $Qcheck->numberOfRows() === 0 ) {
                    if (file_exists(realpath('../' . DIR_WS_IMAGES . 'categories/' . $Qimage->value('categories_image')))) {
                      @unlink(realpath('../' . DIR_WS_IMAGES . 'categories/' . $Qimage->value('categories_image')));
                    }
                  }
                }
              } else {
                $osC_Database->rollbackTransaction();
              }
            } else {
              $osC_Database->rollbackTransaction();
            }
          } else {
            $osC_Database->rollbackTransaction();
          }
        }
      
        foreach ($products_delete as $id) {
          osC_Products_Admin::delete($id);
        }

        osC_Cache::clear('categories');
        osC_Cache::clear('category_tree');
        osC_Cache::clear('also_purchased');
        osC_Cache::clear('sefu-products');
        osC_Cache::clear('new_products');

        return true;
      }

      return false;
    }

    function move($id, $new_id) {
      global $osC_Database;

      $category_array = explode('_', $new_id);

      if ( in_array($id, $category_array)) {
        return false;
      }

      $Qupdate = $osC_Database->query('update :table_categories set parent_id = :parent_id, last_modified = now() where categories_id = :categories_id');
      $Qupdate->bindTable(':table_categories', TABLE_CATEGORIES);
      $Qupdate->bindInt(':parent_id', end($category_array));
      $Qupdate->bindInt(':categories_id', $id);
      $Qupdate->setLogging($_SESSION['module'], $id);
      $Qupdate->execute();

      osC_Cache::clear('categories');
      osC_Cache::clear('category_tree');
      osC_Cache::clear('also_purchased');

      return true;
    }
    
    function setStatus($id, $flag, $product_flag) {
      global $osC_Database;
      
      include_once('../includes/classes/category_tree.php');
      $osC_CategoryTree = new osC_CategoryTree(true, true, false);
      
      $error = false;
      
      $subcategories_array = array($id);
      $subcategories_array = $osC_CategoryTree->getChildren($id, $subcategories_array);
      $categories_id = implode(',', $subcategories_array);
      
      $Qstatus = $osC_Database->query('update :table_categories set categories_status = :categories_status where categories_id in (:categories_id)');
      $Qstatus->bindTable(':table_categories', TABLE_CATEGORIES);
      $Qstatus->bindRaw(":categories_id", $categories_id);
      $Qstatus->bindValue(":categories_status", $flag);
      $Qstatus->execute();
      
      if( !$osC_Database->isError() ) {
        if ( ($flag == 0) && ($product_flag == 1) ) {
          $Qupdate = $osC_Database->query('update :table_products set products_status = 0 where products_id in (select products_id from :table_products_to_categories where categories_id in (:categories_id))');
          $Qupdate->bindTable(':table_products', TABLE_PRODUCTS);
          $Qupdate->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
          $Qupdate->bindRaw(":categories_id", $categories_id);
          $Qupdate->execute();
        }
      }
      
      if( !$osC_Database->isError() ) {
        osC_Cache::clear('categories');
        osC_Cache::clear('category_tree');
        osC_Cache::clear('also_purchased');
        osC_Cache::clear('sefu-products');
        osC_Cache::clear('new_products');
        
        return true;
      }
      
      return false;
    }
  }
?>