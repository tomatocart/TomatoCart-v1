<?php
/*
  $Id: recently_visited.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_RecentlyVisited {
    var $visits = array();

/* Class constructor */

    function osC_RecentlyVisited() {
      if (isset($_SESSION['osC_RecentlyVisited_data']) === false) {
        $_SESSION['osC_RecentlyVisited_data'] = array();
      }

      $this->visits =& $_SESSION['osC_RecentlyVisited_data'];
    }

    function initialize() {
      global $osC_Product, $osC_Category, $osC_Search;

      if (SERVICE_RECENTLY_VISITED_SHOW_PRODUCTS == '1') {
        if (isset($osC_Product) && is_a($osC_Product, 'osC_Product')) {
          $this->setProduct($osC_Product->getID());
        }
      }

      if (SERVICE_RECENTLY_VISITED_SHOW_CATEGORIES == '1') {
        if (isset($osC_Category) && is_a($osC_Category, 'osC_Category')) {
          $this->setCategory($osC_Category->getID());
        }
      }

      if (SERVICE_RECENTLY_VISITED_SHOW_SEARCHES == '1') {
        if (isset($osC_Search) && is_a($osC_Search, 'osC_Search')) {
          if ($osC_Search->hasKeywords()) {
            $this->setSearchQuery($osC_Search->getKeywords());
          }
        }
      }
    }

    function setProduct($id) {
      if (isset($this->visits['products'])) {
        foreach ($this->visits['products'] as $key => $value) {
          if ($value['id'] == $id) {
            unset($this->visits['products'][$key]);
            break;
          }
        }

        if (sizeof($this->visits['products']) > (SERVICE_RECENTLY_VISITED_MAX_PRODUCTS * 2)) {
          array_pop($this->visits['products']);
        }
      } else {
        $this->visits['products'] = array();
      }

      array_unshift($this->visits['products'], array('id' => $id));
    }

    function setCategory($id) {
      if (isset($this->visits['categories'])) {
        foreach ($this->visits['categories'] as $key => $value) {
          if ($value['id'] == $id) {
            unset($this->visits['categories'][$key]);
            break;
          }
        }

        if (sizeof($this->visits['categories']) > (SERVICE_RECENTLY_VISITED_MAX_CATEGORIES * 2)) {
          array_pop($this->visits['categories']);
        }
      } else {
        $this->visits['categories'] = array();
      }

      array_unshift($this->visits['categories'], array('id' => $id));
    }

    function setSearchQuery($keywords) {
      global $osC_Search;

      if (isset($this->visits['searches'])) {
        foreach ($this->visits['searches'] as $key => $value) {
          if ($value['keywords'] == $keywords) {
            unset($this->visits['searches'][$key]);
            break;
          }
        }

        if (sizeof($this->visits['searches']) > (SERVICE_RECENTLY_VISITED_MAX_SEARCHES * 2)) {
          array_pop($this->visits['searches']);
        }
      } else {
        $this->visits['searches'] = array();
      }

      array_unshift($this->visits['searches'], array('keywords' => $keywords,
                                                     'results' => $osC_Search->getNumberOfResults()
                                                    ));
    }

    function hasHistory() {
      if ($this->hasProducts() || $this->hasCategories() || $this->hasSearches()) {
        return true;
      }

      return false;
    }

    function hasProducts() {
      return ( (SERVICE_RECENTLY_VISITED_SHOW_PRODUCTS == '1') && isset($this->visits['products']) && !empty($this->visits['products']) );
    }

    function getProducts() {
      $history = array();

      if (isset($this->visits['products']) && (empty($this->visits['products']) === false)) {
        $counter = 0;

        foreach ($this->visits['products'] as $k => $v) {
          $counter++;

          $osC_Product = new osC_Product($v['id']);
          $osC_Category = new osC_Category($osC_Product->getCategoryID());

          if ($osC_Product->isValid() === true) {
            $history[] = array('name' => $osC_Product->getTitle(),
                               'id' => $osC_Product->getID(),
                               'keyword' => $osC_Product->getKeyword(),
                               'price' => (SERVICE_RECENTLY_VISITED_SHOW_PRODUCT_PRICES == '1') ? $osC_Product->getPriceFormated(true) : '',
                               'image' => $osC_Product->getImage(),
                               'category_name' =>  $osC_Category->getTitle(),
                               'category_path' => $osC_Category->getPath()
                              );
          }

          if ($counter == SERVICE_RECENTLY_VISITED_MAX_PRODUCTS) {
            break;
          }
        }
      }

      return $history;
    }

    function hasCategories() {
      return ( (SERVICE_RECENTLY_VISITED_SHOW_CATEGORIES == '1') && isset($this->visits['categories']) && !empty($this->visits['categories']) );
    }

    function getCategories() {
      $history = array();

      if (isset($this->visits['categories']) && (empty($this->visits['categories']) === false)) {
        $counter = 0;

        foreach ($this->visits['categories'] as $k => $v) {
          $counter++;

          $osC_Category = new osC_Category($v['id']);

          if ($osC_Category->hasParent()) {
            $osC_CategoryParent = new osC_Category($osC_Category->getParent());
          }

          $history[]  = array('id' => $osC_Category->getID(),
                              'name' => $osC_Category->getTitle(),
                              'path' => $osC_Category->getPath(),
                              'image' => $osC_Category->getImage(),
                              'parent_name' => ($osC_Category->hasParent()) ? $osC_CategoryParent->getTitle() : '',
                              'parent_id' => ($osC_Category->hasParent()) ? $osC_CategoryParent->getID() : ''
                             );

          if ($counter == SERVICE_RECENTLY_VISITED_MAX_CATEGORIES) {
            break;
          }
        }
      }

      return $history;
    }

    function hasSearches() {
      return ( (SERVICE_RECENTLY_VISITED_SHOW_SEARCHES == '1') && isset($this->visits['searches']) && !empty($this->visits['searches']) );
    }

    function getSearches() {
      $history = array();

      if (isset($this->visits['searches']) && (empty($this->visits['searches']) === false)) {
        $counter = 0;

        foreach ($this->visits['searches'] as $k => $v) {
          $counter++;

          $history[]  = array('keywords' => $this->visits['searches'][$k]['keywords'],
                              'results' => $this->visits['searches'][$k]['results']
                             );

          if ($counter == SERVICE_RECENTLY_VISITED_MAX_SEARCHES) {
            break;
          }
        }
      }

      return $history;
    }
  }
?>
