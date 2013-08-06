<?php
/*
  $Id: category_tree.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2004 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_CategoryTree {
    var $root_category_id = 0,
        $max_level = 0,
        $data = array(),
        $root_start_string = '',
        $root_end_string = '',
        $parent_start_string = '',
        $parent_end_string = '',
        $parent_group_start_string = '<ul>',
        $parent_group_end_string = '</ul>',
        $child_start_string = '<li>',
        $child_end_string = '</li>',
        $breadcrumb_separator = '_',
        $breadcrumb_usage = true,
        $spacer_string = '',
        $spacer_multiplier = 1,
        $follow_cpath = false,
        $cpath_array = array(),
        $cpath_start_string = '',
        $cpath_end_string = '',
        $show_category_product_count = false,
        $category_product_count_start_string = '&nbsp;(',
        $category_product_count_end_string = ')';

    function osC_CategoryTree($load_from_database = true, $load_all_categories = false, $load_from_cache = true) {
      global $osC_Database, $osC_Cache, $osC_Language;

      if (SERVICES_CATEGORY_PATH_CALCULATE_PRODUCT_COUNT == '1') {
        $this->show_category_product_count = true;
      }

      if ($load_from_database === true) {
        $is_cache_loaded = false;
        
        if ($load_from_cache === true) {
          if ($osC_Cache->read('category_tree-' . $osC_Language->getCode(), 720)) {
            $this->data = $osC_Cache->getCache();
            $is_cache_loaded = true;
          } 
        } 
        
        if ($is_cache_loaded === false) {
          $Qcategories = $osC_Database->query('select c.categories_id, c.parent_id, c.categories_image, cd.categories_name, cd.categories_url, cd.categories_page_title, cd.categories_meta_keywords, cd.categories_meta_description from :table_categories c, :table_categories_description cd where c.categories_id = cd.categories_id and cd.language_id = :language_id');
          $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
          $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
          $Qcategories->bindInt(':language_id', $osC_Language->getID());
          
          if ($load_all_categories === false) {
            $Qcategories->appendQuery('and c.categories_status = :categories_status');
            $Qcategories->bindInt(':categories_status', 1);
          }
          
          $Qcategories->appendQuery('order by c.parent_id, c.sort_order, cd.categories_name');
          $Qcategories->execute();

          $this->data = array();

          while ($Qcategories->next()) {
            $this->data[$Qcategories->valueInt('parent_id')][$Qcategories->valueInt('categories_id')] = array('name' => $Qcategories->value('categories_name'), 'url' => $Qcategories->value('categories_url'), 'page_title' => $Qcategories->value('categories_page_title'), 'meta_keywords' => $Qcategories->value('categories_meta_keywords'), 'meta_description' => $Qcategories->value('categories_meta_description'), 'image' => $Qcategories->value('categories_image'), 'count' => 0);
          }

          $Qcategories->freeResult();

          if ($this->show_category_product_count === true) {
            $this->calculateCategoryProductCount();
          }
          
          if ($load_from_cache === true) {
            $osC_Cache->writeBuffer($this->data);
          }
        }
      }
    }

    function setData(&$data_array) {
      if (is_array($data_array)) {
        $this->data = array();

        for ($i=0, $n=sizeof($data_array); $i<$n; $i++) {
          $this->data[$data_array[$i]['parent_id']][$data_array[$i]['categories_id']] = array('name' => $data_array[$i]['categories_name'], 'count' => $data_array[$i]['categories_count']);
        }
      }
    }

    function reset() {
      $this->root_category_id = 0;
      $this->max_level = 0;
      $this->root_start_string = '';
      $this->root_end_string = '';
      $this->parent_start_string = '';
      $this->parent_end_string = '';
      $this->parent_group_start_string = '<ul>';
      $this->parent_group_end_string = '</ul>';
      $this->child_start_string = '<li>';
      $this->child_end_string = '</li>';
      $this->breadcrumb_separator = '_';
      $this->breadcrumb_usage = true;
      $this->spacer_string = '';
      $this->spacer_multiplier = 1;
      $this->follow_cpath = false;
      $this->cpath_array = array();
      $this->cpath_start_string = '';
      $this->cpath_end_string = '';
      $this->show_category_product_count = (SERVICES_CATEGORY_PATH_CALCULATE_PRODUCT_COUNT == '1') ? true : false;
      $this->category_product_count_start_string = '&nbsp;(';
      $this->category_product_count_end_string = ')';
    }

    function buildBranch($parent_id, $level = 0) {
      $result = $this->parent_group_start_string;

      if (isset($this->data[$parent_id])) {
        foreach ($this->data[$parent_id] as $category_id => $category) {
          if ($this->breadcrumb_usage == true) {
            $category_link = $this->buildBreadcrumb($category_id);
          } else {
            $category_link = $category_id;
          }

          $result .= $this->child_start_string;

          if (isset($this->data[$category_id])) {
            $result .= $this->parent_start_string;
          }

          if ($level == 0) {
            $result .= $this->root_start_string;
          }

          if ( ($this->follow_cpath === true) && in_array($category_id, $this->cpath_array) ) {
            $link_title = $this->cpath_start_string . $category['name'] . $this->cpath_end_string;
          } else {
            $link_title = $category['name'];
          }

          $result .= str_repeat($this->spacer_string, $this->spacer_multiplier * $level) . osc_link_object(osc_href_link(FILENAME_DEFAULT, 'cPath=' . $category_link), $link_title);

          if ($this->show_category_product_count === true) {
            $result .= $this->category_product_count_start_string . $category['count'] . $this->category_product_count_end_string;
          }

          if ($level == 0) {
            $result .= $this->root_end_string;
          }

          if (isset($this->data[$category_id])) {
            $result .= $this->parent_end_string;
          }

          $result .= $this->child_end_string;

          if (isset($this->data[$category_id]) && (($this->max_level == '0') || ($this->max_level > $level+1))) {
            if ($this->follow_cpath === true) {
              if (in_array($category_id, $this->cpath_array)) {
                $result .= $this->buildBranch($category_id, $level+1);
              }
            } else {
              $result .= $this->buildBranch($category_id, $level+1);
            }
          }
        }
      }

      $result .= $this->parent_group_end_string;

      return $result;
    }

    function buildBranchArray($parent_id, $level = 0, $result = '') {
      if (empty($result)) {
        $result = array();
      }

      if (isset($this->data[$parent_id])) {
        foreach ($this->data[$parent_id] as $category_id => $category) {
          if ($this->breadcrumb_usage == true) {
            $category_link = $this->buildBreadcrumb($category_id);
          } else {
            $category_link = $category_id;
          }

          $result[] = array('id' => $category_link,
                            'title' => str_repeat($this->spacer_string, $this->spacer_multiplier * $level) . $category['name']);

          if (isset($this->data[$category_id]) && (($this->max_level == '0') || ($this->max_level > $level+1))) {
            if ($this->follow_cpath === true) {
              if (in_array($category_id, $this->cpath_array)) {
                $result = $this->buildBranchArray($category_id, $level+1, $result);
              }
            } else {
              $result = $this->buildBranchArray($category_id, $level+1, $result);
            }
          }
        }
      }

      return $result;
    }    

    function buildBreadcrumb($category_id, $level = 0) {
      $breadcrumb = '';

      foreach ($this->data as $parent => $categories) {
        foreach ($categories as $id => $info) {
          if ($id == $category_id) {
            if ($level < 1) {
              $breadcrumb = $id;
            } else {
              $breadcrumb = $id . $this->breadcrumb_separator . $breadcrumb;
            }

            if ($parent != $this->root_category_id) {
              $breadcrumb = $this->buildBreadcrumb($parent, $level+1) . $breadcrumb;
            }
          }
        }
      }

      return $breadcrumb;
    }

    function buildTree() {
      return $this->buildBranch($this->root_category_id);
    }

    function getTree($parent_id = '') {
      return $this->buildBranchArray((empty($parent_id) ? $this->root_category_id : $parent_id));
    }

    function exists($id) {
      foreach ($this->data as $parent => $categories) {
        foreach ($categories as $category_id => $info) {
          if ($id == $category_id) {
            return true;
          }
        }
      }

      return false;
    }

    function getChildren($category_id, &$array) {
      foreach ($this->data as $parent => $categories) {
        if ($parent == $category_id) {
          foreach ($categories as $id => $info) {
            $array[] = $id;
            $this->getChildren($id, $array);
          }
        }
      }

      return $array;
    }

    function getData($id) {
      foreach ($this->data as $parent => $categories) {
        foreach ($categories as $category_id => $info) {
          if ($id == $category_id) {
            return array('id' => $id,
                         'name' => $info['name'],
                         'page_title' => $info['page_title'],
                         'meta_keywords' => $info['meta_keywords'],
                         'meta_description' => $info['meta_description'],
                         'parent_id' => $parent,
                         'image' => $info['image'],
                         'count' => $info['count']
                        );
          }
        }
      }

      return false;
    }

    function calculateCategoryProductCount() {
      global $osC_Database;

      $totals = array();

      $Qtotals = $osC_Database->query('select p2c.categories_id, count(*) as total from :table_products p, :table_products_to_categories p2c where p2c.products_id = p.products_id and p.products_status = :products_status group by p2c.categories_id');
      $Qtotals->bindTable(':table_products', TABLE_PRODUCTS);
      $Qtotals->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
      $Qtotals->bindInt(':products_status', 1);
      $Qtotals->execute();

      while ($Qtotals->next()) {
        $totals[$Qtotals->valueInt('categories_id')] = $Qtotals->valueInt('total');
      }

      $Qtotals->freeResult();

      foreach ($this->data as $parent => $categories) {
        foreach ($categories as $id => $info) {
          if (isset($totals[$id]) && ($totals[$id] > 0)) {
            $this->data[$parent][$id]['count'] = $totals[$id];
            
            //whether need to calculate the product in the subcategories
            if ( defined('DISPLAY_SUBCATALOGS_PRODUCTS') && ((int)DISPLAY_SUBCATALOGS_PRODUCTS == 1) ) {
              $parent_category = $parent;
              while ($parent_category != $this->root_category_id) {
                foreach ($this->data as $parent_parent => $parent_categories) {
                  foreach ($parent_categories as $parent_category_id => $parent_category_info) {
                    if ($parent_category_id == $parent_category) {
                      $this->data[$parent_parent][$parent_category_id]['count'] += $this->data[$parent][$id]['count'];
  
                      $parent_category = $parent_parent;
                      break 2;
                    }
                  }
                }
              }
            }
          }
        }
      }

      unset($totals);
    }

    function getNumberOfProducts($id) {
      foreach ($this->data as $parent => $categories) {
        foreach ($categories as $category_id => $info) {
          if ($id == $category_id) {
            return $info['count'];
          }
        }
      }

      return false;
    }

    function setRootCategoryID($root_category_id) {
      $this->root_category_id = $root_category_id;
    }

    function setMaximumLevel($max_level) {
      $this->max_level = $max_level;
    }

    function setRootString($root_start_string, $root_end_string) {
      $this->root_start_string = $root_start_string;
      $this->root_end_string = $root_end_string;
    }

    function setParentString($parent_start_string, $parent_end_string) {
      $this->parent_start_string = $parent_start_string;
      $this->parent_end_string = $parent_end_string;
    }

    function setParentGroupString($parent_group_start_string, $parent_group_end_string) {
      $this->parent_group_start_string = $parent_group_start_string;
      $this->parent_group_end_string = $parent_group_end_string;
    }

    function setChildString($child_start_string, $child_end_string) {
      $this->child_start_string = $child_start_string;
      $this->child_end_string = $child_end_string;
    }

    function setBreadcrumbSeparator($breadcrumb_separator) {
      $this->breadcrumb_separator = $breadcrumb_separator;
    }

    function setBreadcrumbUsage($breadcrumb_usage) {
      if ($breadcrumb_usage === true) {
        $this->breadcrumb_usage = true;
      } else {
        $this->breadcrumb_usage = false;
      }
    }

    function setSpacerString($spacer_string, $spacer_multiplier = 2) {
      $this->spacer_string = $spacer_string;
      $this->spacer_multiplier = $spacer_multiplier;
    }

    function setCategoryPath($cpath, $cpath_start_string = '', $cpath_end_string = '') {
      $this->follow_cpath = true;
      $this->cpath_array = explode($this->breadcrumb_separator, $cpath);
      $this->cpath_start_string = $cpath_start_string;
      $this->cpath_end_string = $cpath_end_string;
    }

    function setFollowCategoryPath($follow_cpath) {
      if ($follow_cpath === true) {
        $this->follow_cpath = true;
      } else {
        $this->follow_cpath = false;
      }
    }

    function setCategoryPathString($cpath_start_string, $cpath_end_string) {
      $this->cpath_start_string = $cpath_start_string;
      $this->cpath_end_string = $cpath_end_string;
    }

    function setShowCategoryProductCount($show_category_product_count) {
      if ($show_category_product_count === true) {
        $this->show_category_product_count = true;
      } else {
        $this->show_category_product_count = false;
      }
    }

    function setCategoryProductCountString($category_product_count_start_string, $category_product_count_end_string) {
      $this->category_product_count_start_string = $category_product_count_start_string;
      $this->category_product_count_end_string = $category_product_count_end_string;
    }

    function getParentCategories($category_id, &$categories) {
      foreach ($this->data as $parent => $sub_categories) {
        foreach ($sub_categories as $id => $info) {
          if ( ($id == $category_id) && ($parent != $this->root_category_id) ) {
            $categories[] = $parent;
            $this->getParentCategories($parent, $categories);
          }
        }
      }
    }

    function getFullcPath($categories_id){
      if ( ereg('_', $categories_id) ){
        return $categories_id;
      } else {
        $categories = array();
        $this->getParentCategories($categories_id, $categories);

        $categories = array_reverse($categories);
        $categories[] = $categories_id;
        $cPath = implode('_', $categories);

        return $cPath;
      }
    }

    function getCategoryUrl($cPath) {
      $cPath = $this->getFullcPath($cPath);
      $categories = @explode('_', $cPath);

      if(sizeof($categories) > 1){
        $category_id = end($categories);
        $parent_id = $categories[sizeof($categories)-2];
      }else{
        $category_id = $cPath;
        $parent_id = $this->root_category_id;
      }

      $category_url = $this->data[$parent_id][$category_id]['url'];
      
      return $category_url;
    }
    
    function getCategoryName($cPath){
      $cPath = $this->getFullcPath($cPath);
      $categories = @explode('_', $cPath);

      if(sizeof($categories) > 1){
        $category_id = end($categories);
        $parent_id = $categories[sizeof($categories)-2];
      }else{
        $category_id = $cPath;
        $parent_id = $this->root_category_id;
      }

      $category_name = $this->data[$parent_id][$category_id]['name'];
      return $category_name;
    }
    
    function buildExtJsonTreeArray($parent_id = 0, $tree_node_cls = 'x-tree-node-collapsed') {
      $result = array();
      
      if (isset($this->data[$parent_id])) {
        foreach ($this->data[$parent_id] as $category_id => $category) {
          $data = array('id' => $category_id, 'text' => $category['name']);

          if (isset($this->data[$category_id])) {
            $data['children'] = $this->buildExtJsonTreeArray($category_id, $tree_node_cls);
          } else {
            $data['leaf'] = true;
            $data['cls'] = $tree_node_cls;
          }
          
          $result[] = $data;
        }
      }
      
      return $result;
    }
  }
  
  /**
   * class toC_CategoryTree
   */
  class toC_CategoryTree extends osC_CategoryTree {
    var $leading_string = '';
    
    function setLeadingString($leading_string) {
      $this->leading_string = $leading_string;
    }
    
    function buildBranch($parent_id, $level = 0) {
      $result = $this->parent_group_start_string;
  
      if (isset($this->data[$parent_id])) {
        foreach ($this->data[$parent_id] as $category_id => $category) {
          if ($this->breadcrumb_usage == true) {
            $category_link = $this->buildBreadcrumb($category_id);
          } else {
            $category_link = $category_id;
          }
  
          $result .= $this->child_start_string;
  
          if (isset($this->data[$category_id])) {
            $result .= $this->parent_start_string;
          }
  
          if ($level == 0) {
            $result .= $this->root_start_string;
          }
  
          if ( ($this->follow_cpath === true) && in_array($category_id, $this->cpath_array) ) {
            $link_title = $this->cpath_start_string . $category['name'] . $this->cpath_end_string;
          } else {
            $link_title = $category['name'];
          }
          
          if ($this->show_category_product_count === true) {
            $result .= osc_link_object(osc_href_link(FILENAME_DEFAULT, 'cPath=' . $category_link), str_repeat($this->spacer_string, $this->spacer_multiplier * $level) . $this->leading_string . $link_title . $this->category_product_count_start_string . $category['count'] . $this->category_product_count_end_string);
          } else {
            $result .= osc_link_object(osc_href_link(FILENAME_DEFAULT, 'cPath=' . $category_link), str_repeat($this->spacer_string, $this->spacer_multiplier * $level) . $this->leading_string . $link_title);
          }
  
          if ($level == 0) {
            $result .= $this->root_end_string;
          }
  
          if (isset($this->data[$category_id])) {
            $result .= $this->parent_end_string;
          }
  
          $result .= $this->child_end_string;
  
          if (isset($this->data[$category_id]) && (($this->max_level == '0') || ($this->max_level > $level+1))) {
            if ($this->follow_cpath === true) {
              if (in_array($category_id, $this->cpath_array)) {
                $result .= $this->buildBranch($category_id, $level+1);
              }
            } else {
              $result .= $this->buildBranch($category_id, $level+1);
            }
          }
        }
      }
  
      $result .= $this->parent_group_end_string;
  
      return $result;
    }
    
    function buildCompleteBranch($categories, $level = 0) {
      $result = ($level == 0) ? '<ul id="categoriesTree">' : '<ul>';
      
      if ( is_array($categories) && !empty($categories) ) {
        foreach($categories as $categories_id => $category) {
          if ($this->breadcrumb_usage == true) {
            $category_link = $this->buildBreadcrumb($categories_id);
          } else {
            $category_link = $category_id;
          }
          
          $result .= $this->child_start_string;     
                 
          if ( ($this->follow_cpath === true) && in_array($category_id, $this->cpath_array) ) {
            $link_title = $this->cpath_start_string . $category['name'] . $this->cpath_end_string;
          } else {
            $link_title = $category['name'];
          }
          
          if ($this->show_category_product_count === true) {
            $result .= osc_link_object(osc_href_link(FILENAME_DEFAULT, 'cPath=' . $category_link), $link_title . $this->category_product_count_start_string . $category['count'] . $this->category_product_count_end_string);
          } else {
            $result .= osc_link_object(osc_href_link(FILENAME_DEFAULT, 'cPath=' . $category_link), $this->leading_string . $link_title);
          }       
          
          if(in_array($categories_id, array_keys($this->data))) {
            $result .= $this->buildCompleteBranch($this->data[$categories_id], $level + 1, $categories_id);
          }
          
          $result .= $this->child_end_string;
        }
      }
      
      $result .= '</ul>';
      
      return $result;
    }
    
    function buildCompleteTree() {
      return $this->buildCompleteBranch($this->data[0], 0);
    }
  }  
?>